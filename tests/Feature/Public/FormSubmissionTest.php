<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_generic_form_submission(): void
    {
        $response = $this->post('/form-submissions', [
            'form_type' => 'recruitment',
            'name' => 'Akiba Tester',
            'contact' => 'tester@example.com',
            'subject' => 'Inscrição para equipe',
            'payload' => [
                'role' => 'Locutor',
                'nickname' => 'Tester',
                'age' => 18,
                'portfolio' => 'https://example.com',
                'message' => 'Quero participar da equipe.',
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('form_submissions', [
            'form_type' => 'recruitment',
            'name' => 'Akiba Tester',
            'contact' => 'tester@example.com',
            'subject' => 'Inscrição para equipe',
            'status' => 'pending',
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->from('/contato')->post('/form-submissions', [
            'form_type' => 'invalid',
            'name' => '',
            'contact' => '',
            'payload' => [],
        ]);

        $response
            ->assertRedirect('/contato')
            ->assertSessionHasErrors([
                'form_type',
                'name',
                'contact',
            ]);

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_it_validates_recruitment_age_range(): void
    {
        $response = $this->from('/contato')->post('/form-submissions', [
            'form_type' => 'recruitment',
            'name' => 'Akiba Tester',
            'contact' => 'tester@example.com',
            'payload' => [
                'age' => 9,
            ],
        ]);

        $response
            ->assertRedirect('/contato')
            ->assertSessionHasErrors(['payload.age']);

        $this->assertDatabaseCount('form_submissions', 0);
    }
}
