@extends('clientes.layouts.cliente')

@section('content')

    <section class="section perfil-card">

        <h2 class="perfil-title">Editar Mi Perfil</h2>

        @if (session('success'))
            <div data-toast="success" data-message="{{ session('success') }}" style="display:none;"></div>
        @endif

        @if (session('error'))
            <div data-toast="error" data-message="{{ session('error') }}" style="display:none;"></div>
        @endif

        <form action="{{ route('cliente.perfil.update') }}" method="POST" id="editar-perfil-form" class="perfil-form">
            @csrf
            @method('PATCH')

            <div class="perfil-grid">

                <label class="perfil-label">
                    <span>Nombre</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                <label class="perfil-label">
                    <span>Correo Electrónico</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    <small style="color:#64748b;font-size:13px;display:block;margin-top:4px;">Si cambias el correo se cerrará tu sesión para aplicar el ajuste.</small>
                    @error('email')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                <label class="perfil-label">
                    <span>Teléfono</span>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required>
                    @error('phone')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                <label class="perfil-label">
                    <span>Dirección</span>
                    <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}">
                    @error('direccion')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                <label class="perfil-label">
                    <span>RFC</span>
                    <input type="text" name="rfc" oninput="this.value = this.value.toUpperCase()" value="{{ old('rfc', $cliente->rfc) }}">
                    @error('rfc')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                <label class="perfil-label">
                    <span>Fecha de nacimiento</span>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento) }}">
                    @error('fecha_nacimiento')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

            </div>

            <div class="perfil-footer">
                <button class="button primary" type="submit">Guardar Cambios</button>
            </div>

        </form>

    </section>

    <section class="section perfil-card" style="margin-top:1.5rem;">
        <h2 class="perfil-title">Cambiar contraseña</h2>
        <p style="color:#64748b;font-size:14px;margin-bottom:1.5rem;">Al guardar una nueva contraseña cerraremos tu sesión por seguridad.</p>

        @if ($errors->hasBag('updatePassword'))
            @foreach ($errors->updatePassword->all() as $error)
                <div data-toast="error" data-message="{{ $error }}" style="display:none;"></div>
            @endforeach
        @endif

        <form method="POST" action="{{ route('cliente.perfil.password') }}" class="perfil-form">
            @csrf
            @method('PUT')

            <div class="perfil-grid" style="grid-template-columns:1fr;">
                <label class="perfil-label">
                    <span>Contraseña actual</span>
                    <input type="password" name="current_password" autocomplete="current-password" required>
                    @foreach ($errors->updatePassword->get('current_password', []) as $message)
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @endforeach
                </label>

                <label class="perfil-label">
                    <span>Nueva contraseña</span>
                    <input type="password" name="password" autocomplete="new-password" required>
                    @foreach ($errors->updatePassword->get('password', []) as $message)
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @endforeach
                </label>

                <label class="perfil-label">
                    <span>Confirmar contraseña</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" required>
                </label>
            </div>

            <div class="perfil-footer">
                <button class="button primary" type="submit">Actualizar contraseña</button>
            </div>
        </form>
    </section>

@endsection
