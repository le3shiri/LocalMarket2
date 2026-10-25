@extends('layouts.staff', ['title' => 'Gestión de Categorías'])

@section('content')
<div class="card">
    <div class="card-title">
        <h2>Categorías de Productos</h2>
        <a href="{{ route('staff.categories.create') }}" class="btn btn-primary btn-sm">+ Nueva Categoría</a>
    </div>

    @if($categories->isEmpty())
        <div class="empty">
            <p>No hay categorías registradas.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">Icono</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td style="font-size: 1.5rem; text-align: center;">{{ $category->icon }}</td>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td class="muted">{{ $category->slug }}</td>
                            <td>{{ $category->products_count }} productos</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge badge-green">Activa</span>
                                @else
                                    <span class="badge badge-red">Inactiva</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('staff.categories.edit', $category) }}" class="btn btn-sm btn-outline">Editar</a>
                                    <form action="{{ route('staff.categories.destroy', $category) }}" method="POST" data-confirm="¿Seguro que deseas eliminar esta categoría?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost" style="color: var(--red-600);">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
