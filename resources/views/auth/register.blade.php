@extends('layouts.app', ['title' => 'Crear cuenta'])

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Crear cuenta</h1>
        <p class="sub">Regístrate para comprar y seguir tus pedidos online.</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="field">
                <label for="name">Nombre y apellidos</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus class="{{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="María Garcia López">
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="{{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="maria@ejemplo.es">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="phone">Teléfono (opcional)</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="612 34 56 78">
                @error('phone')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Contraseña (mínimo 8 caracteres)</label>
                <input type="password" id="password" name="password" required class="{{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="••••••••">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1rem;">Registrarme</button>

            <div class="auth-links" style="justify-content: center; margin-top: 1.2rem;">
                <span>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></span>
            </div>
        </form>
    </div>
</div>
@endsection
