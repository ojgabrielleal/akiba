# Plano De Documentacao

## Objetivo

Criar uma documentacao completa, simples e bilingue para o projeto Akiba, com versoes em PT-BR e EN-US.

A documentacao deve explicar o projeto de forma clara para pessoas tecnicas e tambem para pessoas leigas. Uma pessoa sem experiencia com Laravel, Docker ou testes deve conseguir entender o que o projeto faz, como rodar localmente e onde procurar cada coisa.

## Idiomas

Toda documentacao principal deve ter duas versoes:

- PT-BR: portugues do Brasil.
- EN-US: ingles dos Estados Unidos.

Regra:

- As duas versoes devem ter a mesma estrutura.
- As duas versoes devem explicar os mesmos assuntos.
- A versao em ingles nao deve ser apenas traducao literal se isso deixar o texto estranho; ela deve soar natural.
- A versao em portugues deve ser a referencia inicial quando houver duvida de dominio.

## Publico-Alvo

Escrever pensando em quatro tipos de leitor:

- Pessoa leiga: quer entender o que e o projeto e como usar.
- Pessoa desenvolvedora nova: quer instalar, rodar, testar e contribuir.
- Pessoa mantenedora: quer entender arquitetura, decisoes e processos.
- Pessoa de produto/conteudo: quer entender fluxos, modulos e responsabilidades.

## Principios De Escrita

- Usar linguagem simples.
- Explicar siglas na primeira vez que aparecerem.
- Evitar jargao sem explicacao.
- Dar exemplos concretos.
- Preferir passos numerados quando houver procedimento.
- Separar "o que e", "por que existe" e "como usar".
- Avisar claramente quando um comando altera ou apaga dados.
- Manter tom direto, acolhedor e pratico.

## Estrutura Recomendada

Criar uma pasta `docs/` com subpastas por idioma:

```text
docs/
  pt-BR/
    README.md
    instalacao.md
    ambiente.md
    comandos.md
    testes.md
    arquitetura.md
    backend.md
    frontend.md
    banco-de-dados.md
    permissoes.md
    formularios.md
    radio.md
    deploy.md
    troubleshooting.md
  en-US/
    README.md
    installation.md
    environment.md
    commands.md
    tests.md
    architecture.md
    backend.md
    frontend.md
    database.md
    permissions.md
    forms.md
    radio.md
    deployment.md
    troubleshooting.md
```

Atualizar o `README.md` da raiz para apontar para os dois idiomas.

## Fase 1: Indice E Visao Geral

Objetivo: criar a porta de entrada da documentacao.

Arquivos:

- `docs/pt-BR/README.md`
- `docs/en-US/README.md`
- `README.md` da raiz

Conteudo:

- O que e o Akiba.
- Para quem o projeto existe.
- Quais tecnologias usa.
- Links para instalacao, comandos, testes e arquitetura.
- Aviso sobre ambiente local e banco de dados.

## Fase 2: Instalacao Local

Objetivo: explicar como uma pessoa roda o projeto do zero.

Arquivos:

- `docs/pt-BR/instalacao.md`
- `docs/en-US/installation.md`

Conteudo:

- Requisitos: Docker, Docker Compose, Git.
- Como clonar o projeto.
- Como preparar `.env`.
- Como rodar `./run.sh install`.
- Como subir com `./run.sh up`.
- Onde acessar site, Vite e phpMyAdmin.
- Usuario/senha padrao se existir.
- Problemas comuns de porta ocupada, permissao e container parado.

Importante:

- Explicar que alguns comandos podem apagar e recriar o banco local.

## Fase 3: Ambiente E Comandos

Objetivo: documentar a rotina diaria.

Arquivos:

- `docs/pt-BR/ambiente.md`
- `docs/en-US/environment.md`
- `docs/pt-BR/comandos.md`
- `docs/en-US/commands.md`

Conteudo:

- O que cada container faz.
- Como usar `./run.sh`.
- Como executar Artisan.
- Como executar Composer.
- Como executar NPM/Node.
- Como parar e reiniciar containers.
- Quando nao rodar build automaticamente.

## Fase 4: Testes

Objetivo: explicar como a suite funciona e como adicionar novos testes.

Arquivos:

- `docs/pt-BR/testes.md`
- `docs/en-US/tests.md`

Conteudo:

- Tipos de teste: Unit, Feature, UI futura.
- Como rodar a suite Laravel.
- Como rodar um arquivo especifico.
- Como o banco de testes funciona.
- Por que os testes usam MySQL local em vez de SQLite em memoria.
- Como usar factories.
- Quando usar `RefreshDatabase`.
- Como testar paginas Inertia.
- Regra importante: nao alterar migrations antigas de producao para resolver teste.

## Fase 5: Arquitetura

Objetivo: explicar a organizacao tecnica do projeto sem complicar.

Arquivos:

- `docs/pt-BR/arquitetura.md`
- `docs/en-US/architecture.md`

Conteudo:

- Laravel como backend.
- Svelte com Inertia como frontend.
- Como uma requisicao sai do navegador e chega ao controller.
- Como controllers, requests, actions, filters, models e resources se relacionam.
- Onde ficam rotas publicas e privadas.
- Como permissoes protegem o painel.

Usar diagramas simples em texto quando ajudar.

## Fase 6: Backend

Objetivo: orientar manutencao do backend.

Arquivos:

- `docs/pt-BR/backend.md`
- `docs/en-US/backend.md`

Conteudo:

- Controllers.
- Requests.
- Actions.
- Policies.
- Filters.
- Resources.
- Services externos.
- Jobs/comandos se aplicavel.
- Padroes de nome e organizacao.

## Fase 7: Frontend

Objetivo: explicar como o frontend Inertia/Svelte funciona.

Arquivos:

- `docs/pt-BR/frontend.md`
- `docs/en-US/frontend.md`

Conteudo:

- Onde ficam paginas Svelte.
- Como props chegam do Laravel.
- Como formularios devem ser enviados.
- Como lidar com erros de validacao.
- Como organizar componentes.
- Como usar Vite.

## Fase 8: Banco De Dados

Objetivo: documentar schema, migrations e cuidados.

Arquivos:

- `docs/pt-BR/banco-de-dados.md`
- `docs/en-US/database.md`

Conteudo:

- Banco principal local `akiba`.
- Migrations.
- Seeders.
- Factories.
- Regra de nao alterar migrations antigas ja aplicadas em producao.
- Como criar migration nova para corrigir evolucao de schema.
- Como recriar banco local.

## Fase 9: Modulos Do Produto

Objetivo: documentar as partes principais da aplicacao do ponto de vista de usuario e manutencao.

Arquivos:

- `docs/pt-BR/permissoes.md` e `docs/en-US/permissions.md`
- `docs/pt-BR/formularios.md` e `docs/en-US/forms.md`
- `docs/pt-BR/radio.md` e `docs/en-US/radio.md`

Conteudo:

- O que o modulo faz.
- Quem usa.
- Quais telas existem.
- Quais permissoes importam.
- Quais models/tabelas sustentam o modulo.
- Fluxos comuns.
- Pontos de cuidado.

## Fase 10: Deploy E Operacao

Objetivo: documentar como publicar e manter o sistema.

Arquivos:

- `docs/pt-BR/deploy.md`
- `docs/en-US/deployment.md`

Conteudo:

- Variaveis de ambiente importantes.
- Banco de producao.
- Migrations em producao.
- Build frontend.
- Storage.
- Cache/config.
- Cuidados antes de deploy.
- Checklist simples.

## Fase 11: Troubleshooting

Objetivo: ajudar pessoas a resolver problemas comuns.

Arquivos:

- `docs/pt-BR/troubleshooting.md`
- `docs/en-US/troubleshooting.md`

Conteudo:

- Container nao sobe.
- Porta ocupada.
- Banco inacessivel.
- Erro de migration.
- Vite nao carrega.
- Permissao de arquivo.
- Teste falhando por banco vazio.
- Login nao funciona.

Formato recomendado:

```text
Problema:
O que significa:
Como resolver:
Como confirmar que resolveu:
```

## Criterios De Pronto

- Existe documentacao em PT-BR e EN-US.
- O README raiz aponta para os dois idiomas.
- Uma pessoa nova consegue instalar e rodar seguindo a documentacao.
- Uma pessoa leiga consegue entender o objetivo do projeto.
- Testes, banco e migrations estao explicados com alertas claros.
- Os comandos perigosos indicam que podem alterar ou apagar dados.
- A documentacao usa exemplos reais do projeto.

## Ordem Recomendada

1. Criar estrutura `docs/pt-BR` e `docs/en-US`.
2. Escrever README de cada idioma.
3. Documentar instalacao e comandos.
4. Documentar testes e banco.
5. Documentar arquitetura.
6. Documentar backend e frontend.
7. Documentar modulos principais.
8. Documentar deploy e troubleshooting.
9. Revisar PT-BR para clareza.
10. Criar/revisar EN-US com a mesma estrutura.
