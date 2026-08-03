---
status: rascunho
tipo: arquitetura
atualizado_em: 2026-08-03
---

# Arquitetura

Esta pagina resume a arquitetura geral do Akiba. Para exemplos de estrutura interna de cada arquivo, consulte [desenvolvimento/padroes-de-arquivos](./desenvolvimento/padroes-de-arquivos).

## Stack

- Backend: Laravel 12 com PHP 8.2.
- Frontend: Svelte com Inertia e Vite.
- Estilo: Tailwind CSS.
- Banco de dados: MySQL 8.
- Autenticacao: Laravel Sanctum e fluxo privado do painel.

## Estrutura Principal

- `app/Http/Controllers/Public`: controllers da area publica.
- `app/Http/Controllers/Private`: controllers do painel privado.
- `app/Http/Controllers/Private/Pages`: controllers que renderizam paginas Inertia.
- `app/Http/Controllers/Private/Invokes`: controllers de acoes pontuais.
- `app/Actions`: regras de negocio por modulo.
- `app/Filters`: filtros de consulta.
- `app/Http/Requests`: validacoes de entrada.
- `app/Http/Resources`: formato de retorno para dados enviados ao frontend.
- `resources/js/pages/private`: paginas privadas em Svelte.
- `resources/js/pages/public`: paginas publicas em Svelte.

## Padrao de Controllers

Controllers de renderizacao devem concentrar a composicao de props da pagina e delegar consultas para filters.

Controllers de CRUD devem ser finos:

- validar entrada via Request;
- autorizar operacao;
- chamar Action;
- retornar resposta Inertia ou redirect.

Controllers invocaveis devem representar acoes especificas que nao se encaixam em CRUD tradicional, como concluir tarefa, desativar item ou alterar status.

## Padrao de Actions

Actions devem concentrar mudancas de estado e regras de negocio. Quando uma operacao tocar multiplas tabelas ou tiver passos encadeados, deve usar transacao.

## Padrao de Filters

Filters devem receber parametros em array e compor queries com condicionais claras. O objetivo e manter controllers sem detalhes excessivos de consulta.

## Ordem de Implementacao

Ao criar ou alterar uma funcionalidade, siga esta ordem:

1. Rota em `routes/web` ou `routes/api.php`.
2. Controller de pagina, CRUD ou acao invocavel.
3. Request para validar entrada e checar autorizacao.
4. Action para executar regra de negocio.
5. Filter quando a tela precisar de listagem pesquisavel ou paginada.
6. Resource para formatar dados enviados ao frontend.
7. Pagina Svelte em `resources/js/pages`.
8. Componentes ou widgets reutilizaveis em `resources/js/lib`.

Essa ordem ajuda a manter cada arquivo com uma responsabilidade clara e facilita revisar PRs.
