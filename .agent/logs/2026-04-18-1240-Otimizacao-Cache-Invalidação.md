# Otimização de Cache e Invalidação Automática

Implementação de cache para chamadas de APIs externas e consultas pesadas ao banco de dados, utilizando as ferramentas nativas do Laravel para garantir performance e consistência dos dados.

## Alterações Realizadas

### 1. APIs Externas (Performance Crítica)
- **CastService**: Implementado cache de 30 segundos para os metadados da rádio. Isso evita que cada carregamento de página resulte em uma requisição HTTP externa, reduzindo drasticamente o tempo de resposta do site.

### 2. Modelos com Auto-Invalidação
Implementada a lógica `booted()` nos seguintes modelos para limpar automaticamente o cache quando houver qualquer alteração (Save/Delete):
- **Onair**: Limpa a chave `radio_onair_data`.
- **Post**: Limpa a chave `latest_posts`.
- **Activity**: Limpa a chave `dashboard_activities`.
- **Calendar**: Limpa a chave `dashboard_calendar`.

### 3. Controllers Otimizados
As seguintes consultas agora utilizam `Cache::remember`:
- **CastController**: Dados da rádio agora são servidos via cache (1 hora, invalidado automaticamente pelo Model).
- **DashboardController**: As listagens de Atividades, Posts Recentes e Calendário agora são cacheadas, desafogando o banco de dados em acessos simultâneos.

## Benefícios
- **Economia de Recursos**: Redução de queries ao banco de dados e requisições externas.
- **Inodes**: Como o driver configurado é `database` para cache e `cookie` para sessões, não houve criação de novos arquivos no disco.
- **Consistência**: O uso de eventos de model garante que o usuário nunca verá um dado antigo após uma edição.

---
**Data:** 18 de Abril de 2026
**Responsável:** Antigravity (IA)
