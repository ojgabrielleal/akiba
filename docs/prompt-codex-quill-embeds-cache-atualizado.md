# Quill no Svelte: redimensionamento de imagens, embeds e política de cache

Analise a implementação atual do projeto antes de realizar qualquer alteração.

O frontend desta interface utiliza **Svelte** e possui uma integração do **Quill Editor** já personalizada visualmente. O backend utiliza Laravel e o frontend/assets podem utilizar Vite conforme a configuração atual do projeto.

Não substitua o editor existente, não remova as personalizações atuais e não faça refatorações fora do escopo sem necessidade.

Antes de começar:
- verifique o `package.json`;
- identifique a versão exata do `quill` e wrappers relacionados;
- localize o componente Svelte responsável pelo editor;
- entenda como a instância do Quill é inicializada;
- revise `modules`, `formats`, toolbar, handlers e estilos;
- verifique como o conteúdo é salvo e renderizado no site público;
- considere SSR/browser environment, caso aplicável.

---

## 1. Quill Editor — redimensionamento de imagens

Adicionar redimensionamento visual das imagens inseridas no Quill.

Antes de instalar dependências, descubra a versão do Quill e escolha um módulo de image resize realmente compatível. Evite dependências abandonadas/incompatíveis quando houver alternativa adequada.

### Comportamento esperado
1. O usuário seleciona/clica em uma imagem.
2. Aparecem handles/alças de redimensionamento.
3. É possível alterar visualmente suas dimensões.
4. O tamanho resultante permanece representado no conteúdo salvo.
5. Ao reabrir o conteúdo, o tamanho continua correto.
6. Na matéria/post público, a imagem respeita o tamanho escolhido.

A imagem deve respeitar os limites do container e não quebrar o layout responsivo.

### Movimentação, drag and drop e alinhamento de imagens

Além do redimensionamento, permitir que imagens já inseridas possam ser reposicionadas dentro do conteúdo.

Implementar drag and drop para que o usuário consiga selecionar uma imagem e arrastá-la para outra posição válida entre os blocos/parágrafos do documento.

Durante o arraste:
- exibir um indicador visual da posição de destino;
- impedir drops em posições inválidas;
- preservar o conteúdo ao redor da imagem.

Ao soltar, atualizar corretamente o modelo interno do Quill/Delta. Não implementar a movimentação apenas através de manipulação direta do DOM (`appendChild`, `insertBefore` etc.) se isso puder deixar o estado visual diferente do estado interno do Quill.

A operação deve continuar compatível com undo/redo do editor.

Adicionar também opções de alinhamento para a imagem selecionada:
- esquerda;
- centro;
- direita.

Se fizer sentido para a arquitetura atual, apresentar esses controles através de uma pequena toolbar contextual/flutuante ao selecionar a imagem, mantendo as alças responsáveis pelo resize.

Uma interface conceitual possível:

`← Esquerda | ↔ Centro | Direita → | Remover`

Não é necessário copiar exatamente esse visual. Preserve o design existente do editor.

O tamanho, posição e alinhamento escolhidos devem:
- ser persistidos no conteúdo;
- sobreviver ao ciclo salvar → reabrir → editar;
- continuar editáveis;
- ser reproduzidos corretamente no site público;
- funcionar de forma responsiva.

Antes de adicionar novas dependências, verifique se o módulo de image resize escolhido já oferece parte dessas funcionalidades. Evite instalar múltiplos plugins que disputem o controle da mesma imagem.

Se necessário, implemente um Blot/formato complementar compatível com a versão atual do Quill.

Garanta que movimentação/alinhamento não quebrem:
- seleção da imagem;
- resize;
- exclusão;
- inserção;
- upload;
- undo/redo;
- serialização do conteúdo;
- conteúdo antigo já existente.

Integre o módulo à instância atual do Quill no ciclo de vida correto do Svelte. Preserve toolbar personalizada, upload/inserção atual de imagens, formatos, módulos existentes, conteúdo antigo e aparência do editor.

Se o plugin depender de `window`, `document` ou DOM, inicialize-o somente no browser.

---

## 2. Quill Editor — embeds/iframe seguros

Adicionar suporte a conteúdo incorporado usando os mecanismos apropriados do Quill, preferencialmente `Embed`, `BlockEmbed`, Blot customizado ou API equivalente compatível com a versão instalada.

Adicionar à toolbar/interface uma ação de **Incorporar/Embed**.

Preferir uma experiência baseada em URL:
1. usuário informa uma URL de um provedor suportado;
2. sistema identifica e valida o provedor;
3. gera o embed/player controlado pela aplicação;
4. embed aparece dentro do editor;
5. depois da publicação, funciona também no site público.

Estruture para permitir novos provedores futuramente sem espalhar condicionais pelo editor.

### Segurança

NÃO aceitar HTML ou `<iframe>` arbitrário sem validação.

Criar whitelist explícita de provedores/origens permitidos. Validar e normalizar URLs antes de gerar o embed.

Não permitir:
- JavaScript arbitrário;
- protocolos perigosos;
- injeção de HTML/atributos;
- origens não autorizadas;
- manipulação da URL para escapar da validação.

Se houver `<iframe>`, seus atributos devem ser gerados/controlados pela aplicação, e não copiados de HTML fornecido pelo usuário.

Avaliar atributos apropriados como `sandbox`, `allow`, `referrerpolicy` e `allowfullscreen` de acordo com cada provedor.

O embed deve:
- aparecer corretamente no Quill;
- persistir ao salvar;
- continuar reconhecido ao reabrir/editar;
- funcionar na página pública;
- ser responsivo;
- respeitar a largura máxima do conteúdo;
- não causar overflow horizontal.

Verifique a sanitização existente no frontend/backend para permitir somente os embeds aprovados sem enfraquecer a proteção geral contra XSS.

---

## 3. Política adequada de cache do navegador

Não desabilitar globalmente o cache do site.

O objetivo é impedir que, após deploys/atualizações, usuários continuem vendo HTML/interface/conteúdo dinâmico antigo, preservando ao mesmo tempo o cache que melhora a performance.

Audite, conforme existirem:
- Laravel;
- Svelte;
- Vite;
- servidor web;
- Nginx/Apache;
- hospedagem;
- proxy;
- CDN;
- Service Worker/PWA;
- headers HTTP existentes.

### HTML e conteúdo dinâmico

HTML/documentos e respostas dinâmicas que precisam refletir a versão atual devem ser revalidados.

Quando apropriado, utilizar estratégia equivalente a:

`Cache-Control: no-cache`

A intenção é permitir armazenamento quando útil, mas exigir revalidação antes de reutilizar uma resposta potencialmente antiga.

Não confundir `no-cache` com `no-store`.

### Conteúdo sensível/autenticado

Para respostas realmente sensíveis, especialmente no `/panel`, avaliar onde:

`Cache-Control: no-store`

é apropriado.

Não aplicar `no-store` indiscriminadamente ao site inteiro.

### Assets versionados

JS/CSS gerados pelo Svelte/Vite com hash no nome devem continuar aproveitando cache de longa duração.

Exemplo:

`app.a81f32.js`

Nova build:

`app.f92bc1.js`

Como a URL muda, o navegador baixa o novo bundle naturalmente.

Aplicar o mesmo princípio, quando apropriado, a CSS versionado, fontes, imagens estáticas e demais assets imutáveis/versionados.

### Fluxo desejado após deploy

1. Usuário acessa o site.
2. HTML é revalidado.
3. Servidor entrega/confirma a versão atual.
4. HTML referencia os bundles da build atual.
5. Se os hashes mudaram, o navegador baixa os novos JS/CSS.
6. Assets imutáveis que não mudaram continuam sendo aproveitados do cache.

### Investigar outras camadas

Verificar se conteúdo antigo pode ser causado por cache do Laravel, servidor, CDN, proxy, Service Worker/PWA ou configuração da hospedagem.

Se houver Service Worker, revisar cuidadosamente sua estratégia de atualização.

### Não fazer

Não usar como solução principal:
- timestamps aleatórios em todas as URLs;
- `?random=...`;
- query strings geradas a cada request;
- desativação completa de cache para todos os arquivos;
- middleware aplicado cegamente a todas as respostas;
- mudanças que eliminem o benefício de cache dos assets versionados.

Centralizar a configuração sempre que possível.

---

# Validação

Antes de concluir:
1. Confirmar a versão do Quill.
2. Confirmar compatibilidade do módulo de image resize.
3. Testar inserção e redimensionamento de imagens.
4. Salvar, reabrir e renderizar publicamente uma imagem redimensionada.
5. Testar drag and drop de imagens entre diferentes posições/parágrafos.
6. Confirmar que o indicador visual de destino funciona durante o arraste.
7. Testar alinhamento à esquerda, centro e direita.
8. Confirmar que tamanho, posição e alinhamento persistem após salvar e reabrir.
9. Confirmar que movimentação e alinhamento participam corretamente de undo/redo.
10. Confirmar que a renderização pública reproduz o posicionamento salvo.
11. Testar embed de provedor permitido.
12. Testar URL inválida/origem não permitida e confirmar rejeição.
13. Salvar e reabrir conteúdo contendo embed.
14. Confirmar responsividade dos embeds.
15. Confirmar que as personalizações existentes do Quill continuam funcionando.
16. Verificar SSR/browser environment, se aplicável.
17. Inspecionar headers de HTML, conteúdo dinâmico, `/panel` e assets versionados.
18. Confirmar que um novo deploy não exige limpeza manual do cache para receber os novos bundles.
19. Confirmar que assets versionados continuam aproveitando cache.
20. Verificar Service Worker/CDN/proxy, se existirem.

# Resumo ao finalizar

Apresente:
- arquivos alterados;
- dependências adicionadas/removidas;
- versão do Quill encontrada;
- módulo de image resize escolhido e justificativa de compatibilidade;
- integração do resize com Svelte/Quill;
- forma de persistência do tamanho das imagens;
- implementação dos embeds;
- provedores/origens permitidos;
- medidas de segurança dos embeds/iframes;
- política de cache implementada;
- headers definidos para cada categoria relevante;
- configurações externas que ainda precisem ser feitas na hospedagem/CDN/servidor;
- testes manuais recomendados.
