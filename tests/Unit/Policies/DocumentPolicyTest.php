<?php

namespace Tests\Unit\Policies;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Policies\DocumentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected DocumentPolicy $policy;
    protected User $adminUser;
    protected User $clientUser;
    protected Client $client;
    protected Document $document;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new DocumentPolicy();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'boekhouder']);
        
        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'client_id' => null,
        ]);
        $this->adminUser->assignRole('admin');
        
        // Create client
        $this->client = Client::factory()->create();
        
        // Create client user
        $this->clientUser = User::factory()->create([
            'email' => 'client@test.com',
            'client_id' => $this->client->id,
        ]);
        
        // Create document for client
        $this->document = Document::factory()->create([
            'client_id' => $this->client->id,
        ]);
    }

    #[Test]
    public function admin_can_view_any_documents(): void
    {
        $this->assertTrue($this->policy->viewAny($this->adminUser));
    }

    #[Test]
    public function client_can_view_any_documents(): void
    {
        $this->assertTrue($this->policy->viewAny($this->clientUser));
    }

    #[Test]
    public function admin_can_view_any_document(): void
    {
        $this->assertTrue($this->policy->view($this->adminUser, $this->document));
    }

    #[Test]
    public function client_can_view_own_document(): void
    {
        $this->assertTrue($this->policy->view($this->clientUser, $this->document));
    }

    #[Test]
    public function client_cannot_view_other_client_document(): void
    {
        $otherClient = Client::factory()->create();
        $otherDocument = Document::factory()->create([
            'client_id' => $otherClient->id,
        ]);

        $this->assertFalse($this->policy->view($this->clientUser, $otherDocument));
    }

    #[Test]
    public function admin_can_create_documents(): void
    {
        $this->assertTrue($this->policy->create($this->adminUser));
    }

    #[Test]
    public function client_can_create_documents(): void
    {
        $this->assertTrue($this->policy->create($this->clientUser));
    }

    #[Test]
    public function admin_can_update_documents(): void
    {
        $this->assertTrue($this->policy->update($this->adminUser, $this->document));
    }

    #[Test]
    public function client_cannot_update_documents(): void
    {
        $this->assertFalse($this->policy->update($this->clientUser, $this->document));
    }

    #[Test]
    public function admin_can_delete_documents(): void
    {
        $this->assertTrue($this->policy->delete($this->adminUser, $this->document));
    }

    #[Test]
    public function client_cannot_delete_documents(): void
    {
        $this->assertFalse($this->policy->delete($this->clientUser, $this->document));
    }

    #[Test]
    public function boekhouder_can_view_documents(): void
    {
        $boekhouder = User::factory()->create(['client_id' => null]);
        $boekhouder->assignRole('boekhouder');
        
        $this->assertTrue($this->policy->view($boekhouder, $this->document));
    }

    #[Test]
    public function admin_can_approve_documents(): void
    {
        $this->assertTrue($this->policy->approve($this->adminUser, $this->document));
    }

    #[Test]
    public function client_cannot_approve_documents(): void
    {
        $this->assertFalse($this->policy->approve($this->clientUser, $this->document));
    }
}

