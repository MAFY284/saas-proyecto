<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'referee_card_id',
    'player_id',
    'goals',
    'yellow_cards',
    'direct_red_cards',
    'indirect_red_cards',
])]
class RefereeCardPlayer extends Model
{
    use HasFactory;

    /**
     * Una roja es indirecta cuando el jugador acumula dos amarillas.
     */
    public const YELLOW_CARDS_FOR_INDIRECT_RED = 2;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'goals' => 'integer',
            'yellow_cards' => 'integer',
            'direct_red_cards' => 'integer',
            'indirect_red_cards' => 'integer',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(RefereeCard::class, 'referee_card_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * La roja indirecta se deduce de las amarillas; no se captura a mano.
     */
    public static function indirectRedFromYellowCards(int $yellowCards): int
    {
        return $yellowCards >= self::YELLOW_CARDS_FOR_INDIRECT_RED ? 1 : 0;
    }
}
