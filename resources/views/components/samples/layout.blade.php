<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Plannrly' }} - Design Sample</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f0ff',
                            100: '#e0e0ff',
                            200: '#c4c0ff',
                            300: '#a090ff',
                            400: '#7c5cff',
                            500: '#5a30f0',
                            600: '#4a20d0',
                            700: '#3a15b0',
                            800: '#2a0fa0',
                            900: '#160092',
                            950: '#0d0060',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gray-950 text-white">
    {{ $slot }}
</body>
</html>
