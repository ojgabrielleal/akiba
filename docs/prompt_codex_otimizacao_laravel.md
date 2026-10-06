# Prompt para Codex --- Otimização Laravel

Estamos trabalhando em um projeto **Laravel** já existente.

Quero que você analise o projeto antes de realizar alterações. Preserve
as regras de negócio, autenticação, contratos existentes e comportamento
funcional da aplicação. Evite refatorações arquiteturais desnecessárias.

## 1. Remover cache da aplicação

Analise **recursivamente toda a pasta `/app`**, respeitando a estrutura
e os padrões já existentes no projeto.

Inspecione, entre outros:

-   Controllers
-   Models
-   Services
-   Repositories
-   Jobs
-   Actions
-   Providers
-   Helpers
-   Demais classes e subdiretórios existentes dentro de `/app`

Identifique implementações de cache da aplicação, incluindo padrões
como:

-   `Cache::remember()`
-   `Cache::rememberForever()`
-   `Cache::get()`
-   `Cache::put()`
-   `Cache::forget()`
-   `cache()`
-   Chaves de cache
-   Invalidações
-   Métodos auxiliares criados exclusivamente para gerenciamento de
    cache
-   Outros mecanismos equivalentes encontrados no código

Remova o uso desse cache e ajuste a lógica para obter/processar os dados
diretamente quando necessário.

Após a remoção, elimine código que tenha se tornado inútil
exclusivamente por causa do cache, como invalidações, geração de chaves
e métodos auxiliares sem outra finalidade.

### Exceção obrigatória: sessões

**Não remova nem altere o armazenamento de sessões em arquivo.**

As sessões devem continuar funcionando normalmente e podem continuar
utilizando arquivos.

Não confunda cache da aplicação com sessão.

Não faça uma simples busca e substituição de chamadas de cache. Primeiro
entenda como cada cache é utilizado e então faça a alteração apropriada
sem quebrar o comportamento da aplicação.

## 2. Analisar e otimizar os Services para reduzir uso de CPU

Analise todos os Services existentes em `/app`, incluindo seus
subdiretórios.

Procure pontos com potencial de consumo excessivo ou desnecessário de
CPU, incluindo:

-   Loops desnecessários
-   Loops aninhados que possam ser simplificados
-   Múltiplas iterações sobre a mesma Collection
-   Transformações repetidas
-   Cálculos executados repetidamente
-   Consultas repetidas ao banco
-   Problemas de N+1
-   Carregamento de dados muito maior que o necessário
-   Processamento em PHP que possa ser realizado de maneira mais
    eficiente pelo banco
-   Serializações, conversões ou normalizações repetidas
-   Operações redundantes dentro da mesma request
-   Criação desnecessária de objetos ou Collections

Quando apropriado, prefira realizar filtros, agregações, seleção de
colunas e outras operações diretamente através de consultas eficientes
ao banco, em vez de carregar grandes conjuntos de dados em memória para
processá-los posteriormente em PHP.

Use recursos adequados do Laravel, Eloquent e PHP quando trouxerem ganho
concreto.

**Não faça micro-otimizações sem benefício justificável.**

Não altere regras de negócio apenas para melhorar performance.

Preserve, sempre que possível, as interfaces públicas dos Services para
não quebrar Controllers ou outros consumidores.

A remoção do cache não deve ser compensada com implementações
improvisadas que causem consumo excessivo de CPU.

## 3. Reduzir crescimento desnecessário do uso de disco

Faça uma auditoria do projeto procurando fontes de crescimento
desnecessário de armazenamento.

### Logs

Analise o uso de logs, principalmente `storage/logs`.

Verifique:

-   Crescimento indefinido de arquivos de log
-   Configuração de rotação
-   Retenção de logs antigos
-   Logs excessivos
-   `Log::info()`, `Log::debug()` ou equivalentes executados em loops
-   Informações registradas repetidamente sem necessidade
-   Logs gerados em todas as requests sem benefício operacional claro

Quando apropriado, configure uma estratégia segura de rotação e retenção
de logs.

Não remova logs importantes para diagnóstico de erros, segurança ou
operação do sistema.

### Arquivos temporários

Procure arquivos temporários gerados pela aplicação que possam
permanecer no disco depois de perderem sua utilidade.

Inclua na análise:

-   Arquivos temporários
-   Exports
-   PDFs gerados
-   Thumbnails
-   Imagens intermediárias
-   Arquivos criados durante processamentos
-   Outros artefatos temporários

Garanta que arquivos realmente temporários sejam removidos quando não
forem mais necessários.

### Uploads e arquivos órfãos

Analise fluxos de criação, atualização e exclusão que trabalham com
arquivos.

Procure situações em que:

-   Um registro é excluído, mas seu arquivo permanece no storage
-   Um arquivo é substituído, mas a versão anterior permanece abandonada
-   Uploads incompletos ou temporários permanecem indefinidamente
-   Arquivos deixam de possuir qualquer referência válida na aplicação

Faça correções somente quando for possível determinar com segurança que
o arquivo pode ser removido.

**Nunca exclua automaticamente arquivos persistentes ou dados enviados
pelos usuários apenas por parecerem não utilizados.**

### Jobs e Services

Analise Jobs, Services e outros processamentos que criem arquivos.

Garanta que recursos temporários sejam devidamente liberados e que
arquivos intermediários não sejam acumulados após o processamento.

### Sessões

As sessões continuarão utilizando arquivos.

Não altere essa decisão.

Porém, verifique se **sessões expiradas estão sendo limpas
corretamente** e se não existe algum problema fazendo arquivos de sessão
antigos se acumularem indefinidamente.

Não remova sessões válidas.

## 4. Segurança das alterações

Antes de modificar qualquer trecho:

1.  Entenda o comportamento atual.
2.  Identifique dependências daquele código.
3.  Faça a menor alteração necessária.
4.  Preserve regras de negócio e contratos existentes.
5.  Evite mudanças arquiteturais que não sejam necessárias para estes
    objetivos.

Não faça alterações relacionadas a imagens/BLOB no banco de dados.

Não faça otimizações ou limpezas de `vendor` ou `node_modules`.

Não exclua dados persistentes apenas para economizar espaço.

## 5. Validação

Após as alterações:

-   Verifique se o projeto continua funcionando corretamente.
-   Execute os testes existentes relacionados às áreas alteradas, quando
    disponíveis.
-   Verifique possíveis erros de sintaxe e imports não utilizados.
-   Confira se nenhuma referência ao cache removido ficou quebrada.
-   Confirme que as sessões em arquivo continuam funcionando.
-   Confirme que as otimizações não alteraram o resultado das regras de
    negócio.

## 6. Relatório final

Ao terminar, apresente um resumo objetivo contendo:

-   Arquivos alterados
-   Locais onde cache foi removido
-   Código relacionado a cache que se tornou obsoleto e foi removido
-   Services otimizados
-   Problema de performance encontrado em cada Service alterado
-   Otimização aplicada
-   Problemas de uso de disco encontrados
-   Alterações realizadas em logs
-   Problemas encontrados com arquivos temporários ou órfãos
-   Alterações relacionadas à limpeza de sessões expiradas, se
    necessárias
-   Pontos que foram identificados mas não alterados por não ser
    possível garantir segurança
-   Testes ou validações executados

Priorize alterações seguras, mensuráveis e compatíveis com a arquitetura
atual do projeto.
