# Problema: bloqueio da interface no Laravel + Inertia

Estamos trabalhando em uma aplicação utilizando **Laravel + Inertia**.

Atualmente existe um problema de experiência do usuário: algumas páginas dependem de múltiplos dados vindos do backend e, quando uma consulta, service ou processamento demora, a página inteira acaba demorando para ficar disponível.

Queremos implementar uma estratégia de **carregamento progressivo e reativo**, permitindo que a interface seja exibida e utilizada o mais rápido possível enquanto dados secundários continuam sendo carregados.

## Objetivo

Analise a aplicação e implemente uma solução para que:

- Dados essenciais sejam carregados imediatamente.
- Dados pesados ou secundários não bloqueiem a renderização inicial.
- Componentes que ainda estão esperando dados apresentem um estado de loading.
- Sempre que fizer sentido, utilize skeleton loaders em vez de bloquear toda a página.
- Componentes independentes possam carregar seus dados independentemente.
- A navegação continue responsiva enquanto novas informações são buscadas.
- O usuário sempre tenha feedback visual quando alguma operação estiver carregando.

## 1. Analisar a implementação atual

Antes de alterar qualquer código, analise:

- Controllers Laravel
- Services
- Queries Eloquent
- Props enviadas através do Inertia
- Páginas Inertia
- Componentes utilizados pelas páginas
- Fluxos de navegação
- Requisições que executam consultas ou processamentos potencialmente pesados

Identifique quais dados realmente precisam estar disponíveis na primeira renderização e quais podem ser carregados posteriormente.

Não transforme todas as props indiscriminadamente em carregamento assíncrono.

## 2. Deferred Props

Nas páginas compatíveis, utilize os recursos nativos do Inertia para **Deferred Props**.

Por exemplo, quando apropriado:

```php
return Inertia::render('Dashboard', [
    'user' => $user,

    'stats' => Inertia::defer(
        fn () => $service->calculateStats()
    ),

    'recentActivity' => Inertia::defer(
        fn () => $service->getRecentActivity()
    ),
]);
```

Dados fundamentais para montar a página devem continuar sendo enviados normalmente.

Dados secundários, pesados ou independentes podem utilizar `Inertia::defer()` quando isso realmente melhorar o carregamento.

## 3. Loading nos componentes

Todo componente que receber dados de maneira adiada deve possuir um estado visual adequado enquanto aguarda os dados.

Utilize, conforme o contexto:

- skeleton loader;
- placeholder;
- spinner discreto;
- estado de loading específico do componente.

Evite substituir a página inteira por um único loading.

O objetivo é algo conceitualmente parecido com:

```text
Página
├── Header                ✓ disponível
├── Navegação             ✓ disponível
├── Informações básicas   ✓ disponível
├── Estatísticas          ⏳ skeleton
├── Posts recentes        ✓ disponível
└── Atividades            ⏳ skeleton
```

Quando `Estatísticas` terminar de carregar, somente aquele componente deve atualizar.

## 4. Interface não bloqueante

A prioridade principal desta tarefa é:

> Uma informação secundária lenta não deve impedir o usuário de visualizar ou interagir com partes da página que já poderiam estar disponíveis.

A aplicação deve transmitir sensação de carregamento progressivo, e não de página congelada.

## 5. Navegação Inertia

Analise também o comportamento durante navegações entre páginas.

Quando uma navegação ou ação demorar, deve existir feedback visual imediato.

Utilize os recursos apropriados do próprio Inertia para indicar navegação/carregamento sem desmontar desnecessariamente toda a interface.

Preserve componentes persistentes/layouts quando a arquitetura atual permitir.

## 6. Partial Reloads

Verifique locais onde atualizações de dados possam utilizar os recursos de **partial reload** do Inertia.

Se apenas uma determinada prop precisa ser atualizada, evite solicitar novamente dados pesados que não mudaram.

Exemplo conceitual:

```js
router.reload({
    only: ['stats'],
})
```

Utilize isso apenas onde trouxer benefício real.

## 7. Investigar a causa da lentidão

Deferred loading melhora a experiência do usuário, mas não deve esconder problemas de performance.

Durante a análise, procure também por:

- queries N+1;
- queries duplicadas;
- relacionamentos carregados sem necessidade;
- `get()` trazendo dados excessivos;
- ausência de paginação;
- processamento pesado dentro dos controllers;
- services executando operações desnecessárias;
- consultas repetidas durante a mesma requisição;
- payloads Inertia excessivamente grandes.

Quando encontrar um problema evidente, otimize-o sem alterar a regra de negócio.

## 8. Evitar excesso de requisições

Não crie uma requisição independente para cada pequeno componente.

Agrupe dados relacionados quando isso for mais eficiente.

O objetivo é encontrar equilíbrio entre:

- renderização rápida;
- carregamento progressivo;
- quantidade de requisições;
- carga sobre o Laravel;
- carga sobre o banco de dados.

## 9. Preservar comportamento existente

Não alterar:

- regras de negócio;
- permissões;
- autenticação;
- autorização;
- contratos existentes sem necessidade;
- comportamento funcional das páginas.

Não realizar grandes refatorações fora do escopo.

A mudança deve focar em **performance percebida, carregamento progressivo e responsividade da interface**.

## 10. Compatibilidade

Antes de implementar qualquer API específica do Inertia:

1. Identifique a versão do Laravel.
2. Identifique a versão do Inertia utilizada.
3. Identifique o framework frontend utilizado pelo projeto.
4. Confirme quais APIs estão disponíveis nessa versão.
5. Adapte a implementação à stack existente.

Não atualize Laravel, Inertia ou outras dependências importantes apenas para realizar esta tarefa sem necessidade.

## Resultado esperado

Depois das alterações, queremos que páginas que atualmente funcionam assim:

```text
requisição
    ↓
espera todos os dados
    ↓
página inteira aparece
```

passem a funcionar aproximadamente assim:

```text
requisição
    ↓
estrutura principal aparece
    ↓
interface já pode ser utilizada
    ↓
componentes secundários exibem skeleton/loading
    ↓
dados chegam progressivamente
    ↓
cada componente é atualizado
```

Faça primeiro uma análise da implementação existente e depois aplique as alterações nos pontos onde elas realmente forem necessárias.

Ao final, apresente um resumo contendo:

- arquivos alterados;
- props convertidas para deferred;
- componentes que receberam loading/skeleton;
- partial reloads implementados;
- problemas de performance encontrados;
- otimizações realizadas;
- pontos que permaneceram síncronos e o motivo.
