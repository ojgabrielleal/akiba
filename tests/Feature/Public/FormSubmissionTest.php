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
}
