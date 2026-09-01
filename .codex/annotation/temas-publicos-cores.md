# Temas publicos - mapa de cores anterior

Arquivo analisado: `resources/js/css/themes.css`.

## Base Akiba

O tema padrao vinha do `:root` em `resources/js/css/app.css`. O tema `akiba` quase nao alterava tokens; ele apenas reforcava textos do titulo editorial para `--color-suspense-aurora`.

Tokens base:

| Parte | Cores |
| --- | --- |
| Neutros | `--color-neutral-black: #000000`, `--color-neutral-ink: #050505`, `--color-neutral-white: #ffffff`, `--color-neutral-gray: #808080` |
| Suspense | `--color-suspense-aurora: #fffaf3`, `--color-suspense-sandstone: #e6ded1`, `--color-suspense-honeycream: #ffe8bf` |
| Vermelhos | `--color-red-crimson: #ed3237`, `--color-red-blood: #570300` |
| Laranjas | `--color-orange-morning: #ffd29f`, `--color-orange-citric: #ffaa35`, `--color-orange-amber: #ff8000`, `--color-orange-copper: #f65600` |
| Azuis | `--color-blue-ocean: #002080`, `--color-blue-marinho: #000036`, `--color-blue-skywave: #0091ff`, `--color-blue-cerulean: #0059c0`, `--color-blue-night: #000014` |
| Roxos | `--color-purple-mystic: #b82bff`, `--color-purple-lilac: #BD87FF` |
| Verdes | `--color-green-mint: #00a859`, `--color-green-forest: #009933`, `--color-green-pine: #004100` |

## Tema Light

O tema `light` trocava cores por seletor, componente e utility.

| Parte | Cores usadas |
| --- | --- |
| Fundos principais | `#fffdf8`, `--color-neutral-white`, `--public-light-warm-background`, mixes de `--color-blue-skywave` com branco |
| Areas que eram azuis | `bg-blue-night` virava branco; `bg-blue-marinho` virava branco quente; `bg-blue-ocean` e `bg-blue-cerulean` viravam azuis claros via `color-mix` |
| Textos claros | `text-suspense-aurora` virava `--color-blue-night`; variantes com opacidade viravam `color-mix(... --color-blue-night ... transparent)` |
| Bordas claras | `border-suspense-aurora` virava `--color-blue-night`; variantes opacas viravam mixes com `--color-blue-night` |
| Gradientes | gradientes azuis viravam `--gradient-orange-morning-aurora`; frase do player usava laranja citric/morning |
| Icones/filtros | `filter-suspense-aurora` virava filtro equivalente a azul marinho; algumas excecoes mantinham filtro aurora ou amber |
| Player/header/footer | header e editorial `#fffdf8`; footer/player `color-mix(... white 96% ...)` com bordas azul-night opacas |
| Comentarios/formularios | fundo branco quente, texto `--color-blue-night`, bordas mixes de azul-night, foco `--color-orange-amber` |
| Cards/editorial/anuncios | cards destacados em gradiente laranja; anuncios com mixes de skywave/branco; titulos alternando azul-night, skywave e citric |

## Tema Night

O tema `night` tambem trocava por seletor, componente e utility, escurecendo ainda mais a paleta base.

| Parte | Cores usadas |
| --- | --- |
| Tokens alterados | `--color-blue-marinho: #000014`, `--color-blue-night: #000006` |
| Fundos principais | `--color-blue-night`, `--color-blue-marinho`, mixes entre marinho/night |
| Areas azuis | `bg-blue-night` virava night; `bg-blue-marinho` virava marinho; `bg-blue-ocean` e opacidades viravam mixes escuros |
| Gradientes | gradientes azuis viravam `--gradient-featured-card-night`; cards destacados usavam marinho/ocean |
| Textos e placeholders | mantinham `--color-suspense-aurora` e mixes com transparencia |
| Componentes publicos | navbar/avatar/footer/player/tooltip/cookie/comment/input/anuncios recebiam regras especificas em tons marinho/night |
| Acentos | titulos de secao e linhas usavam `--color-orange-citric`; foco/links usavam `--color-orange-amber`; alguns titulos usavam `--color-orange-morning` |

## Observacao

O sistema antigo nao era apenas troca de variaveis. Ele interceptava classes utilitarias (`bg-*`, `text-*`, `border-*`, `filter-*`, `hover:*`, placeholders) e classes de componentes publicos (`public-footer`, `public-comment-card`, `public-tooltip`, `public-featured-card`, entre outras). A simplificacao nova deve manter o switcher, mas concentrar as diferencas de tema em sobrescritas dos tokens de `app.css`.
