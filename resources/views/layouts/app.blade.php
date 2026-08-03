<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2a211d">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Castaneas">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('icon.svg') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php($isDarkPage = $dashboardDark ?? false)
        @php($isFlushContent = $flushContent ?? false)
        <div class="app-shell">
            @include('layouts.navigation')

            <div class="app-main {{ $isDarkPage ? 'bg-[#1f1916] text-white' : '' }}">
                @isset($header)
                    @unless($isFlushContent)
                    <header class="app-topbar {{ $isDarkPage ? 'border-white/10 bg-[#1f1916]/95' : '' }}">
                        <div class="content-shell {{ $isDarkPage ? 'text-white' : '' }}">
                            <div class="page-header {{ $isDarkPage ? 'border-white/10' : '' }}">
                                {{ $header }}
                            </div>
                        </div>
                    </header>
                    @endunless
                @endisset

                <main class="{{ $isFlushContent ? '' : 'content-shell pb-8' }} {{ $isDarkPage ? 'bg-[#1f1916] text-white' : '' }}">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
