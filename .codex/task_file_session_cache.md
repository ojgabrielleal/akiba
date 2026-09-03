# Task: Migrar sessões e cache para File e remover workaround de limite de cookie

## Contexto

Atualmente a aplicação Laravel utiliza MySQL como banco de dados principal.

O servidor possui filesystem local disponível e o Redis não pode ser utilizado nesse ambiente.

Também identificamos anteriormente um problema relacionado ao tamanho dos cookies/sessão. Como workaround, foi feita uma alteração no retorno/listagem dos posts para evitar retornar/carregar todos os dados, com o objetivo específico de impedir que o limite de tamanho do cookie fosse ultrapassado.

Com a mudança da estratégia de armazenamento de sessão, esse workaround não deve mais ser necessário.

## 1. Alterar o armazenamento de sessão

Configurar o Laravel para utilizar o driver `file` para sessões.

A configuração esperada é equivalente a:

```env
SESSION_DRIVER=file
```

As sessões devem ser armazenadas pelo mecanismo padrão do Laravel em:

```text
storage/framework/sessions
```

Verificar as configurações atuais do projeto antes de realizar a alteração e garantir que nenhuma configuração existente dependa do armazenamento de sessão no banco de dados.

## 2. Alterar o armazenamento de cache

Configurar o cache da aplicação para utilizar o driver `file`.

A configuração esperada é equivalente a:

```env
CACHE_STORE=file
```

Utilizar o mecanismo nativo de file cache do Laravel, sem implementar sistema próprio de arquivos ou SQLite.

Verificar a versão do Laravel e respeitar os nomes/configurações utilizados pela versão atual do projeto.

## 3. Revisar a alteração anterior relacionada aos posts

Anteriormente foi implementada uma alteração para impedir que os posts retornassem/carregassem todos os dados devido ao risco de ultrapassar o limite de tamanho de cookie/sessão.

Localizar essa implementação e entender exatamente o que foi alterado.

Essa alteração foi um workaround para o problema de armazenamento anterior e não deve ser mantida apenas por causa do limite de cookie.

Remover ou normalizar essa lógica para que o fluxo dos posts volte ao comportamento correto esperado antes desse workaround.

IMPORTANTE:

- Não remover paginação ou otimizações legítimas que existam independentemente desse problema.
- Não passar a retornar dados desnecessários apenas porque o limite de cookie deixou de ser uma preocupação.
- Remover especificamente as limitações, cortes ou tratamentos criados exclusivamente para contornar o limite de cookie/sessão.
- Preservar otimizações reais de performance.
- Verificar todos os locais afetados pela alteração anterior antes de modificar o código.
- Não criar uma nova gambiarra para substituir a anterior.

## 4. Verificar uso indevido da sessão

Durante a investigação, verificar se listas completas de posts, collections, models ou payloads grandes estão sendo armazenados diretamente na sessão.

Mesmo utilizando `SESSION_DRIVER=file`, dados grandes que não precisam persistir entre requisições não devem ser colocados na sessão.

A mudança para `file` deve resolver a limitação relacionada ao armazenamento anterior, mas não deve ser usada como justificativa para armazenar dados desnecessários na sessão.

## 5. Limpeza e compatibilidade

Após as alterações:

- verificar `config/session.php`;
- verificar `config/cache.php`;
- verificar `.env` e `.env.example`;
- limpar/configurar corretamente o cache de configuração do Laravel;
- verificar se existem referências ao antigo driver de sessão/cache;
- verificar se existem migrations ou código relacionados ao armazenamento anterior que ainda sejam necessários;
- não remover migrations históricas sem necessidade;
- garantir que autenticação, flash messages, redirects e demais funcionalidades dependentes de sessão continuem funcionando.

## 6. Testes

Validar pelo menos:

1. login e logout;
2. persistência da autenticação entre requisições;
3. flash messages;
4. criação e leitura de cache;
5. expiração/invalidação do cache;
6. listagem de posts;
7. criação, edição e visualização de posts;
8. comportamento anteriormente afetado pelo limite de cookie;
9. ausência de erros relacionados a sessão/cookie grande;
10. funcionamento normal após limpar cache/configuração do Laravel.

## Resultado esperado

A arquitetura deve ficar:

```text
MySQL
└── Dados persistentes da aplicação

File
├── Sessões
└── Cache
```

O MySQL deve continuar sendo utilizado normalmente para os dados da aplicação.

Redis não deve ser introduzido.

SQLite não deve ser introduzido.

O workaround criado anteriormente nos posts exclusivamente para evitar estouro do limite de cookie deve ser removido/normalizado, restaurando o comportamento correto da aplicação sem desfazer otimizações legítimas que não tenham relação com esse problema.
