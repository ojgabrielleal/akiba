# Task: Ajustes de Reviews e implementação do "Enigma no Ar"

## Objetivo geral

Implementar dois conjuntos de alterações:

1.  Refatorar dados específicos de posts do tipo `review`, removendo
    fontes de pesquisa e migrando `studio` para `metadata`.
2.  Implementar o jogo **Enigma no Ar**, incluindo backend, painel
    administrativo, interface pública em `/midias`, permissões, regras
    de participação e integração com notificações push.

Antes de alterar qualquer estrutura, analisar os padrões já existentes
no projeto e reutilizar autenticação, Models, Services, Form Requests,
Policies, Resources, Enums, componentes, sistema de permissões,
notificações e convenções arquiteturais existentes.

Evitar criar sistemas paralelos ou padrões diferentes dos já utilizados
pelo projeto.

------------------------------------------------------------------------

# 1. Remover fontes de pesquisa de Reviews

Fontes de pesquisa não fazem mais parte de posts do tipo `review`.

Criar uma migration responsável por remover/desvincular as fontes de
pesquisa atualmente associadas a posts do tipo `review`.

A migration deve atuar somente sobre reviews e não deve afetar fontes
associadas a outros tipos de publicação.

Além da migration, atualizar toda a cadeia necessária para que criação,
atualização, validação e exibição de reviews não tentem mais manipular
fontes de pesquisa.

Fazer uma busca global pelos pontos relacionados a fontes de pesquisa em
reviews antes de concluir a alteração.

------------------------------------------------------------------------

# 2. Migrar `studio` para `metadata` em Reviews

Atualmente existe uma coluna `studio` diretamente na tabela `posts`.

Essa coluna não deve mais existir.

Para posts do tipo `review`, o estúdio deve ser armazenado dentro do
JSON da coluna `metadata`, junto aos demais dados específicos de
reviews.

Exemplo esperado:

``` json
{
  "synopsis": "...",
  "release_date": "...",
  "studio": "..."
}
```

## Migration

Criar uma migration segura que:

1.  Localize posts existentes do tipo `review`.
2.  Leia o valor atual da coluna `studio`.
3.  Preserve todo o conteúdo já existente em `metadata`.
4.  Adicione o valor de `studio` em `metadata.studio`.
5.  Somente após garantir a migração dos dados, remova a coluna `studio`
    da tabela `posts`.

Não sobrescrever ou remover outros dados existentes em `metadata`.

## Atualização em cascata

Essa mudança não deve ser tratada somente como alteração de banco.

Como `studio` deixará de existir como propriedade direta de `posts`,
analisar e atualizar toda a cadeia afetada, incluindo, quando aplicável:

-   migrations;
-   Models;
-   casts/accessors;
-   DTOs;
-   Resources/Serializers;
-   Form Requests;
-   Services;
-   Controllers;
-   validações;
-   payloads recebidos e retornados;
-   tipos/interfaces do frontend;
-   formulários de criação;
-   formulários de atualização;
-   preenchimento do formulário de edição;
-   cards, grids e páginas de detalhes;
-   qualquer outro código que leia ou escreva `studio`.

Fazer uma busca global no projeto por referências a `studio`.

Ao final, nenhuma parte relacionada a reviews deve depender da antiga
estrutura:

``` text
post.studio
```

O valor deve ser obtido/persistido através de:

``` text
metadata.studio
```

ou da abstração equivalente já utilizada pelo projeto.

## Interface

Não remover visualmente o campo **Estúdio** das reviews.

Para o usuário, o comportamento deve continuar igual. A mudança é
somente na forma como o dado é armazenado, validado, enviado, retornado
e consumido internamente.

Garantir que:

-   criação de review continue funcionando;
-   atualização permita alterar o estúdio;
-   reviews existentes continuem exibindo o estúdio após a migration;
-   o formulário de edição carregue o valor corretamente;
-   a exibição pública continue funcionando;
-   não existam referências quebradas após a remoção da coluna física.

------------------------------------------------------------------------

# 3. Enigma no Ar

Implementar um jogo chamado:

``` text
Enigma no Ar
```

O jogo consiste em um enigma publicado pela equipe. Usuários
autenticados podem fazer perguntas para obter pistas ou enviar uma
resposta definitiva tentando solucionar o enigma.

A funcionalidade deve possuir backend, painel administrativo e interface
pública.

------------------------------------------------------------------------

# 4. Estrutura de dados do Enigma no Ar

Criar as migrations e Models necessários para representar enigmas e suas
interações.

A estrutura de um enigma deve permitir pelo menos:

-   título;
-   conteúdo/texto do enigma;
-   status;
-   solução oficial, quando aplicável;
-   timestamps.

Seguir as convenções de nomenclatura existentes no projeto.

Criar também uma estrutura de interações associada:

-   ao enigma;
-   ao usuário autenticado;
-   ao tipo da interação;
-   ao conteúdo enviado;
-   à resposta administrativa, quando aplicável;
-   ao membro da equipe que respondeu;
-   à data/hora da resposta;
-   aos timestamps.

Tipos iniciais:

``` text
question
final_answer
```

Se o projeto já utilizar Enums, utilizar o padrão existente e evitar
magic strings.

------------------------------------------------------------------------

# 5. Identificação do usuário

A participação exige autenticação.

O projeto possui a estrutura `oauth_accounts`.

Antes de implementar, analisar os Models e relacionamentos atuais e
utilizar a identidade correta do usuário OAuth já existente.

Não criar uma segunda estrutura de usuários.

Cada interação deve ficar vinculada de forma confiável ao usuário
autenticado que a enviou.

------------------------------------------------------------------------

# 6. Regras de participação

As regras são vinculadas à combinação:

``` text
usuário + enigma
```

## Pergunta

Quando um usuário enviar uma pergunta com sucesso, ele fica impedido de
realizar qualquer nova interação naquele enigma durante as próximas **24
horas reais**.

Não utilizar regra de "uma interação por dia calendário".

Exemplo:

``` text
Pergunta enviada:
02/09 às 15:30

Próxima interação permitida:
03/09 às 15:30
```

Durante esse período ele não pode:

-   fazer outra pergunta;
-   enviar uma resposta definitiva.

A validação deve considerar timestamps e uma janela móvel de 24 horas.

## Resposta definitiva

Quando o usuário enviar uma `final_answer`, sua participação naquele
enigma termina.

Depois disso, naquele mesmo enigma, ele não pode:

-   fazer novas perguntas;
-   enviar outra resposta definitiva.

Esse bloqueio não expira após 24 horas.

## Novo enigma = nova rodada

Quando um novo enigma for publicado, todos os bloqueios referentes ao
enigma anterior deixam de afetar os usuários.

Exemplo:

``` text
Enigma A

João:
perguntou há 2 horas
→ bloqueado no Enigma A

Maria:
enviou resposta definitiva
→ participação encerrada no Enigma A

Admin publica Enigma B

João → pode participar
Maria → pode participar
```

Não apagar interações antigas.

Elas devem permanecer armazenadas como histórico.

A publicação de um novo enigma inicia uma nova rodada para todos os
usuários. Nenhum cooldown de 24 horas ou bloqueio causado por resposta
definitiva de enigmas anteriores deve afetar o novo enigma.

## Backend obrigatório

Essas regras não podem depender apenas do frontend.

Antes de permitir uma interação, verificar:

1.  o enigma ativo;
2.  o usuário autenticado;
3.  se existe `final_answer` desse usuário para o enigma;
4.  quando ocorreu sua última interação naquele enigma.

Também proteger contra race conditions/requisições simultâneas que
possam burlar essas regras.

Centralizar essa lógica na camada de Service/domínio, não somente em
Form Requests ou Controllers.

------------------------------------------------------------------------

# 7. Publicação dos enigmas

O sistema deve permitir somente **um enigma ativo/publicado por vez**.

Quando um novo enigma for publicado:

1.  validar os dados;
2.  localizar o enigma atualmente ativo;
3.  desativar o enigma atual;
4.  publicar/ativar o novo;
5.  garantir que somente o novo permaneça disponível publicamente.

Exemplo:

``` text
Enigma A
status: active

Admin publica Enigma B

Enigma A
status: inactive

Enigma B
status: active
```

A troca deve acontecer de forma consistente, preferencialmente dentro de
transaction.

A regra deve existir no backend e considerar concorrência para impedir
dois enigmas ativos simultaneamente.

------------------------------------------------------------------------

# 8. Services, Requests, Models e Policies

Criar ou atualizar tudo que for necessário para o módulo, incluindo:

-   Migrations;
-   Models;
-   Relationships;
-   Services;
-   Form Requests;
-   Policies;
-   Controllers/Endpoints;
-   Resources/DTOs;
-   Enums, se o projeto utilizar;
-   demais estruturas necessárias.

Controllers devem permanecer finos.

Form Requests devem validar formato/entrada, mas regras de negócio como
cooldown e encerramento após resposta definitiva devem permanecer na
camada de Service/domínio.

------------------------------------------------------------------------

# 9. Permissões

As permissões do projeto são definidas em:

``` text
PermissionsSeeder
```

Seguir obrigatoriamente esse sistema.

Não criar um sistema paralelo de autorização.

Criar as permissões necessárias para o Enigma no Ar seguindo exatamente
a nomenclatura e padrão já utilizados no `PermissionsSeeder`.

Avaliar ações equivalentes a:

``` text
mystery.view
mystery.create
mystery.update
mystery.delete
mystery.publish
mystery.respond
```

Os nomes acima são apenas exemplos. Utilizar o padrão real do projeto.

O administrador deve conseguir conceder permissões a outros membros da
equipe.

Um membro autorizado deve poder, por exemplo, responder interações sem
necessariamente possuir permissão para criar, editar ou publicar
enigmas.

As Policies devem utilizar essas permissões.

Não espalhar verificações manuais de cargo/permissão pelos Controllers.

------------------------------------------------------------------------

# 10. Painel administrativo

Criar uma área no painel administrativo para gerenciamento do **Enigma
no Ar**.

Seguir rigorosamente o padrão visual já existente no painel.

Antes de implementar, analisar componentes e páginas administrativas
existentes e reutilizar:

-   containers;
-   cards;
-   formulários;
-   inputs;
-   buttons;
-   tabelas;
-   modais;
-   alerts/toasts;
-   loading;
-   tipografia;
-   espaçamentos;
-   cores;
-   componentes;
-   padrões de autorização na interface.

Usuários autorizados devem poder, conforme suas permissões:

-   criar enigma;
-   editar enigma;
-   publicar enigma;
-   visualizar enigmas anteriores;
-   visualizar perguntas;
-   responder perguntas;
-   visualizar respostas definitivas;
-   responder/tratar respostas definitivas;
-   identificar o usuário que enviou cada interação;
-   visualizar data/hora das interações;
-   visualizar quem da equipe respondeu.

A UI deve respeitar as Policies/permissões retornadas pelo backend.

Não assumir que todo usuário do painel é administrador.

------------------------------------------------------------------------

# 11. Interações no painel

Diferenciar claramente:

``` text
QUESTION
FINAL_ANSWER
```

## Perguntas

Permitir que membros autorizados respondam perguntas.

Registrar:

-   pergunta;
-   usuário que perguntou;
-   resposta da equipe;
-   membro da equipe que respondeu;
-   data/hora da pergunta;
-   data/hora da resposta.

Depois de respondida, a pergunta e sua resposta poderão ser exibidas
publicamente.

## Respostas definitivas

As respostas definitivas também devem aparecer no painel.

Elas não devem ser exibidas publicamente.

A equipe deve conseguir visualizar claramente:

-   resposta enviada;
-   usuário;
-   enigma;
-   data/hora;
-   eventual resposta/tratamento administrativo.

Não misturar visualmente respostas definitivas com perguntas comuns.

------------------------------------------------------------------------

# 12. Interface pública em `/midias`

O Enigma no Ar deve ser integrado à página:

``` text
/midias
```

Não criar uma página pública separada.

## Utilizar `<section>`

O jogo deve existir dentro de uma:

``` html
<section>
```

Isso é importante para manter a estrutura visual utilizada pelo restante
do site.

Antes de implementar, analisar as outras sections existentes em
`/midias` e seguir os mesmos padrões de:

-   container;
-   largura;
-   espaçamentos;
-   margens;
-   títulos;
-   tipografia;
-   cores;
-   breakpoints;
-   responsividade;
-   componentes.

Não criar uma identidade visual ou Design System próprio para o Enigma
no Ar.

A funcionalidade deve parecer parte natural do site atual.

## Layout desktop

Dentro da section, utilizar duas áreas principais.

### Esquerda: interação

A área esquerda deve permitir:

``` text
Fazer uma pergunta
```

ou:

``` text
Enviar resposta definitiva
```

A diferença entre as duas ações deve ficar clara para o usuário.

### Direita: enigma

A área direita deve apresentar o card/bloco contendo o enigma atual.

O conteúdo pode ser:

-   pergunta;
-   frase misteriosa;
-   situação;
-   pequeno texto;
-   outro conteúdo textual definido pela equipe.

Não assumir que todo enigma será necessariamente escrito como pergunta.

Estrutura conceitual:

``` text
┌─────────────────────────────────────────────┐
│              ENIGMA NO AR                   │
│                                             │
│  INTERAÇÃO             MISTÉRIO             │
│  DO USUÁRIO            ATUAL                │
│                                             │
│  [ Perguntar ]         "Uma mulher..."      │
│  [ Resposta final ]                         │
│                                             │
└─────────────────────────────────────────────┘
```

## Responsividade

Em telas menores, reorganizar os blocos naturalmente em uma única
coluna, seguindo os breakpoints existentes no projeto.

Não forçar layout lado a lado quando não houver espaço.

------------------------------------------------------------------------

# 13. Estados da interface pública

A interface deve utilizar o estado fornecido pelo backend.

## Usuário disponível

Mostrar normalmente:

``` text
Fazer uma pergunta
Enviar resposta definitiva
```

## Pergunta enviada

Após enviar uma pergunta, bloquear novas interações naquele enigma por
24 horas.

Durante esse período, informar que o usuário precisa aguardar.

O backend deve fornecer, de forma equivalente, informações suficientes
para a UI saber quando a próxima interação estará disponível.

Exemplos conceituais:

``` text
can_interact
next_interaction_at
has_submitted_final_answer
```

Os nomes são apenas exemplos. Seguir os padrões reais de
Resources/DTOs/Responses do projeto.

Não reproduzir toda a autorização apenas no frontend.

## Resposta definitiva enviada

Depois de enviar resposta definitiva, ocultar/desabilitar os formulários
de participação naquele enigma e informar que a resposta definitiva já
foi enviada.

## Usuário não autenticado

Seguir o fluxo de autenticação existente.

Não criar autenticação específica para o jogo.

------------------------------------------------------------------------

# 14. Perguntas públicas

Dentro da section do Enigma no Ar, exibir também as perguntas que já
receberam resposta da equipe.

Exibir:

``` text
Pergunta do ouvinte
Resposta da equipe
```

Somente perguntas respondidas devem aparecer publicamente.

Não exibir:

-   perguntas ainda não respondidas;
-   respostas definitivas.

A posição exata dessa listagem pode ser definida de acordo com a
estrutura atual de `/midias`, priorizando legibilidade e responsividade.

------------------------------------------------------------------------

# 15. Integração frontend

Atualizar tudo que for necessário para integrar a interface aos
endpoints:

-   API clients/services;
-   stores;
-   tipos/interfaces;
-   formulários;
-   validações;
-   tratamento de erros;
-   loading states;
-   atualização dos dados após ações;
-   componentes;
-   rotas administrativas;
-   página `/midias`;
-   controle de permissões.

Reutilizar componentes e padrões existentes.

Não duplicar regras complexas de negócio no frontend.

------------------------------------------------------------------------

# 16. Notificação push ao responder uma pergunta

Quando uma pergunta enviada por um usuário for respondida pela equipe,
disparar uma **notificação push individual** para o autor da pergunta.

Como a participação exige autenticação, utilizar a identidade associada
à interação para localizar o usuário correto.

Fluxo esperado:

``` text
Pergunta sem resposta
→ equipe responde
→ resposta é persistida com sucesso
→ identificar autor da pergunta
→ localizar dispositivos/tokens push desse usuário
→ enviar notificação somente para ele
```

A notificação deve informar claramente que a pergunta enviada no
**Enigma no Ar** recebeu uma resposta.

Seguir o padrão atual do projeto para:

-   título;
-   mensagem;
-   payload;
-   navegação;
-   Jobs;
-   Events;
-   Listeners;
-   envio push.

Não criar infraestrutura de push exclusiva para o jogo.

O push deve ser disparado somente após a resposta ser persistida com
sucesso.

Se o projeto utilizar Jobs/Events/Listeners, reutilizar esse fluxo em
vez de enviar diretamente pelo Controller.

------------------------------------------------------------------------

# 17. Verificar suporte das notificações a usuários OAuth

Antes de implementar o push do Enigma no Ar, analisar completamente a
infraestrutura atual de notificações.

Verificar:

-   Models;
-   migrations;
-   Services;
-   Jobs;
-   Events/Listeners;
-   tokens/dispositivos;
-   relacionamento entre dispositivo e usuário;
-   cadastro/atualização/remoção de tokens;
-   envio individual;
-   suporte a `users`;
-   suporte a usuários OAuth.

Não presumir a estrutura atual.

## Se OAuth já for suportado

Se o sistema já conseguir notificar individualmente usuários OAuth, não
refatorar desnecessariamente.

Apenas reutilizar a infraestrutura existente.

## Se notificações suportarem somente `users`

Caso os dispositivos/tokens estejam associados exclusivamente à tabela
`users`, refatorar a estrutura para uma **relação polimórfica** que
permita notificar individualmente tanto usuários internos quanto
usuários OAuth.

Utilizar o padrão do Laravel e a arquitetura existente.

Estrutura conceitual:

``` text
notifiable_type
notifiable_id
```

Os nomes são apenas referência.

O objetivo é permitir que a mesma infraestrutura de push seja utilizada
por:

``` text
users
usuários OAuth
```

sem duplicação de tabelas ou sistemas.

------------------------------------------------------------------------

# 18. Migration da relação polimórfica de notificações

Se a refatoração for necessária, criar uma migration segura.

Ela deve:

1.  adicionar a estrutura polimórfica;
2.  migrar registros atualmente associados a `users`;
3.  preservar tokens/dispositivos existentes;
4.  associar registros antigos ao Model correto de usuário interno;
5.  somente depois remover a dependência exclusiva de `user_id`, se
    houver;
6.  criar índices adequados.

Nenhum token push existente deve ser perdido.

Criar `down()` coerente sempre que tecnicamente possível e seguro.

Atualizar em cascata:

-   Models;
-   relationships;
-   Services;
-   Jobs;
-   Events;
-   Listeners;
-   Controllers;
-   Requests;
-   Resources;
-   endpoints de cadastro de token;
-   consultas;
-   atualização/remoção de tokens;
-   envio individual;
-   qualquer código que dependa diretamente de `user_id`.

Evitar condicionais espalhadas do tipo:

``` text
se for user...
se for oauth...
```

quando o relacionamento polimórfico puder centralizar essa
responsabilidade.

------------------------------------------------------------------------

# 19. Evitar push duplicado

A notificação deve representar o evento:

``` text
"sua pergunta foi respondida"
```

e não qualquer atualização do registro.

Comportamento esperado:

``` text
pergunta sem resposta
→ primeira resposta administrativa
→ enviar push
```

Uma edição posterior da resposta administrativa não deve gerar
automaticamente outro push.

Garantir que simples updates posteriores não provoquem notificações
duplicadas.

------------------------------------------------------------------------

# 20. Diretrizes finais de implementação

Antes de criar qualquer estrutura nova, analisar e reutilizar:

-   autenticação existente;
-   `oauth_accounts` e entidade OAuth correspondente;
-   sistema de usuários internos;
-   `PermissionsSeeder`;
-   Policies;
-   Models e relationships;
-   Services;
-   Form Requests;
-   Resources/DTOs;
-   Enums;
-   Events/Listeners/Jobs;
-   infraestrutura de push;
-   componentes do frontend;
-   padrões da página `/midias`;
-   padrões do painel administrativo;
-   convenções de rotas;
-   tratamento de erros;
-   Design System atual.

Fazer buscas globais pelos campos/estruturas que serão refatorados antes
de remover qualquer dependência.

Não criar soluções paralelas quando já existir infraestrutura
equivalente.

Backend e frontend devem ser entregues integrados e funcionais.

Preservar dados existentes durante migrations e evitar alterações
destrutivas antes da migração dos dados necessários.

O resultado final deve manter os padrões arquiteturais e visuais atuais
do projeto.
