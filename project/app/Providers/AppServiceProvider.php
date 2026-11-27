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

            $user = auth()->user();
            $adminItems = [];

            if ($user?->canAccess('users')) {
                $adminItems[] = $link('Usuarios', 'users.index', 'GET', 'users.*');
            }

            if ($user?->canAccess('roles')) {
                $adminItems[] = $link('Roles', 'roles.index', 'GET', 'roles.*');
                $adminItems[] = $link('Acceso', 'access.index', 'GET', 'access.*');
            }

            $sportsItems = [];

            if ($user) {
                $sportsLinks = [
                    ['Ligas', 'leagues', 'leagues'],
                    ['Torneos', 'tournaments', 'tournaments'],
                    ['Partidos', 'matches', 'matches'],
                    ['Cédulas arbitrales', 'referee-cards', 'referee_cards'],
                    ['Equipos', 'teams', 'teams'],
                    ['Jugadores', 'players', 'players'],
                    ['Sedes', 'fields', 'fields'],
                ];

                foreach ($sportsLinks as [$label, $module, $resource]) {
                    if ($user->canAccess($resource)) {
                        $sportsItems[] = $link($label, "$module.index", 'GET', "$module.*");
                    }
                }
            }

            $view->with([
                'mainMenus' => $user ? [
                    ...($adminItems === [] ? [] : [[
                        'id'        => 'admin',
                        'label'     => 'Administración',
                        'url'       => $adminItems[0]['url'],
                        'is_active' => request()->routeIs('users.*', 'roles.*', 'access.*'),
                        'items'     => $adminItems,
                    ]]),
                    ...($sportsItems === [] ? [] : [[
                        'id'        => 'sports',
                        'label'     => 'Gestión',
                        'url'       => $sportsItems[0]['url'],
                        'is_active' => request()->routeIs(
                            'leagues.*',
                            'matches.*',
                            'referee-cards.*',
                            'teams.*',
                            'players.*',
                            'fields.*',
                            'tournaments.*',
                        ),
                        'items'     => $sportsItems,
                    ]]),
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
