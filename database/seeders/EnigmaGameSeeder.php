<?php

namespace Database\Seeders;

use App\Models\EnigmaGame;
use App\Models\EnigmaGameInteraction;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnigmaGameSeeder extends Seeder
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

        $active = EnigmaGame::factory()
            ->active()
            ->for($author, 'author')
            ->create([
                'title' => 'Teste do corredor azul',
                'content' => 'Uma voz some antes do refrão, mas deixa uma pista escondida entre duas sombras.',
                'solution' => 'A chave esta no segundo verso.',
            ]);

        EnigmaGameInteraction::factory(8)
            ->question()
            ->for($active)
            ->create();

        EnigmaGameInteraction::factory(8)
            ->question()
            ->answered($responder)
            ->for($active)
            ->create();

        EnigmaGameInteraction::factory(5)
            ->incorrect($responder)
            ->for($active)
            ->create();

        EnigmaGameInteraction::factory(3)
            ->finalAnswer()
            ->for($active)
            ->create();

        $solved = EnigmaGame::factory()
            ->active()
            ->for($author, 'author')
            ->create([
                'title' => 'Enigma resolvido',
                'content' => 'Este fica para testar como o painel mostra um caso finalizado.',
                'solution' => 'Akiba',
            ]);

        EnigmaGameInteraction::factory(6)
            ->question()
            ->answered($responder)
            ->for($solved)
            ->create();

        EnigmaGameInteraction::factory()
            ->correct($responder)
            ->for($solved)
            ->create([
                'content' => 'Akiba',
            ]);

        EnigmaGame::factory()
            ->draft()
            ->for($author, 'author')
            ->create([
                'title' => 'Rascunho de teste',
                'content' => 'Rascunho para testar o card sem interações.',
                'solution' => 'Ainda nao publicada.',
            ]);

        EnigmaGame::factory()
            ->inactive()
            ->for($author, 'author')
            ->create([
                'title' => 'Enigma inativo',
                'content' => 'Este registro deve ficar fora da lista do painel.',
            ]);
    }
}
