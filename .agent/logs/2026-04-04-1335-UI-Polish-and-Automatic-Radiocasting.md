# Ajustes de UI, Discord Hook e Lógica de Frases Automáticas

Esta atualização focou em melhorias estéticas no painel administrativo e na correção da lógica de encerramento de locução (passagem para o modo automático).

## 1. Refinamentos de Interface (UI)

Foram aplicados ajustes em diversos grids do painel (`MusicRankingGrid`, `PostGrid`, `EventGrid`, `ReviewGrid`, etc.) para garantir uma consistência visual premium:

- **Border Radius:** Correção do uso de `rounded-lg` em componentes `<Preview />`. O problema ocorria porque a propriedade `view` estava sendo sobrescrita sem incluir a classe de arredondamento.
- **Object Fitting:** Adicionado `object-cover` em todas as imagens de ranking e listagens para evitar distorções em imagens com proporções diferentes do container (quadrado).
- **Placeholders:** Padronização dos placeholders de imagem usando o serviço `placehold.co` com a marca da Akiba.

## 2. Integração com Discord

O webhook de avisos de transmissão foi atualizado para incluir uma identidade visual mais forte:

- **Background Image:** Adicionada uma imagem de fundo (Pinterest) ao embed do Discord, proporcionando um visual mais atraente para as notificações de "DJ AO VIVO".
- **Localização:** Alterado em `app/Services/External/DiscordWebhookService.php`.

## 3. Lógica de Locução Automática

Correção crítica no sistema de rádio para quando um DJ encerra sua transmissão (`finishLocution`):

- **Model `Automatic`:** Corrigido o erro de digitação na propriedade `$casts` (estava `$cast`), o que impedia o Laravel de converter o JSON de frases em um array PHP nativo.
- **Sorteio Aleatório:** Implementado o método `collect($auto->phrases)->random()` para selecionar uma frase e imagem aleatória do banco de dados quando a rádio entra em modo automático.
- **Mapeamento de Campos:** Ajustada a criação do registro `Onair` para mapear a chave `image` do JSON para a coluna `icon` do banco de dados, garantindo que o player exiba a arte correta.

### Exemplo da Nova Lógica
```php
    $phrases = (array) $auto->phrases;

    $selected = !empty($phrases) 
        ? collect($phrases)->random() 
        : ['phrase' => 'Akiba no Ar!', 'image' => '/img/locution/default/program.webp'];

    $auto->onair()->create([
        'type'   => 'automatic',
        'phrase' => $selected['phrase'],
        'icon'   => $selected['image'],
    ]);
```
