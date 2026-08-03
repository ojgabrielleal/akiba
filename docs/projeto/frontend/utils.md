---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Utils

Utils são funções auxiliares reutilizáveis no frontend.

## Onde Ficam

```txt
resources/js/lib/utils
```

Áreas atuais:

```txt
access
formatters
media
presentation
timing
```

## Access

Funções ligadas a acesso, permissões e ações pendentes.

Exemplos:

```txt
permissions.js
oauthPendingAction.js
```

Uso comum:

```js
import { postPermissions } from "@/lib/utils";

const can = postPermissions();
```

## Formatters

Funções para formatar dados exibidos na interface.

Exemplo:

```txt
dateTime.js
```

Use quando a formatação é puramente visual e não precisa vir pronta do backend.

## Media

Helpers para mídia, imagens e placeholders.

Exemplo:

```txt
placeholders.js
```

## Presentation

Helpers de apresentação visual.

Exemplo:

```txt
gridStatus.js
```

Uso comum:

```js
import { resolveStatusBackground } from "@/lib/utils";
```

## Timing

Helpers de tempo e controle de execução.

Exemplo:

```txt
debounce.js
```

## Checklist

- A função é reutilizável?
- O nome explica o retorno?
- Ela não depende de DOM sem necessidade?
- Ela não mistura regra de backend?
- Foi exportada pelo `index.js` quando precisa ser importada via `@/lib/utils`?
