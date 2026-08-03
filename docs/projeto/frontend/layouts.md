---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Layouts

Layouts definem a estrutura comum de uma área do site. Eles ficam ao redor do conteúdo da página.

## Onde Ficam

```txt
resources/js/lib/layouts/private/Layout.svelte
resources/js/lib/layouts/public/Layout.svelte
```

## Layout Privado

Use para telas do painel.

Responsabilidades comuns:

- navbar privada;
- toaster de flash messages;
- polling de dados compartilhados do painel;
- estrutura principal com `<slot />`;
- elementos fixos do painel, como métricas de stream.

Exemplo de uso:

```svelte
<Layout>
    <Section title="Dashboard">
        ...
    </Section>
</Layout>
```

## Layout Público

Use para páginas abertas.

Responsabilidades comuns:

- navbar pública;
- player público;
- footer;
- elementos globais como consentimento de cookies;
- estrutura visual do site público.

## Estrutura Esperada

```txt
<script>
    imports globais
    setup de ciclo de vida
    polling ou estado global, quando necessário
</script>

componentes globais
header
main
    <slot />
footer
```

## Regras

- Page escolhe o layout.
- Layout não deve conhecer detalhes de um módulo específico.
- Layout pode cuidar de comportamento global da área.
- Layout deve expor o conteúdo via `<slot />`.

## O Que Evitar

- Colocar formulário de módulo dentro do layout.
- Colocar regra de página específica no layout.
- Criar muitos layouts quase iguais.
- Fazer layout depender de prop que só uma página usa.
