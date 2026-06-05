# Organização de Rotas em Subpastas

Para melhorar a manutenção e a clareza do projeto, as rotas que estavam concentradas no arquivo `routes/web.php` foram segmentadas em arquivos menores e organizadas dentro de uma subpasta dedicada.

## Alterações Realizadas

### 1. Estrutura de Pastas
- Criado o diretório `routes/web/` para agrupar logicamente os diferentes segmentos de rotas da aplicação.

### 2. Segmentação de Rotas
- **private.php**: Criado em `routes/web/private.php`. Contém todas as rotas administrativas sob o prefixo `/panel`, incluindo controladores de Posts, Eventos, Rádio, Administração e Perfil.
- **provisory.php**: Criado em `routes/web/provisory.php`. Abriga as rotas temporárias ou de transição, como a Home e o sistema de Song Requests.
- **public.php**: Criado em `routes/web/public.php`. Estrutura preparada para receber as futuras rotas públicas do site.

### 3. Refatoração do web.php
- O arquivo `routes/web.php` foi limpo e transformado em um hub central. Ele agora utiliza o comando `require` para carregar os arquivos da subpasta:
  ```php
  require __DIR__.'/web/public.php';
  require __DIR__.'/web/private.php';
  require __DIR__.'/web/provisory.php';
  ```

---
**Data:** 18 de Abril de 2026
**Responsável:** Antigravity (IA)
