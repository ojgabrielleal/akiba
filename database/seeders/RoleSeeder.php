<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAdministration();
    }

    private function seedAdministration(): void
    {
        $roles = [
            [
                'name' => 'administrator',
                'label' => 'Administrador',
                'description' => 'Tem acesso total ao sistema, podendo gerenciar usuários, permissões e configurações.',
                'weight' => 1000,
            ],
            [
                'name' => 'developer',
                'label' => 'Desenvolvedor',
                'description' => 'Responsável pela manutenção e implementação de novas funcionalidades no sistema.',
                'weight' => 900,
            ],
            [
                'name' => 'locutioner',
                'label' => 'Locutor',
                'description' => 'Gerencia transmissões ao vivo e interage com o público durante as programações.',
                'weight' => 800,
            ],
            [
                'name' => 'writer',
                'label' => 'Redator',
                'description' => 'Cria e edita artigos, notícias e demais conteúdos de texto para publicação.',
                'weight' => 700,
            ],
            [
                'name' => 'social_media',
                'label' => 'Social Media',
                'description' => 'Gerencia as redes sociais, produz postagens e acompanha o engajamento.',
                'weight' => 600,
            ],
            [
                'name' => 'marketing',
                'label' => 'Marketing',
                'description' => 'Responsável por campanhas, divulgação e estratégias de crescimento da marca.',
                'weight' => 500,
            ],
            [
                'name' => 'podcaster',
                'label' => 'Podcaster',
                'description' => 'Produz, edita e publica episódios de podcast na plataforma.',
                'weight' => 400,
            ],
        ];

        
        foreach($roles as $item){
            Role::updateOrCreate(
                ['label' => $item['label']],
                [
                    'label' => $item['label'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'weight' => $item['weight'],
                ]
            );
        }

        $role = Role::where('name', 'administrator')->first();
        $permissions = Permission::all()->pluck('id');

        $role->permissions()->syncWithoutDetaching($permissions);
    }
}
