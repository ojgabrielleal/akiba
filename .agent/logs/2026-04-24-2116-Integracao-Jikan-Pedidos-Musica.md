# Integração Jikan nos Pedidos de Música

Foi registrada a integração com a API Jikan para enriquecer o fluxo público de pedidos de música, permitindo que o ouvinte pesquise animes e escolha aberturas ou encerramentos relacionados ao título selecionado.

## Alterações Realizadas

### 1. Serviço Externo
- **JikanAnimeService**: Criado serviço dedicado em `app/Services/External/JikanAnimeService.php` para centralizar consultas à API Jikan.
- **Busca por Nome**: Implementada pesquisa de anime por texto usando o endpoint `/anime`, retornando o primeiro resultado encontrado.
- **Busca por ID**: Implementada consulta detalhada por `mal_id` usando o endpoint `/anime/{id}/full`.

### 2. Cache e Resiliência
- As buscas por nome são armazenadas em cache por 6 horas com chave baseada no nome do anime.
- As chamadas HTTP utilizam timeout de 10 segundos e aceitam JSON explicitamente.
- Falhas de API, formatos inesperados e exceções são registradas em log sem interromper o fluxo da aplicação.

### 3. Formulário Público de Pedidos
- **SongRequestForm.svelte**: Integrado consumo direto da Jikan API no formulário de pedido de música.
- Adicionada busca com debounce para listar animes do tipo TV conforme o usuário digita.
- Ao selecionar um anime, o formulário consulta `/anime/{id}/themes` e separa músicas de abertura (`OP`) e encerramento (`ED`).
- Os temas retornados são tratados para extrair nome da música e artista, removendo informações extras entre parênteses.

### 4. Experiência de Seleção
- A seleção de anime exibe título, ano e imagem de capa.
- A seleção de música agrupa os resultados entre Aberturas e Encerramentos.
- Ao alterar o anime pesquisado, os campos de anime e música são limpos para evitar pedidos inconsistentes.

---
**Data:** 24 de Abril de 2026
**Responsável:** Codex (IA)
