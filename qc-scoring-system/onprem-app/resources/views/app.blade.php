<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('') }}">
    <meta name="environment" content="{{ config('app.env') }}">
</head>

<body class="min-h-screen bg-body font-krub font-medium text-[#333030]" x-data="{ sidebarCollapse: false }" id="app-body"
    x-bind:class="sidebarCollapse ? 'sidebar-collapse' : 'sidebar-hide'">

    @inertia

    @include('partials.js')
</body>

</html>
