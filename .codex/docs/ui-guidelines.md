# Guia de UI da Rede Akiba

Categoria: documentação permanente.

Este documento registra os padrões visuais e arquiteturais atuais do frontend. Ele deve ser consultado antes de criar ou alterar páginas, componentes e widgets.

## Stack e arquitetura

- Laravel fornece os dados e rotas.
- Inertia conecta o backend às páginas Svelte.
- Svelte implementa páginas, layouts e componentes.
- Tailwind CSS 4 concentra a maior parte dos estilos.
- O alias `@` aponta para `resources/js`.

Estrutura principal:

```text
resources/js/
├── pages/
│   ├── public/
│   ├── private/
│   └── provisory/
├── ui/
│   ├── layouts/
│   ├── components/
│   │   ├── public/
│   │   └── private/
│   └── widgets/
│       ├── public/
│       └── private/
├── store/
├── data/
├── utils/
└── css/
```

Use:

- `pages` para composição de telas e conexão com os dados da página.
- `layouts` para estrutura compartilhada, navegação, rodapé e polling global.
- `components` para peças pequenas e reutilizáveis.
- `widgets` para blocos de domínio, como grids, formulários, players e métricas.
- `utils` para regras sem apresentação.
- `store` somente para estado realmente compartilhado entre componentes.

## Identidade visual

A identidade da Akiba combina fundo escuro, azul vibrante, laranja e tipografia editorial.

### Cores principais

Use os tokens definidos em `resources/js/css/app.css`. Não replique códigos hexadecimais nos componentes.

- `blue-night`: fundo mais escuro.
- `blue-marinho`: fundo estrutural do painel.
- `blue-ocean`: superfícies e cards.
- `blue-skywave`: ações e destaques primários.
- `orange-citric`: ações e destaques fortes.
- `orange-amber`: títulos e informações editoriais.
- `suspense-aurora`: textos claros e superfícies claras.
- `neutral-gray`: conteúdo secundário.
- `green-forest` e `green-mint`: sucesso e estados ao vivo.
- `red-crimson`: perigo, atraso e ações destrutivas.
- `purple-mystic`: reviews e categorias especiais.

Gradientes e filtros de ícones também devem vir das utilities existentes em `app.css`.

### Tipografia

A fonte principal é Noto Sans:

```html
font-noto-sans
```

O vocabulário visual mais característico usa:

- Caixa alta.
- Itálico.
- `font-extrabold` ou `font-black`.
- Azul, laranja ou branco aurora.

Use essa combinação em títulos, badges, ações importantes e informações editoriais. Textos auxiliares e conteúdo longo devem ser mais leves para manter a hierarquia.

## Site público

O site público possui linguagem mais expressiva e editorial:

- Fundos escuros.
- Imagens grandes.
- Personagens e elementos decorativos.
- Cards de matérias, reviews e destaques.
- Alto contraste entre azul, laranja e branco.
- Player como elemento visual central.

Componentes públicos ficam em:

```text
resources/js/ui/components/public
resources/js/ui/widgets/public
```

Use `Section.svelte` para blocos editoriais com título sempre que o formato existente atender à necessidade.

### Componentes-base públicos

Os componentes públicos reutilizáveis seguem a mesma organização e API por variantes usada no painel:

```text
components/public/
├── actions/
│   ├── Button.svelte
│   └── IconButton.svelte
├── feedback/
│   ├── AuthGuard.svelte
│   ├── Badge.svelte
│   ├── FlashToaster.svelte
│   ├── LoadingSpinner.svelte
│   └── StatusMessage.svelte
├── forms/
│   ├── CheckboxInput.svelte
│   ├── FormField.svelte
│   ├── RadioInput.svelte
│   ├── SelectInput.svelte
│   ├── TextArea.svelte
│   └── TextInput.svelte
├── layout/
│   ├── GridList.svelte
│   ├── PageHeader.svelte
│   ├── Section.svelte
│   ├── SectionDivider.svelte
│   ├── SectionHeader.svelte
│   └── Surface.svelte
├── navigation/
│   ├── Carousel.svelte
│   ├── Pagination.svelte
│   └── Tabs.svelte
└── overlays/
    ├── CustomModal.svelte
    ├── Modal.svelte
    └── Tooltip.svelte
```

Todos os componentes novos são exportados por `components/public/index.js`:

```svelte
import {
    Button,
    GridList,
    PageHeader,
    TextInput,
} from "@/ui/components/public";
```

Convenções da API pública:

- `variant` define função visual ou superfície.
- `size` define dimensões previstas pelo design system.
- `class` permite ajustes locais sem perder a base.
- `loading` e `disabled` representam estados de ação.
- Inputs usam `bind:value`.
- Checkbox e radio mantêm suporte a binding.
- Componentes de layout aceitam slots para conteúdo.
- Ícones decorativos permanecem ocultos de leitores de tela.

## Painel privado

O painel administrativo é mais modular e orientado a produtividade:

- Sections com título, linha horizontal e ações.
- Grids responsivos.
- Cards compactos de status.
- Formulários em página, modal ou offcanvas.
- Navegação e ações condicionadas por permissões.
- Barra inferior fixa com métricas da transmissão.

Antes de criar uma nova peça, verifique os componentes existentes:

- `Button`
- `IconButton`
- `Badge`
- `FormField`
- `TextInput`
- `TextArea`
- `SelectInput`
- `CheckboxInput`
- `RadioInput`
- `Section`
- `GridList`
- `EmptyState`
- `LoadingSpinner`
- `Modal`
- `Offcanvas`
- `Tooltip`
- `Carousel`
- `Pagination`

Prefira variantes e propriedades desses componentes a repetir grandes conjuntos de classes.

## Responsividade

Trabalhe em mobile-first e evolua progressivamente com:

```text
sm → md → lg → xl → 2xl
```

Containers globais:

```css
.container-page
.container-player
```

- `container-page` é o container geral de páginas e sections.
- `container-player` é o container mais estreito usado pelo player.

Regras gerais:

- Evite larguras fixas que causem overflow em telas pequenas.
- Use `min-w-0` em filhos de flex e grid que contenham textos.
- Use `truncate` ou `line-clamp-*` para conteúdo variável.
- Imagens devem usar `object-cover` ou `object-contain` conforme a intenção.
- Overlays devem considerar `dvh` e safe areas.
- Não esconda funcionalidade essencial apenas em hover.
- Respeite `prefers-reduced-motion` em animações relevantes.

O player público possui apresentações distintas:

- `MainPlayer.svelte` para desktop.
- `MobilePlayer.svelte` para celular e tablet.

As duas versões devem compartilhar comportamento e estado, mesmo quando a apresentação for diferente.

## Estado e dados

Widgets existentes normalmente consomem dados globais do Inertia:

```svelte
import { page } from "@inertiajs/svelte";

$: ({ dados } = $page.props);
```

Não copie dados de `$page.props` para um store sem necessidade. Use store quando houver estado de interface ou comportamento realmente compartilhado, como o áudio global.

Quando uma ação depender de dados usados por vários componentes:

1. Prefira realizar a integração uma vez na página ou layout pai.
2. Evite repetir a mesma sincronização em versões desktop e mobile.
3. Evite criar uma segunda requisição se os dados já chegam pelo Inertia.
4. Use `usePoll()` somente quando a tela realmente precisar de atualização periódica.

O player store deve permanecer responsável por comportamento do áudio, como:

- Play e pause.
- Estado real da reprodução.
- Loading e erro.
- Volume e mute.
- Integração com Media Session.

Dados editoriais de programa, locutor e música devem continuar vindo dos props da página, salvo mudança arquitetural explícita.

## Permissões

Navegação e ações privadas devem respeitar os utilitários de permissão existentes.

Não renderize uma ação sem verificar a permissão correspondente. Prefira utilizar os helpers em `resources/js/utils/access/permissions.js` em vez de duplicar regras nos componentes.

## Imagens e ícones

- Reutilize os SVGs existentes em `public/svg`.
- Use os filtros de cor definidos em `app.css`.
- Ícones decorativos devem ter `alt=""` e `aria-hidden="true"`.
- Imagens informativas devem ter um `alt` descritivo.
- Use `resolvePlaceholderImage()` para imagens que possam estar ausentes.
- Evite dependências externas para imagens permanentes da interface.

## Acessibilidade

Ao criar ou alterar UI:

- Use elementos semânticos.
- Toda ação deve ser um `button` ou `Link`, conforme o comportamento.
- Botões apenas com ícone precisam de `aria-label`.
- Campos precisam de label associado.
- Erros devem usar `aria-invalid` e `aria-describedby`.
- Modais devem usar `role="dialog"` e `aria-modal="true"`.
- Conteúdo apenas visual deve ser ocultado de leitores de tela.
- Preserve foco visível e navegação por teclado.
- Novos overlays devem considerar Escape, foco inicial, retenção de foco e restauração ao fechar.

## Feedback e estados

Toda interface baseada em dados deve considerar:

- Loading.
- Erro.
- Sucesso.
- Ação desabilitada.

Na UI pública, blocos sem dados não devem ser renderizados. No painel privado, use `EmptyState` para comunicar coleções vazias. Use `LoadingSpinner`, `FlashToaster` e estados dos componentes-base antes de implementar soluções locais.

Para ações assíncronas:

- Evite envio duplicado.
- Use `disabled` e `aria-busy`.
- Mostre uma resposta visual compatível com a importância da ação.

## Convenções para novas alterações

Antes de implementar:

1. Identifique se a mudança pertence ao público ou ao privado.
2. Procure um componente-base existente.
3. Reutilize tokens, containers e utilities do projeto.
4. Confirme a origem dos dados e evite estados duplicados.
5. Verifique permissões.
6. Projete mobile-first.
7. Inclua estados de loading, vazio, erro e disabled quando aplicável.
8. Revise acessibilidade e conteúdo variável.

Evite:

- Cores hexadecimais locais quando existir token.
- Repetir componentes apenas para pequenas diferenças visuais.
- Colocar regras de negócio dentro do markup.
- Fazer polling ou requisições duplicadas.
- Copiar props para stores sem benefício claro.
- Criar controles dependentes apenas de hover.
- Introduzir outro padrão visual sem necessidade.

## Pontos conhecidos

Estes pontos fazem parte do estado atual do projeto e devem ser considerados em trabalhos futuros:

- O seletor de tema da navbar pública altera o estado visual do controle, mas ainda não aplica um tema global.
- A `CalendarGrid` pública ainda não possui implementação relevante.
- Existem versões públicas e privadas separadas de `Section` e `Modal`.
- Alguns widgets antigos concentram muitas classes, enquanto componentes mais novos usam variantes reutilizáveis.
- O modal privado pode evoluir no controle reativo de overflow e foco.
- A página provisória atualiza `onair` e `stream`; o layout público atualiza periodicamente apenas `onair`.
- Há pequenas inconsistências históricas em rotas e nomenclaturas.

Esses pontos não devem ser corrigidos incidentalmente durante tarefas sem relação. Preserve o escopo de cada alteração.
