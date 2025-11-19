@extends('clientes.layouts.cliente')

@section('content')

    <section class="section perfil-card">

    <h2 class="perfil-title">Editar Mi Perfil</h2>

        <form action="{{ route('cliente.perfil.update') }}" method="POST" id="editar-perfil-form" class="perfil-form">
            @csrf
            @method('PATCH')

            <div class="perfil-grid">

                <label class="perfil-label">
                    <span>Nombre</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}">
                </label>

                <label class="perfil-label">
                    <span>Correo Electrónico</span>
                    <input type="email" value="{{ old('email', $user->email) }}" disabled>
                </label>

                <label class="perfil-label">
                    <span>Teléfono</span>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                </label>

                <label class="perfil-label">
                    <span>Dirección</span>
                    <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}">
                </label>

                <label class="perfil-label">
                    <span>RFC</span>
                    <input type="text" name="rfc" oninput="this.value = this.value.toUpperCase()"
                           value="{{ old('rfc', $cliente->rfc) }}">
                </label>

                <label class="perfil-label">
                    <span>Fecha de nacimiento</span>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento) }}">
                </label>

            </div>

            <div class="perfil-footer">
                <button class="button primary" type="submit">Guardar Cambios</button>
            </div>

        </form>

    </section>

@endsection
