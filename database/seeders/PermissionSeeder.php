<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
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
        $permissions = [
            /*
            |--------------------------------------------------------------------------
            | Accessos as páginas Gerais
            |--------------------------------------------------------------------------
            */
            ['name' => 'dashboard.module.view', 'label' => '[Dashboard] Acesso ao módulo'],
            ['name' => 'warning.module.view', 'label' => '[Avisos] Acesso ao módulo'],
            ['name' => 'post.module.view', 'label' => '[Matérias] Acesso ao módulo'],
            ['name' => 'locution.module.view', 'label' => '[Locução] Acesso ao módulo'],
            ['name' => 'radio.module.view', 'label' => '[Rádio] Acesso ao módulo'],
            ['name' => 'podcast.module.view', 'label' => '[Podcasts] Acesso ao módulo'],
            ['name' => 'marketing.module.view', 'label' => '[Marketing] Acesso ao módulo'],
            ['name' => 'media.module.view', 'label' => '[Mídias] Acesso ao módulo'],
            ['name' => 'administration.module.view', 'label' => '[Administração] Acesso ao módulo'],
            ['name' => 'report.module.view', 'label' => '[Relatórios] Acesso ao módulo'],

            /*
            |--------------------------------------------------------------------------
            | Atividades e avisos
            |--------------------------------------------------------------------------
            */
            ['name' => 'activity.list', 'label' => '[Atividades] Listar'],
            ['name' => 'activity.view', 'label' => '[Atividades] Visualizar'],
            ['name' => 'activity.create', 'label' => '[Atividades] Criar'],
            ['name' => 'activity.update', 'label' => '[Atividades] Atualizar'],
            ['name' => 'activity.participate', 'label' => '[Atividades] Confirmar participação'],

            /*
            |--------------------------------------------------------------------------
            | Tarefas
            |--------------------------------------------------------------------------
            */
            ['name' => 'task.list', 'label' => '[Tarefas] Listar'],
            ['name' => 'task.view', 'label' => '[Tarefas] Visualizar'],
            ['name' => 'task.create', 'label' => '[Tarefas] Criar'],
            ['name' => 'task.update', 'label' => '[Tarefas] Atualizar'],
            ['name' => 'task.deactivate', 'label' => '[Tarefas] Desativar'],
            ['name' => 'task.review', 'label' => '[Tarefas] Enviar para avaliação'],

            /*
            |--------------------------------------------------------------------------
            | Calendário
            |--------------------------------------------------------------------------
            */
            ['name' => 'calendar.list', 'label' => '[Calendário] Listar'],
            ['name' => 'calendar.view', 'label' => '[Calendário] Visualizar'],
            ['name' => 'calendar.create', 'label' => '[Calendário] Criar'],
            ['name' => 'calendar.update', 'label' => '[Calendário] Atualizar'],
            ['name' => 'calendar.deactivate', 'label' => '[Calendário] Excluir'],

            /*
            |--------------------------------------------------------------------------
            | Posts
            |--------------------------------------------------------------------------
            */
            ['name' => 'post.list', 'label' => '[Posts] Listar'],
            ['name' => 'post.view', 'label' => '[Posts] Visualizar'],
            ['name' => 'post.create', 'label' => '[Posts] Criar'],
            ['name' => 'post.update', 'label' => '[Posts] Atualizar'],
            ['name' => 'post.deactivate', 'label' => '[Posts] Desativar'],
            ['name' => 'post.list.own', 'label' => '[Posts] Listar posts próprios'],
            ['name' => 'post.review.opinion.list', 'label' => '[Posts] Listar opiniões de reviews'],
            ['name' => 'post.publish', 'label' => '[Posts] Publicar post (imediatamente)'],
            ['name' => 'post.approve', 'label' => '[Posts] Aprovar post'],

            /*
            |--------------------------------------------------------------------------
            | Locução
            |--------------------------------------------------------------------------
            */
            ['name' => 'locution.start', 'label' => '[Locução] Iniciar programa'],
            ['name' => 'locution.finish', 'label' => '[Locução] Encerrar programa'],


            /*
            |--------------------------------------------------------------------------
            | Pedidos musicais
            |--------------------------------------------------------------------------
            */
            ['name' => 'song.request.list', 'label' => '[Pedidos Música] Listar'],
            ['name' => 'song.request.reproduce', 'label' => '[Pedidos Música] Atender'],
            ['name' => 'song.request.cancel', 'label' => '[Pedidos Música] Cancelar'],
            ['name' => 'song.request.toggle', 'label' => '[Pedidos Música] Ativar/Desativar'],

            /*
            |--------------------------------------------------------------------------
            | Programas
            |--------------------------------------------------------------------------
            */
            ['name' => 'program.list', 'label' => '[Programas] Listar'],
            ['name' => 'program.view', 'label' => '[Programas] Visualizar'],
            ['name' => 'program.create', 'label' => '[Programas] Criar'],
            ['name' => 'program.update', 'label' => '[Programas] Atualizar'],
            ['name' => 'program.deactivate', 'label' => '[Programas] Desativar'],

            /*
            |--------------------------------------------------------------------------
            | Músicas
            |--------------------------------------------------------------------------
            */
            ['name' => 'music.list', 'label' => '[Músicas] Listar'],
            ['name' => 'music.update', 'label' => '[Músicas] Atualizar'],
            ['name' => 'music.ranking.update', 'label' => '[Músicas] Definir ranking'],

            /*
            |--------------------------------------------------------------------------
            | Ouvinte do mês
            |--------------------------------------------------------------------------
            */
            ['name' => 'listener.month.view', 'label' => '[Ouvinte Mês] Visualizar'],
            ['name' => 'listener.month.set', 'label' => '[Ouvinte Mês] Definir ouvinte'],

            /*
            |--------------------------------------------------------------------------
            | Galeria dos ouvintes
            |--------------------------------------------------------------------------
            */
            ['name' => 'listener.gallery.list', 'label' => '[Galeria Ouvintes] Listar'],
            ['name' => 'listener.gallery.view', 'label' => '[Galeria Ouvintes] Visualizar'],
            ['name' => 'listener.gallery.create', 'label' => '[Galeria Ouvintes] Criar'],
            ['name' => 'listener.gallery.update', 'label' => '[Galeria Ouvintes] Atualizar'],
            ['name' => 'listener.gallery.delete', 'label' => '[Galeria Ouvintes] Excluir'],

            /*
            |--------------------------------------------------------------------------
            | Podcasts
            |--------------------------------------------------------------------------
            */
            ['name' => 'podcast.list', 'label' => '[Podcasts] Listar'],
            ['name' => 'podcast.view', 'label' => '[Podcasts] Visualizar'],
            ['name' => 'podcast.create', 'label' => '[Podcasts] Criar'],
            ['name' => 'podcast.update', 'label' => '[Podcasts] Atualizar'],
            ['name' => 'podcast.deactivate', 'label' => '[Podcasts] Desativar'],

            /*
            |--------------------------------------------------------------------------
            | Repository
            |--------------------------------------------------------------------------
            */
            ['name' => 'repository.list', 'label' => '[Marketing] Listar'],
            ['name' => 'repository.view', 'label' => '[Marketing] Visualizar'],
            ['name' => 'repository.create', 'label' => '[Marketing] Adicionar'],
            ['name' => 'repository.update', 'label' => '[Marketing] Atualizar'],
            ['name' => 'repository.deactivate', 'label' => '[Marketing] Desativar'],


            /*
            |--------------------------------------------------------------------------
            | Enquetes
            |--------------------------------------------------------------------------
            */
            ['name' => 'poll.list', 'label' => '[Enquetes] Listar'],
            ['name' => 'poll.view', 'label' => '[Enquetes] Visualizar'],
            ['name' => 'poll.create', 'label' => '[Enquetes] Criar'],
            ['name' => 'poll.update', 'label' => '[Enquetes] Atualizar'],
            ['name' => 'poll.deactivate', 'label' => '[Enquetes] Desativar'],
            ['name' => 'poll.publish', 'label' => '[Enquetes] Publicar enquete (imediatamente)'],
            ['name' => 'poll.approve', 'label' => '[Enquetes] Aprovar enquete'],
            ['name' => 'poll.vote', 'label' => '[Enquetes] Votar'],

            /*
            |--------------------------------------------------------------------------
            | Usuário
            |--------------------------------------------------------------------------
            */
            ['name' => 'user.list', 'label' => '[Usuários] Listar'],
            ['name' => 'user.view', 'label' => '[Usuários] Visualizar'],
            ['name' => 'user.create', 'label' => '[Usuários] Criar'],
            ['name' => 'user.update', 'label' => '[Usuários] Atualizar'],
            ['name' => 'user.deactivate', 'label' => '[Usuários] Desativar'],
            ['name' => 'user.view.own', 'label' => '[Usuários] Visualizar perfil próprio'],
            ['name' => 'user.update.own', 'label' => '[Usuários] Atualizar perfil próprio'],
            ['name' => 'user.authority.update', 'label' => '[Usuários] Atualizar Acessos/Cargos'],

            /*
            |--------------------------------------------------------------------------
            | Cargos e permissões
            |--------------------------------------------------------------------------
            */
            ['name' => 'role.list', 'label' => '[Cargos] Listar'],
            ['name' => 'role.view', 'label' => '[Cargos] Visualizar'],
            ['name' => 'role.create', 'label' => '[Cargos] Criar'],
            ['name' => 'role.update', 'label' => '[Cargos] Atualizar'],
            ['name' => 'role.delete', 'label' => '[Cargos] Excluir'],

            /*
            |--------------------------------------------------------------------------
            | Automáticos
            |--------------------------------------------------------------------------
            */
        ];

        $this->renamePermissions($permissions, [
            'log.module.view' => 'report.module.view',
            'task.complete' => 'task.review',
            'publication.list' => 'post.list',
            'publication.view' => 'post.view',
            'publication.update' => 'post.update',
            'publication.deactivate' => 'post.deactivate',
            'publication.list.own' => 'post.list.own',
            'publication.update.own' => 'post.update.own',
            'publication.approve' => 'post.approve',
            'songrequest.list' => 'song.request.list',
            'songrequest.reproduce' => 'song.request.reproduce',
            'songrequest.cancel' => 'song.request.cancel',
            'songrequest.toggle' => 'song.request.toggle',
            'music.set.ranking' => 'music.ranking.update',
            'listener.gallery.remove' => 'listener.gallery.delete',
            'poll.create.vote' => 'poll.vote',
            'user.update.authority' => 'user.authority.update',
            'role.remove' => 'role.delete',
        ]);

        collect($permissions)->each(fn (array $permission) => Permission::updateOrCreate(
            ['name' => $permission['name']],
            ['label' => $permission['label']]
        ));

        Permission::whereIn('name', [
            'activity.deactivate',
        ])->delete();
    }

    private function renamePermissions(array $permissions, array $renamedPermissions): void
    {
        $labels = collect($permissions)->pluck('label', 'name');

        foreach ($renamedPermissions as $oldName => $newName) {
            $oldPermission = Permission::where('name', $oldName)->first();
            $newPermission = Permission::where('name', $newName)->first();

            if (! $oldPermission) {
                continue;
            }

            if (! $newPermission) {
                $oldPermission->update([
                    'name' => $newName,
                    'label' => $labels->get($newName, $oldPermission->label),
                ]);

                continue;
            }

            DB::table('permissions_pivot')
                ->where('permission_id', $oldPermission->id)
                ->pluck('role_id')
                ->each(function (int $roleId) use ($newPermission): void {
                    DB::table('permissions_pivot')->updateOrInsert([
                        'permission_id' => $newPermission->id,
                        'role_id' => $roleId,
                    ]);
                });

            DB::table('permissions_pivot')->where('permission_id', $oldPermission->id)->delete();
            $oldPermission->delete();
        }
    }
}
