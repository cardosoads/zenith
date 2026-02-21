<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmbedSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('provider');
    }

    public function rules(): array
    {
        return [
            'theme' => ['required', 'in:light,dark,auto'],
            'accent' => ['required', 'in:sky,honey,green'],
            'density' => ['required', 'in:comfortable,medium'],
            'preset' => ['required', 'in:clean,contrast,soft,editorial'],
            'embed_height' => ['required', 'integer', 'min:400', 'max:2000'],
        ];
    }
}
