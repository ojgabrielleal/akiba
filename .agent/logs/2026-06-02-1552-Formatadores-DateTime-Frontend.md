# Formatadores DateTime Frontend

## Contexto

Foram ajustados pontos de exibicao de dias, horas e agendamentos no grid de programas, mantendo o backend enviando valores simples e deixando a responsabilidade de apresentacao no frontend.

## Alteracoes

- Corrigido `AirtimeResource` para retornar `day` como inteiro, evitando chamada de `format()` em valor numerico.
- Criado um utilitario unico em `resources/js/utils/dateTime.js` para centralizar:
  - `resolveDay(day, format = "long")`
  - `resolveHour(hour)`
  - `resolveDateTime(dateTime)`
- Atualizado `resources/js/utils/index.js` para exportar os tres helpers pelo barrel `@/utils`.
- Atualizado `ProgramGrid.svelte` para usar os helpers em:
  - dia da programacao
  - horario da programacao
  - data/hora de planos agendados
- Atualizado `ProgramScheduleGrid.svelte` para reutilizar `resolveDay` e `resolveHour`, removendo o mapa local de dias.
- Consolidado os arquivos separados `days.js`, `hours.js` e `dateTimes.js` no arquivo unico `dateTime.js`.

## Exemplos Validados

```txt
resolveDay(2) -> Terca-feira
resolveHour("15:30:00") -> 15h30
resolveDateTime("04/06/2026 - 14:00:00") -> 04/06/2026 - 14h00
```

## Validacao

- `php -l app\Http\Resources\AirtimeResource.php` executou sem erros de sintaxe.
- `resolveHour` foi testado diretamente com `15:30:00` e `7:05`.
- `resolveDateTime` foi testado diretamente com `04/06/2026 - 14:00` e `04/06/2026 - 14:00:00`.
- `npm run build` nao concluiu porque o Vite nao encontrou a entrada `resources/css/app.css`, antes de compilar os modulos alterados.
