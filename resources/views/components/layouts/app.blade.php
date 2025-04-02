<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @livewireScripts
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <x-app-brand class="px-5 pt-4" />
    
            {{-- MENU --}}
            <x-menu activate-by-route>
                <x-menu-item title="Movies" icon="o-magnifying-glass" link="{{ route('movies.explorer') }}" />
                <x-menu-item title="Reviews" icon="o-pencil-square" link="{{ route('reviews.index') }}" />
                
                 {{-- User --}}
                 @if($user = auth()->user())
                    <x-menu-sub title="Profile" icon="o-cog-6-tooth">
                        <x-menu-item title="Profileinstellungen" icon="o-user-group" link="{{ route('profile.edit') }}" />
                    </x-menu-sub>
                    @if($user->hasRole('admin'))
                        <x-menu-sub title="Administration" icon="o-cog-6-tooth">
                            <x-menu-item title="Users" icon="o-user-group" link="{{ route('users.index') }}" />
                        </x-menu-sub>
                    @endif
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="btn-circle btn-ghost btn-xs">
                                    <x-icon name="o-power" />
                                </button>
                            </form>
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @else
                    <x-menu-item title="Login" icon="o-user-circle" link="/login" />
                    <x-menu-item title="Register" icon="o-user-circle" link="/register" />
                @endif
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>
