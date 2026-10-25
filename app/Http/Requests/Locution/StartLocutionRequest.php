<?php

namespace App\Http\Requests\Locution;

use App\Http\Requests\LoggedWebRequest;
use App\Rules\RespectfulContent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class StartLocutionRequest extends LoggedWebRequest
{
    protected function prepareForValidation(): void
    {
        $social = $this->input('social');

        if (! is_array($social)) {
            return;
        }

        $social['text'] = is_string($social['text'] ?? null) ? trim($social['text']) : ($social['text'] ?? null);
        $social['hashtags'] = collect($social['hashtags'] ?? [])
            ->filter(fn (mixed $hashtag): bool => is_string($hashtag))
            ->map(fn (string $hashtag): string => Str::lower(ltrim(trim($hashtag), '#')))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $this->merge(['social' => $social]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('locution.start') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phrase' => 'required',
            'program' => 'required',
            'send_notification' => 'required|accepted',
            'social' => 'required|array',
            'social.text' => ['required', 'string', 'max:1800', new RespectfulContent],
            'social.hashtags' => 'required|array|min:1|max:5',
            'social.hashtags.*' => ['string', 'max:50', 'regex:/^[\pL\pN_]+$/u'],
        ];
    }
}
