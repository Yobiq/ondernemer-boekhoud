<?php

namespace App\Services\Belastingdienst;

use App\Models\VatPeriod;
use App\Services\AuditLogger;
use App\Services\VatCalculatorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class XmlExportService
{
    protected VatCalculatorService $vatCalculator;
    protected AuditLogger $auditLogger;
    
    public function __construct(VatCalculatorService $vatCalculator, AuditLogger $auditLogger)
    {
        $this->vatCalculator = $vatCalculator;
        $this->auditLogger = $auditLogger;
    }
    
    /**
     * Generate Belastingdienst-compliant XML for BTW aangifte
     */
    public function generateXml(VatPeriod $period): string
    {
        $client = $period->client;
        $documents = $period->documents;
        
        // Calculate totals per rubriek
        $totals = $this->vatCalculator->calculatePeriodTotals($period);
        
        // Create XML document
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Root element: BTW-aangifte
        $root = $xml->createElement('btw-aangifte');
        $root->setAttribute('xmlns', 'http://www.belastingdienst.nl/btw-aangifte');
        $root->setAttribute('versie', '1.0');
        $xml->appendChild($root);
        
        // Aangifte informatie
        $aangifteInfo = $xml->createElement('aangifte-info');
        $aangifteInfo->appendChild($xml->createElement('periode', $period->period_string));
        $aangifteInfo->appendChild($xml->createElement('jaar', $period->year));
        if ($period->quarter) {
            $aangifteInfo->appendChild($xml->createElement('kwartaal', $period->quarter));
        }
        if ($period->month) {
            $aangifteInfo->appendChild($xml->createElement('maand', $period->month));
        }
        $aangifteInfo->appendChild($xml->createElement('status', $period->status));
        $root->appendChild($aangifteInfo);
        
        // Klant informatie
        $klantInfo = $xml->createElement('klant-info');
        $klantInfo->appendChild($xml->createElement('naam', htmlspecialchars($client->name ?? '')));
        $klantInfo->appendChild($xml->createElement('kvk-nummer', $client->kvk_number ?? ''));
        $klantInfo->appendChild($xml->createElement('btw-nummer', $client->btw_number ?? ''));
        $root->appendChild($klantInfo);
        
        // Rubrieken
        $rubrieken = $xml->createElement('rubrieken');
        
        $rubriekMapping = [
            '1a' => 'Leveringen/diensten belast met hoog tarief',
            '1b' => 'Leveringen/diensten belast met laag tarief',
            '1c' => 'Overige tarieven',
            '2a' => 'Verleggingsregelingen binnenland',
            '3a' => 'Leveringen naar/in het buitenland',
            '3b' => 'Diensten naar/in het buitenland',
            '4a' => 'Voorbelasting',
            '5b' => 'Totaal verschuldigde / te ontvangen BTW',
        ];
        
        foreach ($rubriekMapping as $rubriekCode => $rubriekOmschrijving) {
            $rubriek = $xml->createElement('rubriek');
            $rubriek->setAttribute('code', $rubriekCode);
            $rubriek->setAttribute('omschrijving', $rubriekOmschrijving);
            
            $data = $totals[$rubriekCode] ?? [
                'amount' => 0,
                'vat' => 0,
                'count' => 0,
            ];
            
            $rubriek->appendChild($xml->createElement('grondslag', number_format($data['amount'], 2, '.', '')));
            $rubriek->appendChild($xml->createElement('btw-bedrag', number_format($data['vat'], 2, '.', '')));
            $rubriek->appendChild($xml->createElement('aantal-documenten', $data['count']));
            
            $rubrieken->appendChild($rubriek);
        }
        
        $root->appendChild($rubrieken);
        
        // Documenten overzicht
        $documenten = $xml->createElement('documenten');
        foreach ($documents as $document) {
            $doc = $xml->createElement('document');
            $doc->setAttribute('id', $document->id);
            $doc->appendChild($xml->createElement('datum', $document->document_date?->format('Y-m-d') ?? ''));
            $doc->appendChild($xml->createElement('leverancier', htmlspecialchars($document->supplier_name ?? '')));
            $doc->appendChild($xml->createElement('bedrag-excl', number_format($document->amount_excl ?? 0, 2, '.', '')));
            $doc->appendChild($xml->createElement('btw-bedrag', number_format($document->amount_vat ?? 0, 2, '.', '')));
            $doc->appendChild($xml->createElement('btw-code', $document->vat_code ?? ''));
            $doc->appendChild($xml->createElement('rubriek', $document->vat_rubriek ?? ''));
            $documenten->appendChild($doc);
        }
        $root->appendChild($documenten);
        
        // Metadata
        $metadata = $xml->createElement('metadata');
        $metadata->appendChild($xml->createElement('aangemaakt-op', now()->format('Y-m-d H:i:s')));
        $metadata->appendChild($xml->createElement('aangemaakt-door', auth()->user()->name ?? 'Systeem'));
        $metadata->appendChild($xml->createElement('periode-start', $period->period_start->format('Y-m-d')));
        $metadata->appendChild($xml->createElement('periode-eind', $period->period_end->format('Y-m-d')));
        $root->appendChild($metadata);
        
        return $xml->saveXML();
    }
    
    /**
     * Validate XML structure
     */
    public function validateXml(string $xml): bool
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            
            // Basic XML validation
            if (!$dom->schemaValidateSource($this->getXmlSchema())) {
                return false;
            }
            
            // Check required elements
            $requiredElements = ['aangifte-info', 'klant-info', 'rubrieken'];
            foreach ($requiredElements as $element) {
                if ($dom->getElementsByTagName($element)->length === 0) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Download XML file
     */
    public function downloadXml(VatPeriod $period): \Symfony\Component\HttpFoundation\Response
    {
        $xml = $this->generateXml($period);
        
        // Store in audit log
        $this->auditLogger->log('updated', $period, auth()->user(), null, [
            'xml_size' => strlen($xml),
            'exported_at' => now()->toIso8601String(),
        ], [
            'action_type' => 'xml_export', // Store actual action in metadata
        ]);
        
        $filename = sprintf(
            'btw-aangifte-%s-%s-%s.xml',
            $period->client->name ?? 'klant',
            $period->year,
            $period->quarter ? "Q{$period->quarter}" : ($period->month ?? 'custom')
        );
        
        // Clean filename
        $filename = preg_replace('/[^a-z0-9\-_]/i', '_', $filename);
        
        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Get XML schema (XSD) for validation
     */
    public function getXmlSchema(): string
    {
        // Simplified XSD schema for BTW aangifte
        // In production, this should be the official Belastingdienst schema
        return '<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" 
           targetNamespace="http://www.belastingdienst.nl/btw-aangifte"
           elementFormDefault="qualified">
    <xs:element name="btw-aangifte">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="aangifte-info" type="AangifteInfoType"/>
                <xs:element name="klant-info" type="KlantInfoType"/>
                <xs:element name="rubrieken" type="RubriekenType"/>
                <xs:element name="documenten" type="DocumentenType" minOccurs="0"/>
                <xs:element name="metadata" type="MetadataType"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    
    <xs:complexType name="AangifteInfoType">
        <xs:sequence>
            <xs:element name="periode" type="xs:string"/>
            <xs:element name="jaar" type="xs:integer"/>
            <xs:element name="kwartaal" type="xs:integer" minOccurs="0"/>
            <xs:element name="maand" type="xs:integer" minOccurs="0"/>
            <xs:element name="status" type="xs:string"/>
        </xs:sequence>
    </xs:complexType>
    
    <xs:complexType name="KlantInfoType">
        <xs:sequence>
            <xs:element name="naam" type="xs:string"/>
            <xs:element name="kvk-nummer" type="xs:string" minOccurs="0"/>
            <xs:element name="btw-nummer" type="xs:string" minOccurs="0"/>
        </xs:sequence>
    </xs:complexType>
    
    <xs:complexType name="RubriekenType">
        <xs:sequence>
            <xs:element name="rubriek" type="RubriekType" maxOccurs="unbounded"/>
        </xs:sequence>
    </xs:complexType>
    
    <xs:complexType name="RubriekType">
        <xs:sequence>
            <xs:element name="grondslag" type="xs:decimal"/>
            <xs:element name="btw-bedrag" type="xs:decimal"/>
            <xs:element name="aantal-documenten" type="xs:integer"/>
        </xs:sequence>
        <xs:attribute name="code" type="xs:string" use="required"/>
        <xs:attribute name="omschrijving" type="xs:string" use="required"/>
    </xs:complexType>
    
    <xs:complexType name="DocumentenType">
        <xs:sequence>
            <xs:element name="document" type="DocumentType" maxOccurs="unbounded"/>
        </xs:sequence>
    </xs:complexType>
    
    <xs:complexType name="DocumentType">
        <xs:sequence>
            <xs:element name="datum" type="xs:date"/>
            <xs:element name="leverancier" type="xs:string"/>
            <xs:element name="bedrag-excl" type="xs:decimal"/>
            <xs:element name="btw-bedrag" type="xs:decimal"/>
            <xs:element name="btw-code" type="xs:string"/>
            <xs:element name="rubriek" type="xs:string"/>
        </xs:sequence>
        <xs:attribute name="id" type="xs:integer" use="required"/>
    </xs:complexType>
    
    <xs:complexType name="MetadataType">
        <xs:sequence>
            <xs:element name="aangemaakt-op" type="xs:dateTime"/>
            <xs:element name="aangemaakt-door" type="xs:string"/>
            <xs:element name="periode-start" type="xs:date"/>
            <xs:element name="periode-eind" type="xs:date"/>
        </xs:sequence>
    </xs:complexType>
</xs:schema>';
    }
}

