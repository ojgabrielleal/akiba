# Quando o site entrar no ar

Quando o site entrar no ar, ajuste `routes/web/public.php` para remover o prefixo `/site` e deixar as rotas publicas na raiz (`/`).

Trocar o grupo:

```php
Route::prefix("site")->middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
```

Por:

```php
Route::middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
```

Depois, reverta a alteracao feita agora e volte a nomear a rota publica raiz como `home`.

Trocar:

```php
Route::get('', 'render');
```

Por:

```php
Route::get('', 'render')->name('home');
```

Tambem remova o bloqueio do middleware `auth` dos grupos publicos, para desproteger as rotas publicas do login interno Laravel.

Trocar:

```php
Route::middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
```

Por:

```php
Route::middleware(['oauth.resolve', 'inertia'])->group(function () {
```
