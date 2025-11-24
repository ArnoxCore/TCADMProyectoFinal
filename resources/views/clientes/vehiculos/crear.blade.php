@extends('clientes.layouts.cliente')

@section('content')

    <section class="section">
        <h2 style="font-weight:600; margin-bottom:1rem;">Agregar Vehículo</h2>

        <form action="{{ route('cliente.vehiculos.store') }}" method="POST" class="perfil-form">
            @csrf

            <div class="perfil-grid">
                @php
                    $marcaSeleccionada = old('marca');
                @endphp

                {{-- MARCA --}}
                <label class="perfil-label">
                    <span>Marca</span>
                    <select name="marca" id="marca" required>
                        <option value="">Selecciona una marca</option>
                        @foreach($marcas as $marca)
                            <option
                                value="{{ $marca->nombre }}"
                                data-make-id="{{ $marca->id }}"
                                {{ $marcaSeleccionada === $marca->nombre ? 'selected' : '' }}
                            >
                                {{ $marca->nombre }}
                            </option>
                        @endforeach
                    </select>
                </label>

                {{-- MODELO --}}
                <label class="perfil-label">
                    <span>Modelo</span>
                    <select name="modelo" id="modelo" {{ $marcaSeleccionada ? '' : 'disabled' }} required>
                        <option value="">Selecciona un modelo</option>
                    </select>
                </label>

                {{-- AÑO --}}
                <label class="perfil-label">
                    <span>Año</span>
                    <select name="ano" id="ano" {{ old('modelo') ? '' : 'disabled' }} required>
                        <option value="">Selecciona un año</option>
                    </select>
                </label>

                {{-- PLACA --}}
                <label class="perfil-label">
                    <span>Placa</span>
                    <input type="text" name="placa" id="placa" placeholder="AAA-123-A" required>
                </label>

                {{-- VIN + BOTÓN --}}
                <label class="perfil-label" style="position:relative;">
                    <span>VIN</span>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="text"
                               name="vin"
                               id="vin-input"
                               maxlength="17"
                               placeholder="5NPLM4AG1MH056782"
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
                    <input type="text" name="color" id="color" placeholder="GRIS OXFORD">
                </label>

                {{-- KILOMETRAJE --}}
                <label class="perfil-label">
                    <span>Kilometraje</span>
                    <input type="number" name="kilometraje" min="0" PLACEHOLDER="85000">
                </label>

            </div>

            <div class="perfil-footer">
                <a href="{{ route('cliente.vehiculos.index') }}" class="button ghost" style="margin-right:1rem;">
                    Cancelar
                </a>
                <button class="button primary">Guardar Vehículo</button>
            </div>

        </form>

        <script>
            window.CATALOGO_ENDPOINTS = {
                modelos: "{{ route('cliente.vehiculos.catalogo.modelos', ['make' => '__MAKE__']) }}"
            };
            window.CATALOGO_OLD = {
                modelo: "{{ old('modelo') }}",
                ano: "{{ old('ano') }}"
            };
        </script>

    </section>

@endsection
