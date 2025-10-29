<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title inertia>{{ config('app.name') }} {{ @$pageTitle ? "| {$pageTitle}" : '' }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link rel="stylesheet" href="https://muhammadlailil.github.io/iconsax/style/iconsax.css" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @routes
    @vite('resources/css/app.css')
    @vite(['resources/js/app.ts'])
    @inertiaHead


    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('') }}">
    <meta name="environment" content="{{ config('app.env') }}">
</head>

<body class="min-h-screen bg-body font-krub-medium text-[#333030]" x-data="{ sidebarCollapse: false }" id="app-body"
    x-bind:class="sidebarCollapse ? 'sidebar-collapse' : 'sidebar-hide'">

    @inertia

</body>

</html>
