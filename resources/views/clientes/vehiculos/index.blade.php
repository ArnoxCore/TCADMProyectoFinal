@extends('clientes.layouts.cliente')

@section('content')

    <section class="section">
        <div style="display:flex; justify-content: space-between; align-items:center;">
            <h2 style="font-weight: 600;">Mis Vehículos</h2>

            <a href="{{ route('cliente.vehiculos.crear') }}" class="button primary">
                + Agregar Vehículo
            </a>
        </div>

        <hr style="border: none; border-top:1px solid var(--line); margin:1rem 0;">

        @if($vehiculos->count() > 0)

            @foreach($vehiculos as $vehiculo)

                <div class="appointment car-card">

                    {{-- INFO --}}
                    <div class="car-info">
                        <header>
                            <h3>{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</h3>
                        </header>

                        <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center; margin:0.35rem 0 0.5rem;">
                            <span class="badge info">{{ $vehiculo->placa }}</span>
                            @if($vehiculo->vin_verificado)
                                <span class="badge success" title="VIN confirmado con VPIC">VIN confirmado con VPIC</span>
                            @else
                                <span class="badge warning" title="VIN capturado manualmente">VIN capturado manualmente</span>
                            @endif
                        </div>

                        <ul class="appointment-info">
                            <li><strong>Año:</strong> {{ $vehiculo->ano }}</li>
                            <li><strong>Color:</strong> {{ $vehiculo->color ?? '—' }}</li>
                            <li><strong>Kilometraje:</strong>
                                {{ $vehiculo->kilometraje ? $vehiculo->kilometraje . ' km' : '—' }}
                            </li>
                        </ul>

                        <footer>
                            <a href="{{ route('cliente.vehiculos.editar', $vehiculo->id) }}"
                               class="button ghost small">Editar</a>
                        </footer>
                    </div>

                    {{-- FOTO DEL VEHÍCULO --}}
                    <div class="car-img-wrapper">

                        {{-- Skeleton mientras carga --}}
                        <div class="car-skeleton"></div>

                        {{-- Imagen real (oculta al inicio) --}}
                        <img
                            class="car-photo"
                            data-marca="{{ $vehiculo->marca }}"
                            data-modelo="{{ $vehiculo->modelo }}"
                            data-ano="{{ $vehiculo->ano }}"
                            alt="Foto del vehículo"
                            style="display:none;"
                        >
                    </div>

                </div>

            @endforeach

        @else
            <p style="color: var(--muted); margin-top:1rem;">
                Aún no has registrado vehículos.
            </p>
        @endif

    </section>

@endsection
