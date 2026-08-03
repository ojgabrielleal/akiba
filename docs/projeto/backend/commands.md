---
status: ativo
tipo: guia-backend
atualizado_em: 2026-08-03
---

# Commands e Schedules

Commands executam rotinas de console. Schedules definem quando essas rotinas rodam automaticamente.

## Onde Ficam

```txt
app/Console/Commands
routes/console.php
```

## Agenda Atual

Arquivo:

```txt
routes/console.php
```

Agenda:

```php
Schedule::command('programs:start')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('audience:collect')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('audience:prune')
    ->cron('0 3 1 1,7 *')
    ->withoutOverlapping();
```

## programs:start

Arquivo:

```txt
app/Console/Commands/Schedules/StartPrograms.php
```

Assinatura:

```txt
programs:start
```

Frequência:

```txt
a cada minuto
```

O que faz:

- inicia programas agendados com status `pending`;
- pula execução quando existe locução ao vivo;
- expira schedules vencidos quando locução ao vivo impede execução;
- encerra o programa atual antes de iniciar outro;
- inicia Auto DJ padrão quando não há nada no ar;
- marca schedule como `completed`, `expired` ou `failed`.

Models envolvidos:

```txt
Program
ProgramSchedule
Onair
```

Testes relacionados:

```txt
tests/Feature/Console/StartProgramsTest.php
tests/Unit/Actions/Locution/LocutionProgramScheduleTest.php
```

Rodar manualmente:

```bash
./run.sh artisan programs:start
```

## audience:collect

Arquivo:

```txt
app/Console/Commands/Schedules/CollectAudience.php
```

Assinatura:

```txt
audience:collect
```

Frequência:

```txt
a cada 10 minutos
```

O que faz:

- chama `AudienceCollectorService`;
- percorre rádios ativas;
- coleta audiência via endpoint externo;
- salva snapshots;
- atualiza pico de audiência do programa atual.

Arquivos envolvidos:

```txt
app/Services/Process/AudienceCollectorService.php
app/Services/External/AudienceService.php
app/Models/RadioStation.php
app/Models/RadioAudienceSnapshot.php
app/Models/Onair.php
```

Testes relacionados:

```txt
tests/Feature/Console/CollectAudienceTest.php
tests/Unit/Services/AudienceCollectorServiceTest.php
tests/Unit/Services/AudienceServiceTest.php
```

Rodar manualmente:

```bash
./run.sh artisan audience:collect
```

## audience:prune

Arquivo:

```txt
app/Console/Commands/Schedules/PruneAudienceSnapshots.php
```

Assinatura:

```txt
audience:prune
```

Frequência:

```txt
03:00 nos meses 1 e 7
```

O que faz:

- remove snapshots de audiência com mais de seis meses;
- mantém a tabela de histórico sob controle.

Model envolvido:

```txt
RadioAudienceSnapshot
```

Rodar manualmente:

```bash
./run.sh artisan audience:prune
```

## Como Rodar o Scheduler

Em produção, o scheduler do Laravel precisa ser chamado pelo cron ou pelo processo configurado no deploy:

```bash
php artisan schedule:run
```

No ambiente Docker local, use o wrapper quando quiser testar um comando específico:

```bash
./run.sh artisan programs:start
./run.sh artisan audience:collect
./run.sh artisan audience:prune
```

## Checklist

- O command tem assinatura clara?
- Rotina agendada está registrada em `routes/console.php`?
- `withoutOverlapping()` foi usado quando a rotina não pode rodar em paralelo?
- Existe teste para regra crítica?
- Command chama service/action quando a regra cresce?
- Rodar manualmente não quebra dados locais inesperadamente?
