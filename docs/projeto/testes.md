---
status: ativo
tipo: guia-testes
atualizado_em: 2026-08-03
---

# Testes

Os testes ficam em `tests/` e cobrem models, filters, actions, services, páginas privadas, páginas públicas e comandos.

## Onde Ficam

```txt
tests/Feature
tests/Unit
tests/TestCase.php
```

## Estrutura Atual

```txt
tests/Feature/Console
tests/Feature/Private
tests/Feature/Public
tests/Unit/Actions
tests/Unit/Filters
tests/Unit/Models
tests/Unit/Services
```

## Quando Criar Teste Unitário

Use `tests/Unit` para:

- action com regra de negócio;
- service com fallback, normalização ou integração fakeada;
- filter com query importante;
- model com relacionamento, cast ou método de domínio.

Exemplo de service com HTTP fake:

```php
Http::fake([
    'https://stream.example.test/status' => Http::response([
        'listeners' => 27,
    ]),
]);

$result = (new AudienceService)->get($radioStation);

$this->assertSame(27, $result['listeners']);
$this->assertSame('online', $result['status']);
```

## Quando Criar Teste Feature

Use `tests/Feature` para:

- página Inertia;
- permissão de rota;
- redirect de guest;
- fluxo HTTP completo;
- comandos artisan.

Exemplo de página Inertia:

```php
$this
    ->actingAs($user)
    ->get('/panel/reports')
    ->assertOk()
    ->assertInertia(fn ($page) => $page
        ->component('private/Reports', false)
        ->has('audience')
        ->has('onair')
    );
```

## Banco nos Testes

Use `RefreshDatabase` quando o teste cria ou consulta dados:

```php
use RefreshDatabase;
```

Use factories para montar cenário:

```php
$user = User::factory()->create();
$post = Post::factory()->for($user, 'author')->create();
```

## Permissões nos Testes

Quando a rota exige permissão, crie role e permissions:

```php
$role = Role::factory()->create();
$permission = Permission::factory()->create(['name' => 'report.module.view']);
$role->permissions()->attach($permission);

$user = User::factory()->create();
$user->roles()->attach($role);
```

## Comandos

Testes de comandos ficam em:

```txt
tests/Feature/Console
```

Comandos atuais relacionados:

```txt
audience:collect
audience:prune
programs:start
```

## Como Rodar

```bash
./run.sh artisan test
```

Para um arquivo específico:

```bash
./run.sh artisan test tests/Unit/Services/AudienceServiceTest.php
```

## Checklist

- Teste unitário cobre regra isolada?
- Teste feature cobre rota, permissão e props importantes?
- Integração externa usa `Http::fake()`?
- Banco usa `RefreshDatabase` quando necessário?
- Factories criam dados válidos?
- Cenário de guest e usuário sem permissão foi testado quando a rota é protegida?
