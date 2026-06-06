# Integração das Permissões nos Controllers Privados

Esta implementação aplicou o sistema nativo de Policies (criadas anteriormente) e de Gates em todos os controladores privados do painel, garantindo que o front-end (Inertia/Vue/Svelte) lide com faltas de permissão de maneira graciosa sem falhar com o código HTTP 403.

## Padrão Arquitetural Implementado

A regra estabelecida e propagada para toda a aplicação foi: **Substituir o retorno agressivo de "403 Forbidden" por retornos nulos (`null` no PHP, que é interpretado como `undefined`/vazio pelo framework JS)**.

Isso é de suma importância em arquiteturas com Inertia onde funções como `indexRole()` ou `indexPosts()` injetam propriedades diretamente num único método `render`. Disparar um 403 nesses componentes quebraria a visualização de toda a página que renderizava múltiplos recursos.

### Exemplo do Padrão Adotado

```php
    public function updatePost(Request $request, Post $post)
    {
        if ($request->user()->cannot('update', $post)) {
            return null; // Frontend Svelte ignorará ou lidará silenciamente com a negativa de acesso
        }

        $post->fill([ ... ]);
```

## Controllers Atualizados

Foram protegidos um total de 10 Controllers do diretório `app/Http/Controllers/Private/`:

1. **`AdministrationController`**: Permissões para Cargos, Permissões, Usuários e Atividades.
2. **`DashboardController`**: Validação seccionada para Atividades, Tarefas, Logs de Calendário e Posts na tela mestre.
3. **`EventController`**: Protegido por `EventPolicy`.
4. **`LocutionController`**: Usando Gates nativos (`locution.start` e `locution.finish`) para o painel de rádio.
5. **`MediaController`**: Lidando com Eventos e Votos de Enquetes.
6. **`PodcastController`**: Checagens na `PodcastPolicy`.
7. **`PostController`**: Simplificado, removendo "ifs" literais para resolver listas próprias vs de outros de forma nativa pela Policy.
8. **`ProfileController`**: Tratativas de escopo individual (perfil próprio).
9. **`RadioController`**: Refatorações aplicadas sob Rankings, Listeners e Streamers.
10. **`RepositoryController`**: Lógica de visibilidade sob `RepositoryPolicy`.
11. **`ReviewController`**: Regras padrão mapeadas pela `ReviewPolicy`.

## Ajuste em ProfileController & UserPolicy

As regras do perfil, como a diferenciação do que um usuário comum pode ver/alterar versus o administrador, foram concentradas de forma robusta e atômica dentro da `UserPolicy`. A regra final de update do sistema agora é definida unicamente como:

```php
    public function update(User $user, User $model): bool
    {
        if ($user->hasPermission('user.view') || $user->hasPermission('user.update')) {
            return true;
        }

        return $user->hasPermission('user.view.own') && $user->id === $model->id;
    }
```
Isso desobstruiu o código do `ProfileController`, permitindo que ele dependa 100% do motor de policies do Laravel.
