# Ideia: feedback de operações controlado pelo front-end

## Status

Ideia em avaliação. Não implementar sem uma nova revisão do impacto no fluxo do Inertia.

## Contexto atual

Os controllers utilizam `HasFlashMessages` para retornar um redirect com uma mensagem na sessão:

```php
return back()->with('flash', $flash);
```

O `HandleInertiaRequestsMiddleware` compartilha a mensagem com o front-end, e o `FlashToaster` observa `page.props.flash` para exibi-la.

Esse redirect também permite que os formulários do Inertia recebam uma nova página, atualizem suas props e decidam se devem fechar o modal.

## Proposta

Transferir para o front-end a responsabilidade de apresentar o resultado das operações.

O backend ficaria responsável por:

- executar e proteger a regra de negócio;
- retornar o status HTTP correto;
- retornar um código semântico estável;
- opcionalmente retornar uma mensagem técnica ou contextual.

Exemplo de conflito:

```json
{
  "code": "ROLE_HAS_MEMBERS",
  "message": "A função possui membros vinculados."
}
```

```http
409 Conflict
```

O front-end ficaria responsável por:

- escolher a mensagem amigável;
- escolher ícone e aparência;
- disparar o toast;
- fechar ou manter o modal aberto;
- atualizar apenas os dados necessários da página.

## Possível estrutura no front-end

Criar um store global de notificações e um catálogo baseado nos códigos retornados pelo backend:

```js
const feedback = {
    ROLE_HAS_MEMBERS: {
        type: "error",
        icon: "⛓️",
        message: "Tire os vínculos antes! Senão dá ruim.",
    },
};
```

O `FlashToaster` observaria esse store em vez de depender exclusivamente de `page.props.flash`.

## Impactos

O `FlashToaster` pode centralizar a exibição, mas não consegue capturar sozinho as respostas feitas pelo cliente interno do Inertia.

Para remover o redirect, será necessário revisar as mutações atuais feitas com `useForm` e `router`. Uma alternativa é utilizar Axios e uma camada central de tratamento de respostas.

Também será necessário tratar explicitamente:

- erros de validação `422`;
- conflitos `409`;
- erros de autorização `403`;
- estado de processamento;
- upload e progresso de arquivos;
- atualização de listagens;
- fechamento de modais;
- preservação de scroll.

## Consequências arquiteturais

Com a migração completa:

- `HasFlashMessages` poderá ser removido;
- as exceptions poderão carregar mensagem, código semântico e status;
- o backend deixará de definir tom, ícone e texto de interface;
- o front-end passará a controlar integralmente a experiência de feedback.

## Estratégia possível

Fazer a migração de forma gradual:

1. Manter redirects e flash nos CRUDs atuais.
2. Aplicar respostas JSON primeiro em ações específicas, como conflitos, toggles e refreshes.
3. Criar o store e o catálogo global de feedback no front-end.
4. Padronizar o contrato de resposta do backend.
5. Migrar os formulários somente depois de garantir tratamento equivalente para validação e uploads.

