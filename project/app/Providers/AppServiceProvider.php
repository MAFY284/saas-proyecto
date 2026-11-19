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
                    'label'     => $label,
                    'url'       => route($route),
                    'method'    => $method,
                    'is_active' => request()->routeIs($active ?? $route),
                    'style'     => 'link',
                ];
            };

            $view->with([
                'mainMenus' => auth()->check() ? [
                    [
                        'id'        => 'admin',
                        'label'     => 'Administración',
                        'url'       => route('users.index'),
                        'is_active' => request()->routeIs('users.*', 'roles.*', 'access.*'),
                        'items'     => [
                            $link('Usuarios', 'users.index', 'GET', 'users.*'),
                            $link('Roles',    'roles.index', 'GET', 'roles.*'),
                            $link('Acceso',   'access.index', 'GET', 'access.*'),
                        ],
                    ],
                    [
                        'id'        => 'sports',
                        'label'     => 'Gestión',
                        'url'       => route('matches.index'),
                        'is_active' => request()->routeIs(
                            'leagues.*',
                            'matches.*',
                            'match-reports.*',
                            'referee-cards.*',
                            'teams.*',
                            'players.*',
                            'fields.*',
                            'tournaments.*',
                        ),
                        'items' => [
                            $link('Ligas',        'leagues.index',       'GET', 'leagues.*'),
                            $link('Torneos',      'tournaments.index',   'GET', 'tournaments.*'),
                            $link('Partidos',     'matches.index',       'GET', 'matches.*'),
                            $link('Actas',        'match-reports.index', 'GET', 'match-reports.*'),
                            $link('Cédulas arb.', 'referee-cards.index', 'GET', 'referee-cards.*'),
                            $link('Equipos',      'teams.index',         'GET', 'teams.*'),
                            $link('Jugadores',    'players.index',       'GET', 'players.*'),
                            $link('Sedes',        'fields.index',        'GET', 'fields.*'),
                        ],
                    ],
                ] : [],
                'accountMenus' => auth()->check() ? [
                    [
                        'id'        => 'cuenta',
                        'label'     => 'Cuenta',
                        'is_active' => false,
                        'items'     => [
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
