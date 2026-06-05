# Implementação de Permissões e Autorização

Esta implementação estabelece o sistema de autorização nativo do Laravel 12, mapeando as permissões definidas no `PermissionSeeder` para **Policies** e **Gates**.

## Componentes Implementados

### 1. Model User & Trait
- **Arquivo**: `app/Traits/HasPermissions.php`
- **Model**: `app/Models/User.php`
- **Descrição**: Adicionado o método `hasPermission($name)` que verifica no banco de dados se o usuário possui a permissão através de seus cargos (`Roles`).

### 2. Policies (Model-Specific)
Foram criadas 14 Policies para cobrir todos os modelos do sistema:

| Policy | Modelo | Permissões Mapeadas |
|---|---|---|
| `ActivityPolicy` | `Activity` | list, view, create, update, deactivate, participate |
| `TaskPolicy` | `Task` | list, view, create, update, deactivate, complete |
| `PostPolicy` | `Post` | list, view, create, update, deactivate, list.own, update.own |
| `UserPolicy` | `User` | list, view, create, update, deactivate, view.own, update.authority |
| `RolePolicy` | `Role` | list, view, create, update, remove |
| `CalendarPolicy` | `Calendar` | list, view, create, update, deactivate |
| `ReviewPolicy` | `Review` | list, view, create, update, deactivate |
| `EventPolicy` | `Event` | list, view, create, update, deactivate |
| `SongRequestPolicy` | `SongRequest` | list, reproduce, cancel, toggle |
| `ProgramPolicy` | `Program` | list, view, create, update, deactivate |
| `MusicPolicy` | `Music` | list, update, set.ranking |
| `PodcastPolicy` | `Podcast` | list, view, create, update, deactivate |
| `RepositoryPolicy` | `Repository` | list, view, create, update, deactivate |
| `PollPolicy` | `Poll` | list, view, create, update, deactivate, create.vote |

### 3. Gates (Global Access)
As permissões de acesso geral (que não pertencem a um modelo específico) foram registradas como **Gates** no `AppServiceProvider`.

- **Permissões registradas**: `dashboard.view`, `warning.view`, `locution.view`, `radio.view`, `marketing.view`, `media.view`, `administration.view`, `log.view`, `locution.start`, `locution.finish`, `listener.month.view`, `listener.month.set`.

## Exemplos de Uso

### No Blade/Svelte:
```html
{#if user.can('post.create')}
    <button>Criar Post</button>
{/if}
```

### No Controller:
```php
public function update(Post $post)
{
    $this->authorize('update', $post);
    // ...
}
```

### Nas Rotas:
```php
Route::get('/admin/logs', ...)->middleware('can:log.view');
```
