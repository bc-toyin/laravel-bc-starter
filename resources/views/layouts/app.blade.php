<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400&display=swap" rel="stylesheet"/>

    <script src="https://kit.fontawesome.com/db85af4214.js" crossorigin="anonymous"></script>
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        [x-cloak] {
            display: none;
        }
    </style>

    @yield('head')
</head>
<body class="bg-[#f6f7f9]">
    <div class="p-10 mx-auto" style="max-width: 1200px;">
        @yield('content')
    </div>

    @yield('footer')
</body>
</html>
