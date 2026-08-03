---
status: ativo
tipo: registro-de-decisoes
atualizado_em: 2026-08-03
---

# Decisoes

## Documentacao

- A documentacao de planejamento sera mantida em Markdown puro.
- Os arquivos devem ser compatíveis com VitePress, usando links Markdown relativos no formato `[texto](./pagina)`.
- A documentacao deve ficar versionada no repositorio em `docs/planejamento/`.
- A primeira fase nao altera o software; apenas organiza o planejamento.

## Organizacao Backend

- Controllers de pagina ficam em `app/Http/Controllers/Private/Pages`.
- Controllers invocaveis para operacoes especificas ficam em `app/Http/Controllers/Private/Invokes`.
- Controllers de CRUD ficam na raiz do escopo privado, como `TaskController`, `PostController` e `ProgramController`.
- Operacoes de `store`, `update` e `delete` devem delegar regra de negocio para Actions.
- Validacoes de formulario devem usar Form Requests dedicados.
- Metodos privados devem ter chamadas de autorizacao, exceto quando a autorizacao estiver no Form Request.

## Organizacao Frontend

- Paginas privadas ficam em `resources/js/pages/private`.
- Widgets reutilizaveis privados ficam em `resources/js/lib/widgets/private`.
- Layout privado fica em `resources/js/lib/layouts/private`.
- A navegacao privada atual e definida em `resources/js/lib/constants/default/navbar.json`.

## Organizacao de Consultas

- Filters ficam em `app/Filters`.
- Cada filter deve representar um modelo ou escopo claro.
- Filtros devem receber array `filters`.
- Consultas condicionais devem preferir `when` do Eloquent.

