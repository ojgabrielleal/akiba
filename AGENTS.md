# Instruções do projeto

## Frontend

- Antes de criar ou alterar páginas, componentes ou widgets, leia `.codex/docs/ui-guidelines.md`.
- Reutilize componentes, variantes e tokens visuais existentes.
- Não replique códigos de cores diretamente nos componentes.
- Siga a organização atual entre `pages`, `layouts`, `components` e `widgets`.
- Implemente interfaces seguindo mobile-first.
- Exporte novos componentes públicos pelo arquivo `index.js` correspondente.
- Em páginas Svelte, extraia as props do Inertia por atribuição reativa e desestruturação de `$page.props`, como em `$: ({ onair: { data: [air] }, stream } = $page.props);`.

## Factories e seeders

- Em factories e seeders que precisem de uma imagem em destaque, use `public/img/placeholders/avatar.webp` como imagem padrão, exceto para imagens ou logos de programa.
- Nas factories que geram dados para o player, campos que representem a imagem ou logo do programa devem usar a imagem do próprio programa; ao criar um programa fictício sem imagem própria, use `public/img/placeholders/program.webp`.
- Nas factories que geram dados para o player, o campo `phrase.icon` deve usar um dos ícones cadastrados em `resources/js/data/locution/icon.json`; não use nele a imagem do programa, placeholders genéricos nem URLs aleatórias.
- Ao concluir uma tarefa que altere factories ou seeders, se os containers estiverem ativos e o `.env` definir `APP_ENV=local`, execute `./run.sh artisan migrate:fresh --seed`.

## Ambiente de desenvolvimento

- Antes de executar qualquer comando que dependa de PHP, Node.js ou MySQL, levante os containers do projeto com `./run.sh up`.

## Fluxo Git

- Antes de iniciar qualquer tarefa que altere arquivos, execute `git status` e identifique todas as alterações existentes.
- Crie a branch da tarefa antes de realizar novas alterações, partindo da `main` e usando um nome descritivo, como `feat/nome-da-tarefa` ou `fix/nome-da-correcao`.
- Se o diretório de trabalho já contiver alterações, identifique a qual tarefa elas pertencem antes de criar ou trocar de branch; não misture, descarte, mova ou inclua alterações alheias sem autorização.
- Uma tarefa pode conter mais de um objetivo, mas cada objetivo deve resultar em um grupo coerente de alterações e em seu próprio commit.

## Finalização de tarefas

- Quando o usuário informar que uma tarefa foi finalizada, verifique se os containers do projeto estão ativos.
- Se os containers estiverem ativos e a tarefa tiver alterado qualquer arquivo do frontend, execute `./run.sh npm run build`.
- Antes de criar commits, execute os testes aplicáveis à tarefa; quando a tarefa envolver PHP, execute a suíte com `./run.sh artisan test`.
- Não crie commits nem faça merge enquanto houver testes ou builds aplicáveis falhando.
- Após os testes e o build aplicável terminarem com sucesso, analise `git status` e `git diff` para identificar e separar as alterações por objetivo ou tarefa.
- Crie commits separados para cada grupo coerente de alterações, adicionando ao stage somente os arquivos pertencentes ao respectivo grupo; não use `git add .` e não inclua alterações alheias no commit.
- Para cada grupo, use `git commit -m "<mensagem descritiva da alteração>"`.
- Depois dos commits, confirme que a branch da tarefa não possui alterações pendentes.
- Com a branch limpa e todas as validações aprovadas, volte para a `main` e faça o merge com `git merge --no-ff <branch-da-tarefa>`.
- Se houver conflitos, alterações pendentes ou falhas nas validações, interrompa o merge e informe o usuário.
- Não exclua automaticamente a branch da tarefa após o merge.
