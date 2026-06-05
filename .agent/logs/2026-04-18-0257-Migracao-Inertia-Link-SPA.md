# 2026-04-18-0257-Migracao-Inertia-Link-SPA.md

## Contexto
Para garantir uma navegação fluida e sem recarregamentos parciais no painel administrativo (Single Page Application), todas as tags de link interno foram migradas de `<a>` para o componente `<Link>` do Inertia.js.

## Mudanças Realizadas

### Frontend (Svelte)
- Substituição em massa de tags `<a>` por `<Link>` nos principais componentes de navegação e listagem (Grids).
- Importação do componente `Link` de `@inertiajs/svelte` em todos os arquivos afetados.
- Preservação de tags `<a>` para links que utilizam `target="_blank"`, garantindo que visualizações externas continuem abrindo em novas abas sem interferência do Inertia.

### Arquivos Modificados
- `resources/js/ui/widgets/private/navbar/Navbar.svelte`
- `resources/js/ui/widgets/private/grid/UserGrid.svelte`
- `resources/js/ui/widgets/private/grid/PostGrid.svelte`
- `resources/js/ui/widgets/private/grid/ReviewGrid.svelte`
- `resources/js/ui/widgets/private/grid/PodcastGrid.svelte`
- `resources/js/ui/widgets/private/grid/EventGrid.svelte`
- `resources/js/ui/widgets/private/grid/RapidAccessGrid.svelte`
- `resources/js/ui/widgets/private/form/PostForm.svelte`
- `resources/js/ui/widgets/private/form/ReviewForm.svelte`
- `resources/js/ui/widgets/private/form/EventForm.svelte`

## Resultados Esperados
- Navegação instantânea entre as páginas do painel.
- Aparecimento da barra de progresso do Inertia durante as transições.
- Manutenção do estado da aplicação durante a navegação.

## Próximos Passos
- Monitorar eventuais comportamentos inesperados em links de download (se aplicável).
- Validar se a navegação SPA afeta o carregamento de charts ou scripts pesados em páginas específicas.
