---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Actions

Actions executam regra de negócio e mudanças de estado. Quando uma operação salva dados, altera relações ou dispara processos, ela deve morar em uma action.

## Onde Ficam

```txt
app/Actions/{Dominio}/{Verbo}{Modelo}Action.php
```

Exemplos:

```txt
app/Actions/Post/StorePostAction.php
app/Actions/Post/UpdatePostAction.php
app/Actions/Post/DeactivatePostAction.php
app/Actions/Locution/StartLocutionAction.php
```

## Arquitetura do Arquivo

```txt
namespace
imports de models
imports de services
imports de facades

class NomeAction
{
    propriedades privadas para dependências

    construtor

    execute() como método principal

    métodos privados para cada passo interno
}
```

## Responsabilidade

Uma action deve responder “como esta operação acontece?”.

Exemplos:

- criar post com tags e referências;
- atualizar programa e horários;
- iniciar locução;
- concluir tarefa;
- desativar item sem apagar do banco;
- reativar item inativo;
- processar upload antes de salvar.

O controller chama a action. A action não deve conhecer detalhes de Inertia, flash message, redirect ou layout.

## Exemplo

```php
class StorePostAction
{
    public function __construct(
        private ImageProcessService $image,
    ) {}

    public function execute(User $user, array $data, ?UploadedFile $image = null): Post
    {
        return DB::transaction(function () use ($user, $data, $image) {
            $post = $this->storePost($user, $data, $image);
            $this->storeTags($post, $data['tags'] ?? []);

            return $post;
        });
    }

    private function storePost(User $user, array $data, ?UploadedFile $image): Post
    {
        return Post::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'image' => $this->image->store('posts', $image),
        ]);
    }
}
```

## Assinatura do Execute

O método `execute()` deve receber dependências de execução de forma explícita:

```php
public function execute(User $user, array $data): Post
```

```php
public function execute(Post $post, array $data): Post
```

```php
public function execute(Post $post): void
```

Use:

- model quando a operação altera algo existente;
- `User` quando a operação depende do usuário logado;
- `array $data` para dados validados;
- `UploadedFile` para arquivos;
- retorno tipado quando o chamador precisa do resultado.

## Quando Usar Transação

Use `DB::transaction()` quando a action:

- cria ou atualiza mais de uma tabela;
- salva relações;
- depende de vários passos que precisam falhar juntos;
- altera status e registra histórico;
- processa arquivo e salva model no mesmo fluxo.

## Organização dos Passos

Prefira quebrar uma action grande em métodos privados com verbos claros:

```php
public function execute(User $user, array $data): Post
{
    return DB::transaction(function () use ($user, $data) {
        $post = $this->storePost($user, $data);
        $this->storeTags($post, $data['tags'] ?? []);
        $this->storeReferences($post, $data['references'] ?? []);

        return $post;
    });
}
```

Isso deixa o fluxo legível sem espalhar a regra por vários arquivos.

## Quando Não Criar Action

Não precisa criar action para:

- renderizar uma página;
- apenas formatar dados;
- validação simples de formulário;
- regra de permissão;
- query de listagem sem mudança de estado.

Nesses casos, use controller, resource, request, policy ou filter.

## O Que Evitar

- Chamar `request()` dentro da action.
- Retornar response, redirect ou Inertia.
- Fazer autorização dentro da action quando já existe policy/request.
- Receber dados não validados.
- Criar action genérica demais, como `ManagePostAction`.
- Deixar regras importantes escondidas em métodos com nomes vagos como `handleData()`.

## Checklist

- O método público principal se chama `execute()`?
- A action recebe dados já validados?
- Existe transação quando há múltiplas alterações?
- Cada passo interno tem método privado próprio?
- A action retorna o model ou resultado que o controller precisa?
