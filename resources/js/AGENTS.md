# Front-end Rules

Escopo: tudo em `resources/js`.

Atente-se às regras, entenda o contexto da tarefa e leia apenas as seções necessárias.

## Stack

Leia se a tarefa envolver setup, build, imports globais ou dependências.

- Svelte + Inertia (`@inertiajs/svelte`) + Vite.
- Tailwind CSS v4 com tokens em `css/app.css`.
- Use imports internos com alias `@/`.

## Estrutura

Leia se a tarefa envolver criar, mover ou localizar arquivos.

- `pages/`: páginas Inertia; apenas orquestram layout, dados e widgets.
- `ui/layouts/{private,public}`: layouts por contexto.
- `ui/components/{private,public}`: peças reutilizáveis pequenas.
- `ui/widgets/{private,public}`: blocos de produto, como forms, grids, navbar, player e tabelas.
- `store/`: estado compartilhado.
- `utils/`: helpers puros por domínio.
- `data/`: opções/configurações estáticas.
- `config/`: itens globais, como `Meta`.

## Regras

Leia se a tarefa envolver criar ou alterar componentes/widgets reutilizáveis.

- Preserve a separação `private`/`public`.
- Antes de criar algo novo, procure componente/widget parecido.
- Componentes reutilizáveis usam `export let`, slots quando fizer sentido e `class` via alias quando precisarem aceitar extensão visual.
- Variantes visuais devem ficar em mapas internos (`variants`, `sizes`, `shapes`) com fallback padrão.
- Inputs/botões reutilizáveis devem repassar `{...$$restProps}` quando apropriado.
- Atualize o `index.js` do diretório ao criar componente/widget reutilizável.

## Páginas e Forms

Leia se a tarefa envolver página Inertia, `$page.props`, formulário ou upload.

- Páginas importam `Meta`, `Layout` do contexto correto e widgets.
- Leia dados via `$page.props`; use `$:` quando depender de atualização.
- Forms grandes pertencem a `ui/widgets/.../form`, não diretamente em `pages/`.
- Use `useForm`; passe erros para os campos; use `bind:value={$form.campo}`.
- Uploads devem usar `forceFormData: true`.
- Normalize arrays/objetos opcionais antes de montar o form.

## Estilo

Leia se a tarefa envolver UI, classes Tailwind, cores, responsividade ou design.

- Use tokens existentes: `blue-marinho`, `blue-skywave`, `orange-amber`, `suspense-aurora`, etc.
- Novas cores, gradientes ou filtros devem entrar em `css/app.css` antes de serem usados.
- Use `font-noto-sans`.
- Hover/focus de item clicável deve usar `orange-citric` no texto, indicador e ícone quando houver.
- Prefira `Section`, `GridList`, `Surface`, `Badge`, `Button`, `IconButton`, `Modal`, `Tooltip` e inputs existentes.
- Cards/listas clicáveis com texto devem usar microinteração padronizada: `transition duration-300 ease-out`, um leve `hover:-translate-y-0.5`, foco acessível com ring e `motion-reduce`.
- Blocos que forem só imagem/visual devem usar apenas scale leve animado, como `hover:scale-[1.02]`, sem brilho ou deslocamento vertical.
- Mantenha telas responsivas com classes explícitas (`grid-cols-1`, `lg:*`, `min-w-0`, `overflow-x-clip`).

## Finalização

Leia antes de concluir mudanças em `resources/js`.

- Não altere fora de `resources/js` sem necessidade explícita.
