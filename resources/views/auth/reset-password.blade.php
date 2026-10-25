@extends('layouts.app', ['title' => 'Restablecer contraseña'])

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Nueva contraseña</h1>
        <p class="sub">Introduce tu nueva contraseña para acceder a tu cuenta.</p>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus class="{{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="ejemplo@correo.es">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Nueva contraseña (mínimo 8 caracteres)</label>
                <input type="password" id="password" name="password" required class="{{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="••••••••">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmar nueva contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1rem;">Guardar nueva contraseña</button>
        </form>
    </div>
</div>
@endsection
