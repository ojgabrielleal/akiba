<?php

namespace Database\Seeders;

use App\Models\Mystery;
use App\Models\MysteryInteraction;
use App\Models\User;
use Illuminate\Database\Seeder;

class MysterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::query()->firstOrFail();
        $responder = User::query()
            ->where('id', $author->id)
            ->firstOrFail();

        $active = Mystery::factory()
            ->active()
            ->for($author, 'author')
            ->create([
                'title' => 'Teste do corredor azul',
                'content' => 'Uma voz some antes do refrão, mas deixa uma pista escondida entre duas sombras.',
                'solution' => 'A chave esta no segundo verso.',
            ]);

        MysteryInteraction::factory(8)
            ->question()
            ->for($active)
            ->create();

        MysteryInteraction::factory(8)
            ->question()
            ->answered($responder)
            ->for($active)
            ->create();

        MysteryInteraction::factory(5)
            ->incorrect($responder)
            ->for($active)
            ->create();

        MysteryInteraction::factory(3)
            ->finalAnswer()
            ->for($active)
            ->create();

        $solved = Mystery::factory()
            ->active()
            ->for($author, 'author')
            ->create([
                'title' => 'Enigma resolvido',
                'content' => 'Este fica para testar como o painel mostra um caso finalizado.',
                'solution' => 'Akiba',
            ]);

        MysteryInteraction::factory(6)
            ->question()
            ->answered($responder)
            ->for($solved)
            ->create();

        MysteryInteraction::factory()
            ->correct($responder)
            ->for($solved)
            ->create([
                'content' => 'Akiba',
            ]);

        Mystery::factory()
            ->draft()
            ->for($author, 'author')
            ->create([
                'title' => 'Rascunho de teste',
                'content' => 'Rascunho para testar o card sem interações.',
                'solution' => 'Ainda nao publicada.',
            ]);

        Mystery::factory()
            ->inactive()
            ->for($author, 'author')
            ->create([
                'title' => 'Enigma inativo',
                'content' => 'Este registro deve ficar fora da lista do painel.',
            ]);
    }
}
