@extends('layouts.staff', ['title' => 'Editar Categoría'])

@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-title">
        <h2>Editar Categoría: {{ $category->name }}</h2>
        <a href="{{ route('staff.categories.index') }}" class="btn btn-sm btn-ghost">&larr; Volver</a>
    </div>

    <form action="{{ route('staff.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="name">Nombre de la categoría *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="icon">Icono Emoji</label>
            <input type="text" id="icon" name="icon" value="{{ old('icon', $category->icon) }}">
            @error('icon') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="description">Descripción</label>
            <textarea id="description" name="description">{{ old('description', $category->description) }}</textarea>
            @error('description') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="check" style="margin: 1rem 0;">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
            <label for="is_active">Categoría activa</label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('staff.categories.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
