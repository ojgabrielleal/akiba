---
status: ativo
tipo: guia-database
atualizado_em: 2026-08-03
---

# Seeders

Seeders populam o banco com dados iniciais e dados úteis para desenvolvimento.

## Onde Ficam

```txt
database/seeders
```

## Seeder Principal

O ponto de entrada é:

```txt
database/seeders/DatabaseSeeder.php
```

Ordem atual:

```txt
PermissionSeeder
RoleSeeder
UserSeeder
PostSeeder
PageViewSeeder
RadioStationSeeder
PodcastSeeder
MusicSeeder
PlaylistBattleSeeder
ListenerMonthSeeder
PollSeeder
PollVoteSeeder
ListenerGallerySeeder
TaskSeeder
RepositorySeeder
ActivitySeeder
CalendarSeeder
ProgramSeeder
ProgramScheduleSeeder
OnairSeeder
SongRequestSeeder
```

## Grupos no DatabaseSeeder

```php
$this->call([
    PermissionSeeder::class,
    RoleSeeder::class,
    UserSeeder::class,
]);

$this->post();
$this->radio();
$this->variable();
$this->locution();
```

Esses grupos deixam claro que permissões, roles e usuários vêm antes dos dados de domínio.

## PermissionSeeder

Arquivo:

```txt
database/seeders/PermissionSeeder.php
```

O que faz:

- cria permissões do painel;
- organiza permissões por módulo;
- define nomes usados por policies, middlewares e frontend.

Exemplos:

```txt
dashboard.module.view
post.create
post.feed.view
locution.start
report.module.view
inactive.restore
```

Arquivos relacionados:

```txt
app/Policies
resources/js/lib/utils/access/permissions.js
app/Http/Middleware/Auth/ShareAuthenticatedUserMiddleware.php
```

## Quando Criar ou Alterar Seeder

Altere seeders quando:

- nova permissão precisa existir localmente;
- nova role padrão precisa receber permissão;
- nova tela depende de dado inicial;
- novo módulo precisa de dados mínimos;
- teste manual local precisa de cenário previsível.

## Cuidados

- Seeders devem ser idempotentes quando possível.
- Permissões precisam bater exatamente com policies e frontend.
- Dados obrigatórios devem ser criados antes dos dados que dependem deles.
- Evite seeders que fazem chamada externa.

## Como Rodar

```bash
./run.sh artisan db:seed
```

Seeder específico:

```bash
./run.sh artisan db:seed --class=PermissionSeeder
```

## Checklist

- A ordem no `DatabaseSeeder` respeita dependências?
- Permissões novas foram vinculadas a roles quando necessário?
- Factories usadas pelo seeder geram dados válidos?
- Seeder não depende de API externa?
- Nomes de permissões batem com policies e utils do frontend?
