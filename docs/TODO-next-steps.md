# Próximas etapas

## 1. Validar a interface atual

Antes de iniciar novas refatorações ou desenvolver funcionalidades, precisamos rodar a aplicação e navegar pela interface completa.

Objetivos:

- verificar se as mudanças recentes não quebraram nenhum fluxo;
- conferir se todas as páginas continuam carregando corretamente;
- testar formulários, ações, navegação e estados de permissão;
- identificar componentes que ficaram visualmente estranhos;
- verificar erros no console do navegador e requisições com falha;
- registrar qualquer regressão encontrada antes de continuar.

## 2. Revisar Seeders e Factories

Vamos revisar os Seeders e Factories do projeto de forma geral.

Pontos para analisar:

- organização e responsabilidade de cada Seeder;
- dados duplicados, obsoletos ou inconsistentes;
- dependências e ordem de execução entre Seeders;
- idempotência, garantindo que possam ser executados novamente com segurança;
- alinhamento entre permissões, roles e regras atuais da aplicação;
- cobertura das Factories para os models existentes;
- estados reutilizáveis nas Factories;
- consistência dos dados gerados e dos relacionamentos;
- utilidade das Factories para testes e ambiente de desenvolvimento.

## 3. Revisar o frontend Svelte

Vamos analisar a estrutura atual do frontend em Svelte para encontrar oportunidades de reutilização e simplificação.

Objetivos:

- identificar trechos repetidos que podem virar componentes reutilizáveis;
- avaliar componentes grandes ou com responsabilidades demais;
- simplificar componentes difíceis de entender ou manter;
- revisar a composição entre páginas, layouts, widgets e componentes básicos;
- verificar se props, eventos e estados estão sendo usados de forma consistente;
- evitar abstrações prematuras e extrair somente o que realmente for reutilizável;
- preservar o comportamento e a aparência atuais durante a refatoração.

## 4. Revisar Utils e Stores do frontend

Também vamos revisar as camadas de `utils` e `stores`.

Pontos para analisar:

- funções duplicadas ou específicas demais;
- responsabilidades que deveriam ficar próximas dos componentes;
- helpers que podem ser simplificados ou removidos;
- organização e nomenclatura dos arquivos;
- stores globais que poderiam ser estado local;
- estados compartilhados que realmente justificam uma Store;
- consistência da leitura e atualização dos estados;
- dependências desnecessárias entre Utils, Stores e componentes.

## 5. Entender as convenções atuais de Svelte e JavaScript

Antes de propor mudanças maiores, precisamos documentar como Svelte e JavaScript são usados hoje no projeto.

Vamos avaliar:

- convenções atuais de arquivos, componentes, props, eventos e estado;
- padrões de nomenclatura e organização;
- uso da API e da sintaxe da versão atual do Svelte;
- diferenças entre o padrão existente e as recomendações atuais do ecossistema;
- pontos que podem ser melhorados sem gerar uma migração grande demais.

Com esse diagnóstico, devemos preparar uma sugestão para adoção de TypeScript no frontend.

A proposta precisa considerar:

- benefícios reais para este projeto;
- custo e risco da migração;
- estratégia gradual, sem exigir conversão completa de uma vez;
- configuração de Svelte, Vite e ferramentas relacionadas;
- tipagem de props, formulários, respostas do Inertia, Utils e Stores;
- convivência temporária entre arquivos JavaScript e TypeScript;
- ordem recomendada para iniciar a migração.

## 6. Preparar a página de Administração para o módulo de Calendário

Vamos preparar a página de Administração para receber o primeiro módulo de Calendário.

Nesta etapa devemos apenas:

- entender como o módulo se encaixará na página;
- revisar as props e os componentes necessários;
- planejar a estrutura visual e os pontos de integração;
- identificar dependências de backend e frontend;
- documentar o escopo inicial da funcionalidade.

O desenvolvimento do módulo de Calendário será tratado como uma feature separada e deverá acontecer em uma branch própria.

Não devemos iniciar a implementação dessa feature na mesma branch das revisões e refatorações gerais.

## Ordem sugerida

1. Rodar e validar toda a interface atual.
2. Corrigir regressões encontradas.
3. Revisar Seeders e Factories.
4. Analisar componentes Svelte e oportunidades de reutilização.
5. Simplificar componentes, Utils e Stores.
6. Documentar as convenções atuais de Svelte e JavaScript.
7. Elaborar a proposta de adoção gradual de TypeScript.
8. Planejar a integração do módulo de Calendário na página de Administração.
9. Criar uma branch separada para desenvolver a feature de Calendário.
