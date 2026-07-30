# Regras De Actions

Escopo: tudo em `app/Actions`.

## Regra Principal

Actions concentram fluxos de escrita e operacoes de dominio chamadas por controllers, jobs ou outros servicos.

## Estrutura

- Mantenha actions organizadas por pasta de escopo, seguindo a estrutura atual de `app/Actions`.
- Nomeie cada action pela operacao e modulo, como `StorePodcast` ou `UpdateUser`.

## Responsabilidades

- Use actions para criacao, atualizacao, exclusao e operacoes encadeadas de dominio.
- Envolva escritas e operacoes encadeadas em `DB::transaction`.
- Passe dados e models ja conhecidos por parametros de metodo; evite consultar novamente models que o chamador ja possui.
- Mova trabalho extra, buscas auxiliares ou efeitos colaterais para metodos privados dentro da action.

## Organizacao Interna

- Mantenha imports ordenados como defaults/facades do Laravel, exceptions, models e depois services.

## Finalizacao

- Nao coloque validacao de payload em actions; isso pertence a `app/Http/Requests`.
- Nao coloque composicao de query de pagina em actions; isso pertence a `app/Filters`.
