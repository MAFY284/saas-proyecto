<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.partials.navbar', function ($view) {
            $link = function (string $label, string $route, string $method = 'GET', ?string $active = null): array {
                return [
                    'label' => $label,
                    'url' => route($route),
                    'method' => $method,
                    'is_active' => request()->routeIs($active ?? $route),
                    'style' => 'link',
                ];
            };

            $view->with([
                'mainMenus' => auth()->check() ? [
                    [
                        'id' => 'users',
                        'label' => 'Usuarios',
                        'url' => route('users.index'),
                        'is_active' => request()->routeIs('users.*'),
                        'items' => [],
                    ],
[
                    'id' => 'sports',
                    'label' => 'Gestión',
                    'url' => route('matches.index'),
                    'is_active' => request()->routeIs(
                        'matches.*',
                        'match-reports.*',
                        'referee-cards.*',
                        'teams.*',
                        'players.*',
                        'fields.*',
                        'tournaments.*',
                    ),
                    'items' => [
                        $link('Torneos', 'tournaments.index', 'GET', 'tournaments.*'),
                        $link('Partidos', 'matches.index', 'GET', 'matches.*'),
                        $link('Actas', 'match-reports.index', 'GET', 'match-reports.*'),
                        $link('Cédulas arb.', 'referee-cards.index', 'GET', 'referee-cards.*'),
                        $link('Equipos', 'teams.index', 'GET', 'teams.*'),
                        $link('Jugadores', 'players.index', 'GET', 'players.*'),
                        $link('Sedes', 'fields.index', 'GET', 'fields.*'),
                    ],
                ],













                    
                    [
                        'id' => 'leagues',
                        'label' => 'Ligas',
                        'url' => route('leagues.index'),
                        'is_active' => request()->routeIs('leagues.*'),
                        'items' => [],
                    ],
                ] : [],
                'accountMenus' => auth()->check() ? [
                    [
                        'id' => 'cuenta',
                        'label' => 'Cuenta',
                        'is_active' => false,
                        'items' => [
                            $link('Cerrar sesión', 'logout', 'POST'),
                        ],
                    ],
                ] : [],
                'guestMenus' => auth()->check() ? [] : [
                    $link('Login', 'login'),
                    array_merge($link('Registro', 'register'), ['style' => 'button']),
                ],
            ]);
        });
    }
}