---
status: rascunho
tipo: mapa
atualizado_em: 2026-08-03
---

# Mapa de Modulos

## Painel Privado

| Modulo | Rota | Pagina Svelte | Controller de Pagina | Nota |
| --- | --- | --- | --- | --- |
| Dashboard | `/panel/dashboard` | `private/Dashboard` | `DashboardPageController` | [dashboard](./modulos/dashboard) |
| Materias | `/panel/post` | `private/Post` | `PostPageController` | [postagens](./modulos/postagens) |
| Locucao | `/panel/locution` | `private/Locution` | `LocutionPageController` | [locucao](./modulos/locucao) |
| Radio | `/panel/radio` | `private/Radio` | `RadioPageController` | [radio](./modulos/radio) |
| Podcasts | `/panel/podcast` | `private/Podcast` | `PodcastPageController` | [podcasts](./modulos/podcasts) |
| Marketing | `/panel/marketing` | `private/Marketing` | `RepositoryPageController` | [marketing](./modulos/marketing) |
| Midias | `/panel/media` | `private/Media` | `MediaPageController` | [midias](./modulos/midias) |
| Administracao | `/panel/administration` | `private/Administration` | `AdministrationPageController` | [administracao](./modulos/administracao) |
| Relatorios | `/panel/reports` | `private/Reports` | `ReportsPageController` | [relatorios](./modulos/relatorios) |
| Lixeira | `/panel/trash` | `private/Trash` | `TrashController` | [lixeira](./modulos/lixeira) |

## Padrao de Planejamento por Modulo

Cada modulo deve manter:

- objetivo;
- funcionalidades atuais;
- arquivos envolvidos;
- permissoes principais;
- pendencias;
- riscos;
- referencias para outros modulos.
