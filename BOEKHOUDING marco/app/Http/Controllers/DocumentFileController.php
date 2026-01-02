<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Dompdf\Dompdf;
use Dompdf\Options;

class DocumentFileController extends Controller
{
    use AuthorizesRequests;
    /**
     * Serve a document file (preview/download)
     * 
     * SECURITY FIX: Added proper authorization check using policies
     */
    public function serve(Request $request, Document $document)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('filament.client.auth.login');
        }

        // SECURITY FIX: Use authorization policy
        $this->authorize('view', $document);

        // Check if file exists
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        // SECURITY FIX: Use signed temporary URL for better security
        // Generate temporary URL that expires in 15 minutes
        try {
            $url = Storage::disk('local')->temporaryUrl(
                $document->file_path,
                now()->addMinutes(15)
            );
            
            // Redirect to signed URL
            return redirect($url);
        } catch (\Exception $e) {
            // Fallback to direct file serving if temporaryUrl not supported
            // (e.g., local disk without S3 driver)
            $filePath = Storage::disk('local')->path($document->file_path);
            $mimeType = Storage::disk('local')->mimeType($document->file_path);

            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => $request->get('download') ? 'attachment; filename="' . $document->original_filename . '"' : 'inline',
            ]);
        }
    }

    /**
     * Download a document file
     * 
     * SECURITY FIX: Added proper authorization check using policies
     */
    public function download(Document $document)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('filament.client.auth.login');
        }

        // SECURITY FIX: Use authorization policy
        $this->authorize('view', $document);

        // Check if file exists
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        // Return download response
        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_filename
        );
    }

    /**
     * Download invoice as PDF/HTML
     * 
     * SECURITY FIX: Added proper authorization check using policies
     */
    public function downloadInvoicePdf(Document $document)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('filament.client.auth.login');
        }

        // SECURITY FIX: Use authorization policy
        $this->authorize('view', $document);

        // Verify it's a sales invoice
        if ($document->document_type !== 'sales_invoice') {
            abort(404, 'Document is not an invoice');
        }

        $invoiceData = $document->ocr_data ?? [];
        
        // Get logo from client profile (preferred) or from invoice data (fallback)
        $logoPath = null;
        if ($document->client && $document->client->logo) {
            $logoPath = $document->client->logo;
        } elseif (isset($invoiceData['sender_logo']) && $invoiceData['sender_logo']) {
            $logoPath = $invoiceData['sender_logo'];
        }
        
        // Handle logo - convert storage path to base64 for PDF
        if ($logoPath) {
            try {
                $logoContent = null;
                
                // Handle array (Filament FileUpload returns array sometimes)
                if (is_array($logoPath)) {
                    $logoPath = $logoPath[0] ?? null;
                }
                
                if ($logoPath) {
                    // Check if it's a storage path (from Filament FileUpload)
                    if (!filter_var($logoPath, FILTER_VALIDATE_URL)) {
                        // It's a storage path - read from public disk
                        if (Storage::disk('public')->exists($logoPath)) {
                            $logoContent = Storage::disk('public')->get($logoPath);
                        } else {
                            // Try with full path
                            $fullPath = 'client-logos/' . basename($logoPath);
                            if (Storage::disk('public')->exists($fullPath)) {
                                $logoContent = Storage::disk('public')->get($fullPath);
                            }
                        }
                    } else {
                        // It's a URL - try to fetch it
                        $logoContent = @file_get_contents($logoPath);
                    }
                    
                    if ($logoContent) {
                        $logoBase64 = base64_encode($logoContent);
                        // Detect mime type from content or extension
                        $mimeType = 'image/png';
                        if (str_contains($logoPath, '.jpg') || str_contains($logoPath, '.jpeg')) {
                            $mimeType = 'image/jpeg';
                        } elseif (str_contains($logoPath, '.png')) {
                            $mimeType = 'image/png';
                        } elseif (str_contains($logoPath, '.svg')) {
                            $mimeType = 'image/svg+xml';
                        } elseif (str_contains($logoPath, '.gif')) {
                            $mimeType = 'image/gif';
                        }
                        $invoiceData['sender_logo'] = 'data:' . $mimeType . ';base64,' . $logoBase64;
                    } else {
                        unset($invoiceData['sender_logo']);
                    }
                }
            } catch (\Exception $e) {
                // If logo loading fails, remove it
                \Log::warning('Failed to load logo for PDF: ' . $e->getMessage(), [
                    'logo_path' => $invoiceData['sender_logo'] ?? null,
                    'exception' => $e->getMessage()
                ]);
                unset($invoiceData['sender_logo']);
            }
        }
        
        // Generate PDF HTML
        $html = view('filament.client.components.invoice-pdf', [
            'document' => $document,
            'data' => $invoiceData,
        ])->render();
        
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isPhpEnabled', true);
        
        // Create Dompdf instance
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->set_option('enable-css-float', true);
        $dompdf->set_option('enable-html5-parser', true);
        $dompdf->render();
        
        // Generate filename
        $invoiceNumber = $invoiceData['invoice_number'] ?? 'INV-' . $document->id;
        $filename = "Factuur_{$invoiceNumber}.pdf";
        
        // Return PDF download
        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

