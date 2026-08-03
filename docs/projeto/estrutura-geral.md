---
status: ativo
tipo: guia
atualizado_em: 2026-08-03
---

# Estrutura Geral

## Raiz do Projeto

```txt
app/              Backend Laravel
database/         Estrutura e dados iniciais do banco
docs/             Documentação VitePress
resources/js/     Interface Svelte/Inertia
routes/           Rotas HTTP
```

## Backend

```txt
app/Actions       Regras de negócio e mudanças de estado
app/Filters       Consultas reutilizáveis e filtros de listagem
app/Http          Controllers, requests, resources e middleware
app/Models        Models Eloquent e relacionamentos
app/Policies      Regras de autorização
app/Services      Integrações externas e processos reutilizáveis
app/Support       Helpers e objetos de suporte do domínio
```

## Frontend

```txt
resources/js/pages        Páginas Inertia renderizadas por controllers
resources/js/lib/layouts  Layouts públicos e privados
resources/js/lib/widgets  Blocos grandes de interface por domínio
resources/js/lib/components Componentes menores e compartilháveis
resources/js/lib/stores   Estados compartilhados
resources/js/lib/utils    Funções auxiliares
resources/js/lib/constants Constantes do frontend
```

## Rotas

```txt
routes/web        Rotas web separadas por contexto
routes/web/private.php   Painel privado
routes/web/public.php    Área pública
routes/web/provisory.php Rotas provisórias
```

## Banco

```txt
database/migrations  Criação e alteração de tabelas
database/seeders     Dados iniciais e dados de desenvolvimento
database/factories   Fábricas para testes e seeds
```

## Como Encontrar uma Feature

Se você quer entender “postagens”, por exemplo, procure por:

```txt
app/Http/Controllers/Private/PostController.php
app/Http/Controllers/Private/Pages/PostPageController.php
app/Actions/Post
app/Filters/PostFilter.php
app/Models/Post.php
app/Http/Resources/Post
resources/js/pages/private/Post.svelte
resources/js/lib/widgets/private
```
