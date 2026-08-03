---
status: ativo
tipo: checklist
atualizado_em: 2026-08-03
---

# Checklist Pre-Publicacao

Use antes de compartilhar a documentacao fora da rotina interna.

## Conteudo

- [ ] A nota ou pacote tem contexto suficiente para leitura.
- [ ] Links principais foram revisados.
- [ ] Notas rascunho foram identificadas.
- [ ] Decisoes propostas nao foram apresentadas como aprovadas.
- [ ] Fluxos operacionais foram validados quando necessario.

## Seguranca

- [ ] Nao ha credenciais.
- [ ] Nao ha tokens.
- [ ] Nao ha dados pessoais sensiveis.
- [ ] Nao ha detalhes internos que nao devem sair da equipe.
- [ ] Informacoes de permissao foram descritas sem expor riscos desnecessarios.

## Qualidade

- [ ] Rodar [auditoria-local](../automacao/auditoria-local) quando o ambiente estiver disponivel.
- [ ] Conferir [checklist-de-revisao](../governanca/checklist-de-revisao).
- [ ] Atualizar [historico](../historico) se a publicacao representar marco relevante.
- [ ] Confirmar que [index](../index) continua sendo uma boa entrada.

## Distribuicao

- [ ] Definir destinatarios.
- [ ] Definir se sera snapshot ou link do repositorio.
- [ ] Definir se a pessoa vai acessar o site VitePress local, um deploy estático ou o Markdown no repositório.
