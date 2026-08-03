---
status: rascunho
tipo: modulo
area: midias
atualizado_em: 2026-08-03
---

# Midias

## Objetivo

Gerenciar conteudos interativos e visuais da area publica.

## Escopos Atuais

- Galeria de ouvintes.
- Enquetes.
- Votos em enquetes.
- Desativacao de enquetes.

## Funcionalidades

- Listar enquetes ativas.
- Exibir enquete aberta mais recente.
- Carregar opcoes, votos e usuarios dos votos.
- Criar e editar enquetes.
- Votar em opcoes.
- Desativar enquetes.
- Listar galerias de ouvintes com paginacao.
- Criar, editar e remover itens de galeria.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Media.svelte`.
- Controller de pagina: `MediaPageController`.
- Controllers de recurso: `PollController`, `ListenerGalleryController`.
- Invokes: `DeactivatePollController`, `PollVoteController`.
- Filters: `PollFilter`, `ListenerGalleryFilter`.
- Models: `Poll`, `PollOption`, `PollVote`, `ListenerGallery`.

## Permissoes

- `media.module.view` controla acesso ao modulo no menu e na rota.
- Regras de enquetes ficam em `PollPolicy` e `PollOptionPolicy`.
- Regras de galeria ficam em `ListenerGalleryPolicy`.

## Riscos

- Enquetes abertas precisam ter regra clara de expiracao.
- Votos precisam evitar duplicidade quando aplicavel.
- Galerias podem crescer em volume e exigir criterios melhores de organizacao.

## Planejamento

- [ ] Documentar regras de galerias.
- [ ] Definir ciclo de vida das enquetes.
- [ ] Mapear regras de voto.
- [ ] Registrar o que deve aparecer na area publica.

## Referencias

- [radio](./radio)
