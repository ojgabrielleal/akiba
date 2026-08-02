<?php

namespace Tests\Feature\Private;

use App\Models\FormSubmission;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_approve_a_form_submission(): void
    {
        $reviewer = $this->userWithPermissions(['form.submission.review']);
        $formSubmission = FormSubmission::factory()->pending()->create();

        $this
            ->actingAs($reviewer)
            ->patch("/panel/administration/form-submission/{$formSubmission->uuid}/approve")
            ->assertRedirect();

        $formSubmission->refresh();

        $this->assertSame('approved', $formSubmission->status);
        $this->assertTrue($formSubmission->reviewer->is($reviewer));
        $this->assertNotNull($formSubmission->reviewed_at);
    }

    public function test_user_with_permission_can_reject_a_form_submission(): void
    {
        $reviewer = $this->userWithPermissions(['form.submission.review']);
        $formSubmission = FormSubmission::factory()->pending()->create();

        $this
            ->actingAs($reviewer)
            ->patch("/panel/administration/form-submission/{$formSubmission->uuid}/reject")
            ->assertRedirect();

        $formSubmission->refresh();

        $this->assertSame('rejected', $formSubmission->status);
        $this->assertTrue($formSubmission->reviewer->is($reviewer));
        $this->assertNotNull($formSubmission->reviewed_at);
    }

    public function test_review_permission_is_required_to_approve_a_form_submission(): void
    {
        $formSubmission = FormSubmission::factory()->pending()->create();

        $this
            ->actingAs(User::factory()->create())
            ->patch("/panel/administration/form-submission/{$formSubmission->uuid}/approve")
            ->assertForbidden();

        $this->assertSame('pending', $formSubmission->refresh()->status);
        $this->assertNull($formSubmission->reviewed_by);
        $this->assertNull($formSubmission->reviewed_at);
    }

    public function test_guest_is_redirected_when_reviewing_a_form_submission(): void
    {
        $formSubmission = FormSubmission::factory()->pending()->create();

        $this
            ->patch("/panel/administration/form-submission/{$formSubmission->uuid}/approve")
            ->assertRedirect('/panel');

        $this->assertSame('pending', $formSubmission->refresh()->status);
    }

    private function userWithPermissions(array $permissionNames): User
    {
        $role = Role::factory()->create();
        $permissions = collect($permissionNames)
            ->map(fn (string $name) => Permission::factory()->create(['name' => $name]));
        $role->permissions()->attach($permissions);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
