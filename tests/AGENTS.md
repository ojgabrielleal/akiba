# AGENTS.md

## Escopo

Estas instrucoes valem para arquivos dentro de `tests/`.

## Estilo Dos Testes

- Escreva testes no estilo idiomatico do Laravel, usando `Tests\TestCase`, helpers de teste do framework e asserts expressivos.
- Prefira testar comportamento observavel em vez de detalhes internos de implementacao.
- Use nomes de metodos descritivos em `snake_case`, deixando claro o cenario e o resultado esperado.
- Mantenha cada teste focado em um comportamento principal.
- Evite mocks quando factories, fakes ou objetos reais simples deixarem o teste mais confiavel.

## Organizacao

- Use `tests/Feature` para fluxos HTTP, paginas Inertia, comandos Artisan, autorizacao, validacao, persistencia e integracoes entre camadas.
- Use `tests/Unit` para regras isoladas, models, filtros, actions e services que nao precisam do kernel HTTP completo.
- Mantenha a estrutura de pastas alinhada ao dominio testado, por exemplo:
  - `tests/Feature/Private`
  - `tests/Feature/Public`
  - `tests/Feature/Console`
  - `tests/Unit/Models`
  - `tests/Unit/Actions`
  - `tests/Unit/Services`

## Banco De Dados

- Use `RefreshDatabase` quando o teste persistir ou consultar dados.
- Crie dados com factories sempre que possivel.
- Nao dependa de ordem de execucao, dados preexistentes ou seeders globais, a menos que o proprio teste execute o seeder necessario.
- O ambiente de teste local usa MySQL via `phpunit.xml`, apontando para o banco local `akiba`.
- Como os testes usam o banco local `akiba`, assuma que a suite pode destruir e recriar o schema com `RefreshDatabase`/migrations.
- Nao altere migrations antigas ja aplicadas em producao para resolver falhas de teste. Se uma migration legada depender de SQL especifico do MySQL, mantenha a compatibilidade pelo ambiente de teste sem reescrever historico de producao.

## Laravel Fakes

- Use fakes nativos do Laravel para isolar efeitos externos:
  - `Storage::fake()`
  - `Mail::fake()`
  - `Notification::fake()`
  - `Queue::fake()`
  - `Event::fake()`
  - `Http::fake()`
- Ao usar fake, tambem faca asserts sobre o efeito esperado, como envio, nao envio, arquivo criado ou job despachado.

## Feature Tests

- Para rotas privadas, teste pelo menos:
  - usuario nao autenticado;
  - usuario autenticado sem permissao;
  - usuario autenticado com permissao.
- Para paginas Inertia, valide o componente renderizado e as props essenciais.
- Para formularios, cubra casos validos e erros de validacao importantes.
- Para endpoints que alteram estado, confirme a resposta e o estado final no banco.

## Unit Tests

- Teste regras de dominio com entradas pequenas e saidas claras.
- Evite bootstrapping desnecessario quando uma regra puder ser testada diretamente.
- Para models, cubra casts, relacionamentos, accessors/mutators e scopes que tenham regra propria.

## Comandos

- Rode testes pelo wrapper do projeto:

```bash
./run.sh artisan test
```

- Para diagnosticar uma area especifica:

```bash
./run.sh artisan test --testsuite=Unit
./run.sh artisan test --testsuite=Feature
./run.sh artisan test tests/Feature/Private/MediaPageTest.php
```

- Nao rode `./run.sh npm run build` automaticamente apos alteracoes em testes.
- Nao suba os containers automaticamente; se forem necessarios, peca para o usuario executar `./run.sh up`.
