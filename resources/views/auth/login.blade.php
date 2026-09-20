<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | {{ $siteBrandName }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: Arial, sans-serif; background: linear-gradient(135deg, #fff5ec, #ffe2cc); color: #2a180f; }
        .card { width: 100%; max-width: 430px; padding: 38px; background: #fff; border-radius: 22px; box-shadow: 0 18px 50px rgba(255, 107, 0, .14); }
        .brand { margin-bottom: 8px; color: #ff6b00; font-size: 30px; font-weight: 800; text-align: center; }
        .subtitle { margin: 0 0 28px; color: #76665d; text-align: center; }
        label { display: block; margin: 0 0 8px; font-weight: 700; }
        input[type=email], input[type=password] { width: 100%; padding: 13px 14px; border: 1px solid #ebded4; border-radius: 10px; font-size: 16px; outline: none; }
        input:focus { border-color: #ff6b00; box-shadow: 0 0 0 3px rgba(255,107,0,.12); }
        .field { margin-bottom: 20px; }
        .error { margin: 7px 0 0; color: #c62828; font-size: 14px; }
        .remember { display: flex; gap: 8px; align-items: center; margin-bottom: 22px; color: #52645a; }
        button { width: 100%; padding: 14px; border: 0; border-radius: 10px; background: #ff6b00; color: white; font-size: 16px; font-weight: 700; cursor: pointer; }
        button:hover { background: #d95800; }
        .home { display: block; margin-top: 22px; color: #ff6b00; text-align: center; text-decoration: none; }
        body{min-height:100dvh}.card{min-width:0}.brand,.error{overflow-wrap:anywhere}
        @media(max-width:480px){body{padding:16px}.card{padding:28px 20px}.brand{font-size:27px}}
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">@if($siteBrandLogo)<img src="{{ $siteBrandLogo }}" alt="" style="display:block;max-width:160px;max-height:72px;margin:0 auto 12px">@endif{{ $siteBrandName }}</div>
        <p class="subtitle">Log in to the admin panel</p>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required>
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>
            <label class="remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button type="submit">Log In</button>
        </form>
        <a class="home" href="{{ route('home') }}">← Back to website</a>
    </main>
</body>
</html>
