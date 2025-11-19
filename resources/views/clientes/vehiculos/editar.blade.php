@extends('clientes.layouts.cliente')

@section('content')

    <section class="section">
        <h2 style="font-weight:600; margin-bottom:1rem;">Editar Vehículo</h2>

        <form action="{{ route('cliente.vehiculos.update', $vehiculo->id) }}"
              method="POST"
              class="perfil-form">
            @csrf
            @method('PUT')

            <div class="perfil-grid">

                {{-- MARCA --}}
                <label class="perfil-label">
                    <span>Marca</span>
                    <input type="text"
                           name="marca"
                           id="marca"
                           value="{{ old('marca', $vehiculo->marca) }}"
                           required>
                </label>

                {{-- MODELO --}}
                <label class="perfil-label">
                    <span>Modelo</span>
                    <input type="text"
                           name="modelo"
                           id="modelo"
                           value="{{ old('modelo', $vehiculo->modelo) }}"
                           required>
                </label>

                {{-- AÑO --}}
                <label class="perfil-label">
                    <span>Año</span>
                    <input type="number"
                           name="ano"
                           id="ano"
                           min="1900"
                           max="2100"
                           value="{{ old('ano', $vehiculo->ano) }}"
                           required>
                </label>

                {{-- PLACA --}}
                <label class="perfil-label">
                    <span>Placa</span>
                    <input type="text"
                           name="placa"
                           id="placa"
                           placeholder="AAA-123-A"
                           value="{{ old('placa', $vehiculo->placa) }}"
                           required>
                </label>

                {{-- VIN + BOTÓN --}}
                <label class="perfil-label" style="position:relative;">
                    <span>VIN</span>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="text"
                               name="vin"
                               id="vin-input"
                               maxlength="17"
                               value="{{ old('vin', $vehiculo->vin) }}"
                               required
                               style="flex:1;">

                        <button type="button"
                                id="buscar-vin-btn"
                                class="button ghost small"
                                style="white-space:nowrap;">
                            Buscar VIN
                        </button>
                    </div>

                    <small id="vin-status"
                           style="
                           color:#62748e;
                           font-size:13px;
                           display:none;
                           margin-top:4px;
                       ">
                    </small>
                </label>

                {{-- COLOR --}}
                <label class="perfil-label">
                    <span>Color</span>
                    <input type="text"
                           name="color"
                           id="color"
                           value="{{ old('color', $vehiculo->color) }}">
                </label>

                {{-- KILOMETRAJE --}}
                <label class="perfil-label">
                    <span>Kilometraje</span>
                    <input type="number"
                           name="kilometraje"
                           min="0"
                           value="{{ old('kilometraje', $vehiculo->kilometraje) }}">
                </label>

            </div>

            <div class="perfil-footer">
                <a href="{{ route('cliente.vehiculos.index') }}" class="button ghost" style="margin-right:1rem;">
                    Cancelar
                </a>
                <button class="button primary">Guardar cambios</button>
            </div>

        </form>

    </section>

@endsection
