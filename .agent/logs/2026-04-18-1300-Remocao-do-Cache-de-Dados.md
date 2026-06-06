# Remoção da Lógica de Cache de Dados

Devido a restrições de espaço em disco e armazenamento na hospedagem limitada do servidor de produção, toda a lógica de cache de dados implementada anteriormente foi removida para evitar o crescimento da tabela de cache no banco de dados.

## Alterações Realizadas (Reversão)

### 1. Serviços e Controllers
- **CastService**: Removido o cache da consulta aos metadados da rádio. As chamadas voltam a ser feitas diretamente à API externa em cada requisição.
- **DashboardController**: Removido o cache das listagens de Atividades, Posts e Calendário. As consultas voltam a ser feitas diretamente ao banco de dados.
- **CastController**: Removido o cache da consulta Onair.

### 2. Modelos (Invalidação)
- Foi removido o método `booted()` dos modelos `Post`, `Onair`, `Activity` e `Calendar`. Os modelos não disparam mais eventos de limpeza de cache ao serem salvos ou excluídos.

### 3. O que foi mantido
- **SESSION_DRIVER=cookie**: Mantivemos esta configuração no `.env` pois ela utiliza o armazenamento local do navegador do usuário e resolve o erro 419 de CSRF de forma segura, sem ocupar espaço no servidor.

---
**Data:** 18 de Abril de 2026
**Responsável:** Antigravity (IA)
