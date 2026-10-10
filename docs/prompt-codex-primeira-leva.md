# Ajustes de autenticação, enquetes e modais

Analise a implementação atual do projeto antes de realizar as alterações abaixo.

O projeto utiliza Laravel. Evite criar soluções paralelas quando já existir um mecanismo, componente, service, middleware, guard ou padrão equivalente no sistema.

Antes de modificar qualquer coisa:

- Identifique como a autenticação atual funciona.
- Identifique os guards, middlewares e rotas envolvidos no site público e no `/panel`.
- Identifique o componente padrão/reutilizável de modal utilizado pelo sistema.
- Identifique os componentes responsáveis pelos cards de enquetes.
- Preserve a arquitetura e o padrão de código existentes.
- Evite duplicação de lógica.
- Não faça refatorações fora do escopo sem necessidade.
- Não altere comportamentos que não estejam relacionados aos requisitos abaixo.

---

## 1. Login: remover "Permanecer conectado"

Na tela de login, remova completamente a opção de:

- "Permanecer conectado"
- "Lembrar de mim"
- `remember me`
- ou qualquer equivalente atualmente existente.

Remova também a lógica específica relacionada a essa opção no backend, caso exista.

O login deve utilizar somente o comportamento padrão de autenticação e sessão utilizado pelo Laravel/PHP no projeto.

Não queremos uma opção de persistência de autenticação controlada pelo usuário.

Garanta que a remoção não prejudique:

- login;
- logout;
- sessão;
- guards;
- middlewares;
- redirecionamentos;
- proteção das rotas autenticadas.

---

## 2. Reconhecimento de navegador utilizado por perfil interno

Existe uma área administrativa em:

`/panel`

Atualmente um usuário interno pode realizar login normalmente no painel, porém, ao navegar posteriormente pelo site público `/` e clicar em **Entrar** através do fluxo do `AuthGuard`, o sistema pode solicitar login novamente.

Queremos melhorar esse comportamento.

### Objetivo

Depois que um usuário interno realizar uma autenticação válida, o sistema deve conseguir reconhecer que aquele navegador já foi utilizado para acessar um perfil interno.

Ao clicar posteriormente em **Entrar** no site público, se o sistema conseguir identificar de forma válida que aquele navegador pertence a um usuário interno já reconhecido, o fluxo deve direcioná-lo para:

`/panel`

em vez de apresentar desnecessariamente o login novamente.

### Importante

Esse reconhecimento NÃO deve ser simplesmente uma recriação da funcionalidade "Permanecer conectado".

Também NÃO queremos criar um segundo sistema de autenticação paralelo ao Laravel.

Analise a arquitetura atual e implemente a solução mais adequada utilizando, sempre que possível, recursos nativos do Laravel.

O reconhecimento persistido no navegador:

- não deve armazenar senha;
- não deve expor informações sensíveis;
- não deve utilizar um ID de usuário simples como prova de autenticação;
- não deve permitir adulteração trivial;
- deve ser criado somente depois de uma autenticação válida;
- deve possuir estratégia de expiração;
- deve considerar revogação;
- deve considerar logout;
- deve ser validado pelo backend.

O dado armazenado no navegador deve funcionar como um mecanismo seguro de reconhecimento/associação, e não como uma autorização cega para acessar `/panel`.

### Fluxo esperado

1. Usuário interno acessa o login.
2. Realiza autenticação válida.
3. Entra em `/panel`.
4. O navegador passa a possuir o mecanismo seguro de reconhecimento definido pela implementação.
5. Posteriormente o usuário acessa `/`.
6. Clica em "Entrar".
7. O `AuthGuard`/fluxo correspondente verifica a situação atual.
8. Caso exista sessão válida ou reconhecimento válido daquele navegador associado ao perfil interno, direcionar para `/panel`.
9. Caso não exista reconhecimento válido, apresentar o fluxo normal de autenticação.

Revise também possíveis conflitos entre autenticação do site público e autenticação do painel.

Não enfraqueça a proteção das rotas administrativas para implementar esse comportamento.

---

## 3. Corrigir número cortado nos cards das enquetes

Na área administrativa existem cards de enquetes.

Cada card representa uma enquete e possui uma faixa inferior contendo informações e botões de ação.

Atualmente o número exibido nessa faixa inferior está sendo cortado verticalmente.

Exemplo: o valor `12` aparece parcialmente cortado.

### Corrigir o layout

Investigue principalmente propriedades relacionadas a:

- `height`;
- `min-height`;
- `line-height`;
- `overflow`;
- `padding`;
- alinhamento vertical;
- flexbox/grid;
- dimensões do container.

O ícone e o número devem aparecer integralmente e corretamente centralizados.

A solução precisa funcionar para valores com diferentes quantidades de dígitos, por exemplo:

- `1`
- `12`
- `150`
- `1200`

Não faça uma correção específica apenas para o valor atualmente exibido.

### Preservar

Não redesenhe o card.

Preserve:

- título;
- cores;
- dimensões gerais sempre que possível;
- botões de visualizar;
- botão de excluir;
- botão de editar;
- demais comportamentos existentes.

Faça somente os ajustes necessários para impedir o clipping e manter o layout responsivo.

---

## 4. Padronizar `max-height` dos modais

O projeto já possui um componente padrão/reutilizável de modal.

Atualmente alguns modais que possuem muito conteúdo podem ultrapassar a área visível da viewport.

Um exemplo é o modal que mostra os usuários/respostas de uma enquete.

### Objetivo

Não corrija esse problema individualmente em cada modal.

Primeiro localize o componente base/reutilizável responsável pelos modais do sistema.

Implemente a correção centralizadamente nele para que os modais que utilizam esse componente herdem automaticamente o comportamento.

Defina aproximadamente:

`max-height: 80vh`

O objetivo é que nenhum modal grande saia da área útil da tela.

### Comportamento esperado

`80vh` deve representar uma ALTURA MÁXIMA.

Não transforme todos os modais em modais com 80% da altura da tela.

Modais pequenos devem continuar utilizando apenas o espaço necessário.

Quando o conteúdo ultrapassar o limite disponível:

- manter o modal dentro da viewport;
- permitir scroll vertical;
- preferencialmente aplicar o scroll somente no corpo/conteúdo do modal;
- manter header visível, caso exista;
- manter footer/ações visíveis, caso exista.

Evite criar scrolls aninhados desnecessariamente.

Considere também:

- desktop;
- notebook;
- telas menores;
- diferentes alturas de viewport.

Após alterar o componente compartilhado, revise seus usos existentes para garantir que nenhum modal tenha sido quebrado.

---

# Critérios gerais

Antes de finalizar:

1. Revise as alterações realizadas.
2. Verifique se não houve duplicação desnecessária de lógica.
3. Confirme que componentes compartilhados foram aproveitados quando possível.
4. Não altere funcionalidades fora deste escopo.
5. Preserve os padrões visuais existentes.
6. Preserve compatibilidade com a arquitetura Laravel atual do projeto.
7. Não reduza a segurança da autenticação para facilitar o redirecionamento.
8. Verifique possíveis regressões no login, logout, sessão, `/panel`, AuthGuard, cards de enquetes e modais.
9. Caso encontre uma implementação existente que já resolva parcialmente algum requisito, prefira adaptá-la em vez de criar outra solução.

Ao terminar, apresente um resumo objetivo contendo:

- arquivos alterados;
- o que foi alterado em cada arquivo;
- decisões tomadas para o reconhecimento do navegador;
- impacto sobre autenticação e sessão;
- componente de modal alterado;
- eventuais pontos que mereçam teste manual.
