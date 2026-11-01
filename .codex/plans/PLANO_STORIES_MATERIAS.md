# Plano — Conversão de Stories para matérias

## Objetivo

Transformar Stories publicados automaticamente em tráfego para as matérias da Rede Akiba, mesmo sem a possibilidade de inserir automaticamente um link clicável na imagem do Story.

## Solução proposta

Usar um endereço permanente na bio do Instagram:

```text
https://akiba.com.br/instagram
```

Esse endereço abrirá uma página pública com as matérias divulgadas recentemente nos Stories. A matéria mais recente ficará em destaque no topo.

O endereço da bio não precisará ser alterado a cada publicação. O sistema atualizará automaticamente o conteúdo da página.

## Jornada do usuário

1. Uma matéria é publicada no site.
2. O sistema gera a arte vertical da matéria.
3. O Story é publicado automaticamente no Instagram.
4. A arte apresenta o CTA `LEIA A MATÉRIA — LINK NA BIO`.
5. O usuário abre o perfil da Rede Akiba e toca no link da bio.
6. A página `/instagram` mostra a matéria divulgada no topo.
7. O usuário toca no card e acessa a matéria completa.

## Escopo do MVP

### 1. Página pública `/instagram`

- Exibir apenas matérias publicadas e disponíveis publicamente.
- Destacar a matéria promovida mais recentemente.
- Listar as demais matérias divulgadas nos Stories em ordem decrescente.
- Mostrar imagem, categoria, título e horário ou data da publicação.
- Levar o usuário diretamente à matéria ao tocar no card.
- Ser rápida e priorizar a experiência em celular.
- Exibir um estado vazio adequado quando não houver matérias promovidas.

### 2. Registro das divulgações

Registrar cada matéria enviada para Story com, no mínimo:

- matéria relacionada;
- rede social;
- formato utilizado;
- data e hora da publicação;
- situação da publicação;
- identificador retornado pela plataforma, quando disponível.

Sempre que um Story de matéria for publicado com sucesso, essa matéria passa a aparecer em `/instagram`.

### 3. Arte automática para Story

- Formato `1080 × 1920`.
- Título da matéria com limite seguro de linhas.
- Imagem principal da matéria.
- Categoria ou editoria.
- Marca da Rede Akiba.
- CTA visível: `LEIA A MATÉRIA` e `LINK NA BIO`.
- Respeitar as áreas seguras do Instagram para não cobrir informações com a interface do aplicativo.

### 4. Rastreamento

Os links dos cards em `/instagram` devem incluir parâmetros de campanha:

```text
utm_source=instagram
utm_medium=story
utm_campaign=materias
```

Também deverá ser registrado o clique antes do redirecionamento, permitindo medir:

- acessos à página `/instagram`;
- cliques por matéria;
- taxa de clique da página para a matéria;
- matérias com maior conversão;
- data e horário dos cliques.

## Arquitetura sugerida

### Backend

- Criar um controller público para a página `/instagram`.
- Concentrar a seleção das matérias promovidas em um Service.
- Reutilizar os registros existentes de publicação social sempre que eles já contiverem os dados necessários.
- Criar uma tabela específica de promoções somente se os registros atuais não forem suficientes.
- Criar um endpoint de redirecionamento rastreável, por exemplo `/instagram/materia/{post}`.

### Frontend

- Criar uma página pública Svelte via Inertia.
- Usar o padrão visual público da Rede Akiba.
- Aplicar `cardInteractive` nos cards clicáveis e `imageZoom` nas imagens.
- Usar `orange-citric` nos elementos clicáveis e estados de interação.
- Priorizar uma coluna no celular e ampliar a grade progressivamente em telas maiores.

## Regras de negócio

- Uma matéria só aparece depois que a publicação do Story for confirmada como bem-sucedida.
- Publicações com erro ou canceladas não aparecem.
- Matérias removidas, privadas ou não publicadas deixam de aparecer automaticamente.
- A ordenação considera a data da divulgação no Story, não apenas a data original da matéria.
- O MVP pode limitar a página às últimas 10 ou 20 matérias divulgadas.
- A matéria mais recente permanece em destaque até uma nova divulgação bem-sucedida.

## Etapas de implementação

### Etapa 1 — Levantamento

- Identificar os models e tabelas atuais de posts e publicações sociais.
- Confirmar onde ficam armazenados formato, status, rede social e data da publicação.
- Definir se os dados existentes atendem à página sem nova migration.

### Etapa 2 — Backend

- Implementar a consulta das matérias promovidas.
- Criar a rota e o controller público de `/instagram`.
- Implementar o redirecionamento rastreável.
- Adicionar testes de autorização, visibilidade, ordenação e rastreamento.

### Etapa 3 — Interface

- Implementar a página responsiva.
- Criar destaque para a matéria mais recente.
- Criar lista de matérias anteriores.
- Implementar estado vazio, carregamento e tratamento de imagens ausentes.

### Etapa 4 — Modelo do Story

- Adaptar ou criar o modelo vertical para matérias.
- Adicionar o CTA `LEIA A MATÉRIA — LINK NA BIO`.
- Validar títulos curtos e longos.
- Gerar prévias com matérias reais antes da ativação.

### Etapa 5 — Integração e métricas

- Ligar a publicação bem-sucedida do Story à página `/instagram`.
- Adicionar UTMs aos destinos.
- Registrar os cliques.
- Exibir as métricas no painel administrativo, caso isso faça parte de uma segunda entrega.

## Critérios de aceite

- O endereço `/instagram` funciona sem autenticação.
- A matéria promovida mais recentemente aparece primeiro.
- Apenas matérias públicas com Story publicado com sucesso são exibidas.
- Todos os cards levam à matéria correta.
- Os acessos possuem UTMs e os cliques são contabilizados.
- A página funciona corretamente em celular e desktop.
- A arte do Story deixa claro que a matéria está no link da bio.
- Títulos longos não ultrapassam os limites do layout.
- Testes automatizados cobrem a consulta, a página e o redirecionamento.

## Evoluções futuras

- QR Code individual por matéria.
- Endereços curtos, como `/i/ABC12`.
- Ranking de conversão por matéria e campanha.
- Expiração configurável das matérias na página.
- Destaques separados para matérias, eventos e programação ao vivo.
- Notificação para a equipe adicionar manualmente uma figurinha de link quando desejado.

## Fora do escopo inicial

- Automação por API privada ou simulação do aplicativo do Instagram.
- Alteração automática do link da bio.
- Figurinha de link adicionada automaticamente ao Story.
- Campanhas pagas e anúncios com botão de destino.

