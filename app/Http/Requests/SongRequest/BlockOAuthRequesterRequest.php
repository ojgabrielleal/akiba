<?php

namespace App\Http\Requests\SongRequest;

use App\Http\Requests\LoggedWebRequest;
use App\Models\SongRequestBlock;
use App\Services\SongRequestBlockService;
use Illuminate\Validation\Rule;

class BlockOAuthRequesterRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SongRequestBlock::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'oauth_account' => ['required', 'uuid'],
            'modules' => ['required', 'array', 'min:1', 'max:4'],
            'modules.*' => ['required', 'string', 'distinct', Rule::in([
                SongRequestBlockService::MODULE_SONG_REQUESTS,
                SongRequestBlockService::MODULE_POLLS,
                SongRequestBlockService::MODULE_COMMENTS,
                SongRequestBlockService::MODULE_ENIGMAGAME,
            ])],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'modules.required' => 'Selecione pelo menos um módulo.',
            'modules.min' => 'Selecione pelo menos um módulo.',
            'reason.required' => 'Informe o motivo do bloqueio.',
            'reason.string' => 'Informe um motivo válido.',
            'reason.max' => 'O motivo deve ter no máximo 1000 caracteres.',
        ];
    }
}
