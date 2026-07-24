# Factories e Seeders

## Imagens

* Imagem padrão: `public/img/placeholders/avatar.webp`.
* Programa sem imagem própria: `public/img/placeholders/program.webp`.
* Imagens e logos de programas devem usar a imagem do próprio programa.

## Posts

* `image`: use `public/img/placeholders/avatar.webp`.
* `cover`: pode ser gerado com `Database\Factories\Concerns\HasFakeImages`.

## Player

* Campos de imagem ou logo devem usar a imagem do programa.
* `phrase.icon` deve usar um ícone de `resources/js/data/locution/icon.json`.
* Não use em `phrase.icon` imagens de programa, placeholders genéricos ou URLs aleatórias.

## Após alterações

Se os containers estiverem ativos e `APP_ENV=local`, execute:

```sh
./run.sh artisan migrate:fresh --seed
```
