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

        $active = EnigmaGame::factory()
            ->active()
            ->for($author, 'author')
            ->create([
                'title' => 'Teste do corredor azul',
                'content' => 'No corredor azul, tres portas repetem o mesmo simbolo, mas apenas uma delas guarda a chave. A pista esta naquilo que aparece fora de lugar na imagem.',
                'image' => '/img/placeholders/default.webp',
                'solution' => 'A chave esta no segundo verso.',
            ]);

        foreach ([
            'A pista envolve uma joia?',
            'O enigma acontece durante a noite?',
            'Existe uma senha escondida na imagem?',
            'A resposta tem relação com música?',
            'O baú é apenas decoração?',
            'A cor azul é importante?',
            'O símbolo repetido aponta para uma ordem?',
            'Tem algum número escondido no cenário?',
        ] as $content) {
            EnigmaGameInteraction::factory()
                ->question()
                ->for($active)
                ->create([
                    'content' => $content,
                ]);
        }

        foreach ([
            'A chave esta atras da segunda porta azul.',
            'A resposta é o símbolo diferente na parede.',
            'A ordem correta é porta, verso e chave.',
            'É a música que toca no início da transmissão.',
            'O número escondido é 02.',
        ] as $content) {
            EnigmaGameInteraction::factory()
                ->finalAnswer()
                ->for($active)
                ->create([
                    'content' => $content,
                ]);
        }

        EnigmaGame::factory()
            ->draft()
            ->for($author, 'author')
            ->create([
                'title' => 'Rascunho de teste',
                'content' => 'Um mascote deixou tres pistas no estudio: uma cor, um numero e um verso de abertura. Quando as tres se juntam, revelam uma senha.',
                'image' => '/img/placeholders/default.webp',
                'solution' => 'Ainda nao publicada.',
            ]);

        EnigmaGame::factory()
            ->inactive()
            ->for($author, 'author')
            ->create([
                'title' => 'Enigma inativo',
                'content' => 'Entre fitas antigas e luzes apagadas, uma resposta ficou escondida no primeiro pedido musical da noite.',
                'image' => '/img/placeholders/default.webp',
            ]);
    }
}
