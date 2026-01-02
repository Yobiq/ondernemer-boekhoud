<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Document;
use App\Models\LedgerAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_client(): void
    {
        $client = Client::factory()->create();
        $document = Document::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Client::class, $document->client);
        $this->assertEquals($client->id, $document->client->id);
    }

    #[Test]
    public function it_belongs_to_a_ledger_account(): void
    {
        $account = LedgerAccount::factory()->create();
        $document = Document::factory()->create(['ledger_account_id' => $account->id]);

        $this->assertInstanceOf(LedgerAccount::class, $document->ledgerAccount);
        $this->assertEquals($account->id, $document->ledgerAccount->id);
    }

    #[Test]
    public function it_casts_ocr_data_to_array(): void
    {
        $ocrData = [
            'supplier' => ['name' => 'Test Supplier'],
            'amounts' => ['excl' => 100.00],
        ];

        $document = Document::factory()->create(['ocr_data' => $ocrData]);

        $this->assertIsArray($document->ocr_data);
        $this->assertEquals('Test Supplier', $document->ocr_data['supplier']['name']);
    }

    #[Test]
    public function it_casts_amounts_to_decimal(): void
    {
        $document = Document::factory()->create([
            'amount_excl' => 100.00,
            'amount_vat' => 21.00,
            'amount_incl' => 121.00,
        ]);

        $this->assertEquals('100.00', $document->amount_excl);
        $this->assertEquals('21.00', $document->amount_vat);
        $this->assertEquals('121.00', $document->amount_incl);
    }

    #[Test]
    public function it_casts_confidence_score_to_decimal(): void
    {
        $document = Document::factory()->create(['confidence_score' => 95.75]);

        $this->assertEquals('95.75', $document->confidence_score);
    }

    #[Test]
    public function it_casts_document_date_to_date(): void
    {
        $date = '2024-12-18';
        $document = Document::factory()->create(['document_date' => $date]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $document->document_date);
        $this->assertEquals($date, $document->document_date->format('Y-m-d'));
    }

    #[Test]
    public function it_checks_if_document_is_locked(): void
    {
        $approvedDoc = Document::factory()->create(['status' => 'approved']);
        $archivedDoc = Document::factory()->create(['status' => 'archived']);
        $pendingDoc = Document::factory()->create(['status' => 'pending']);

        $this->assertTrue($approvedDoc->isLocked());
        $this->assertTrue($archivedDoc->isLocked());
        $this->assertFalse($pendingDoc->isLocked());
    }

    #[Test]
    public function it_scopes_pending_documents(): void
    {
        Document::factory()->create(['status' => 'pending']);
        Document::factory()->create(['status' => 'approved']);
        Document::factory()->create(['status' => 'pending']);

        $pendingCount = Document::pending()->count();

        $this->assertEquals(2, $pendingCount);
    }

    #[Test]
    public function it_scopes_review_required_documents(): void
    {
        Document::factory()->create(['status' => 'review_required']);
        Document::factory()->create(['status' => 'approved']);
        Document::factory()->create(['status' => 'review_required']);

        $reviewCount = Document::reviewRequired()->count();

        $this->assertEquals(2, $reviewCount);
    }

    #[Test]
    public function it_scopes_approved_documents(): void
    {
        Document::factory()->create(['status' => 'approved']);
        Document::factory()->create(['status' => 'pending']);
        Document::factory()->create(['status' => 'approved']);

        $approvedCount = Document::approved()->count();

        $this->assertEquals(2, $approvedCount);
    }
}

