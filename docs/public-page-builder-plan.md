# Planejamento: Construtor de paginas publicas

## Regra antes de executar

Criar uma branch separada antes de qualquer implementacao desta iniciativa.

Sugestao:

```bash
git checkout -b feature/public-page-builder
```

Nao fazer isso direto na branch principal. A mudanca toca banco, admin, frontend publico, renderizacao por componentes, tema e validacao de configuracoes. Se misturar com outras tarefas, vai dar xabu.

## Objetivo

Criar no painel administrativo um construtor modular para paginas publicas da Akiba.

O admin deve conseguir:

- criar paginas publicas novas;
- definir titulo, slug, icone e exibicao no navbar;
- montar o conteudo com blocos existentes;
- ordenar blocos com drag and drop;
- escolher variacoes de cada bloco;
- personalizar conteudo, layout e aparencia;
- visualizar preview por tema;
- ajustar pontos especificos de aparencia por tema, sem quebrar o design global.

## Ideia central

Cada pagina publica sera composta por blocos.

Cada bloco sera uma unidade completa, carregando:

- `content`: textos, ids selecionados, limites e dados do bloco;
- `layout`: variante, colunas, exibicao de imagem, densidade e alinhamentos;
- `appearance`: tokens visuais padrao e overrides pontuais por tema.

Os temas definem o significado visual dos tokens. O bloco nao deve saber que a cor real e `orange-citric`; ele deve dizer que usa `primary`, `accent`, `surface`, `muted`, `text` ou outro token permitido.

## Hierarquia de aparencia

A resolucao visual deve seguir esta ordem:

1. Tema ativo do site define os tokens reais.
2. Tipo do bloco define defaults recomendados.
3. Bloco salvo pode alterar seus defaults.
4. Override do bloco por tema muda apenas pontos especificos.

Exemplo de `appearance` salvo no bloco:

```json
{
  "defaults": {
    "title": "primary",
    "background": "surface",
    "button": "accent"
  },
  "overrides": {
    "akiba_dark": {
      "title": "accent"
    },
    "akiba_light": {
      "background": "muted"
    }
  }
}
```

Se o tema atual for `akiba_dark`, somente `title` muda para `accent`. `background` e `button` continuam herdando de `defaults`.

## Etapa 1: especificar o dominio minimo

Objetivo da etapa: definir o contrato inicial sem ainda construir a tela completa.

Tarefas:

- listar quais paginas publicas customizaveis entram no MVP;
- definir se a home atual tambem sera migrada para esse sistema agora ou depois;
- definir os primeiros tipos de bloco;
- definir os primeiros icones permitidos no navbar;
- definir os primeiros temas disponiveis.

Sugestao para o MVP:

- pagina customizada generica;
- home fica para uma etapa posterior, se estiver arriscado migrar logo;
- blocos iniciais:
  - Hero;
  - Lista de posts;
  - Destaques manuais;
  - CTA/Banner;
  - Texto simples;
- temas iniciais:
  - `akiba_default`;
  - `akiba_light`;
  - `akiba_dark`.

Resultado esperado:

- documento curto com tipos de blocos, variantes e campos;
- decisao clara sobre incluir ou nao a home no primeiro ciclo.

## Etapa 2: modelagem do banco

Objetivo da etapa: criar as tabelas base sem mexer ainda na experiencia completa do admin.

Tabelas propostas:

```txt
public_pages
- id
- title
- slug
- navbar_label
- navbar_icon
- show_in_navbar
- navbar_order
- status
- theme_key nullable
- published_at nullable
- created_at
- updated_at

public_page_blocks
- id
- public_page_id
- type
- variant
- sort_order
- enabled
- content JSON
- layout JSON
- appearance JSON
- created_at
- updated_at
```

Tarefas:

- criar migrations;
- criar models;
- criar casts para JSON;
- criar factories simples se fizer sentido;
- definir statuses permitidos da pagina.

Resultado esperado:

- banco preparado;
- models prontos;
- sem tela ainda.

## Etapa 3: registry dos blocos

Objetivo da etapa: criar a fonte oficial dos blocos permitidos.

O registry deve responder:

- quais tipos de bloco existem;
- quais variantes existem por tipo;
- quais campos de `content`, `layout` e `appearance` cada tipo aceita;
- quais tokens visuais podem ser usados;
- quais defaults cada tipo de bloco usa.

Exemplo conceitual:

```php
[
    'latest_posts' => [
        'label' => 'Ultimos posts',
        'variants' => ['grid', 'carousel', 'compact'],
        'defaults' => [
            'appearance' => [
                'title' => 'primary',
                'background' => 'surface',
                'button' => 'accent',
            ],
        ],
    ],
]
```

Tarefas:

- criar uma classe de suporte ou service para o registry;
- expor os blocos permitidos para o admin via controller;
- validar `type` e `variant` contra o registry;
- impedir tokens e campos desconhecidos.

Resultado esperado:

- backend ja sabe quais blocos existem;
- admin ainda pode ser simples ou inexistente.

## Etapa 4: camada de temas

Objetivo da etapa: definir temas e tokens de forma segura.

Primeira versao recomendada:

- temas fixos no codigo ou seedados;
- sem editor livre de tema ainda;
- tokens fechados.

Tokens iniciais:

```txt
primary
secondary
accent
surface
muted
text
text-muted
border
button
```

Tarefas:

- criar um registry/service de temas;
- mapear tokens para classes Tailwind permitidas;
- criar helper para resolver token final considerando overrides do bloco;
- garantir que classes dinamicas nao quebrem o build do Tailwind.

Resultado esperado:

- dado um bloco e um tema, o backend/frontend consegue saber quais tokens finais usar.

## Etapa 5: CRUD simples de paginas

Objetivo da etapa: permitir criar e editar pagina sem drag and drop ainda.

Tarefas:

- criar rotas privadas no padrao atual do projeto;
- criar controller privado na raiz do escopo correto;
- criar requests de validacao;
- criar tela de listagem no admin;
- criar tela de criar/editar pagina;
- campos:
  - titulo;
  - slug;
  - label do navbar;
  - icone do navbar;
  - mostrar no navbar;
  - ordem;
  - status.

Resultado esperado:

- admin cria paginas;
- ainda sem editor visual de blocos.

## Etapa 6: CRUD simples de blocos

Objetivo da etapa: permitir adicionar, editar, ativar/desativar e remover blocos.

Tarefas:

- criar endpoints para blocos da pagina;
- adicionar bloco por tipo;
- escolher variante;
- editar `content`, `layout` e `appearance`;
- remover bloco;
- ativar/desativar bloco;
- ordenar por controles simples primeiro, se drag and drop ficar grande.

Resultado esperado:

- pagina ja possui blocos persistidos;
- editor ainda pode ser funcional e simples.

## Etapa 7: drag and drop no admin

Objetivo da etapa: melhorar a experiencia de ordenacao.

Tarefas:

- escolher biblioteca Svelte apropriada, como `svelte-dnd-action`, se ja fizer sentido instalar;
- se dependencia nova for necessaria, avaliar com calma antes;
- criar lista ordenavel de blocos;
- persistir `sort_order`;
- manter estados de loading e erro.

Resultado esperado:

- admin reordena blocos visualmente.

## Etapa 8: renderizacao publica basica

Objetivo da etapa: fazer a pagina publica funcionar com os blocos.

Tarefas:

- criar rota publica por slug;
- buscar pagina publicada;
- carregar blocos ativos ordenados;
- resolver dados de cada bloco;
- criar renderizador Svelte que mapeia `type` e `variant` para componente;
- criar componentes iniciais ou adaptar componentes publicos existentes.

Resultado esperado:

- uma pagina criada no admin aparece no site publico.

## Etapa 9: preview por tema

Objetivo da etapa: permitir ao admin ver como o bloco fica em cada tema.

Tarefas:

- adicionar seletor de tema no editor de bloco;
- renderizar preview do bloco com tema selecionado;
- mostrar tokens finais aplicados;
- indicar campos que possuem override naquele tema;
- adicionar botao para resetar overrides do tema atual.

Resultado esperado:

- admin consegue alternar entre `akiba_default`, `akiba_light` e `akiba_dark` no preview.

## Etapa 10: overrides pontuais de aparencia

Objetivo da etapa: permitir corrigir visual de um bloco em um tema especifico.

Tarefas:

- criar UI para alterar tokens por tema;
- salvar somente os campos alterados em `appearance.overrides`;
- manter fallback para `appearance.defaults`;
- validar que override usa tokens permitidos;
- permitir resetar override por campo ou por tema.

Resultado esperado:

- admin consegue dizer: "no tema escuro, o titulo deste bloco deve usar accent".

## Etapa 11: navbar publico dinamico

Objetivo da etapa: usar paginas customizadas no navbar publico.

Tarefas:

- buscar paginas com `show_in_navbar = true`;
- ordenar por `navbar_order`;
- renderizar icone permitido;
- destacar pagina atual;
- garantir que paginas despublicadas nao aparecem.

Resultado esperado:

- pagina criada no admin pode aparecer no navbar publico com icone.

## Etapa 12: polimento e seguranca

Objetivo da etapa: preparar para uso real.

Tarefas:

- revisar permissoes do admin;
- validar slugs unicos;
- tratar pagina nao publicada;
- tratar blocos com dados incompletos;
- criar estados vazios no admin;
- adicionar confirmacao para remover bloco;
- revisar responsividade;
- revisar contraste entre tokens;
- revisar animacoes publicas usando `publicAnimations` quando aplicavel.

Resultado esperado:

- experiencia pronta para uso controlado.

## Etapa 13: migrar home publica, se aprovado

Objetivo da etapa: transformar a home atual em uma pagina montada por blocos.

Esta etapa deve ser feita so depois do construtor estar estavel.

Tarefas:

- mapear secoes atuais da home;
- criar blocos equivalentes;
- seedar uma pagina `home`;
- ajustar rota inicial para renderizar pela nova estrutura;
- comparar visual com a home antiga;
- manter fallback temporario, se necessario.

Resultado esperado:

- home publica tambem passa a ser administravel por blocos.

## Etapas pequenas sugeridas para execucao futura

Para nao consumir contexto demais, executar em rodadas separadas:

1. Branch + migrations + models.
2. Registry de blocos + registry de temas.
3. CRUD de paginas no admin.
4. CRUD simples de blocos no admin.
5. Renderizacao publica basica.
6. Drag and drop.
7. Preview por tema.
8. Overrides pontuais por tema.
9. Navbar dinamico.
10. Polimento.
11. Migracao da home.

## Decisoes ainda abertas

- A home entra no MVP ou fica para depois?
- Os temas ficam somente no codigo no inicio ou tambem no banco?
- Quais blocos entram primeiro?
- O admin podera duplicar blocos ja na primeira versao?
- O preview sera fiel ao componente real desde o inicio ou comecara simplificado?
- Paginas customizadas terao permissao por role desde o inicio?

## Cuidados especificos deste projeto

- Nao rodar `docker compose exec node npm run build` automaticamente.
- Nao subir Docker automaticamente.
- Usar `orange-citric` para botoes e elementos clicaveis.
- Usar `orange-amber` ou `orange-morning` em elementos nao clicaveis conforme proximidade visual.
- Em botoes com texto e icone, usar icone `w-6` por padrao.
- Na interface publica, usar os padroes de `publicAnimations`.
- Nao criar novas camadas em `app/Actions` ou `app/Filters`.
- Regras de negocio devem ficar em `app/Services`.
- Processamentos reutilizaveis internos devem ficar em `app/Processing` com sufixo `Process`.
