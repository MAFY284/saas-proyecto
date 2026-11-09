<?php

namespace App\Models;

use Database\Factories\LeagueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'slug',
    'short_name',
    'description',
    'country_id',
    'currency_id',
    'contact_email',
    'contact_phone',
    'logo_url',
    'website_url',
    'time_zone',
    'is_active',
])]
class League extends Model
{
    /** @use HasFactory<LeagueFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): LeagueFactory
    {
        return LeagueFactory::new();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function initials(): string
    {
        $source = $this->short_name ?: $this->name;
        $parts = preg_split('/\s+/', trim((string) $source)) ?: [];

        $letters = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)));

        return $letters->implode('') ?: 'L';
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_url) {
            return null;
        }

        if (str_starts_with($this->logo_url, 'http://') || str_starts_with($this->logo_url, 'https://')) {
            return $this->logo_url;
        }

        return Storage::disk('public')->url($this->logo_url);
    }

    public function updateLogo(UploadedFile $file): void
    {
        $this->deleteLogoFile();
        $this->logo_url = $file->store('leagues', 'public');
    }

    public function deleteLogoFile(): void
    {
        if ($this->logo_url && ! str_starts_with($this->logo_url, 'http://') && ! str_starts_with($this->logo_url, 'https://')) {
            Storage::disk('public')->delete($this->logo_url);
        }
    }
}
