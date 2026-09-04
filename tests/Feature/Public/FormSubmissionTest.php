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

    public function test_it_stores_an_event_registration_form_submission(): void
    {
        $response = $this->from('/event/neko-fest')->post('/form-submissions', [
            'form_type' => 'event_registration',
            'subject' => 'Informações de evento',
            'payload' => [
                'event_uuid' => 'event-uuid',
                'event_title' => 'Neko Fest',
                'event_name' => 'Neko Fest',
                'city' => 'Sao Jose dos Campos',
                'state' => 'SP',
                'social_links' => 'https://instagram.com/neko',
            ],
        ]);

        $response->assertRedirect('/event/neko-fest');

        $this->assertDatabaseHas('form_submissions', [
            'form_type' => 'event_registration',
            'name' => null,
            'contact' => null,
            'subject' => 'Informações de evento',
            'status' => 'pending',
        ]);
    }

    public function test_it_validates_event_registration_required_payload_fields(): void
    {
        $response = $this->from('/event/neko-fest')->post('/form-submissions', [
            'form_type' => 'event_registration',
            'payload' => [],
        ]);

        $response
            ->assertRedirect('/event/neko-fest')
            ->assertSessionHasErrors([
                'payload.city',
                'payload.event_name',
                'payload.state',
                'payload.social_links',
            ]);

        $this->assertDatabaseCount('form_submissions', 0);
    }
}
