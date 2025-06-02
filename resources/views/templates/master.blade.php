<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            background-color: beige;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        p {
            margin: 10px 0;
        }

        strong {
            color: #000;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
