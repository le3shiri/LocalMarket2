@extends('layouts.app', ['title' => 'Recuperar contraseña'])

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Recuperar contraseña</h1>
        <p class="sub">Indica tu email y te enviaremos un enlace seguro para restablecerla.</p>

        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="{{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="ejemplo@correo.es">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1rem;">Enviar enlace</button>

            <div class="auth-links" style="justify-content: center; margin-top: 1.2rem;">
                <a href="{{ route('login') }}">Volver a iniciar sesión</a>
            </div>
        </form>
    </div>
</div>
@endsection
