@extends('layouts.app', ['title' => 'Iniciar sesión'])

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Iniciar sesión</h1>
        <p class="sub">Accede con tu cuenta de cliente o empleado.</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="{{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="ejemplo@correo.es">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="check" style="margin-bottom: 1.2rem;">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Recordarme en este equipo</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Entrar</button>

            <div class="auth-links">
                <a href="{{ route('password.request') }}">¿Has olvidado la contraseña?</a>
                <a href="{{ route('register') }}">¿No tienes cuenta? Regístrate</a>
            </div>
        </form>

        <div class="demo-box">
            <strong>Credenciales de prueba para desarrollo:</strong><br>
            • Admin: <code>admin@localmarket.test</code> / <code>password123</code><br>
            • Almacén: <code>almacen@localmarket.test</code> / <code>password123</code><br>
            • Cajero: <code>cajero@localmarket.test</code> / <code>password123</code><br>
            • Cliente: <code>cliente@localmarket.test</code> / <code>password123</code>
        </div>
    </div>
</div>
@endsection
