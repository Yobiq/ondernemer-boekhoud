<?php

namespace Tests\Feature\ClientPortal;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $clientUser;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = Client::factory()->create();
        $this->clientUser = User::factory()->create([
            'client_id' => $this->client->id,
        ]);
    }

    public function test_all_pages_require_authentication(): void
    {
        $pages = [
            '/klanten',
            '/klanten/mijn-documenten',
            '/klanten/smart-upload',
            '/klanten/factuur-maken',
            '/klanten/rapporten',
            '/klanten/profile',
            '/klanten/hulp',
            '/klanten/onboarding',
        ];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertRedirect('/klanten/login');
        }
    }

    public function test_authenticated_client_can_access_all_pages(): void
    {
        $pages = [
            '/klanten' => 'Dashboard',
            '/klanten/mijn-documenten' => 'Mijn Documenten',
            '/klanten/smart-upload' => 'Slim Document Uploaden',
            '/klanten/factuur-maken' => 'Factuur Maken',
            '/klanten/rapporten' => 'Rapporten',
            '/klanten/profile' => 'Mijn Profiel',
            '/klanten/hulp' => 'Hulp & FAQ',
            '/klanten/onboarding' => 'Handleiding',
        ];

        foreach ($pages as $url => $expectedText) {
            $response = $this->actingAs($this->clientUser)->get($url);
            $response->assertStatus(200);
            $response->assertSee($expectedText, false);
        }
    }

    public function test_login_page_is_accessible_without_authentication(): void
    {
        $response = $this->get('/klanten/login');

        $response->assertStatus(200);
        $response->assertSee('MARCOFIC', false);
    }
}





