<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Someone who asked for a link to Loft to open on their computer (decision 50), usually from a phone.
 * Kept for KEEP_DAYS, to see whether they came back and, if they agreed, to follow up.
 */
#[Fillable(['email', 'page', 'signup_source', 'follow_up_consent_at', 'link_sent_at', 'studio_id', 'converted_at'])]
class Lead extends Model
{
    public const PAGES = ['home', 'demo'];

    /** Deleted this long after they asked (App\Console\Commands\Housekeeping). */
    public const KEEP_DAYS = 90;

    protected function casts(): array
    {
        return [
            'signup_source' => 'array',
            'follow_up_consent_at' => 'datetime',
            'link_sent_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }
}
