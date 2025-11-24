@extends('clientes.layouts.cliente')

@section('content')

    <section class="section">

        <div class="card-form">

            <h2 class="title-form">Agendar Nueva Cita</h2>

            <form action="{{ route('cliente.citas.store') }}" method="POST">
                @csrf

                <div class="form-grid">

                    {{-- SERVICIO --}}
                    <label class="form-label">
                        <span>Servicio</span>
                        <select name="servicios[]" id="servicio_id" required>
                            <option value="">Selecciona un servicio</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id }}">
                                    {{ $servicio->nombre }} - ${{ number_format($servicio->precio_base, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    {{-- VEHÍCULO --}}
                    <label class="form-label">
                        <span>Vehículo</span>
                        <select name="vehiculo_id" id="vehiculo_id" required>
                            <option value="">Selecciona un vehículo</option>
                            @foreach($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}">
                                    {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->placa }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    {{-- FECHA --}}
                    <label class="form-label">
                        <span>Fecha</span>
                        <input type="date" name="fecha" required>
                    </label>

                    {{-- HORA --}}
                    <label class="form-label">
                        <span>Hora</span>
                        <select name="hora" id="hora" required>
                            <option value="">Selecciona hora</option>
                            @foreach(['08','09','10','11','12','13','14','15','16','17'] as $h)
                                <option value="{{ $h }}:00">
                                    {{ $h <= 12 ? $h : $h-12 }}:00 {{ $h < 12 ? 'AM':'PM' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    {{-- FOOTER --}}
                    <div class="form-footer">
                        <button class="button primary" type="submit">Agendar Cita</button>
                    </div>

                </div>

            </form>

        </div>

    </section>

@endsection
