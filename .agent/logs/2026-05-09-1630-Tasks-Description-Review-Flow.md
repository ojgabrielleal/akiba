# Fluxo de Tasks com Descricao e Revisao

Foi registrado o ajuste do fluxo de tasks para trocar o campo `content` por `description`, simplificar os status possiveis e exibir as tarefas pendentes no dashboard com informacoes de prazo, atraso e envio para avaliacao.

## Alteracoes Realizadas

### 1. Banco de Dados
- Criada a migration `2026_05_09_160258_update_tasks_schema_for_description_and_pending_status.php`.
- O campo `content` passou a se chamar `description`.
- O tipo de `description` foi ajustado para `string`.
- O enum `status` foi reduzido para:
  - `pending`
  - `in_review`
  - `completed`
- O status padrao passou a ser `pending`.
- O metodo `down` restaura os status anteriores e renomeia `description` de volta para `content`.

### 2. Backend de Tasks
- `CreateTaskAction` e `UpdateTaskAction` passaram a gravar `description` em vez de `content`.
- `TaskResource` passou a expor:
  - `is_overdue`
  - `days_remaining`
  - `status`
  - `description`
- `Task` passou a preencher `description` e deixou de usar os appends `is_over` e `is_due`.
- Foram adicionados os appends:
  - `days_remaining`
  - `is_overdue`
- O calculo de prazo agora retorna `0` para tasks concluidas ou vencidas e a quantidade de dias restantes para tasks futuras.
- O calculo de atraso ignora tasks concluidas e marca como atrasadas apenas tasks pendentes com prazo passado.

### 3. Fluxo de Revisao
- O metodo do dashboard para concluir task foi renomeado para `markTaskToReview`.
- A rota `POST /panel/dashboard/task/{task:uuid}/complete` permanece a mesma para o frontend, mas agora muda o status da task para `in_review`.
- O dashboard passou a buscar tasks incompletas que ainda nao estejam com status `completed`.

### 4. Interface do Dashboard
- `Dashboard.svelte` voltou a exibir o `TaskCarrousel` na tela inicial do painel privado.
- `TaskCarrousel` foi reorganizado para mostrar tasks em lista vertical dentro da secao.
- Cards pendentes usam gradiente azul, cards em avaliacao usam gradiente verde e tasks pendentes atrasadas usam gradiente vermelho.
- Tasks pendentes exibem um bloco de dias restantes.
- Tasks atrasadas exibem o aviso de strike.
- Tasks em avaliacao exibem o estado `Em avaliacao`.
- O botao de verificacao envia a task para revisao quando o usuario possui permissao.

### 5. Formulario e Grid
- `TaskForm` passou a usar `description` no estado do formulario e nos dados carregados para edicao.
- O campo de descricao foi alterado de `textarea` para `input` de texto.
- `TaskGrid` passou a colorir tasks com base em `is_overdue` e `days_remaining`.

### 6. Testes e Dados de Fabrica
- `TaskFactory` passou a gerar `description`.
- Os testes unitarios de `Task` foram atualizados para o status `pending`.
- Os testes dos atributos do model passaram a validar `days_remaining` e `is_overdue`.

### 7. Build
- Os assets em `public/build` e o `manifest.json` foram atualizados pela build do frontend.

---
**Data:** 09 de Maio de 2026  
**Responsavel:** Codex (IA)
