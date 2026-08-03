---
status: rascunho
tipo: modulo
area: postagens
atualizado_em: 2026-08-03
---

# Postagens

## Objetivo

Gerenciar conteudos editoriais, como noticias, colunas, reviews e eventos publicados no site.

## Escopos Atuais

- Criacao de posts.
- Edicao de posts.
- Leitura individual no painel.
- Desativacao de posts.
- Comentarios, reacoes e curtidas no publico.
- Feed externo de noticias de anime.

## Funcionalidades

- Listar posts ativos com paginacao.
- Buscar posts por termo.
- Exibir posts com contagem de visualizacoes.
- Filtrar acesso de acordo com o usuario autenticado.
- Consultar fontes e itens do feed externo quando o usuario tiver permissao.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Post.svelte`.
- Controller de pagina: `PostPageController`.
- Controller de recurso: `PostController`.
- Invokes: `DeactivatePostController`.
- Service: `AnimeNewsFeedService`.
- Filter: `PostFilter`.
- Resource: `PostResource`.
- Models: `Post`, `PostTag`, `PostReference`, `PostReview`, `PostComment`, `PostReaction`, `PostLike`.

## Permissoes

- `post.module.view` controla acesso ao modulo no menu.
- `post.feed.view` controla acesso ao feed externo.
- Regras principais ficam em `PostPolicy`.

## Riscos

- O modelo `Post` concentra tipos diferentes de conteudo.
- Reviews, eventos e materias precisam ter regras claras para evitar campos opcionais confusos.
- Feed externo precisa de tratamento para indisponibilidade do servico.

## Planejamento

- [ ] Documentar tipos de conteudo suportados.
- [ ] Definir fluxo de revisao editorial.
- [ ] Mapear tags, referencias e extensoes de review.
- [ ] Registrar regras para conteudo publico versus privado.

## Referencias

- [dashboard](./dashboard)
- [marketing](./marketing)
