<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Pembukuan') }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(180deg, #f8fbff 0%, #edf3ff 100%);
            color: #162033;
        }
        .card {
            width: min(440px, calc(100vw - 32px));
            background: #fff;
            border: 1px solid #d9e2f2;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }
        .title { font-size: 24px; font-weight: 800; margin: 0 0 8px; }
        .muted { color: #667085; margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; }
        input,
        button {
            box-sizing: border-box;
        }
        input {
            width: 100%;
            border: 1px solid #d9e2f2;
            border-radius: 14px;
            padding: 12px 14px;
            font: inherit;
            margin-bottom: 16px;
        }
        button {
            width: 100%;
            border: 0;
            border-radius: 14px;
            padding: 12px 14px;
            background: #1d4ed8;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .error { color: #dc2626; font-size: 14px; margin-top: -8px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="title" style="text-align: center;">Sign In</h1>

        <form method="POST" action="/login">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="error">{{ $message }}</div> @enderror

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
