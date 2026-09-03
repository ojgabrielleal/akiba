# Task: Auditar e implementar cache com invalidação por conteúdo

## Objetivo

Analisar a aplicação Laravel e implementar uma estratégia de cache focada principalmente nos conteúdos públicos e consultas repetidas ao MySQL.

A aplicação utiliza:

- MySQL como banco de dados principal;
- `file` como driver de cache;
- `file` como driver de sessão;
- Redis não está disponível no servidor.

O objetivo é reduzir consultas repetidas ao MySQL sem deixar conteúdo desatualizado.

A estratégia esperada é:

```text
Primeiro acesso
    ↓
Cache miss
    ↓
Consulta MySQL
    ↓
Salva no cache
    ↓
Entrega resposta

Próximos acessos
    ↓
Cache hit
    ↓
Entrega direto do cache
```

Quando um conteúdo relacionado for criado, atualizado, publicado, despublicado ou excluído, os caches dependentes dele devem ser invalidados.

Exemplo:

```text
Novo post publicado
        ↓
Salva no MySQL
        ↓
Invalida caches relacionados a posts
        ↓
Próximo acesso consulta o MySQL novamente
        ↓
Reconstrói o cache com os dados atualizados
```

---

## 1. Auditar a aplicação antes de implementar

Revisar:

- Models;
- Controllers;
- Services;
- Repositories, caso existam;
- Actions;
- Scopes;
- Observers;
- Events;
- Listeners;
- Jobs;
- Providers;
- endpoints públicos;
- páginas públicas;
- consultas utilizadas pela Home;
- consultas utilizadas em `/materias`;
- consultas utilizadas em `/midias`;
- consultas de posts;
- reviews;
- eventos;
- enquetes;
- Enigma no Ar;
- categorias;
- destaques;
- configurações públicas;
- outras consultas executadas com frequência.

Não sair adicionando `Cache::remember()` indiscriminadamente.

Primeiro entender quais dados realmente se beneficiam de cache.

---

## 2. Identificar bons candidatos a cache

Priorizar dados que:

- são públicos;
- possuem muitas leituras;
- recebem poucas alterações;
- executam consultas repetidas;
- são usados em várias páginas;
- não dependem diretamente do usuário autenticado;
- podem ser reconstruídos facilmente após invalidação.

Exemplos possíveis:

```text
home:posts
home:destaques
home:enquete

posts:recentes
posts:publicados
posts:categoria:{id}
post:{id}

reviews:recentes
eventos:proximos

midias:enquete:atual
midias:enigma:ativo

config:publica
```

Esses nomes são apenas referências.

Verificar a estrutura real da aplicação antes de definir as chaves finais.

---

## 3. Cachear consultas, não esconder problemas

Antes de cachear uma consulta, verificar:

- N+1;
- falta de eager loading;
- `with`;
- `withCount`;
- selects desnecessários;
- queries duplicadas;
- consultas mal estruturadas;
- relacionamentos carregados sem necessidade.

Se houver uma query ineficiente, corrigir primeiro.

O cache não deve ser utilizado como máscara para problemas de consulta.

---

## 4. Utilizar o cache nativo do Laravel

Utilizar preferencialmente:

```php
Cache::remember(...)
```

Exemplo:

```php
$posts = Cache::remember(
    'home:posts',
    now()->addMinutes(30),
    function () {
        return Post::query()
            ->published()
            ->latest()
            ->take(10)
            ->get();
    }
);
```

Não criar mecanismo próprio de cache.

Não introduzir Redis.

Não introduzir SQLite.

---

## 5. Invalidação de cache por conteúdo

Esta é uma parte essencial da task.

Quando um conteúdo for alterado, invalidar somente os caches que dependem dele.

### Exemplo com Post

Se um post for:

- criado;
- atualizado;
- excluído;
- publicado;
- despublicado;
- marcado/desmarcado como destaque;
- movido de categoria;

identificar quais caches dependem desse post e invalidá-los.

Exemplo conceitual:

```php
Cache::forget('home:posts');
Cache::forget('home:destaques');
Cache::forget('posts:recentes');
Cache::forget("post:{$post->id}");
```

Se existirem caches de categoria:

```php
Cache::forget("posts:categoria:{$categoryId}");
```

IMPORTANTE:

Não usar `Cache::flush()` como solução padrão.

Não apagar cache de enquete, enigma, reviews ou outros conteúdos quando somente um post foi alterado.

A invalidação deve ser específica.

---

## 6. Mapear dependências

Criar um mapa mental/lógico de quais alterações afetam quais caches.

Exemplo:

```text
Post
├── Home
├── /materias
├── Destaques
├── Categoria
└── Página individual

Review
├── Home, caso apareça nela
├── Página de reviews
└── Página individual

Evento
├── Home, caso apareça nela
├── Lista de eventos
└── Página individual

Enquete
├── Home
└── /midias

Enigma
└── /midias
```

Não assumir esse mapa exatamente.

Analisar o projeto real e construir a estratégia correta.

---

## 7. Onde colocar a invalidação

Avaliar qual abordagem combina melhor com a arquitetura atual.

Possibilidades:

### Service

Se as mutações passam centralmente por Services, pode ser adequado invalidar ali.

### Observer

Se vários fluxos diferentes alteram o mesmo Model, considerar Observer.

Exemplo conceitual:

```text
Post saved/deleted
      ↓
PostObserver
      ↓
Invalida caches relacionados
```

### Events / Listeners

Utilizar somente se a arquitetura atual já se beneficiar disso.

Não criar complexidade desnecessária.

A solução deve ser simples, previsível e fácil de manter.

---

## 8. Cache de páginas paginadas

Se uma consulta paginada for cacheada, a chave deve incluir a página e parâmetros relevantes.

Exemplo:

```text
posts:publicados:page:1
posts:publicados:page:2

posts:categoria:4:page:1
posts:categoria:4:page:2
```

Se existirem:

- filtros;
- buscas;
- ordenações;
- categorias;
- parâmetros de URL;

eles devem ser considerados na chave.

Não reutilizar a mesma chave para resultados diferentes.

---

## 9. Invalidação de múltiplas páginas

Se um conteúdo altera uma listagem paginada, verificar como invalidar todas as páginas relacionadas sem depender de uma lista infinita de `Cache::forget()`.

Avaliar uma estratégia adequada, como:

- versionamento de chave;
- namespace lógico;
- contador de versão;
- cache tags somente se o driver utilizado oferecer suporte real;
- outra abordagem simples e compatível com `file`.

IMPORTANTE:

O driver `file` não deve receber uma implementação que dependa de recursos exclusivos do Redis.

Escolher uma solução compatível com o driver real da aplicação.

---

## 10. TTL como segurança

Mesmo utilizando invalidação por eventos/mutações, manter TTL.

Sugestão inicial:

```text
Conteúdo mais dinâmico:
5 a 15 minutos

Conteúdo normal:
30 a 60 minutos

Conteúdo pouco alterado:
2 a 6 horas

Configurações quase estáticas:
6 a 24 horas
```

O TTL funciona como proteção caso alguma invalidação falhe.

Não depender exclusivamente de TTL quando for possível invalidar imediatamente.

---

## 11. Conteúdo individual

Avaliar se páginas individuais também devem ser cacheadas.

Exemplo:

```text
post:123
review:55
evento:22
```

Quando o conteúdo for atualizado:

```php
Cache::forget("post:{$post->id}");
```

Quando for excluído:

```php
Cache::forget("post:{$post->id}");
```

Garantir que conteúdo excluído, despublicado ou privado não continue sendo servido do cache.

---

## 12. Status de publicação

Ter cuidado especial com conteúdos que possuem estados como:

- rascunho;
- avaliação;
- publicado;
- despublicado;
- agendado;
- destaque.

Conteúdo não público nunca deve aparecer em um cache público por engano.

Quando o status mudar, invalidar todos os caches públicos afetados.

---

## 13. Não cachear indiscriminadamente dados de usuário

Evitar cache global para:

- dados privados;
- sessão;
- autenticação;
- permissões;
- notificações individuais;
- informações específicas do usuário;
- respostas que dependem de identidade/autorização.

Se algum cache por usuário realmente for necessário, a chave precisa incluir um identificador seguro do usuário.

Não implementar isso sem necessidade real.

---

## 14. Serialização

Verificar se o projeto está armazenando no cache:

- Models Eloquent;
- Collections;
- DTOs;
- arrays.

Preferir a solução mais segura e simples para cada caso.

Evitar armazenar objetos gigantes ou relacionamentos desnecessários.

Selecionar apenas os dados utilizados pela página quando fizer sentido.

---

## 15. Testes funcionais obrigatórios

Validar pelo menos os seguintes cenários:

### Cache miss

1. limpar cache relacionado;
2. acessar a página;
3. confirmar consulta ao MySQL;
4. confirmar criação do cache.

### Cache hit

1. acessar novamente;
2. confirmar uso do cache;
3. confirmar redução de queries.

### Criação

1. criar/publicar um conteúdo;
2. confirmar invalidação;
3. acessar página pública;
4. confirmar que o novo conteúdo aparece.

### Atualização

1. editar conteúdo;
2. confirmar invalidação;
3. confirmar atualização imediata na interface pública.

### Exclusão

1. excluir conteúdo;
2. confirmar invalidação;
3. confirmar que ele não continua aparecendo via cache.

### Publicação/despublicação

1. alterar status;
2. confirmar invalidação;
3. garantir que apenas conteúdo público seja exibido.

### Paginação e filtros

1. testar várias páginas;
2. testar categorias;
3. testar filtros;
4. garantir que resultados não se misturem entre chaves.

---

## 16. Medir ganho real

Antes e depois da implementação, verificar quando possível:

- quantidade de queries por página;
- queries duplicadas;
- tempo de resposta;
- tempo de consulta;
- cache hit;
- cache miss.

Utilizar ferramentas já existentes no projeto.

Não instalar dependência pesada apenas para medir isso.

---

## 17. Não alterar comportamento da aplicação

A implementação de cache não deve alterar:

- regras de negócio;
- estrutura das respostas;
- paginação;
- ordenação;
- validações;
- autenticação;
- permissões;
- status;
- conteúdo exibido.

O cache deve ser transparente para o restante da aplicação.

---

## Resultado esperado

O comportamento final deve ser semelhante a:

```text
Usuário acessa página
        ↓
Cache existe?
   ┌────┴────┐
   │         │
  SIM       NÃO
   │         │
Cache      MySQL
   │         │
   │      Consulta
   │         │
   │      Salva cache
   └────┬────┘
        ↓
     Resposta
```

E quando houver alteração:

```text
Admin cria/edita/remove/publica conteúdo
                ↓
             MySQL
                ↓
        Invalida somente
        caches relacionados
                ↓
        Próxima requisição
        reconstrói o cache
```

O objetivo principal é reduzir significativamente consultas repetidas ao MySQL mantendo os conteúdos públicos atualizados imediatamente após alterações.

---

## Entrega

Ao concluir, fornecer um resumo com:

- arquivos analisados;
- pontos escolhidos para cache;
- pontos que não foram cacheados e o motivo;
- padrão de chaves criado;
- TTL utilizado;
- estratégia de invalidação;
- Models/Services/Controllers/Observers alterados;
- tratamento utilizado para paginação;
- queries reduzidas;
- possíveis melhorias futuras.

Não fazer alterações além do necessário para implementar uma estratégia de cache segura e coerente.
