# Task — Interface Pública

Esta primeira parte da task é exclusivamente para a interface pública.

## 1. Pulse dos pedidos do player

Crie uma animação `pulse` pequena e suave para o botão de pedidos existente tanto no `PlayerMain` quanto no `PlayerBarra`.

- Já existe uma animação semelhante no projeto: localize-a e use-a como referência.
- Centralize a animação no `custom.css` para que `PlayerMain` e `PlayerBarra` utilizem exatamente o mesmo comportamento.
- O efeito deve ser discreto, suave e não causar mudanças de layout.
- Reutilize a implementação existente sempre que possível, evitando criar animações duplicadas.
- Não altere outros comportamentos ou elementos dos players nesta etapa.

## 2. Enquete mais recente na Home

Na página pública `/midias`, a enquete mais recente é exibida em um bloco grande no topo da seção de enquetes.

Exiba exatamente esse mesmo bloco na Home, acima da seção de destaques.

- Reutilize o mesmo componente/widget usado para a enquete grande em `/midias`; não recrie, adapte ou faça uma versão semelhante.
- Exiba somente a enquete grande referente à enquete mais recente.
- Não leve para a Home a listagem/cards de outras enquetes que aparecem abaixo dela em `/midias`.
- Reaproveite exatamente a mesma estrutura, estilo, opções de resposta, barras/estado dos votos, total de votos, mensagem e ação de votar da enquete grande.
- Apenas forneça à Home os dados necessários para renderizar esse mesmo componente.
- A página `/midias` deve permanecer com seu comportamento atual.
- Se não houver enquete disponível, não renderize nada relacionado a enquetes na Home, incluindo título, container ou espaçamento reservado.
