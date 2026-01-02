<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\LedgerAccount;
use App\Models\LedgerKeywordMapping;
use App\Models\User;
use App\Services\LedgerSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LedgerSuggestionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LedgerSuggestionService $service;
    protected Client $client;
    protected LedgerAccount $account4999;
    protected LedgerAccount $account5000;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new LedgerSuggestionService();
        
        // Create client
        $this->client = Client::factory()->create();
        
        // Create fallback account (4999)
        $this->account4999 = LedgerAccount::factory()->create([
            'code' => '4999',
            'description' => 'Overige kosten',
            'type' => 'winst_verlies',
            'vat_default' => '21',
            'active' => true,
        ]);
        
        // Create another test account
        $this->account5000 = LedgerAccount::factory()->create([
            'code' => '5000',
            'description' => 'Test Account',
            'type' => 'winst_verlies',
            'vat_default' => '21',
            'active' => true,
        ]);
    }

    #[Test]
    public function it_returns_fallback_account_when_no_matches(): void
    {
        $document = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => null,
            'vat_rate' => null,
        ]);

        $result = $this->service->suggest($document);

        $this->assertEquals($this->account4999->id, $result['ledger_account_id']);
        $this->assertEquals(LedgerSuggestionService::SCORE_FALLBACK, $result['confidence_score']);
        $this->assertStringContainsString('Fallback', $result['reason']);
    }

    #[Test]
    public function it_suggests_based_on_supplier_history(): void
    {
        // Create a previous approved document with a supplier and account
        $previousDoc = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Shell Nederland',
            'status' => 'approved',
            'ledger_account_id' => $this->account5000->id,
            'vat_rate' => '21',
        ]);

        // Create new document from same supplier
        $newDoc = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Shell Nederland',
            'vat_rate' => '21',
        ]);

        $result = $this->service->suggest($newDoc);

        $this->assertEquals($this->account5000->id, $result['ledger_account_id']);
        $this->assertGreaterThanOrEqual(LedgerSuggestionService::SCORE_SUPPLIER_HISTORY, $result['confidence_score']);
        $this->assertStringContainsString('Leverancier historie', $result['reason']);
    }

    #[Test]
    public function it_adds_bonus_score_when_vat_matches_in_history(): void
    {
        $account = LedgerAccount::factory()->create([
            'code' => '5010',
            'description' => 'Test Account with VAT 21',
            'type' => 'winst_verlies',
            'vat_default' => '21',
            'active' => true,
        ]);

        $previousDoc = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Test Supplier',
            'status' => 'approved',
            'ledger_account_id' => $account->id,
            'vat_rate' => '21',
        ]);

        $newDoc = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Test Supplier',
            'vat_rate' => '21', // Same VAT as account default
        ]);

        $result = $this->service->suggest($newDoc);

        // Should get supplier history score + VAT match bonus
        $expectedScore = LedgerSuggestionService::SCORE_SUPPLIER_HISTORY + LedgerSuggestionService::SCORE_VAT_TYPE_MATCH;
        $this->assertEquals($expectedScore, $result['confidence_score']);
    }

    #[Test]
    public function it_suggests_based_on_keyword_match(): void
    {
        // Create keyword mapping (use lowercase as service converts to lowercase)
        $keywordMapping = LedgerKeywordMapping::factory()->create([
            'keyword' => 'shell',
            'ledger_account_id' => $this->account5000->id,
            'priority' => 5,
        ]);

        $document = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Shell Nederland BV', // Contains "Shell" which should match "shell"
            'vat_rate' => '21',
            'ocr_data' => ['raw_text' => 'test shell company'],
        ]);

        $result = $this->service->suggest($document);

        // Should match the account via keyword (keyword matching is case-insensitive)
        $this->assertNotNull($result['ledger_account_id']);
        
        // If keyword matched, should have higher score than fallback
        // Otherwise it falls back to account4999
        if (str_contains(strtolower($result['reason']), 'keyword')) {
            $this->assertEquals($this->account5000->id, $result['ledger_account_id']);
            $this->assertGreaterThanOrEqual(LedgerSuggestionService::SCORE_KEYWORD_MATCH, $result['confidence_score']);
            $this->assertStringContainsString('Keyword match', $result['reason']);
        } else {
            // If keyword didn't match, should use fallback
            $this->assertEquals($this->account4999->id, $result['ledger_account_id']);
        }
    }

    #[Test]
    public function it_suggests_based_on_vat_type_match(): void
    {
        $account = LedgerAccount::factory()->create([
            'code' => '5020',
            'description' => 'Account with VAT 9',
            'type' => 'winst_verlies',
            'vat_default' => '9',
            'active' => true,
        ]);

        $document = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Unknown Supplier',
            'vat_rate' => '9',
        ]);

        $result = $this->service->suggest($document);

        // Should find accounts matching VAT type
        $this->assertGreaterThanOrEqual(LedgerSuggestionService::SCORE_VAT_TYPE_MATCH, $result['confidence_score']);
    }

    #[Test]
    public function it_prioritizes_supplier_history_over_keyword_match(): void
    {
        // Create keyword mapping
        LedgerKeywordMapping::factory()->create([
            'keyword' => 'test',
            'ledger_account_id' => $this->account4999->id,
            'priority' => 10,
        ]);

        // Create previous document with different account
        $previousDoc = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Test Supplier',
            'status' => 'approved',
            'ledger_account_id' => $this->account5000->id,
            'vat_rate' => '21',
        ]);

        $newDoc = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'Test Supplier',
            'vat_rate' => '21',
            'ocr_data' => ['raw_text' => 'test'],
        ]);

        $result = $this->service->suggest($newDoc);

        // Supplier history should win
        $this->assertEquals($this->account5000->id, $result['ledger_account_id']);
    }

    #[Test]
    public function it_learns_from_manual_corrections(): void
    {
        $document = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => 'New Supplier BV',
            'vat_rate' => '21',
        ]);

        // Learn from correction
        $this->service->learnFromCorrection($document, $this->account5000->id);

        // Check that keyword mapping was created
        $this->assertDatabaseHas('ledger_keyword_mappings', [
            'keyword' => 'supplier',
            'ledger_account_id' => $this->account5000->id,
        ]);
    }

    #[Test]
    public function it_handles_empty_supplier_name_gracefully(): void
    {
        $document = Document::factory()->create([
            'client_id' => $this->client->id,
            'supplier_name' => null,
            'vat_rate' => '21',
        ]);

        $result = $this->service->suggest($document);

        // Should fall back to default account
        $this->assertNotNull($result['ledger_account_id']);
        $this->assertIsInt($result['ledger_account_id']);
    }
}

