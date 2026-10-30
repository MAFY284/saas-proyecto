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
                        'id' => 'dashboard',
                        'label' => 'Dashboard',
                        'url' => route('dashboard'),
                        'is_active' => request()->routeIs('dashboard'),
                        'items' => [],
                    ],
                    [
                        'id' => 'users',
                        'label' => 'Usuarios',
                        'url' => route('users.index'),
                        'is_active' => request()->routeIs('users.*'),
                        'items' => [],
                    ],
                ] : [],
                'accountMenus' => auth()->check() ? [
                    [
                        'id' => 'cuenta',
                        'label' => 'Cuenta',
                        'is_active' => false,
                        'items' => [
                            $link('Salir', 'logout', 'POST'),
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
