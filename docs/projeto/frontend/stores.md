---
status: ativo
tipo: guia-frontend
atualizado_em: 2026-08-03
---

# Stores

Stores guardam estado compartilhado entre componentes que não têm relação direta de pai e filho.

## Onde Ficam

```txt
resources/js/lib/stores
```

Arquivos atuais:

```txt
playerStore.js
index.js
```

## Quando Usar

Use store quando:

- o mesmo estado aparece em várias partes da interface;
- componentes distantes precisam reagir ao mesmo dado;
- o estado representa comportamento global, como player;
- passar props por muitas camadas deixaria o código confuso.

Não use store para estado simples de formulário ou controle local de modal.

## Estrutura

```js
import { writable } from "svelte/store";

const initialState = {
    playing: false,
    loading: false,
};

export const player = writable(initialState);
```

## Padrão do Player

O `playerStore.js` concentra estado e comportamento do player:

```txt
playing
loading
volume
muted
error
waveLevels
```

Ele também encapsula detalhes de áudio, análise de frequência, fallback visual e controle de loading.

## Regras

- Store deve ter estado inicial claro.
- Funções auxiliares podem ficar no próprio arquivo quando pertencem ao estado.
- Componentes devem consumir store sem duplicar lógica global.
- Estado local continua local quando não precisa ser compartilhado.

## O Que Evitar

- Colocar todos os estados da aplicação em store.
- Guardar dados de formulário grande em store sem necessidade.
- Fazer store depender de componente específico.
- Criar store para esconder problema de organização de props.
