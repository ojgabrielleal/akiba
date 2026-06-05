# Robustez de Dados e Padronização de Permissões (.module.view)

Esta atualização focou na resolução de erros críticos de tempo de execução (runtime errors) em produção e na reorganização do sistema de permissões para maior clareza entre acesso a módulos e visualização de recursos.

## 1. Correção de Erros de "Undefined" em Produção

Foram corrigidos diversos pontos onde a interface quebrava ao tentar acessar propriedades de objetos nulos ou arrays incompletos vindos da API:

- **`PostForm.svelte`**: Implementada lógica para garantir que os arrays de `categories` e `references` sempre tenham pelo menos 2 elementos. Isso evita o erro "Cannot read properties of undefined (reading 'name')" quando uma matéria tem poucas tags ou nenhuma referência.
- **`PollForm.svelte`**: Adicionado *optional chaining* e fallbacks no carregamento das opções de enquete.
- **`MobilePlayer.svelte` & `MainPlayer.svelte`**: Proteção completa do objeto `onair`. Agora, se a rádio estiver offline ou os dados vierem parciais, o player exibe fallbacks seguros (ex: "Akiba rádio", "Estamos offline") em vez de travar a página.
- **`SongRequestForm.svelte`**: Adicionadas verificações de nulidade nas mensagens de sucesso e de "indisponível" que exibem informações do DJ.

## 2. Padronização de Permissões e Gates

As permissões de acesso aos módulos do painel administrativo foram renomeadas no `PermissionSeeder.php` para seguir um padrão mais explícito, diferenciando o "poder entrar no módulo" do "poder visualizar um recurso específico".

- **Nova Nomenclatura:** Permissões gerais de página agora utilizam o sufixo `.module.view` (ex: `post.module.view` em vez de `post.view`).
- **`AppServiceProvider.php`**: Atualizado o registro de Gates para refletir os novos nomes:
    - `dashboard.module.view`
    - `warning.module.view`
    - `post.module.view`
    - `locution.module.view`
    - `radio.module.view`
    - `podcast.module.view`
    - `marketing.module.view`
    - `media.module.view`
    - `administration.module.view`
    - `log.module.view`
- **`navbar.json`**: Atualizado para que os links do menu lateral utilizem as novas permissões de módulo.

## 3. Manutenção de Políticas (Policies)

As Políticas de recurso (`PostPolicy`, `RolePolicy`, `UserPolicy`, etc.) continuam utilizando as permissões específicas (`.list`, `.view`, `.create`, `.update`) para manter a granularidade fina de controle sobre os registros individuais, enquanto os novos Gates cuidam do acesso macro aos módulos.
