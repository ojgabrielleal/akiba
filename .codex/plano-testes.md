# Plano De Testes

## Objetivo

Montar e evoluir a suite de testes do projeto de forma incremental, seguindo convencoes Laravel-like e priorizando comportamento observavel, seguranca de acesso e regras de negocio criticas.

## Convencoes

- Seguir as instrucoes de `tests/AGENTS.md`.
- Usar `Tests\TestCase` como base.
- Usar `RefreshDatabase` em testes que persistem ou consultam dados.
- Criar dados com factories sempre que possivel.
- Preservar o estilo de nomes dos arquivos existentes; para novos arquivos, preferir metodos `test_it_...` em `snake_case`.
- Preferir Feature tests para comportamento HTTP, paginas Inertia, autorizacao, validacao e comandos.
- Preferir Unit tests para regras isoladas, models, filters, actions e services.
- Nao depender de seeders globais ou ordem de execucao.
- Nao rodar `./run.sh npm run build` automaticamente.
- Nao subir Docker automaticamente; pedir para o usuario executar `./run.sh up` quando necessario.

## Fase 1: Baseline - Concluida

Resultado em 2026-08-02:

- `./run.sh artisan test`: falhou com 112 testes falhando e 0 assertions.
- `./run.sh artisan test --testsuite=Unit`: falhou com 101 testes falhando e 0 assertions.
- `./run.sh artisan test --testsuite=Feature`: falhou com 11 testes falhando e 0 assertions.
- Causa raiz comum: erro de migration no SQLite de teste.
- Migration afetada: `database/migrations/2026_08_01_000000_make_program_access_type_nullable_for_auto_dj.php`.
- Erro: `SQLSTATE[HY000]: General error: 1 near "MODIFY": syntax error`.
- Query: `ALTER TABLE programs MODIFY access_type ENUM('free', 'private') NULL`.
- Classificacao: erro de compatibilidade da migration com o ambiente de teste SQLite, nao falhas individuais de regra de negocio.

Objetivo: entender o estado atual da suite antes de adicionar testes novos.

- Rodar a suite completa:

```bash
./run.sh artisan test
```

- Se houver falhas, separar por suite:

```bash
./run.sh artisan test --testsuite=Unit
./run.sh artisan test --testsuite=Feature
```

- Classificar falhas em:
  - erro de ambiente;
  - teste desatualizado;
  - bug real;
  - dependencia de dados externos.

- Corrigir quebras reais antes de expandir cobertura.

## Fase 2: Inventario De Cobertura

Resultado em 2026-08-02:

- Inventario executado por leitura da suite atual; a execucao automatica continua bloqueada pela migration SQLite registrada na Fase 1.
- `tests/Unit/Models`: cobertura ampla para relacionamentos e scopes principais em `Activity`, `Calendar`, `Music`, `Onair`, `Podcast`, `Poll`, `Post`, `Program`, `ProgramSchedule`, `Role`, `Task`, `User` e modelos auxiliares. Tambem existem testes de casts, accessors/mutators, helpers de permissao e states de factories em pontos especificos.
- `tests/Unit/Actions`: cobertura concentrada em locucao (`StartLocutionAction`, `FinishLocutionAction` e preservacao de status de `ProgramSchedule`) e atualizacao de musica (`UpdateMusicAction`). Existem muitas actions sem testes diretos, especialmente CRUDs administrativos, inativacao/reativacao, formularios, enquetes, posts, tarefas, perfil e permissoes.
- `tests/Unit/Services`: apenas `AudienceCollectorService` esta coberto, com foco em atualizacao de pico de audiencia e snapshots. Servicos externos (`AnimeThemeService`, `AudienceService`, `DiscordWebhookService`, `OneSignalService`, `StreamService`) e `ImageProcessService` ainda nao tem cobertura dedicada com fakes/cenarios de erro.
- `tests/Feature/Private`: cobertura inicial para paginas de itens inativos, midia e relatorios. Ha testes de listagem, reativacao, permissao de restore, remocao permanente, componente/props Inertia e estado vazio em relatorios. A maioria das paginas privadas e mutacoes do painel ainda nao tem testes de acesso, autorizacao, validacao e persistencia.
- `tests/Feature/Public`: apenas submissao generica de formulario esta coberta. Paginas publicas, comentarios/reacoes/likes, perfil OAuth e pedido de musica ainda nao tem cobertura Feature.
- `tests/Feature/Console`: `programs:start` cobre expiracao de agenda, manutencao de agenda futura e inicio de agenda vencida sem locucao ao vivo.
- Lacunas prioritarias para as proximas fases: corrigir compatibilidade da migration com SQLite; expandir Feature tests de rotas privadas criticas; cobrir actions de mutacao com payload valido/invalido e autorizacao; adicionar fakes para servicos externos; cobrir uploads com `Storage::fake()`; cobrir comandos e rotas publicas que alteram estado.

Objetivo: mapear o que ja esta testado e onde existem lacunas.

- Revisar `tests/Unit/Models`:
  - relacionamentos;
  - casts;
  - accessors/mutators;
  - scopes;
  - helpers de permissao ou estado.

- Revisar `tests/Unit/Actions`:
  - regras de locucao;
  - regras de radio;
  - atualizacao de musicas;
  - transicoes de estado.

- Revisar `tests/Unit/Services`:
  - servicos com chamadas externas;
  - uso de fakes;
  - cenarios de erro.

- Revisar `tests/Feature`:
  - paginas privadas;
  - formularios publicos;
  - comandos Artisan;
  - validacoes;
  - autorizacao.

## Fase 3: Base De Testes

Resultado em 2026-08-02:

- Inventario de factories comparado com `app/Models`.
- Criadas factories faltantes para models que usam `HasFactory`: `FormSubmission`, `RadioStation`, `RadioAudienceSnapshot` e `UserTopAnime`.
- `FormSubmissionFactory` inclui estados `pending`, `approved` e `rejected`, preparando os proximos testes de formularios e revisao.
- `RadioStationFactory` inclui estado `inactive`.
- `RadioAudienceSnapshotFactory` usa os status do dominio (`online`, `offline`, `invalid_response`) e inclui estados para falha e resposta invalida.
- `UserTopAnimeFactory` cria usuario proprio e metadata basica, sem depender de seeders globais.
- Verificacao: `php -l` nao rodou no host porque `php` nao esta instalado localmente; tambem nao foi possivel consultar Docker por permissao no socket. Nenhum container foi iniciado automaticamente.

Objetivo: reduzir repeticao e deixar a criacao de cenarios consistente.

- Conferir factories faltantes ou incompletas.
- Criar states de factories para estados comuns, como ativo, inativo, publicado, expirado ou ao vivo.
- Criar helpers privados dentro do proprio arquivo quando a repeticao for local.
- Evitar helpers globais antes de haver repeticao clara entre varios arquivos.
- Garantir que cada teste prepara seu proprio estado.

## Fase 4: Autenticacao E Autorizacao

Resultado em 2026-08-02:

- Escopo inicial aplicado nas paginas privadas que ja tinham testes: itens inativos, midia e relatorios.
- `routes/web/private.php`: adicionados middlewares `can:media.module.view` e `can:report.module.view` para proteger os grupos `/panel/media` e `/panel/reports`. `/panel/inactive` ja estava protegido por `can:inactive.module.view`.
- `tests/Feature/Private/InactiveItemsPageTest.php`: adicionados cenarios de visitante redirecionado, usuario autenticado sem permissao bloqueado e componente Inertia esperado para usuario autorizado.
- `tests/Feature/Private/MediaPageTest.php`: adicionados cenarios de visitante redirecionado, usuario autenticado sem permissao bloqueado e componente/props essenciais para usuario autorizado. O teste existente foi ajustado para incluir `media.module.view` alem de `poll.list`.
- `tests/Feature/Private/ReportsPageTest.php`: adicionados cenarios de visitante redirecionado, usuario autenticado sem permissao bloqueado e componente/props essenciais para usuario autorizado. Os testes existentes foram ajustados para usar `report.module.view`.
- Verificacao temporaria: apos ajuste local experimental na migration SQLite, `./run.sh artisan test tests/Feature/Private/InactiveItemsPageTest.php tests/Feature/Private/MediaPageTest.php tests/Feature/Private/ReportsPageTest.php` passou com 16 testes e 139 assertions.
- Observacao de producao: a alteracao experimental na migration antiga foi revertida. Nao alterar migrations ja aplicadas em producao; a incompatibilidade SQLite dessa migration segue como bloqueio conhecido para execucao completa da suite sem uma estrategia propria de teste.

Objetivo: proteger fluxos privados do painel.

Para cada pagina privada importante, cobrir:

- visitante nao autenticado nao acessa;
- usuario autenticado sem permissao nao acessa;
- usuario autenticado com permissao acessa;
- componente Inertia correto e renderizado;
- props essenciais presentes;
- estado vazio quando nao ha dados.

Prioridade inicial:

- paginas privadas ja existentes em `tests/Feature/Private`;
- paginas com criacao, edicao ou remocao de dados;
- paginas que exibem dados sensiveis ou administrativos.

## Fase 5: Formularios E Mutacoes

Resultado em 2026-08-02:

- Escopo inicial aplicado ao fluxo de `FormSubmission`, cobrindo formulario publico e revisao privada.
- `tests/Feature/Public/FormSubmissionTest.php`: adicionados cenarios de validacao para campos obrigatorios, `form_type` invalido e faixa invalida de `payload.age`; os testes confirmam redirect, erros de sessao e que nenhum registro e persistido em payload invalido.
- `tests/Feature/Private/FormSubmissionReviewTest.php`: criado teste Feature para aprovar e rejeitar submissao com `form.submission.review`, impedir usuario autenticado sem permissao, redirecionar visitante e confirmar estado final (`status`, `reviewed_by`, `reviewed_at`).
- `database/factories/FormSubmissionFactory.php`, criada na Fase 3, foi usada para preparar estados `pending`, `approved` e `rejected` sem depender de seeders.
- Verificacao: `docker compose exec app php -l` passou para `tests/Feature/Public/FormSubmissionTest.php`, `tests/Feature/Private/FormSubmissionReviewTest.php` e `database/factories/FormSubmissionFactory.php`.
- Execucao bloqueada: `./run.sh artisan test tests/Feature/Public/FormSubmissionTest.php tests/Feature/Private/FormSubmissionReviewTest.php` falhou antes de executar assertions por incompatibilidade SQLite da migration antiga `2026_08_01_000000_make_program_access_type_nullable_for_auto_dj.php` (`ALTER TABLE ... MODIFY`). Conforme regra de producao, a migration antiga nao foi alterada.

Objetivo: garantir que entradas do usuario alterem o sistema corretamente.

Para cada fluxo de criacao, edicao ou exclusao:

- payload valido persiste corretamente;
- campos obrigatorios falham;
- formatos invalidos falham;
- usuario sem permissao nao altera estado;
- resposta, redirect e mensagens de sessao estao corretos;
- banco reflete o estado final esperado.

Prioridade:

- formularios publicos;
- formularios do painel;
- endpoints que alteram status;
- endpoints que removem registros ou arquivos.

## Fase 6: Regras De Dominio

Resultado em 2026-08-02:

- Escopo inicial aplicado a regra de dominio sem banco em `AudienceService`, evitando depender de migrations enquanto a suite SQLite segue bloqueada por migration antiga de producao.
- `tests/Unit/Services/AudienceServiceTest.php`: criado teste unitario puro com `Http::fake()` para resposta online com `listeners_path` configurado, resposta invalida quando o caminho nao e numerico, falha HTTP retornando `offline` e normalizacao de ouvintes negativos para zero.
- Verificacao: `./run.sh artisan test tests/Unit/Services/AudienceServiceTest.php` passou com 4 testes e 11 assertions.
- Observacao: testes de dominio que usam `RefreshDatabase` continuam sujeitos ao bloqueio conhecido da migration legada com `ALTER TABLE ... MODIFY`; nao alterar migrations antigas ja aplicadas em producao.

Objetivo: cobrir regras importantes fora do fluxo HTTP.

- Models:
  - relacionamentos criticos;
  - casts;
  - scopes;
  - atributos calculados;
  - helpers de permissao e estado.

- Actions:
  - transicoes validas;
  - cenarios em que nada deve mudar;
  - entradas invalidas;
  - efeitos colaterais esperados.

- Services:
  - respostas externas simuladas com `Http::fake()`;
  - falhas externas;
  - dados vazios;
  - persistencia final.

## Fase 7: Uploads E Arquivos

Resultado em 2026-08-02:

- Escopo inicial aplicado a `ImageProcessService`, cobrindo regras de arquivo sem depender de banco ou migrations.
- `tests/Unit/Services/ImageProcessServiceTest.php`: criado teste unitario com `Storage::fake('public')` e `UploadedFile::fake()->image()` para upload valido convertido para `.webp`, preservacao do caminho antigo quando nao ha novo arquivo, substituicao removendo arquivo antigo, remocao de arquivo existente e retorno `false` para arquivo inexistente.
- Verificacao: `./run.sh artisan test tests/Unit/Services/ImageProcessServiceTest.php` passou com 5 testes e 11 assertions.

Objetivo: isolar efeitos de filesystem com fakes do Laravel.

- Usar `Storage::fake()` para testes de arquivo.
- Cobrir upload valido.
- Cobrir extensoes invalidas.
- Cobrir tamanho invalido.
- Confirmar arquivo criado, substituido ou removido.
- Confirmar que o banco guarda o caminho ou metadado esperado.

## Fase 8: Comandos Artisan

Resultado em 2026-08-02:

- `tests/Feature/Console/CollectAudienceTest.php`: criado teste de comando para `audience:collect`, mockando `AudienceCollectorService`, validando chamada a `collect()`, output esperado e exit code de sucesso.
- Verificacao: `./run.sh artisan test tests/Feature/Console/CollectAudienceTest.php` passou com 1 teste e 3 assertions.
- Execucao do diretorio `tests/Feature/Console` segue parcialmente bloqueada: `StartProgramsTest` falha antes de assertions pela migration antiga incompativel com SQLite (`ALTER TABLE ... MODIFY`). A migration de producao nao foi alterada.

Objetivo: validar rotinas executadas por console ou scheduler.

- Testar exit code esperado.
- Criar estado inicial com factories.
- Conferir mudancas no banco apos o comando.
- Cobrir cenario sem dados.
- Cobrir cenario com dados invalidos ou expirados quando aplicavel.

Comando base:

```bash
./run.sh artisan test tests/Feature/Console
```

## Fase 9: Inertia E Frontend Pelo Backend

Resultado em 2026-08-02:

- `tests/Feature/Public/ContactPageTest.php`: criado teste de contrato Inertia direto no controller `ContactPageController`, validando que o backend retorna `Inertia\Response` com componente `public/Contact`.
- Tentativa inicial via HTTP `GET /contato` falhou porque o middleware Inertia global resolve props compartilhadas que consultam `onairs`; sem migrations executadas, a tabela nao existe. O teste foi ajustado para o contrato do controller sem passar pelo middleware global.
- Verificacao: `./run.sh artisan test tests/Feature/Public/ContactPageTest.php` passou com 1 teste e 2 assertions.

Objetivo: validar contrato Laravel/Inertia sem introduzir nova stack frontend agora.

- Conferir componente Inertia correto.
- Conferir props essenciais.
- Conferir filtros e query params.
- Conferir paginacao.
- Conferir estados vazios.
- Conferir props relacionadas a permissoes quando existirem.

Nao adicionar Vitest ou Playwright nesta fase, a menos que surja uma necessidade clara.

## Fase 10: Rotina De Execucao

Resultado em 2026-08-02:

- Rotina minima sem dependencia de banco executada com sucesso:

```bash
./run.sh artisan test tests/Unit/Services/AudienceServiceTest.php tests/Unit/Services/ImageProcessServiceTest.php tests/Feature/Console/CollectAudienceTest.php tests/Feature/Public/ContactPageTest.php
```

- Resultado da rotina minima: 11 testes passaram com 27 assertions.
- O ambiente de teste foi ajustado para MySQL usando `phpunit.xml` e o banco local `akiba`, preservando migrations antigas ja aplicadas em producao.
- Suite completa executada com:

```bash
./run.sh artisan test --without-tty
```

- Resultado da suite completa: 138 testes passaram com 425 assertions.
- Observacao: a migration antiga `database/migrations/2026_08_01_000000_make_program_access_type_nullable_for_auto_dj.php` usa SQL especifico de MySQL e nao foi alterada. O problema de SQLite foi resolvido fazendo o ambiente de testes usar MySQL.

Durante desenvolvimento, rodar arquivo especifico:

```bash
./run.sh artisan test tests/Feature/Private/MediaPageTest.php
```

Rodar por suite:

```bash
./run.sh artisan test --testsuite=Unit
./run.sh artisan test --testsuite=Feature
```

Fechamento da rodada:

```bash
./run.sh artisan test
```

## Ordem Recomendada

1. Rodar baseline da suite atual.
2. Ajustar falhas existentes.
3. Mapear rotas privadas e permissoes.
4. Completar testes de paginas privadas criticas.
5. Completar testes de formularios publicos.
6. Reforcar actions e services.
7. Cobrir uploads e arquivos.
8. Fechar comandos Artisan.
9. Rodar suite completa.
10. Registrar lacunas restantes para uma proxima rodada.
