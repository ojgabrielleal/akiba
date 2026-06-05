# Revisao do Type em Review

Foi avaliada a criacao de um accessor `type` no model `Review`, mas a abordagem foi revertida para manter o status editorial centralizado em `Post`.

## Alteracoes Realizadas

### 1. Remocao do Append no Model
- O append `type` foi removido do model `app/Models/Review.php`.
- O accessor `type()` tambem foi removido do model `Review`.
- O import `Illuminate\Database\Eloquent\Casts\Attribute` deixou de ser necessario e foi removido.

### 2. Decisao de Modelagem
- `Review` permanece como extensao de `Post`, sem possuir um `type` proprio.
- O status editorial principal continua pertencendo ao model `Post`.
- As `opinions` continuam podendo ter seus proprios valores de `type`, como `published`, `revision` e `draft`.

### 3. Direcionamento
- A regra para exibir uma review como `revision` quando alguma `opinion` estiver em revisao deve ser tratada na camada de Resource.
- Essa abordagem preserva o contrato do grid, que segue consumindo a chave `type`, sem criar conflito semantico no model `Review`.

### 4. Escopo
- Nenhum teste ou factory foi alterado.
- Nenhum controller, arquivo de frontend ou resource foi alterado neste ajuste.

### 5. Validacao
- A sintaxe PHP do model `Review` deve ser validada apos a remocao do accessor.

---
**Data:** 16 de Maio de 2026  
**Responsavel:** Codex (IA)
