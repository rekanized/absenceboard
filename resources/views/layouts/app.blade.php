<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Personnel Vacation Planner' }}</title>
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            [x-cloak] { display: none !important; }
            :root {
                --primary-bg: #f8fafc;
                --card-bg: rgba(255, 255, 255, 0.8);
                --text-main: #1e293b;
                --text-muted: #64748b;
                --border-color: #e2e8f0;
                --accent-red: #ef4444;
                --accent-green: #22c55e;
                --accent-blue: #3b82f6;
                --accent-yellow: #eab308;
            }

            body {
                margin: 0;
                padding: 0;
                background-color: var(--primary-bg);
                color: var(--text-main);
                font-family: 'Inter', sans-serif;
                -webkit-font-smoothing: antialiased;
            }

            * {
                box-sizing: border-box;
            }

            .icon {
                font-family: 'Material Symbols Outlined';
                font-weight: normal;
                font-style: normal;
                font-size: 24px;
                line-height: 1;
                letter-spacing: normal;
                text-transform: none;
                display: inline-block;
                white-space: nowrap;
                word-wrap: normal;
                direction: ltr;
                -webkit-font-smoothing: antialiased;
            }
        </style>

        @livewireStyles
    </head>
    <body class="bg-slate-50">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
