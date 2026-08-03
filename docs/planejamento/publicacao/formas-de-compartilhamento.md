---
status: ativo
tipo: guia-publicacao
atualizado_em: 2026-08-03
---

# Formas de Compartilhamento

## Site VitePress

Formato principal de leitura. A documentação é escrita em Markdown e servida como site navegável.

### Quando Usar

- leitura diária;
- onboarding;
- revisão de arquitetura;
- consulta durante implementação;
- demonstrações para pessoas não técnicas.

## Repositorio

Formato principal de edição. A documentação fica versionada em `docs/` junto do código.

### Quando Usar

- equipe técnica;
- revisão por pull request;
- histórico de mudanças;
- consulta durante implementação;
- revisão do diff da documentação.

## Build Estático

Formato indicado para publicação ou hospedagem fora do ambiente Docker local.

### Quando Usar

- deploy em servidor estático;
- compartilhamento com alguém sem ambiente local;
- congelar uma versão para revisão;
- publicar documentação interna.

## Markdown Avulso

Formato para copiar uma página específica.

### Quando Usar

- discutir um módulo;
- revisar um fluxo operacional;
- enviar uma decisão específica.

## Nao Oficial Por Enquanto

- base de conhecimento externa;
- PDF consolidado;
- documentação dentro do painel.

Esses formatos podem existir no futuro, mas a fonte principal deve continuar sendo o Markdown versionado e publicado pelo VitePress.
