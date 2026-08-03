---
status: ativo
tipo: riscos
atualizado_em: 2026-08-03
---

# Riscos de Obsolescencia

## Riscos Principais

## Codigo Muda e Documentacao Nao

- Impacto: guia tecnico fica incorreto.
- Mitigacao: atualizar nota do modulo junto da implementacao.

## Fluxo Real Diverge do Guia Operacional

- Impacto: equipe passa a ignorar a documentacao.
- Mitigacao: validar fluxos com usuarios reais do painel.

## Links Ficam Ambiguos

- Impacto: VitePress pode gerar link quebrado ou página fora da navegação.
- Mitigacao: usar nomes claros e caminho completo quando necessario.

## Backlog Vira Deposito

- Impacto: planejamento perde utilidade.
- Mitigacao: revisar backlog ao menos uma vez por ciclo.

## Decisoes Nao Sao Registradas

- Impacto: escolhas importantes se perdem.
- Mitigacao: usar [criterios-de-decisao](../governanca/criterios-de-decisao) antes de fechar mudancas estruturais.

## Documentacao Fica Tecnica Demais

- Impacto: operacao nao consegue usar os guias.
- Mitigacao: manter separacao entre `modulos/` e `operacao/`.

