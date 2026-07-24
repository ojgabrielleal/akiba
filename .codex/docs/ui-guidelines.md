# Guia de UI

Antes de alterar UI:

1. Reutilize componentes, variantes, tokens e utilities existentes.
2. Preserve a estrutura entre `pages`, `layouts`, `components` e `widgets`.
3. Implemente mobile-first e respeite `prefers-reduced-motion`.
4. Não use cores hardcoded nem duplique dados, requisições ou regras de permissão.
5. Use props do Inertia diretamente; stores apenas para estado realmente compartilhado.
6. Considere acessibilidade, conteúdo variável e estados de loading, vazio, erro e disabled.
7. Exporte componentes públicos pelo `index.js`.
8. Não corrija problemas fora do escopo da tarefa.

Consulte quando necessário:

* Arquitetura: `.codex/docs/ui/architecture.md`
* Componentes: `.codex/docs/ui/components.md`
* Design visual: `.codex/docs/ui/design-system.md`
* Responsividade e acessibilidade: `.codex/docs/ui/quality.md`
* Player e dados: `.codex/docs/ui/player.md`
* Pendências conhecidas: `.codex/docs/ui/known-issues.md`
