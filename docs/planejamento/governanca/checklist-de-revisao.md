---
status: ativo
tipo: checklist
atualizado_em: 2026-08-03
---

# Checklist de Revisao

Use este checklist ao revisar documentacao nova ou alterada.

## Estrutura

- [ ] A nota tem frontmatter.
- [ ] O titulo principal existe e e claro.
- [ ] A nota tem links internos para assuntos relacionados.
- [ ] O texto esta em Markdown puro.
- [ ] O conteudo esta no diretorio correto.

## Clareza

- [ ] A nota explica o objetivo.
- [ ] O publico da nota esta claro.
- [ ] O texto separa regra atual de pendencia.
- [ ] Termos de sistema estao consistentes com o codigo.

## Tecnico

- [ ] Arquivos citados existem ou sao intencionalmente planejados.
- [ ] Rotas citadas batem com `routes/web/private.php` ou rotas publicas.
- [ ] Permissoes citadas batem com policies, gates ou navbar.
- [ ] Status citados batem com enums ou constantes existentes.

## Operacao

- [ ] O fluxo descreve inicio, acao e resultado esperado.
- [ ] Cuidados operacionais foram registrados.
- [ ] O guia evita detalhes excessivos de implementacao.

## VitePress

- [ ] Links Markdown apontam para páginas existentes ou intencionalmente planejadas.
- [ ] A página aparece na sidebar quando fizer parte do fluxo principal.
- [ ] `./run.sh npm run docs:build` passa sem erro.
- [ ] Nomes de arquivos evitam acentos e espacos.
- [ ] Páginas pequenas foram preferidas a documentos longos demais.
