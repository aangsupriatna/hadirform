<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\Organization;
use App\Models\Response;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $userOrgA;
    protected User $userOrgB;
    protected Event $eventOrgB;
    protected Form $formOrgB;
    protected Response $responseOrgB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgA = Organization::create(['name' => 'Org A', 'slug' => 'org-a']);
        $this->orgB = Organization::create(['name' => 'Org B', 'slug' => 'org-b']);

        $this->userOrgA = User::create([
            'organization_id' => $this->orgA->id,
            'name' => 'User Org A',
            'email' => 'user_a@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->userOrgB = User::create([
            'organization_id' => $this->orgB->id,
            'name' => 'User Org B',
            'email' => 'user_b@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->eventOrgB = Event::create([
            'organization_id' => $this->orgB->id,
            'title' => 'Event Org B',
            'status' => 'published',
            'created_by' => $this->userOrgB->id,
        ]);

        $this->formOrgB = Form::create([
            'organization_id' => $this->orgB->id,
            'event_id' => $this->eventOrgB->id,
            'title' => 'Form Org B',
            'public_slug' => 'form-org-b',
            'status' => 'published',
        ]);

        $version = FormVersion::create([
            'form_id' => $this->formOrgB->id,
            'version_number' => 1,
            'schema_json' => ['title' => 'Form Org B', 'fields' => []],
            'published_at' => now(),
            'schema_hash' => 'hash_org_b',
        ]);
        $this->formOrgB->update(['current_version_id' => $version->id]);

        $this->responseOrgB = Response::create([
            'organization_id' => $this->orgB->id,
            'event_id' => $this->eventOrgB->id,
            'form_id' => $this->formOrgB->id,
            'form_version_id' => $version->id,
            'receipt_code' => 'HF-ORGB12345',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function test_user_a_cannot_view_or_edit_event_of_org_b(): void
    {
        $response = $this->actingAs($this->userOrgA)->get("/admin/events/{$this->eventOrgB->id}");
        $response->assertStatus(403);

        $updateResp = $this->actingAs($this->userOrgA)->put("/admin/events/{$this->eventOrgB->id}", [
            'title' => 'Hacked Event',
            'timezone' => 'Asia/Jakarta',
            'status' => 'published',
        ]);
        $updateResp->assertStatus(403);
    }

    public function test_user_a_cannot_view_or_export_form_of_org_b(): void
    {
        $response = $this->actingAs($this->userOrgA)->get("/admin/forms/{$this->formOrgB->id}");
        $response->assertStatus(403);

        $exportResp = $this->actingAs($this->userOrgA)->post("/admin/forms/{$this->formOrgB->id}/exports", [
            'format' => 'xlsx',
        ]);
        $exportResp->assertStatus(403);
    }

    public function test_user_a_cannot_view_or_verify_response_of_org_b(): void
    {
        $response = $this->actingAs($this->userOrgA)->get("/admin/responses/{$this->responseOrgB->id}");
        $response->assertStatus(403);

        $verifyResp = $this->actingAs($this->userOrgA)->post("/admin/responses/{$this->responseOrgB->id}/verify", [
            'decision' => 'verified',
        ]);
        $verifyResp->assertStatus(403);
    }
}
