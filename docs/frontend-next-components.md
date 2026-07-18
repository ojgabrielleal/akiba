# Próximos componentes do frontend

Revisão planejada para **19 de julho de 2026**.

## Estado atual dos formulários

A etapa principal de componentização dos formulários privados foi concluída.

Componentes disponíveis:

- `FormField`: label, ajuda, erro, variante visual e espaçamento;
- `TextInput`: variantes `default`, `offcanvas`, `editorial`, `profile`, `pillLeft` e `pillRight`;
- `SelectInput`: variantes `default`, `offcanvas`, `pill` e `profile`;
- `TextArea`: variantes `default`, `offcanvas` e `profile`;
- `RadioInput`;
- `CheckboxInput`;
- `Button` e `IconButton`.

Melhorias realizadas:

- IDs únicos em campos dinâmicos;
- associação entre label, controle e mensagem de erro;
- borda de erro separada da borda das variantes;
- estilos de offcanvas, perfil e editorial encapsulados;
- formulários editoriais e administrativos migrados.

Exceções mantidas como controles nativos:

- login, por ter identidade visual própria;
- locução, por possuir layout e interação específicos;
- select múltiplo de permissões do `RoleForm`.

Melhoria futura não bloqueante:

- evitar a passagem duplicada de `error` ao `FormField` e ao controle, possivelmente usando slot props ou componentes integrados.

## Próximo grupo recomendado

### 1. StatusBadge

Centralizar apresentação de estados como:

- publicado;
- rascunho;
- revisão;
- ativo;
- desativado;
- atrasado.

O componente deve receber apenas apresentação (`tone` e conteúdo). A decisão do status permanece nos helpers e widgets de domínio.

### 2. ActionGroup

Organizar conjuntos de `IconButton` e padronizar:

- espaçamento;
- alinhamento;
- quebra responsiva;
- descrição acessível do grupo.

Não deve conhecer permissões, URLs ou operações de negócio.

### 3. LoadingSpinner

Extrair o indicador usado em botões, paginações e carregamentos, mantendo tamanho e cor configuráveis sem duplicar marcação.

### 4. EmptyState

Padronizar listas sem registros com:

- título;
- descrição opcional;
- ícone opcional;
- ação opcional.

### 5. Infraestrutura de paginação

Revisar `ButtonPagination` e `InfinitePagination` para compartilhar:

- descoberta da próxima página;
- estado de carregamento;
- navegação Inertia;
- concatenação de resultados.

Os dois componentes visuais devem continuar separados.

## Decisão sobre cards

Não criar agora um card genérico para todos os grids. `PostGrid`, `PodcastGrid`, `PollGrid` e os demais possuem conteúdo e regras diferentes. Reavaliar somente depois dos componentes pequenos acima.

## Checklist da próxima revisão

- [ ] Mapear todos os estilos de status existentes;
- [ ] definir os `tones` iniciais de `StatusBadge`;
- [ ] escolher dois grids para validar o componente;
- [ ] revisar grupos repetidos de `IconButton`;
- [ ] localizar estados vazios existentes;
- [ ] comparar a lógica das duas paginações;
- [ ] confirmar se a duplicação de `error` será tratada agora ou depois.
