<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

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
                'public_label' => 'Administradores',
                'description' => 'Tem acesso total ao sistema, podendo gerenciar usuários, permissões e configurações.',
                'icon' => '/svg/crown.svg',
                'weight' => 1000,
            ],
            [
                'name' => 'developer',
                'label' => 'Desenvolvedor',
                'public_label' => 'Desenvolvedores',
                'description' => 'Responsável pela manutenção e implementação de novas funcionalidades no sistema.',
                'icon' => '/svg/cog.svg',
                'weight' => 900,
            ],
            [
                'name' => 'locutioner',
                'label' => 'Locutor',
                'public_label' => 'Locutores',
                'description' => 'Gerencia transmissões ao vivo e interage com o público durante as programações.',
                'icon' => '/svg/locution.svg',
                'weight' => 800,
            ],
            [
                'name' => 'writer',
                'label' => 'Redator',
                'public_label' => 'Colunistas',
                'description' => 'Cria e edita artigos, notícias e demais conteúdos de texto para publicação.',
                'icon' => '/svg/materials.svg',
                'weight' => 700,
            ],
            [
                'name' => 'social_media',
                'label' => 'Social Media',
                'public_label' => 'Social Media',
                'description' => 'Gerencia as redes sociais, produz postagens e acompanha o engajamento.',
                'icon' => '/svg/media.svg',
                'weight' => 600,
            ],
            [
                'name' => 'marketing',
                'label' => 'Marketing',
                'public_label' => 'Marketing',
                'description' => 'Responsável por campanhas, divulgação e estratégias de crescimento da marca.',
                'icon' => '/svg/marketing.svg',
                'weight' => 500,
            ],
            [
                'name' => 'podcaster',
                'label' => 'Podcaster',
                'public_label' => 'Podcasters',
                'description' => 'Produz, edita e publica episódios de podcast na plataforma.',
                'icon' => '/svg/podcasts.svg',
                'weight' => 400,
            ],
        ];
        collect($roles)->each(function (array $item) {
            Role::updateOrCreate(
                ['label' => $item['label']],
                [
                    'label' => $item['label'],
                    'public_label' => $item['public_label'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'icon' => $item['icon'],
                    'weight' => $item['weight'],
                ]
            );
        });

        $role = Role::where('name', 'administrator')->firstOrFail();
        $permissions = Permission::query()->pluck('id');

        $role->permissions()->syncWithoutDetaching($permissions);
    }
}
