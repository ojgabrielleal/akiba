---
status: rascunho
tipo: modulo
area: podcasts
atualizado_em: 2026-08-03
---

# Podcasts

## Objetivo

Gerenciar episodios e publicacao de podcasts.

## Escopos Atuais

- Criacao de podcast.
- Edicao de podcast.
- Visualizacao individual no painel.
- Desativacao de podcast.
- Listagem publica de podcasts.

## Funcionalidades

- Listar podcasts ativos com autor.
- Paginar episodios.
- Criar episodio.
- Editar episodio.
- Abrir episodio no painel.
- Desativar episodio.

## Arquivos Envolvidos

- Pagina: `resources/js/pages/private/Podcast.svelte`.
- Controller de pagina: `PodcastPageController`.
- Controller de recurso: `PodcastController`.
- Invokes: `DeactivatePodcastController`.
- Actions: `StorePodcastAction`, `UpdatePodcastAction`, `DeactivatePodcastAction`.
- Filter: `PodcastFilter`.
- Model: `Podcast`.

## Permissoes

- `podcast.module.view` controla acesso ao modulo no menu.
- Regras principais ficam em `PodcastPolicy`.

## Riscos

- Temporada e episodio precisam ter regra de ordenacao clara.
- Uploads ou links de midia precisam de validacao consistente.

## Planejamento

- [ ] Documentar campos obrigatorios.
- [ ] Definir fluxo de publicacao.
- [ ] Registrar regras de capa, audio e descricao.

## Referencias

- [postagens](./postagens)
