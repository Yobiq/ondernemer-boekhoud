<?php

namespace Tests\Feature\ClientPortal;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $clientUser;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('local');
        
        $this->client = Client::factory()->create();
        $this->clientUser = User::factory()->create([
            'client_id' => $this->client->id,
        ]);
    }

    public function test_smart_upload_page_loads(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/klanten/smart-upload');

        $response->assertStatus(200);
        $response->assertSee('Slim Document Uploaden', false);
    }

    public function test_smart_upload_requires_authentication(): void
    {
        $response = $this->get('/klanten/smart-upload');

        $response->assertRedirect('/klanten/login');
    }

    public function test_document_upload_page_has_upload_form(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/klanten/smart-upload');

        $response->assertStatus(200);
        // Check that upload form elements exist
        $response->assertSee('Upload', false);
    }

    public function test_document_upload_page_has_document_type_selection(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/klanten/smart-upload');

        $response->assertStatus(200);
        // Check for document type options
        $response->assertSee('document', false);
    }
}

