@extends('clientes.layouts.cliente')

@section('content')

    <section class="section">
        <h2 style="font-weight:600; margin-bottom:1rem;">Editar Vehículo</h2>

        <form action="{{ route('cliente.vehiculos.update', $vehiculo->id) }}"
              method="POST"
              class="perfil-form">
            @csrf
            @method('PUT')
             <input type="hidden" name="vin_verificado" id="vinVerificado"
                 value="{{ old('vin_verificado', $vehiculo->vin_verificado ? 1 : 0) }}">
             <input type="hidden" name="vin_detected_marca" id="vinDetectedMarca"
                 value="{{ old('vin_detected_marca', $vehiculo->vin_detected_marca) }}">
             <input type="hidden" name="vin_detected_modelo" id="vinDetectedModelo"
                 value="{{ old('vin_detected_modelo', $vehiculo->vin_detected_modelo) }}">
             <input type="hidden" name="vin_detected_ano" id="vinDetectedAno"
                 value="{{ old('vin_detected_ano', $vehiculo->vin_detected_ano) }}">

            <div class="perfil-grid">
                @php
                    $marcaSeleccionada = old('marca', $vehiculo->marca);
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
                    @error('marca')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                {{-- MODELO --}}
                <label class="perfil-label">
                    <span>Modelo</span>
                    <select name="modelo" id="modelo" {{ $marcaSeleccionada ? '' : 'disabled' }} required>
                        <option value="">Selecciona un modelo</option>
                    </select>
                    @error('modelo')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                {{-- AÑO --}}
                <label class="perfil-label">
                    <span>Año</span>
                    <select name="ano" id="ano" {{ old('modelo', $vehiculo->modelo) ? '' : 'disabled' }} required>
                        <option value="">Selecciona un año</option>
                    </select>
                    @error('ano')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                {{-- PLACA --}}
                @php
                    $placaValor = old('placa', $vehiculo->placa);
                    $placaPartes = ['', '', ''];
                    if ($placaValor) {
                        $partes = explode('-', strtoupper($placaValor));
                        $placaPartes[0] = $partes[0] ?? '';
                        $placaPartes[1] = $partes[1] ?? '';
                        $placaPartes[2] = $partes[2] ?? '';
                    }
                @endphp
                <label class="perfil-label">
                          <span>Placa</span>
                          <input type="hidden"
                              name="placa"
                              id="placa"
                              value="{{ $placaValor ?? '' }}">
                    <div class="placa-inputs" style="display:flex; align-items:center; gap:0.35rem;">
                        <input type="text"
                               class="placa-block"
                               data-placa-segment="prefix"
                               maxlength="3"
                               inputmode="text"
                               placeholder="ABC"
                               value="{{ $placaPartes[0] }}"
                               required>
                        <span style="font-weight:600; color:#94a3b8;">-</span>
                        <input type="text"
                               class="placa-block"
                               data-placa-segment="numbers"
                               maxlength="3"
                               inputmode="numeric"
                               placeholder="123"
                               value="{{ $placaPartes[1] }}"
                               required>
                        <span style="font-weight:600; color:#94a3b8;">-</span>
                        <input type="text"
                               class="placa-block"
                               data-placa-segment="suffix"
                               maxlength="1"
                               inputmode="text"
                               placeholder="A"
                               value="{{ $placaPartes[2] }}"
                               required>
                    </div>
                    @error('placa')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
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
                    @error('vin')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                {{-- COLOR --}}
                <label class="perfil-label">
                    <span>Color</span>
                          <input type="text"
                              name="color"
                              id="color"
                              value="{{ old('color', $vehiculo->color) }}">
                    @error('color')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

                {{-- KILOMETRAJE --}}
                <label class="perfil-label">
                    <span>Kilometraje</span>
                          <input type="number"
                              name="kilometraje"
                              min="0"
                              value="{{ old('kilometraje', $vehiculo->kilometraje) }}">
                    @error('kilometraje')
                        <small style="color:#b91c1c;font-size:13px;margin-top:4px;display:block;">{{ $message }}</small>
                    @enderror
                </label>

            </div>

            <div class="perfil-footer">
                <a href="{{ route('cliente.vehiculos.index') }}" class="button ghost" style="margin-right:1rem;">
                    Cancelar
                </a>
                <button class="button primary">Guardar cambios</button>
            </div>

        </form>

        <script>
            window.CATALOGO_ENDPOINTS = {
                modelos: "{{ route('cliente.vehiculos.catalogo.modelos', ['make' => '__MAKE__']) }}"
            };
            window.CATALOGO_OLD = {
                modelo: "{{ old('modelo', $vehiculo->modelo) }}",
                ano: "{{ old('ano', $vehiculo->ano) }}"
            };
        </script>

    </section>

@endsection
