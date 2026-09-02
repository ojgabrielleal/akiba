# Task: Ajustar fontes e metadados de Reviews

Realizar os seguintes ajustes exclusivamente para posts do tipo **Review**.

## Painel / Formulário de Review

Reviews não devem possuir fontes de pesquisa.

- Manter o campo de fontes de pesquisa visível no formulário, porém desativado.
- Aplicar ao campo a mesma opacidade/estado visual utilizado atualmente no campo de `tags` quando está desativado.
- O usuário não deve conseguir interagir ou preencher fontes de pesquisa em Reviews.
- Usar o padrão já existente no projeto para campos desativados.
- Não alterar o comportamento das fontes de pesquisa em Matérias ou Eventos.

## Interface pública / Leitura de Review

Na página pública de leitura de uma Review, não exibir:

- Bloco de autor.
- Bloco de fontes de pesquisa.
- Informação/bloco de `Publicado`.

Esses elementos devem continuar funcionando normalmente na leitura dos outros tipos de posts onde forem utilizados.

## Regras

- Alterações exclusivas para Reviews.
- Não remover componentes compartilhados globalmente.
- Fazer a exibição/estado depender do tipo do post.
- Reutilizar estilos e lógica existentes.
- Não refatorar fora do escopo.
- Não alterar Matérias ou Eventos.