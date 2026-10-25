@extends('layouts.staff', ['title' => 'Panel de Control'])

@section('content')
<div class="stats">
    <div class="stat">
        <div class="label">Rol activo</div>
        <div class="value" style="font-size: 1.4rem;">{{ auth()->user()->roleLabel() }}</div>
        <span class="ico">👤</span>
    </div>
    <div class="stat">
        <div class="label">Estado de cuenta</div>
        <div class="value" style="font-size: 1.4rem; color: var(--green-700);">Activa</div>
        <span class="ico">✓</span>
    </div>
    <div class="stat">
        <div class="label">Ubicación asignada</div>
        <div class="value" style="font-size: 1.4rem;">Barcelona</div>
        <span class="ico">📍</span>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h2>Bienvenido/a al área de gestión, {{ auth()->user()->name }}</h2>
    </div>
    <p>Has iniciado sesión en el sistema de gestión interna de <strong>LocalMarket</strong> con perfil de <strong>{{ auth()->user()->roleLabel() }}</strong>.</p>
    <p class="muted">Desde el menú lateral puedes acceder a las secciones autorizadas para tu rol de acuerdo con el modelo de control de accesos (RBAC).</p>
</div>
@endsection
