# Plano De Testes De UI

## Objetivo

Criar uma camada pequena e confiavel de testes de interface para validar que a aplicacao funciona no navegador de verdade, cobrindo a integracao entre Laravel, Inertia, Svelte, rotas, permissoes e formularios.

Estes testes nao substituem os testes Laravel. Eles complementam a suite atual pegando problemas que so aparecem quando a tela renderiza e o usuario interage com ela.

## Escopo Inicial

Comecar com poucos fluxos criticos, evitando uma suite grande e lenta.

Prioridades:

- Paginas publicas essenciais carregam sem erro.
- Formularios publicos exibem validacao e enviam dados corretamente.
- Login e acesso ao painel funcionam.
- Paginas privadas principais respeitam autenticacao e permissao.
- Fluxos importantes do painel permitem acao basica sem quebrar a UI.

## Ferramenta Recomendada

Usar Cypress como primeira opcao.

Motivos:

- Facil de rodar e debugar localmente.
- Boa experiencia visual para acompanhar cliques, inputs e respostas.
- Adequado para fluxos Inertia/Svelte.
- Mais simples para uma equipe com foco inicial em testes de UI.

Nao adicionar Cypress sem uma fase dedicada de instalacao e configuracao.

## Regras

- Nao rodar `./run.sh npm run build` automaticamente.
- Nao subir Docker automaticamente; pedir para o usuario executar `./run.sh up` quando necessario.
- Antes de rodar testes de UI, garantir que backend, Vite e MySQL estejam ativos.
- Como os testes locais usam o banco `akiba`, assumir que a suite pode destruir e recriar o schema.
- Cada teste deve preparar os dados de que precisa.
- Evitar depender de dados cadastrados manualmente.
- Preferir seletores estaveis, como `data-cy`, em vez de textos longos ou classes CSS.

## Estrutura Sugerida

Arquivos:

```text
cypress/
  e2e/
    public-contact.cy.js
    auth.cy.js
    private-pages.cy.js
  support/
    commands.js
    e2e.js
cypress.config.js
```

Comandos desejados:

```bash
./run.sh npm run cy:open
./run.sh npm run cy:run
```

Scripts sugeridos no `package.json`:

```json
{
  "cy:open": "cypress open",
  "cy:run": "cypress run"
}
```

## Preparacao De Dados

Opcoes possiveis:

1. Rodar `./run.sh artisan migrate:fresh --seed` antes da suite Cypress.
2. Criar comandos Artisan especificos para seed de teste de UI.
3. Criar endpoints internos apenas para ambiente `testing/local`, se for realmente necessario.

Preferencia inicial: usar `migrate:fresh --seed`, porque o projeto local ja aceita destruir o banco.

## Fase 1: Instalacao E Smoke Test

Objetivo: confirmar que Cypress abre a aplicacao local.

Tarefas:

- Instalar Cypress como dependencia de desenvolvimento.
- Criar `cypress.config.js`.
- Adicionar scripts no `package.json`.
- Criar primeiro teste visitando a home.
- Validar que a pagina nao retorna erro 500.

Teste minimo:

- Visitar `/`.
- Confirmar que a pagina carregou.
- Confirmar que nao houve erro visivel de servidor.

## Fase 2: Paginas Publicas

Objetivo: garantir que paginas publicas principais renderizam no navegador.

Fluxos:

- Home.
- Pagina de contato.
- Pagina de leitura de post/evento/review, se houver seed confiavel.
- Pagina de radio/midia publica, se for parte critica da experiencia.

Validacoes:

- Status visual basico da pagina.
- Elementos principais aparecem.
- Links principais navegam.
- A pagina nao fica em branco.

## Fase 3: Formularios Publicos

Objetivo: cobrir o comportamento que o usuario comum usa sem login.

Fluxos:

- Enviar formulario valido.
- Enviar formulario vazio e ver mensagens de erro.
- Enviar dados invalidos importantes, como idade fora do intervalo.

Validacoes:

- Campos aceitam input.
- Erros aparecem quando esperado.
- Submissao valida mostra feedback de sucesso ou redireciona corretamente.
- O backend persiste a submissao.

## Fase 4: Login E Sessao

Objetivo: validar entrada e saida do painel.

Fluxos:

- Usuario sem login e redirecionado ao acessar rota privada.
- Usuario faz login com credenciais validas.
- Usuario com credenciais invalidas recebe erro.
- Usuario autenticado consegue acessar o painel.
- Usuario consegue sair da sessao.

Observacao:

- Se o seeder cria usuario admin padrao, usar esse usuario.
- Se nao houver seeder confiavel, criar um comando de seed proprio para UI.

## Fase 5: Paginas Privadas Criticas

Objetivo: garantir que as telas principais do painel abrem e recebem dados.

Fluxos iniciais:

- `/panel/media`
- `/panel/reports`
- `/panel/inactive`

Validacoes:

- Usuario sem permissao nao acessa.
- Usuario com permissao acessa.
- Titulo ou conteudo principal aparece.
- Estados vazios nao quebram a tela.
- Componentes com tabelas/listas renderizam pelo menos um item quando houver seed.

## Fase 6: Acoes No Painel

Objetivo: cobrir pequenas acoes reais de usuario.

Fluxos candidatos:

- Reativar item inativo.
- Excluir item inativo.
- Aprovar submissao de formulario.
- Rejeitar submissao de formulario.

Validacoes:

- Botao ou acao aparece apenas para usuario autorizado.
- Acao mostra feedback.
- Estado muda no banco.
- A tela atualiza sem erro.

## Fase 7: Integracao Com CI

Objetivo: deixar os testes de UI reproduziveis fora da maquina local.

Tarefas:

- Definir comando unico para preparar banco.
- Definir comando unico para subir servidor Laravel e Vite.
- Rodar Cypress headless.
- Salvar screenshots/videos em falha.

Nao bloquear deploy com Cypress ate a suite ficar estavel.

## Criterios De Pronto

- Cypress instalado e documentado.
- Pelo menos 3 fluxos criticos cobertos.
- Dados preparados automaticamente.
- Comando local claro.
- Falhas geram evidencia util.
- Testes nao dependem de dados manuais.

## Fora Do Escopo Inicial

- Testar todos os componentes Svelte isoladamente.
- Testar layout pixel-perfect.
- Cobrir todos os browsers.
- Criar suite grande de regressao visual.
- Rodar build automaticamente.
