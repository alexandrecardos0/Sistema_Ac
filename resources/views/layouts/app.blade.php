<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap');
        body {
            font-family: 'Oswald', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex">

    <!-- Sidebar -->
    <aside class="menu-lateral w-64 bg-white p-4 sticky top-0 h-screen overflow-y-auto">
        <img src="/img/logo.png" alt="Logo do sistema" class="w-46 mx-auto">
        <nav class="flex flex-col gap-2">
            @php
                $links = [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'href' => route('dashboard'), 'match' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8'],
                    ['label' => 'Obras', 'route' => 'obras', 'href' => route('obras'), 'match' => 'obras*', 'icon' => 'M4 6h16M4 10h16M10 14h10M4 14h4m-4 4h10'],
                    ['label' => 'Funcionários', 'route' => 'funcionarios', 'href' => route('funcionarios'), 'match' => 'funcionarios*', 'icon' => 'M16 11c1.657 0 3-1.567 3-3.5S17.657 4 16 4s-3 1.567-3 3.5 1.343 3.5 3 3.5zM2 20c0-3.314 4.03-6 9-6s9 2.686 9 6M8 9.5C8 11.433 6.657 13 5 13s-3-1.567-3-3.5S3.343 6 5 6s3 1.567 3 3.5z'],
                    ['label' => 'Materiais', 'route' => 'materiais', 'href' => route('materiais'), 'match' => 'materiais*', 'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h10'],
                    ['label' => 'Relatórios', 'route' => 'relatorios.index', 'href' => route('relatorios.index'), 'match' => 'relatorios*', 'icon' => 'M9 17v-6h6v6m-9 4h12'],
                    ['label' => 'Pagamentos', 'route' => 'relatorios.pagamentos', 'href' => route('relatorios.pagamentos'), 'match' => 'relatorios-pagamentos*', 'icon' => 'M12 8c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 14c-4.418 0-8 1.79-8 4v2h16v-2c0-2.21-3.582-4-8-4z'],
                ];
            @endphp

            @foreach ($links as $link)
                @php
                    $active = request()->routeIs($link['route']) || request()->is($link['match']);
                @endphp
                <a href="{{ $link['href'] }}"
                   class="flex items-center gap-2 rounded px-3 py-2 transition {{ $active ? 'bg-black text-white' : 'text-gray-800 hover:bg-gray-100' }}">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                    </svg>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6 bg-black">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
