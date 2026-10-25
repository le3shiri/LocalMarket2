@extends('layouts.staff', ['title' => 'Gestión de Productos'])

@section('content')
<div class="card">
    <div class="card-title">
        <h2>Catálogo de Productos</h2>
        <a href="{{ route('staff.products.create') }}" class="btn btn-primary btn-sm">+ Nuevo Producto</a>
    </div>

    <form method="GET" action="{{ route('staff.products.index') }}" class="filters">
        <div class="field">
            <label for="q">Buscar por nombre</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Nombre del producto...">
        </div>
        <div class="field">
            <label for="category_id">Filtrar por categoría</label>
            <select id="category_id" name="category_id">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-outline btn-sm" style="margin-bottom: 2px;">Filtrar</button>
        @if(request()->hasAny(['q', 'category_id']))
            <a href="{{ route('staff.products.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom: 2px;">Limpiar</a>
        @endif
    </form>

    @if($products->isEmpty())
        <div class="empty">
            <p>No se encontraron productos registrados.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="num">Precio</th>
                        <th>Unidad</th>
                        <th>Ubicación</th>
                        <th>Caducidad</th>
                        <th>Estado</th>
                        <th style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                <div class="muted" style="font-size: .78rem;">{{ $product->slug }}</div>
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td class="num"><strong>{{ eur($product->price) }}</strong></td>
                            <td>{{ $product->unitLabel() }}</td>
                            <td class="muted">{{ $product->shelf_location ?: '—' }}</td>
                            <td>
                                @if($product->tracks_expiry)
                                    <span class="badge badge-amber">Por lotes</span>
                                @else
                                    <span class="muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-green">Activo</span>
                                @else
                                    <span class="badge badge-red">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('staff.products.edit', $product) }}" class="btn btn-sm btn-outline">Editar</a>
                                    <form action="{{ route('staff.products.destroy', $product) }}" method="POST" data-confirm="¿Seguro que deseas eliminar este producto?">
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
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
