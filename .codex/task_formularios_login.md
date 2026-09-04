# Task: Ajustar ícones de formulário e reformular a tela `/entrar`

Esta task possui duas partes principais:

1. Ajustar os ícones nativos de campos `select` e calendário para seguirem o tema visual do site.
2. Reformular a página `/entrar` para ficar com aparência real de uma tela de login, mantendo a identidade anime/otaku do projeto e garantindo responsividade.

---

## 1. Ajustar ícones de `select` e campos de data

Atualmente os inputs de texto e `textarea` já possuem uma estilização personalizada seguindo o padrão visual do site.

Os campos `select` e os inputs relacionados a calendário/data também receberam essa estilização geral, porém alguns elementos nativos do navegador continuam destoando visualmente do restante da interface.

Os principais problemas são:

- A seta padrão exibida nos campos `select`.
- O ícone de calendário exibido nos campos `input[type="date"]` ou campos equivalentes.
- Esses ícones não seguem as cores, contraste e aparência visual adotados pelos demais componentes do formulário.

### Objetivo

Ajustar esses elementos para que fiquem visualmente integrados ao tema atual do site.

### Requisitos

1. Antes de alterar qualquer coisa, localizar e analisar a estilização já existente dos inputs, `textarea` e `select`.
2. Manter o padrão visual existente. Não criar um novo estilo de formulário independente.
3. Ajustar a seta dos campos `select`.
4. Ajustar o ícone dos campos de calendário/data.
5. Caso seja necessário remover a aparência nativa do `select` com `appearance: none`, adicionar uma seta personalizada que siga o padrão visual do projeto.
6. Caso seja utilizado SVG ou outro ícone customizado, reutilizar preferencialmente o sistema de ícones já existente no projeto.
7. Não adicionar bibliotecas externas apenas para resolver esses ícones.
8. Preservar completamente o funcionamento dos campos:
   - abertura do `select`;
   - abertura do seletor de data;
   - navegação via teclado;
   - foco;
   - hover;
   - disabled;
   - validação;
   - acessibilidade.
9. Verificar também os estados de tema existentes no projeto, caso existam variações como tema claro/escuro.
10. Evitar soluções frágeis ou específicas de uma única página. O ajuste deve ser feito no estilo compartilhado responsável pelos formulários sempre que possível.

### Compatibilidade

Levar em consideração principalmente navegadores Chromium, mas evitar quebrar Firefox ou outros navegadores suportados pelo projeto.

Para inputs de data, verificar com cuidado pseudo-elementos específicos do navegador, como:

```css
::-webkit-calendar-picker-indicator
```

Não esconder o seletor de data nem substituir seu funcionamento nativo sem necessidade.

### Resultado esperado

Os campos `select` e calendário devem continuar funcionando exatamente como atualmente, porém a seta e o ícone de calendário devem combinar visualmente com:

- cor dos textos;
- cor dos ícones;
- fundo dos inputs;
- estados de hover/focus;
- tema geral do site.

A alteração deve ser estritamente visual e não deve modificar regras de negócio, validações ou comportamento dos formulários.

---

## 2. Reformular a página `/entrar`

A página `/entrar` deve ser revisada visualmente.

Atualmente ela deve continuar cumprindo exatamente a mesma função de autenticação, mas quero que visualmente pareça de fato uma tela de login completa e bem estruturada.

### Objetivo visual

Criar uma tela de login moderna, limpa e bem organizada, porém integrada à identidade anime/otaku já existente no site.

Não quero uma página genérica de sistema administrativo.

Também não quero exagerar nos elementos visuais a ponto de prejudicar a leitura ou parecer uma landing page.

A página precisa continuar claramente sendo uma tela de login.

### Direção visual

Usar como referência os elementos e padrões já existentes no projeto:

- paleta de cores;
- tipografia;
- bordas;
- sombras;
- ícones;
- elementos decorativos;
- identidade anime/otaku;
- mascotes, ilustrações ou elementos gráficos já existentes, caso façam sentido.

Antes de criar novos elementos visuais, verificar o que já existe no projeto e reutilizar sempre que possível.

A identidade anime/otaku deve aparecer principalmente através de detalhes visuais, composição, ilustrações e decoração, sem prejudicar a usabilidade.

### Estrutura sugerida

A tela pode seguir uma composição semelhante a:

- área central de login;
- formulário claramente destacado;
- título ou mensagem de boas-vindas;
- elementos decorativos ligados ao universo anime/otaku;
- possibilidade de uma ilustração lateral em telas maiores;
- formulário mais centralizado em telas menores.

Não é obrigatório seguir exatamente essa estrutura caso o projeto já tenha um padrão melhor.

O importante é manter uma composição equilibrada.

### Responsividade

A página `/entrar` deve funcionar corretamente em:

- desktop;
- notebook;
- tablet;
- smartphones.

Em telas grandes, pode existir uma composição dividida entre formulário e elemento visual.

Em dispositivos móveis:

- priorizar o formulário;
- evitar excesso de decoração;
- evitar overflow horizontal;
- manter margens adequadas;
- inputs e botões devem ocupar largura confortável;
- nenhuma informação importante deve sair da viewport.

### Formulário

Manter os campos, validações e fluxo de autenticação já existentes.

Não alterar:

- endpoints;
- lógica de autenticação;
- validações;
- mensagens;
- OAuth;
- redirects;
- tratamento de erros;
- sessão;
- regras de negócio.

Alterar apenas o necessário para melhorar apresentação, layout e experiência visual.

Os inputs dessa página também devem utilizar o mesmo padrão visual compartilhado dos demais formulários do site.

### Acessibilidade

Garantir:

- labels corretamente associados;
- contraste adequado;
- foco visível;
- navegação por teclado;
- botões com estados claros;
- mensagens de erro legíveis;
- responsividade sem perda de conteúdo.

### Importante

Não criar uma segunda identidade visual exclusiva para `/entrar`.

Ela deve parecer parte do mesmo site.

A ideia é:

> "Uma tela de login de um portal anime/otaku"

e não:

> "Um template genérico de login com alguns elementos de anime jogados por cima."

---

## Resultado esperado

Após as alterações:

1. Os ícones de `select` e calendário devem estar integrados visualmente ao tema.
2. A página `/entrar` deve parecer uma tela de login completa, moderna e organizada.
3. A identidade anime/otaku deve estar claramente presente sem prejudicar usabilidade.
4. O layout deve estar corretamente responsivo.
5. Toda a funcionalidade atual de autenticação deve continuar intacta.
6. Reutilizar componentes, estilos, ícones e assets existentes antes de criar novas soluções.
7. Não instalar bibliotecas adicionais sem necessidade.
8. Não alterar regras de negócio ou arquitetura apenas por causa dessa reformulação visual.
