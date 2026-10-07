# Prompt para Codex — Reestruturação do Sistema de Emblemas + Easter Eggs

Estamos trabalhando em um projeto **Laravel** que já possui um sistema de emblemas implementado.

Os administradores redefiniram algumas regras do sistema. O objetivo é **adaptar o que já existe**, preservando arquitetura, dados, relacionamentos e funcionalidades válidas.

O trabalho deve ser realizado obrigatoriamente em duas fases:

1. **Adaptar o sistema atual de emblemas às novas regras.**
2. **Somente depois de concluir e revisar a Fase 1, implementar os Easter Eggs.**

---

# REGRA PRINCIPAL

Antes de alterar qualquer arquivo, faça uma análise geral do sistema atual de emblemas.

Analise, conforme existirem:

- Models;
- Controllers;
- Services;
- Actions;
- Repositories;
- migrations e tabelas;
- relacionamentos;
- enums e constantes;
- Form Requests e validações;
- rotas;
- Jobs;
- Commands;
- Schedulers;
- eventos/listeners;
- componentes frontend;
- telas administrativas;
- perfil do usuário;
- categorias;
- targets/gatilhos;
- regras de concessão;
- notificações existentes;
- registro de audiência da rádio;
- podcasts;
- pedidos musicais;
- enquetes.

## Muito importante

**NÃO reconstrua o sistema de emblemas do zero.**

Estas regras representam uma evolução do sistema existente.

Antes de criar código novo:

1. identifique o que já existe;
2. identifique o que continua válido;
3. identifique o que pode ser reutilizado;
4. adapte somente o necessário;
5. preserve dados e emblemas já concedidos.

Não crie um segundo sistema paralelo de emblemas.

Não faça migrations destrutivas sem necessidade.

Não altere funcionalidades não relacionadas.

---

# FASE 1 — ADAPTAÇÃO DO SISTEMA DE EMBLEMAS

Conclua esta fase antes de implementar qualquer Easter Egg.

---

# 1. Categoria: CONCEDIDO

## 1.1 Remover "Todos os usuários"

Atualmente existe a opção:

`Todos os usuários`

Essa opção não será mais necessária.

Remova essa possibilidade da interface administrativa e adapte as validações/backend relacionados.

Antes de remover qualquer estrutura persistida, verifique se ela é usada por dados antigos ou outras funcionalidades.

Não prejudique emblemas que já foram concedidos.

---

## 1.2 Usuário selecionado

A opção de concessão para usuários específicos deve continuar, mas agora deve aceitar:

- nenhum usuário;
- um usuário;
- múltiplos usuários.

Portanto, a cardinalidade passa a ser:

`0..N usuários`

O administrador deve poder criar/configurar o emblema sem escolher ninguém naquele momento.

Não selecionar usuários **não significa conceder para todos**. Significa apenas que nenhum usuário será selecionado/contemplado naquele momento.

A interface deve permitir adicionar e remover múltiplos usuários seguindo o padrão visual atual do painel.

Reutilize os relacionamentos existentes sempre que possível.

---

# 2. Categoria: COMPETITIVO

Preserve as origens competitivas existentes.

Adicione uma nova origem:

`Enquete`

A regra será:

`Quem mais respondeu enquetes`

Analise como enquetes, respostas e usuários estão relacionados atualmente e reutilize esses dados.

Não crie um mecanismo paralelo de contabilização se as respostas já estiverem armazenadas.

---

## 2.1 Regra de empate

A nova regra geral dos emblemas competitivos é:

**Se dois ou mais usuários estiverem empatados na primeira posição, nenhum deles recebe o emblema.**

Exemplo:

```text
João: 30
Maria: 25
Pedro: 18
```

Resultado:

```text
João recebe.
```

Mas:

```text
João: 30
Maria: 30
Pedro: 18
```

Resultado:

```text
Ninguém recebe.
```

Se as diferentes origens competitivas utilizarem o mesmo mecanismo, aplique essa regra de forma centralizada a todas elas.

Não duplique a lógica de empate em cada origem.

---

# 3. Categoria: EVENTO

## 3.1 Alvo automático

Na categoria Evento, não será mais necessário selecionar manualmente um alvo.

Os alvos/elegíveis serão automaticamente:

`Usuários/ouvintes autenticados que derem play na rádio durante o evento.`

Remova da interface a configuração manual de alvo dessa categoria.

Antes de implementar, analise como o sistema atual registra:

- play;
- ouvintes;
- audiência;
- sessões;
- histórico da rádio.

Reutilize essa infraestrutura.

Não crie um segundo sistema de audiência.

---

## 3.2 Janela "Todo dia"

Adicione a possibilidade de configurar uma janela diária:

`Todo dia, de X até X horas`

Exemplo:

```text
Todo dia
18:00 até 22:00
```

Durante o período geral do evento, somente plays realizados dentro dessa janela diária tornam o usuário elegível.

A interface administrativa deve permitir configurar:

- horário inicial;
- horário final;
- regra/janela "Todo dia".

Respeite o timezone utilizado pelo projeto.

Se a arquitetura permitir, trate corretamente janelas que atravessem a meia-noite, por exemplo:

```text
22:00 até 02:00
```

---

# 4. Categoria: CONQUISTA

A categoria `Conquista` passará a funcionar como um playground de gatilhos.

Antes de alterar sua estrutura, analise como targets/gatilhos são implementados atualmente.

Estenda o sistema existente em vez de substituí-lo.

---

## 4.1 Gatilho: Podcasts

Adicionar:

`Podcasts`

Condição:

`Número de podcasts ouvidos`

O número deve ser configurável pelo administrador.

Exemplos:

```text
1 podcast
5 podcasts
10 podcasts
50 podcasts
```

Analise como o sistema determina atualmente que um podcast foi reproduzido/ouvido.

Não permita contabilização indevida da mesma reprodução se já houver uma regra de consumo válida.

---

## 4.2 Gatilho: Pedidos musicais

Adicionar:

`Pedidos musicais`

A condição será baseada na posição ordinal/global dos pedidos efetivamente atendidos.

Exemplo:

`Ser a pessoa que teve o pedido musical nº 1.000 atendido.`

O número deve ser configurável.

Exemplos:

```text
Pedido nº 100
Pedido nº 500
Pedido nº 1000
Pedido nº 5000
```

Não hardcode o número `1000`.

Analise primeiro:

- como pedidos são registrados;
- como são associados ao usuário;
- qual estado/evento significa que o pedido foi atendido;
- se já existe uma numeração ou ordem confiável.

O emblema deve ser concedido somente quando o pedido correspondente for efetivamente considerado atendido.

---

# 5. MODAL GLOBAL DE NOVO EMBLEMA

Sempre que um usuário receber um novo emblema, exiba um modal.

Reutilize o design system, modal e componentes existentes sempre que possível.

Conteúdo:

## Título

`Você recebeu um novo emblema!`

## Texto

`Confira todos no seu perfil.`

## Imagem

Exibir a imagem do emblema recebido.

## Botão

`Confirmar recebimento`

### Importante

O botão é apenas uma confirmação visual.

Quando o modal aparecer, **o usuário já recebeu o emblema**.

O botão NÃO deve:

- conceder;
- aceitar;
- aprovar;
- rejeitar;
- remover;
- alterar a propriedade do emblema.

Ele serve somente para marcar/confirmar que o usuário viu a conquista e fechar o modal.

Não existe opção de recusar.

Fluxo:

```text
Gatilho acontece
        ↓
Backend valida
        ↓
Backend concede
        ↓
Frontend identifica novo emblema
        ↓
Modal aparece
        ↓
Usuário clica em "Confirmar recebimento"
        ↓
Modal fecha
```

A concessão nunca pode depender do modal.

---

## 5.1 Persistência do modal

O mesmo modal não pode aparecer infinitamente a cada reload.

Primeiro procure algum mecanismo existente de:

- notificações;
- `read_at`;
- `seen_at`;
- acknowledgment;
- estado de visualização equivalente.

Reutilize-o se existir.

Caso seja necessário persistir especificamente que a conquista foi visualizada, implemente da maneira mais simples e compatível com a arquitetura atual.

Se houver vários emblemas novos ainda não visualizados, mostre-os de forma organizada, preferencialmente em sequência.

Não sobreponha vários modais.

---

# 6. REVISÃO OBRIGATÓRIA DA FASE 1

Antes de iniciar os Easter Eggs:

- revise as alterações;
- procure regressões;
- confirme que emblemas antigos continuam funcionando;
- confirme que concessões antigas permanecem intactas;
- confirme que as categorias existentes continuam funcionando;
- confirme que o modal funciona independentemente da origem da conquista;
- confirme que as novas regras foram integradas ao sistema atual.

**Somente depois disso inicie a Fase 2.**

---

# FASE 2 — EASTER EGGS

Os Easter Eggs devem ser implementados como gatilhos da categoria:

`Conquista`

Não crie uma categoria independente.

---

# 7. Easter Egg como gatilho configurável

Adicionar o gatilho:

`Easter Egg`

O Easter Egg **não deve estar hardcoded a um emblema específico**.

O administrador deve escolher qual Easter Egg dispara cada emblema.

Estrutura conceitual:

```text
Categoria: Conquista
Gatilho: Easter Egg
Easter Egg: [selecionar]
```

Quando o administrador escolher `Easter Egg`, exiba um select com os Easter Eggs disponíveis.

Inicialmente:

- Konami Code;
- DOOM — God Mode;
- Mortal Kombat — Blood Code;
- Sonic 2 — Level Select;
- Super Mario Bros. — Continue;
- Página 404.

Use identificadores internos estáveis, por exemplo:

```text
konami
doom_iddqd
mortal_kombat_abacabb
sonic_2_level_select
super_mario_continue
page_404
```

Não use o nome amigável exibido no painel como identificador da regra.

---

# 8. Associação dinâmica Easter Egg → Emblema

Exemplo:

```text
Emblema: Mestre dos Clássicos
Categoria: Conquista
Gatilho: Easter Egg
Easter Egg: Konami Code
```

Nesse caso, executar o Konami Code dispara a avaliação desse emblema.

Outro exemplo:

```text
Emblema: God Mode
Categoria: Conquista
Gatilho: Easter Egg
Easter Egg: DOOM — God Mode
```

Nesse caso, `IDDQD` dispara a avaliação desse emblema.

## Não faça isto:

```text
konami => badge_id 5
doom => badge_id 8
```

A associação entre Easter Egg e emblema deve vir da configuração realizada pelo administrador.

O detector não deve conhecer IDs de emblemas.

---

# 9. Catálogo inicial de Easter Eggs

Centralize o catálogo de Easter Eggs de forma coerente com a arquitetura atual.

Cada entrada deve possuir, conceitualmente:

- identificador estável;
- nome amigável;
- tipo de detecção;
- sequência/condição necessária.

Não crie uma arquitetura excessivamente genérica apenas para isso.

---

## 9.1 Konami Code

Identificador sugerido:

`konami`

Sequência:

```text
↑ ↑ ↓ ↓ ← → ← → B A
```

Teclas:

```text
ArrowUp
ArrowUp
ArrowDown
ArrowDown
ArrowLeft
ArrowRight
ArrowLeft
ArrowRight
B
A
```

O Konami Code deve ser detectado **na página inicial do site**, conforme a regra definida pelos administradores.

---

## 9.2 DOOM — God Mode

Identificador:

`doom_iddqd`

Código:

```text
IDDQD
```

---

## 9.3 Mortal Kombat — Blood Code

Identificador:

`mortal_kombat_abacabb`

Código:

```text
ABACABB
```

---

## 9.4 Sonic 2 — Level Select

Identificador:

`sonic_2_level_select`

Código original:

```text
19 65 09 17
```

No site, detectar continuamente como:

```text
19650917
```

---

## 9.5 Super Mario Bros. — Continue

Identificador:

`super_mario_continue`

Adaptação para teclado:

```text
A + Enter
```

Esse caso é uma combinação de teclas, não uma sequência textual comum.

---

## 9.6 Página 404

Identificador:

`page_404`

Esse Easter Egg não depende do teclado.

Ele ocorre quando um usuário autenticado navega para uma URL inexistente e efetivamente chega à página 404 navegável do site.

Não considere como gatilho:

- 404 de API;
- imagem inexistente;
- asset ausente;
- AJAX/fetch;
- requisição interna;
- recurso secundário que retornou 404.

O gatilho representa especificamente a visualização da página 404 principal pelo usuário.

---

# 10. Detector de Easter Eggs

Não crie um listener completamente separado para cada código.

Implemente uma solução pequena e reutilizável capaz de reconhecer:

- sequências de setas;
- letras;
- números;
- combinações de teclas;
- eventos de navegação especiais, quando aplicável.

Exemplos:

```text
Konami:
↑ ↑ ↓ ↓ ← → ← → B A

DOOM:
IDDQD

Mortal Kombat:
ABACABB

Sonic 2:
19650917

Mario:
A + Enter
```

Centralize as definições.

Não espalhe códigos hardcoded por vários componentes.

O detector deve tratar corretamente tentativas incorretas e permitir que uma nova sequência comece sem recarregar a página.

Evite múltiplas requisições em curto intervalo para o mesmo Easter Egg.

---

# 11. Não interferir com campos de entrada

Os códigos de teclado não devem ser capturados quando o usuário estiver digitando/interagindo em elementos como:

- `input`;
- `textarea`;
- `select`;
- campos de busca;
- `contenteditable`;
- outros controles onde a captura global prejudique a interação normal.

---

# 12. Fluxo de resolução

O frontend detecta **qual Easter Egg ocorreu**, não qual emblema deve ser concedido.

Exemplo:

```text
Usuário digita IDDQD
        ↓
Frontend detecta:
doom_iddqd
        ↓
Backend recebe o evento
        ↓
Backend valida o identificador
        ↓
Procura emblemas configurados com:
Categoria = Conquista
Gatilho = Easter Egg
Easter Egg = doom_iddqd
        ↓
Aplica regras normais de elegibilidade
        ↓
Verifica se o usuário já possui
        ↓
Concede quando aplicável
        ↓
Modal global informa a nova conquista
```

O frontend não deve enviar `badge_id`.

---

# 13. Mais de um emblema no mesmo Easter Egg

Não suponha obrigatoriamente que cada Easter Egg terá apenas um emblema.

Se a estrutura administrativa permitir que dois ou mais emblemas sejam configurados com o mesmo Easter Egg, todos devem passar individualmente pelas regras normais de elegibilidade/concessão.

Não introduza uma constraint `unique` entre Easter Egg e emblema sem uma necessidade de negócio existente.

---

# 14. Segurança

O frontend pode detectar os códigos, mas não é autoridade para conceder emblemas.

O backend deve:

1. exigir autenticação quando aplicável;
2. validar o identificador do Easter Egg;
3. localizar os emblemas configurados para aquele gatilho;
4. verificar elegibilidade;
5. verificar se o usuário já possui;
6. impedir concessão duplicada;
7. conceder utilizando o mecanismo existente;
8. registrar a conquista conforme os padrões atuais;
9. alimentar o sistema do modal.

Não permita concessão apenas manipulando estado JavaScript.

Ao mesmo tempo, mantenha a solução proporcional: Easter Eggs são uma funcionalidade recreativa, não precisam de uma arquitetura de segurança exagerada.

---

# 15. Emblemas secretos

Os emblemas associados a Easter Eggs devem poder ser tratados como secretos.

Antes da conquista, não revele desnecessariamente:

- sequência;
- código;
- condição;
- descrição que entregue como desbloquear.

Se o sistema atual já possui alguma forma de ocultar emblemas secretos, reutilize-a.

Depois da conquista, o emblema pode aparecer normalmente no perfil do usuário.

---

# 16. Extensibilidade dos Easter Eggs

Queremos conseguir adicionar futuramente códigos como:

```text
ROSEBUD
```

ou:

```text
HOW DO YOU TURN THIS ON
```

sem reescrever todo o detector.

Adicionar um novo Easter Egg deve exigir o mínimo possível de alterações e, idealmente, fazê-lo aparecer automaticamente no select administrativo a partir do catálogo central.

Não implemente `ROSEBUD` ou `HOW DO YOU TURN THIS ON` agora.

Eles são apenas exemplos de extensibilidade futura.

---

# 17. Integração com a arquitetura existente

Antes de criar:

- novas tabelas;
- novas colunas;
- enums;
- JSONs de configuração;
- services;
- abstrações;

analise como os demais gatilhos de `Conquista` armazenam suas configurações.

Se já existir uma estrutura genérica para target/gatilho, reutilize-a.

Não crie uma tabela exclusiva de Easter Eggs se não for necessária.

A prioridade continua sendo:

**adaptar o sistema existente, não reconstruí-lo.**

---

# 18. Ordem obrigatória de execução

Siga esta ordem:

```text
Analisar sistema existente
        ↓
FASE 1
        ↓
Adaptar Concedido
        ↓
Adaptar Competitivo
        ↓
Adicionar origem Enquete
        ↓
Implementar regra de empate
        ↓
Adaptar Evento
        ↓
Implementar janela diária
        ↓
Expandir Conquista
        ↓
Adicionar Podcasts
        ↓
Adicionar Pedidos musicais
        ↓
Implementar modal global
        ↓
Revisar/testar Fase 1
        ↓
FASE 2
        ↓
Adicionar gatilho Easter Egg
        ↓
Adicionar seleção administrativa do Easter Egg
        ↓
Criar catálogo central
        ↓
Implementar detector
        ↓
Konami
        ↓
DOOM
        ↓
Mortal Kombat
        ↓
Sonic 2
        ↓
Mario
        ↓
Página 404
        ↓
Integrar com concessão existente
        ↓
Integrar com modal global
        ↓
Testes finais e regressão
```

Não comece os Easter Eggs antes de concluir a adaptação do sistema de emblemas.

---

# 19. Não faça

- Não reconstrua o sistema de emblemas do zero.
- Não crie um segundo sistema de concessão.
- Não apague dados existentes.
- Não remova emblemas já concedidos.
- Não altere IDs existentes sem necessidade.
- Não faça migrations destrutivas sem necessidade.
- Não duplique regras existentes.
- Não coloque regra de negócio complexa diretamente em Controllers se o projeto já usa Services/Actions.
- Não faça overengineering.
- Não altere funcionalidades não relacionadas.
- Não substitua arquivos inteiros quando mudanças localizadas forem suficientes.
- Não faça o modal controlar a concessão.
- Não conceda emblemas exclusivamente pelo frontend.
- Não envie `badge_id` a partir do detector de Easter Eggs.
- Não associe códigos a IDs fixos de emblemas.
- Não revele desnecessariamente os Easter Eggs secretos ao usuário.

---

# 20. Migrations

Caso sejam necessárias alterações de banco:

- utilize migrations incrementais;
- preserve dados existentes;
- considere registros antigos;
- evite alterações destrutivas;
- reutilize relacionamentos existentes;
- não recrie tabelas apenas para adequá-las às novas regras.

Analise os dados atuais antes de alterar constraints ou relacionamentos.

---

# 21. Testes

Adicione ou adapte testes seguindo o padrão já existente no projeto.

Cubra principalmente:

## Concedido

- nenhum usuário selecionado;
- um usuário;
- múltiplos usuários;
- ausência da antiga opção "Todos os usuários".

## Competitivo

- vencedor único;
- empate em primeiro lugar;
- origem Enquete.

## Evento

- play dentro do período;
- play fora do período;
- janela diária;
- intervalo atravessando meia-noite, se suportado.

## Conquista

- quantidade de podcasts;
- pedido musical ordinal;
- concessão única.

## Modal

- novo emblema dispara modal;
- imagem correta;
- confirmação fecha o modal;
- confirmação não concede novamente;
- reload não mostra infinitamente a mesma conquista;
- múltiplas conquistas pendentes são tratadas de maneira organizada.

## Easter Eggs

- administrador consegue escolher um Easter Egg para o emblema;
- associação é persistida corretamente;
- Konami correto;
- Konami incorreto;
- `IDDQD`;
- `ABACABB`;
- `19650917`;
- `A + Enter`;
- página 404 navegável;
- 404 de API não concede;
- 404 de asset não concede;
- usuário que já possui o emblema não recebe duplicado;
- detector não interfere com campos de formulário;
- frontend envia o identificador do Easter Egg, não o ID do emblema;
- dois emblemas configurados para o mesmo Easter Egg são avaliados corretamente, se essa configuração for permitida.

---

# 22. Relatório final

Ao terminar, apresente um resumo objetivo.

## Sistema existente

Explique:

- como funcionava;
- o que foi preservado;
- o que precisou ser adaptado.

## Arquivos

Liste:

- arquivos criados;
- arquivos alterados;
- migrations adicionadas.

## Concedido

Explique:

- remoção de "Todos os usuários";
- seleção opcional;
- seleção múltipla.

## Competitivo

Explique:

- origem Enquete;
- regra de empate.

## Evento

Explique:

- alvo automático por play na rádio;
- janela diária.

## Conquista

Explique:

- Podcasts;
- Pedidos musicais;
- estrutura dos gatilhos.

## Modal

Explique:

- como novas conquistas são detectadas;
- como o modal é apresentado;
- como a visualização é persistida;
- como múltiplos emblemas pendentes são tratados.

## Easter Eggs

Explique:

- como o administrador escolhe o Easter Egg de cada emblema;
- onde fica o catálogo;
- como funciona o detector;
- como funciona a resolução no backend;
- Konami Code;
- DOOM;
- Mortal Kombat;
- Sonic 2;
- Super Mario Bros.;
- página 404.

## Extensibilidade

Explique como adicionar:

- um novo gatilho de Conquista;
- um novo Easter Egg;
- um novo código ao catálogo.

## Verificação final

Informe quais testes e verificações foram realizados para garantir que o sistema anterior de emblemas não sofreu regressões.

---

# PRIORIDADE FINAL

A prioridade durante todo o trabalho é:

1. compreender o sistema atual;
2. adaptar o que já existe;
3. preservar dados e funcionalidades válidas;
4. implementar as novas regras administrativas;
5. somente depois adicionar os Easter Eggs;
6. manter a solução simples, integrada e extensível.

**Não transforme essa alteração em uma reconstrução completa do módulo de emblemas.**
