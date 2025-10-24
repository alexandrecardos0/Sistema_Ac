<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap');
        body {
            font-family: 'Oswald', sans-serif;
        }
    </style>
    
</head>
<body class="min-h-screen flex bg-white">

    <!-- Sidebar -->
    <aside class="menu-lateral w-64 border-r border-gray-200 bg-white/80 p-6 backdrop-blur sticky top-0 h-screen overflow-y-auto">
        <img src="/img/logo.png" alt="Logo do sistema" class="w-48 mx-auto mb-6">

        <nav class="flex flex-col gap-2 text-sm font-medium">
            @php
                $links = [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'href' => route('dashboard'), 'match' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8'],
                    ['label' => 'Obras', 'route' => 'obras', 'href' => route('obras'), 'match' => 'obras*', 'icon' => 'M4 6h16M4 10h16M10 14h10M4 14h4m-4 4h10'],
                    ['label' => 'Funcionários', 'route' => 'funcionarios', 'href' => route('funcionarios'), 'match' => 'funcionarios*', 'icon' => 'M16 11c1.657 0 3-1.567 3-3.5S17.657 4 16 4s-3 1.567-3 3.5 1.343 3.5 3 3.5zM2 20c0-3.314 4.03-6 9-6s9 2.686 9 6M8 9.5C8 11.433 6.657 13 5 13s-3-1.567-3-3.5S3.343 6 5 6s3 1.567 3 3.5z'],
                    ['label' => 'Materiais', 'route' => 'materiais', 'href' => route('materiais'), 'match' => 'materiais*', 'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h10'],
                    ['label' => 'Compras', 'route' => 'comprar', 'href' => route('comprar'), 'match' => 'comprar*', 'icon' => 'M3 5h12M9 3v2m6 9h4l-2 2l2 2h-4m-2 4H5a2 2 0 0 1-2-2V7h16v7'],
                    ['label' => 'Carro', 'route' => 'gastos.veiculos', 'href' => route('gastos.veiculos'), 'match' => 'gastos-veiculos*', 'icon' => 'M3 11l2-2h3l2-3h4l2 3h3l2 2v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5z'],
                    ['label' => 'Relatórios', 'route' => 'relatorios.index', 'href' => route('relatorios.index'), 'match' => 'relatorios*', 'icon' => 'M9 17v-6h6v6m-9 4h12'],
                    ['label' => 'Pagamentos', 'route' => 'relatorios.pagamentos', 'href' => route('relatorios.pagamentos'), 'match' => 'relatorios-pagamentos*', 'icon' => 'M12 8c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 14c-4.418 0-8 1.79-8 4v2h16v-2c0-2.21-3.582-4-8-4z'],
                ];
            @endphp

            @foreach ($links as $link)
                @php
                    $active = request()->routeIs($link['route']) || request()->is($link['match']);
                @endphp
                <a href="{{ $link['href'] }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 transition {{ $active ? 'bg-teal-500/20 text-teal-600 shadow-inner shadow-teal-500/10' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="h-5 w-5 text-teal-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                    </svg>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-8 bg-white">
        {{ $slot }}
    </main>

    @livewireScripts
    
</body>
</html>
