<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\League;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeagueManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('leagues.index')) {
            require base_path('routes/leagues.php');
        }
    }

    public function test_un_invitado_no_entra_al_crud_de_ligas(): void
    {
        $this->get(route('leagues.index'))->assertRedirect(route('login'));
        $this->get(route('leagues.create'))->assertRedirect(route('login'));
    }

    public function test_se_puede_crear_una_liga_con_los_campos_de_la_tabla(): void
    {
        $actor = $this->createAuthenticatedUser();
        $country = Country::create(['name' => 'México', 'iso2_code' => 'MX']);
        $currency = Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$']);

        $this->actingAs($actor)
            ->post(route('leagues.store'), $this->leaguePayload([
                'name' => 'Liga Premier México',
                'slug' => 'liga-premier-mexico',
                'short_name' => 'LPM',
                'description' => 'Liga principal de fútbol.',
                'country_id' => $country->id,
                'currency_id' => $currency->id,
                'contact_email' => 'contacto@ligapremier.mx',
                'contact_phone' => '5559876543',
                'website_url' => 'https://ligapremier.mx',
                'time_zone' => 'America/Mexico_City',
                'is_active' => '1',
            ]))
            ->assertRedirect(route('leagues.index'))
            ->assertSessionHas('status', 'Liga creada.');

        $created = League::query()->where('slug', 'liga-premier-mexico')->first();

        $this->assertNotNull($created);
        $this->assertSame('Liga Premier México', $created->name);
        $this->assertSame('LPM', $created->short_name);
        $this->assertSame('contacto@ligapremier.mx', $created->contact_email);
        $this->assertSame('5559876543', $created->contact_phone);
        $this->assertSame('America/Mexico_City', $created->time_zone);
        $this->assertSame($country->id, $created->country_id);
        $this->assertSame($currency->id, $created->currency_id);
        $this->assertTrue($created->is_active);
    }

    public function test_se_puede_actualizar_una_liga(): void
    {
        $actor = $this->createAuthenticatedUser();
        $country = Country::create(['name' => 'México', 'iso2_code' => 'MX']);
        $currency = Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$']);

        $league = League::create([
            'name' => 'Liga Original',
            'slug' => 'liga-original',
            'country_id' => $country->id,
            'currency_id' => $currency->id,
            'is_active' => true,
            'time_zone' => 'UTC',
        ]);

        $this->actingAs($actor)
            ->put(route('leagues.update', $league), $this->leaguePayload([
                'name' => 'Liga Actualizada',
                'slug' => 'liga-actualizada',
                'short_name' => 'LA',
                'country_id' => $country->id,
                'currency_id' => $currency->id,
                'is_active' => '0',
            ]))
            ->assertRedirect(route('leagues.index'))
            ->assertSessionHas('status', 'Liga actualizada.');

        $league->refresh();

        $this->assertSame('Liga Actualizada', $league->name);
        $this->assertSame('liga-actualizada', $league->slug);
        $this->assertSame('LA', $league->short_name);
        $this->assertFalse($league->is_active);
    }

    public function test_se_puede_eliminar_una_liga(): void
    {
        $actor = $this->createAuthenticatedUser();
        $country = Country::create(['name' => 'México', 'iso2_code' => 'MX']);
        $currency = Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$']);

        $league = League::create([
            'name' => 'Liga a Eliminar',
            'slug' => 'liga-a-eliminar',
            'country_id' => $country->id,
            'currency_id' => $currency->id,
            'time_zone' => 'UTC',
            'is_active' => true,
        ]);

        $this->actingAs($actor)
            ->delete(route('leagues.destroy', $league))
            ->assertRedirect(route('leagues.index'))
            ->assertSessionHas('status', 'Liga eliminada.');

        $this->assertDatabaseMissing('leagues', ['id' => $league->id]);
    }

    public function test_se_pueden_eliminar_varias_ligas_en_lote(): void
    {
        $actor = $this->createAuthenticatedUser();
        $country = Country::create(['name' => 'México', 'iso2_code' => 'MX']);
        $currency = Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$']);

        $leagues = collect([1, 2, 3])->map(fn ($i) => League::create([
            'name' => "Liga Lote {$i}",
            'slug' => "liga-lote-{$i}",
            'country_id' => $country->id,
            'currency_id' => $currency->id,
            'time_zone' => 'UTC',
            'is_active' => true,
        ]));

        $this->actingAs($actor)
            ->delete(route('leagues.destroy'), [
                'ids' => $leagues->pluck('id')->all(),
            ])
            ->assertRedirect(route('leagues.index'))
            ->assertSessionHas('status', '3 ligas eliminadas.');

        $this->assertSame(0, League::query()->count());
    }

    public function test_crear_liga_con_logo(): void
    {
        Storage::fake('public');
        $actor = $this->createAuthenticatedUser();
        $country = Country::create(['name' => 'México', 'iso2_code' => 'MX']);
        $currency = Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$']);

        $file = UploadedFile::fake()->image('logo.png', 100, 100);

        $this->actingAs($actor)
            ->post(route('leagues.store'), array_merge(
                $this->leaguePayload([
                    'name' => 'Liga con Logo',
                    'country_id' => $country->id,
                    'currency_id' => $currency->id,
                ]),
                ['logo' => $file]
            ))
            ->assertRedirect(route('leagues.index'));

        $league = League::query()->where('name', 'Liga con Logo')->first();
        $this->assertNotNull($league);
        $this->assertNotNull($league->logo_url);
        Storage::disk('public')->assertExists($league->logo_url);
    }

    private function createAuthenticatedUser(): User
    {
        $language = Language::firstOrCreate(
            ['iso_code' => 'es'],
            ['name' => 'Español', 'native_name' => 'Español']
        );

        return User::factory()->create([
            'language_id' => $language->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function leaguePayload(array $overrides = []): array
    {
        $countryId = $overrides['country_id'] ?? (Country::first()?->id ?? Country::create(['name' => 'México', 'iso2_code' => 'MX'])->id);
        $currencyId = $overrides['currency_id'] ?? (Currency::first()?->id ?? Currency::create(['name' => 'Peso Mexicano', 'code' => 'MXN', 'symbol' => '$'])->id);

        return array_merge([
            'name' => 'Liga de Prueba',
            'slug' => 'liga-de-prueba',
            'short_name' => 'LDP',
            'description' => 'Descripción de prueba.',
            'country_id' => $countryId,
            'currency_id' => $currencyId,
            'contact_email' => 'test@league.com',
            'contact_phone' => '1234567890',
            'website_url' => 'https://league.example.com',
            'time_zone' => 'UTC',
            'is_active' => '1',
        ], $overrides);
    }
}
