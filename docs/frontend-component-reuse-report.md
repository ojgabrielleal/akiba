# Relatório de reutilização de componentes Svelte

## Escopo da análise

A análise cobriu as páginas, layouts, componentes e widgets em `resources/js`, com foco em repetição estrutural, tamanho dos arquivos, responsabilidades acumuladas e oportunidades de composição.

O frontend foi validado com `npm run build` no container Node. O build terminou com sucesso, transformando 1.078 módulos.

Inventário atual:

- 9.285 linhas entre arquivos Svelte e JavaScript;
- 21 formulários privados;
- 15 grids privados;
- 3 carrosséis privados;
- 2 players públicos com mais de 300 linhas cada;
- `ProgramForm.svelte` é o maior componente, com 470 linhas.

## Resultado principal: componentes básicos reutilizáveis

Esta é a camada que deve ser trabalhada primeiro. A interface repete diretamente botões, labels, inputs e controles com a mesma marcação e as mesmas classes.

### 1. `Button.svelte`

Há vários botões de envio repetidos com as mesmas características: fonte em negrito e itálico, texto em caixa alta, fundo azul ou laranja, padding e bordas arredondadas.

API sugerida:

```svelte
<Button variant="primary" type="submit">Salvar</Button>
<Button variant="secondary">Atualizar</Button>
<Button variant="danger">Desativar</Button>
<Button variant="outline">Cancelar</Button>
```

Variantes iniciais suficientes:

- `primary`: azul com texto claro;
- `accent`: laranja com texto escuro;
- `danger`: vermelho;
- `outline`: transparente com borda;
- estados `disabled` e `loading`.

Não deve aceitar classes completas para definir cada aparência, pois isso apenas deslocaria a duplicação para quem usa o componente.

### 2. `IconButton.svelte`

Os grids repetem botões quadrados de editar, excluir, desativar e visualizar. Eles normalmente possuem as mesmas dimensões, fundo, ícone, tooltip e `aria-label`.

API sugerida:

```svelte
<IconButton icon="/svg/edit.svg" label="Atualizar" on:click={edit} />
<IconButton icon="/svg/trash.svg" label="Desativar" tone="danger" on:click={remove} />
```

O componente pode compor o `Tooltip` já existente e oferecer modo de botão ou link.

### 3. `FormField.svelte`

Foram encontradas 47 ocorrências do mesmo estilo de label e container de campo nos formulários privados. Além do visual, repetem-se texto de ajuda, espaçamento e futura posição de erro.

API sugerida:

```svelte
<FormField label="Nome" for="name" help="Nome exibido no painel" error={errors.name}>
    <TextInput id="name" bind:value={$form.name} />
</FormField>
```

Responsabilidades:

- renderizar `label` associado ao controle;
- texto auxiliar;
- mensagem de validação;
- espaçamento consistente;
- estado obrigatório, sem duplicar a regra de validação.

### 4. `TextInput.svelte`

Há pelo menos 30 repetições exatas do estilo principal de input administrativo, além de versões desabilitadas. Os tipos usados incluem texto, URL, data, hora, senha e número.

API sugerida:

```svelte
<TextInput type="text" bind:value={$form.name} />
<TextInput type="date" bind:value={$form.birthday} />
<TextInput type="password" bind:value={$form.password} />
```

O mesmo componente pode suportar os tipos nativos. Devem existir apenas variantes visuais claras, por exemplo `light` para offcanvas e `dark` para formulários sobre o fundo do painel.

### 5. `SelectInput.svelte`

Os selects repetem altura, fundo, borda, fonte e padding em formulários como programa, perfil, post, evento e administração.

O componente deve estilizar o `<select>` e preservar um slot para as `<option>`, sem receber arrays ou conhecer modelos do domínio.

```svelte
<SelectInput id="role" bind:value={$form.role}>
    {#each roles as role}
        <option value={role.id}>{role.label}</option>
    {/each}
</SelectInput>
```

### 6. `TextArea.svelte`

Biografia, descrições e outros textos longos repetem a mesma base visual de input. Um componente pequeno elimina classes duplicadas e mantém `disabled`, `required`, erro e foco consistentes.

### 7. `Checkbox.svelte` e `Radio.svelte`

Checkboxes e radios aparecem menos que inputs de texto, mas repetem controle, label clicável e alinhamento. Vale extrair depois dos componentes anteriores.

Não é necessário um único `ChoiceInput` excessivamente configurável. Dois componentes simples deixam a marcação e a acessibilidade mais claras.

### 8. `StatusBadge.svelte`

Status de publicação, programa, tarefa, locução e usuário usam cápsulas ou pequenos blocos coloridos com texto em caixa alta. A regra que escolhe o status continua nos helpers do domínio; o componente recebe apenas `tone` e conteúdo.

```svelte
<StatusBadge tone="success">Publicado</StatusBadge>
```

### 9. `FormActions.svelte`

Muitos formulários terminam com a mesma área centralizada contendo Salvar, Atualizar ou Cancelar. Esse componente pode organizar os botões sem decidir qual operação será executada.

### Ordem recomendada para os componentes básicos

1. Criar `Button` e `IconButton`.
2. Criar `FormField` e `TextInput`.
3. Criar `SelectInput` e `TextArea`.
4. Migrar dois formulários pequenos para validar a API.
5. Migrar dois grids para validar `IconButton`.
6. Criar `Checkbox`, `Radio`, `StatusBadge` e `FormActions` somente após confirmar repetição real durante as migrações.

Os componentes devem encapsular classes e acessibilidade, mas continuar aceitando atributos nativos como `type`, `name`, `required`, `disabled`, `value` e eventos.

## Prioridade alta

### 1. Campos editoriais compartilhados

Arquivos principais:

- `form/PostForm.svelte`
- `form/EventForm.svelte`
- `form/ReviewForm.svelte`

Os três repetem blocos de título, capa, imagem, conteúdo com WYSIWYG, tags, referências e normalização dos arrays. A repetição é extensa e representa regras do mesmo domínio editorial.

Extrações recomendadas:

- `EditorialContentFields.svelte`: título, conteúdo e imagem/capa;
- `TagFields.svelte`: coleção fixa ou configurável de tags;
- `ReferenceFields.svelte`: nome e URL das fontes;
- helpers JavaScript para `normalizeTags` e `normalizeReferences`.

Os formulários devem continuar responsáveis pelo payload específico de matéria, evento ou review. Não é recomendado criar um único `PostForm` configurável com muitas flags.

### 2. Ações de status de Post e Poll

Arquivos:

- `form/actions/PostActions.svelte`
- `form/actions/PollActions.svelte`

Os componentes têm o mesmo fluxo de estados (`draft`, `revision`, aprovação e publicação). As diferenças são apenas classes e textos curtos.

Extração recomendada:

- `PublicationStatusActions.svelte`, recebendo `status`, `can`, variante visual e labels opcionais.

É uma extração pequena, de baixo risco e com regra comportamental claramente compartilhada.

### 3. Botões de ação de entidades

Os grids de posts, podcasts, programas, enquetes, galeria, ranking e usuários repetem botões com tooltip, ícone, `aria-label`, permissão e estilos para editar/desativar.

Extrações recomendadas:

- `IconAction.svelte`: botão ou link com tooltip, ícone e rótulo acessível;
- `EntityActions.svelte`: composição opcional de editar e desativar sobre `IconAction`.

O componente deve cuidar apenas da apresentação e do evento. URLs, permissões e operações Inertia continuam no widget de domínio.

### 4. Infraestrutura de paginação

Arquivos:

- `components/private/ButtonPagination.svelte`
- `components/private/InfinitePagination.svelte`

Ambos repetem a descoberta da próxima página, controle de carregamento, chamada `router.visit`, `only`, concatenação de dados e spinner.

Extrações recomendadas:

- helper/controlador `createInertiaPagination.js` para a navegação e concatenação;
- `LoadingSpinner.svelte` para o indicador visual.

Os dois componentes visuais devem permanecer separados: um é acionado por botão e o outro por `IntersectionObserver`.

## Prioridade média

### 5. Primitivos de formulário administrativo

Foram encontradas 47 repetições do mesmo estilo de label e 30 repetições do mesmo estilo de input nos formulários privados.

Extrações recomendadas:

- `FormField.svelte`: label, ajuda, erro e slot do controle;
- `TextInput.svelte`;
- `SelectInput.svelte`;
- `CheckboxField.svelte`.

Esses componentes devem padronizar marcação, acessibilidade e aparência, sem receber regras de negócio. A adoção deve começar pelos formulários menores antes de alcançar `ProgramForm` e `ProfileForm`.

### 6. Cartões de conteúdo privado

`PostGrid.svelte` e `PodcastGrid.svelte` compartilham uma estrutura forte: grade responsiva, cartão, faixa inferior, métrica, informação central e ações à direita. `PollGrid.svelte` usa uma variação próxima.

Extração recomendada:

- `ContentCard.svelte` com slots para conteúdo, métrica, metadado e ações.

Não é recomendado transformar todos os grids em um `EntityGrid` genérico. Marketing, usuários, programas e galeria têm estruturas suficientemente diferentes.

### 7. Dados e controles dos players

Arquivos:

- `player/MainPlayer.svelte`
- `player/MobilePlayer.svelte`

Os layouts são diferentes, mas ambos montam o mesmo modelo de programa, locutor, música atual, volume e reprodução.

Extrações recomendadas:

- `createPlayerViewModel.js` para normalizar `air` e `stream`;
- `PlayerControls.svelte` para play/pause e volume, se a marcação permitir a mesma composição;
- helper compartilhado para status do modo de execução.

Não é recomendado unificar os dois players em um componente responsivo único. A apresentação desktop e mobile diverge demais e ficaria condicionada por muitas flags.

### 8. Menu do usuário nas navbars privadas

A navbar privada repete avatar, identificação, link de perfil e logout nas versões desktop e mobile.

Extração recomendada:

- `UserMenu.svelte`, com variante `desktop`/`mobile` apenas se as diferenças permanecerem pequenas;
- alternativamente, extrair somente `UserIdentity.svelte` e `LogoutAction.svelte`.

As navbars pública e privada não devem ser fundidas: navegação e responsabilidades são distintas.

## Decomposição para manutenção, não necessariamente reutilização

### 9. `ProgramForm.svelte`

Com 470 linhas, concentra dados básicos, apresentador, tipo de acesso, modo de execução, horários e planos. Mesmo que algumas partes sejam usadas apenas nesse formulário, a decomposição melhora teste e leitura.

Divisão sugerida:

- `ProgramIdentityFields.svelte`;
- `ProgramExecutionFields.svelte`;
- `ProgramAirtimeFields.svelte`;
- `ProgramPlanFields.svelte`.

### 10. `SongRequestGrid.svelte`

Com 259 linhas, mistura notificações do navegador, ações de locução, status da caixa e apresentação dos pedidos.

Divisão sugerida:

- `NotificationPermissionAction.svelte`;
- `SongRequestBoxActions.svelte`;
- `SongRequestItem.svelte`.

### 11. `ProfileForm.svelte`

Com 261 linhas, pode ser dividido por seções já visíveis na interface: identidade, localização, biografia e preferências. A reutilização externa é secundária; o ganho principal é reduzir responsabilidade.

## Componentes existentes que devem ser preservados

As abstrações atuais já cobrem bem padrões recorrentes:

- `Section.svelte` para título e ações de seção;
- `Offcanvas.svelte` para formulários laterais;
- `Preview.svelte` para mídia enviada;
- `Tooltip.svelte`;
- `Wysiwyg.svelte`;
- `Carrousel.svelte`.

Antes de criar wrappers maiores, os novos componentes devem compor essas bases.

## Abstrações que não são recomendadas agora

- um `CrudGrid.svelte` universal;
- um formulário universal baseado em schema;
- unificar todos os grids privados apenas porque usam `Section`;
- unificar players desktop e mobile em um único template;
- fundir navbar pública e privada;
- mover regras de domínio ou permissões para componentes puramente visuais.

Essas opções reduziriam linhas inicialmente, mas criariam muitas props condicionais e acoplamento entre módulos.

## Ordem recomendada de refatoração

1. Unificar `PostActions` e `PollActions`.
2. Extrair `IconAction` e migrar dois grids como prova de conceito.
3. Extrair normalizadores e campos compartilhados de Post/Event/Review.
4. Extrair a infraestrutura comum das paginações.
5. Introduzir os primitivos de formulário em dois formulários pequenos.
6. Dividir `ProgramForm`, `SongRequestGrid` e `ProfileForm` por responsabilidade.
7. Normalizar o view model dos players sem unir seus layouts.

Cada etapa deve preservar a API das páginas e ser acompanhada por build e validação visual das telas afetadas.
