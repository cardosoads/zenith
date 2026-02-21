<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_agenda_id',
        'public_token',
        'theme',
        'accent',
        'density',
        'customer_extra_fields',
    ];

    protected function casts(): array
    {
        return [
            'customer_extra_fields' => 'array',
        ];
    }

    public function providerAgenda(): BelongsTo
    {
        return $this->belongsTo(ProviderAgenda::class);
    }
}
