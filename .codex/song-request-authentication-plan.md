# Plano: autenticação para pedidos musicais

## Objetivo

Quando os pedidos musicais estiverem abertos, visitantes não autenticados devem ser convidados a entrar com o Discord antes de acessar o formulário. Visitantes autenticados continuam normalmente para o preenchimento e envio do pedido.

O frontend deve antecipar essa exigência, enquanto o backend permanece como a barreira definitiva de segurança.

## Situação atual

- O formulário é exibido sempre que `air.allows_song_requests` é verdadeiro.
- A rota `POST /song-request` já utiliza o middleware `oauth`.
- Quando o cookie OAuth não existe ou é inválido, o middleware redireciona para `/oauth/discord/redirect`.
- Portanto, um visitante atualmente consegue preencher todo o formulário e só descobre que precisa entrar quando tenta enviá-lo.
- O callback OAuth cria ou atualiza a conta e grava o cookie `akiba_oauth_token`, mas atualmente não retorna uma resposta de redirecionamento.
- O estado da autenticação OAuth ainda não é compartilhado com o frontend pelo Inertia.

## Fluxo desejado

| Pedidos musicais | Autenticação OAuth | Resultado exibido |
| --- | --- | --- |
| Fechados | Ausente | Mensagem atual de pedidos indisponíveis |
| Fechados | Presente | Mensagem atual de pedidos indisponíveis |
| Abertos | Ausente | Convite para entrar com o Discord |
| Abertos | Presente | Formulário de pedido musical |
| Pedido enviado | Presente | Confirmação atual de envio |

## Etapas de implementação

### 1. Resolver a autenticação OAuth nas páginas públicas

- Ler o cookie `akiba_oauth_token` no servidor.
- Buscar uma `OAuthAccount` pelo hash SHA-256 do token.
- Considerar autenticado somente quando o cookie corresponder a uma conta existente.
- Disponibilizar a conta resolvida como atributo da requisição, permitindo seu reaproveitamento por middlewares e recursos.
- Evitar expor o token ou seu hash ao frontend.

Para não duplicar a regra entre o compartilhamento do Inertia e o middleware obrigatório, concentrar a resolução da conta em um único componente, como um middleware opcional ou um serviço dedicado.

### 2. Compartilhar somente o estado necessário com o Inertia

- Adicionar uma propriedade compartilhada, por exemplo:

```text
oauthAuthenticated: true | false
```

- O frontend precisa apenas saber se pode liberar o formulário.
- Dados da conta poderão ser compartilhados futuramente se houver uma necessidade concreta, mas não fazem parte deste escopo.

### 3. Atualizar o `SongRequestForm`

Organizar a renderização nesta ordem:

1. Se o pedido já foi enviado, exibir a confirmação.
2. Se os pedidos estiverem fechados, exibir a mensagem atual de indisponibilidade.
3. Se os pedidos estiverem abertos e não houver autenticação, exibir o convite de login.
4. Se os pedidos estiverem abertos e houver autenticação, exibir o formulário.

O convite deve:

- explicar brevemente que o login é necessário para enviar pedidos;
- oferecer um botão claro, como `Entrar com Discord`;
- direcionar para `/oauth/discord/redirect`;
- manter o visual e o espaço do modal atual.

### 4. Completar a ida e a volta do OAuth

- Antes de redirecionar para o Discord, registrar na sessão uma URL interna segura para retorno.
- Após o callback:
  - validar `state` e `code` como já ocorre;
  - trocar o código pelo token do provedor;
  - criar ou atualizar a `OAuthAccount`;
  - gravar o cookie local;
  - retornar uma resposta de redirecionamento para a página original ou para a home como fallback.
- Aceitar somente URLs internas como destino de retorno, evitando redirecionamentos abertos.

Como melhoria de experiência, o retorno poderá incluir um indicador para reabrir automaticamente o modal de pedidos. Isso deve ser feito apenas se houver uma forma simples e consistente de os players desktop e mobile consumirem o mesmo estado.

### 5. Manter a proteção obrigatória no backend

- Manter o middleware `oauth` em `POST /song-request`.
- Fazer o middleware reutilizar a conta já resolvida quando ela estiver disponível.
- Se o cookie estiver ausente, inválido ou associado a uma conta removida, iniciar novamente o login OAuth.
- Continuar obtendo `oauth_account_id` exclusivamente no servidor; nunca aceitar esse identificador enviado pelo formulário.

## Testes previstos

### Backend

- A home compartilha `oauthAuthenticated = false` sem cookie.
- A home compartilha `oauthAuthenticated = false` com cookie inválido.
- A home compartilha `oauthAuthenticated = true` com cookie válido.
- Um POST sem autenticação não cria pedido e inicia o OAuth.
- Um POST com cookie inválido não cria pedido e inicia o OAuth.
- Um POST autenticado cria o pedido associado à `OAuthAccount` correta.
- O callback OAuth grava o cookie e redireciona para um destino interno válido.
- O callback utiliza a home quando não existe destino de retorno.
- Uma URL externa não é aceita como destino de retorno.

### Frontend e validação manual

- Pedidos fechados mostram a mensagem atual, independentemente da autenticação.
- Pedidos abertos sem autenticação mostram o convite de login, sem renderizar o formulário.
- Pedidos abertos com autenticação mostram o formulário.
- O botão de login inicia o fluxo do Discord.
- Depois do login, o usuário retorna ao site autenticado.
- O envio e a confirmação do pedido continuam funcionando.
- O comportamento é equivalente nos players desktop e mobile.

## Critérios de aceite

- Um visitante não autenticado não vê o formulário quando os pedidos estão abertos.
- O visitante recebe uma chamada clara para entrar com o Discord.
- Após autenticar, o visitante retorna ao site e consegue acessar o formulário.
- Nenhum pedido pode ser criado sem uma `OAuthAccount` válida.
- A proteção do backend não depende do estado informado pelo frontend.
- O fluxo de pedidos fechados permanece inalterado.
- Não são expostos tokens, hashes ou identificadores internos desnecessários ao navegador.

## Fora do escopo

- Logout da conta OAuth.
- Tela de perfil do ouvinte.
- Suporte visual a outros provedores além do Discord.
- Alterações nas regras de abertura e fechamento dos pedidos.
- Mudanças nos campos ou nas validações do pedido musical, exceto ajustes necessários para dados que já vêm da conta autenticada.

## Ordem recomendada

1. Centralizar a resolução da `OAuthAccount`.
2. Compartilhar `oauthAuthenticated` pelo Inertia.
3. Corrigir o retorno do callback OAuth.
4. Criar o estado de login no `SongRequestForm`.
5. Adicionar os testes de backend.
6. Executar o build e validar manualmente desktop e mobile.

