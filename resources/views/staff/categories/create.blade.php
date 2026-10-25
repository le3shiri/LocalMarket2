@extends('layouts.staff', ['title' => 'Nueva Categoría'])

@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-title">
        <h2>Crear Categoría</h2>
        <a href="{{ route('staff.categories.index') }}" class="btn btn-sm btn-ghost">&larr; Volver</a>
    </div>

    <form action="{{ route('staff.categories.store') }}" method="POST">
        @csrf

        <div class="field">
            <label for="name">Nombre de la categoría *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Ej: Carnes y Aves">
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="icon">Icono Emoji</label>
            <input type="text" id="icon" name="icon" value="{{ old('icon', '🛒') }}" placeholder="🍎, 🥩, 🧀, 🥦">
            <span class="hint">Introduce un emoji descriptivo representativo de la sección.</span>
            @error('icon') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" placeholder="Breve descripción para la categoría">{{ old('description') }}</textarea>
            @error('description') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="check" style="margin: 1rem 0;">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label for="is_active">Categoría activa y visible en la tienda</label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar categoría</button>
            <a href="{{ route('staff.categories.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
