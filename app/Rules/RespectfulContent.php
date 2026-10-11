<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class RespectfulContent implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    private array $blockedPatterns = [
        '/\b(lixo|merda|bosta)\s+(de\s+)?(site|akiba|rede\s+akiba|equipe|radio|rádio)\b/iu',
        '/\b(site|akiba|rede\s+akiba|equipe|radio|rádio)\s+(lixo|merda|bosta|horr[ií]vel|rid[ií]cul[ao])\b/iu',
        '/\b(vai\s+se\s+fude(?:r)?|vai\s+toma(?:r)?\s+no\s+cu|filh[ao]\s+d[ae]\s+puta|fdp|arrombado|escrot[ao]|desgracad[ao]|merdinh[ao]|porra|caralh[ao])\b/iu',
        '/\b(babaca|idiota|imbecil|ot[aá]ri[ao]|burro|burr[ao]|retardad[ao]|mongol(?:oide)?|doente\s+mental)\b/iu',
        '/\b(macac[ao]|crioulo|preto\s+imund[ao]|negr[ao]\s+imund[ao]|judeu\s+imund[ao]|nazista|hitlerista)\b/iu',
        '/\b(viado|bicha|boiola|traveco|sapat[aã]o|gayzinh[ao])\b/iu',
        '/\b(gordo\s+nojent[ao]|gorda\s+nojent[ao]|aleijad[ao]|deficiente\s+in[uú]til)\b/iu',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $text = $this->normalize($value);

        foreach ($this->blockedPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                $term = $matches[0] ?? 'termo ofensivo';

                $fail("Revise o texto para manter um tom respeitoso com a comunidade Akiba. Termo bloqueado: {$term}.");

                return;
            }
        }
    }

    private function normalize(string $value): string
    {
        $text = html_entity_decode(strip_tags($value));
        $text = Str::of($text)
            ->replaceMatches('/(.)\1{2,}/u', '$1$1')
            ->replaceMatches('/[^\pL\pN\s]+/u', ' ')
            ->squish()
            ->lower()
            ->toString();

        return Str::ascii($text);
    }
}
