---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Rotas

Rotas são a porta de entrada HTTP. Elas devem deixar claro o contexto: público, privado, API ou provisório.

## Onde Ficam

```txt
routes/web
```

## Contextos

| Contexto | Uso |
| --- | --- |
| Público | Páginas abertas, leitura de posts, rádio, contato e busca. |
| Privado | Painel administrativo protegido por autenticação. |
| API | Endpoints consumidos por integrações ou scripts. |
| Provisório | Rotas temporárias durante migração ou desenvolvimento. |

## Arquitetura

```txt
imports dos controllers

grupo de middleware
grupo de prefixo
grupo de nomes

rotas de página
rotas de CRUD
rotas de ações pontuais
```

## Exemplo

```php
Route::middleware(['auth'])
    ->prefix('panel')
    ->name('panel.')
    ->group(function () {
        Route::get('/post', [PostPageController::class, 'render'])->name('post');
        Route::post('/post', [PostController::class, 'store'])->name('post.store');
        Route::put('/post/{post}', [PostController::class, 'update'])->name('post.update');
    });
```

## Ordem Recomendada

Dentro de um grupo, organize assim:

1. rotas de página com `GET`;
2. rotas de criação com `POST`;
3. rotas de atualização com `PUT` ou `PATCH`;
4. rotas de exclusão/desativação com `DELETE`;
5. rotas invocáveis de ações pontuais.

Exemplo:

```php
Route::get('/task', [TaskPageController::class, 'render'])->name('task');
Route::post('/task', [TaskController::class, 'store'])->name('task.store');
Route::put('/task/{task}', [TaskController::class, 'update'])->name('task.update');
Route::delete('/task/{task}', DeactivateTaskController::class)->name('task.deactivate');
Route::post('/task/{task}/complete', CompleteTaskController::class)->name('task.complete');
```

## Nome das Rotas

Use nomes previsíveis:

```txt
panel.post
panel.post.store
panel.post.update
panel.post.deactivate
panel.task.complete
```

Isso ajuda o frontend, redirects e testes a encontrarem a rota sem depender do caminho literal.

## O Que Evitar

- Closure com regra de negócio dentro da rota.
- Rota privada fora do middleware de autenticação.
- Nome de rota genérico, como `save` ou `action`.
- Controller de página recebendo formulário.
- Misturar rotas públicas e privadas no mesmo grupo.

## Checklist

- A rota está no arquivo de contexto certo?
- O nome da rota segue o padrão do grupo?
- A rota chama controller, não lógica inline?
- A rota de página usa controller de página?
- A rota de ação pontual usa controller invocável quando fizer sentido?
