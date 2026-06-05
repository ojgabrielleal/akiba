# Hotfix: Correção de Estouro de Memória e Erro 500

Após a implementação inicial do cache, o ambiente de produção apresentou erros 500 e `Allowed memory size exhausted`. Identificamos que a causa era a tentativa de serializar objetos `ResourceCollection` do Inertia/Laravel, que são pesados e mantêm referências ao objeto Request.

## Alterações Realizadas

### 1. Refatoração da Serialização do Cache
- **DashboardController**: Alterados os métodos `indexActivities`, `indexPosts` e `indexCalendar` para cachear apenas as Collections de **Models** (dados brutos). O encapsulamento em `Resource::collection()` agora acontece **após** a recuperação do cache.
- **CastController**: Aplicada a mesma lógica para a consulta de `Onair`.

### 2. Otimização de Memória
- Ao remover os Resources de dentro do `Cache::remember`, reduzimos drasticamente o tamanho dos dados serializados e evitamos referências circulares que causavam o estouro dos 128MB de limite do PHP.

## Próximos Passos (Manual)
- O usuário deve rodar `php artisan migrate` no servidor para garantir a existência da tabela `cache`.
- Cache deve ser limpo no servidor com `php artisan cache:clear` para remover os restos de objetos mal-serializados.

---
**Data:** 18 de Abril de 2026
**Responsável:** Antigravity (IA)
