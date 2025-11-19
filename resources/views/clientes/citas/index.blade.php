@extends('clientes.layouts.cliente')

@section('content')

    <section class="section">
        <h2 style="margin-bottom: 1rem; font-weight: 600;">Mis Citas</h2>

        {{-- Aquí se mostrarán las citas reales del cliente --}}
        @if(isset($citas) && count($citas) > 0)
            @foreach($citas as $cita)
                @include('clientes.components.cita-card', [
                    'titulo' => optional($cita->servicios->first())->nombre ?? 'Servicio',
                    'estado' => $cita->estatus === 'completada' ? 'success' : 'warning',
                    'estadoTexto' => ucfirst($cita->estatus),
                    'detalles' => [
                        $cita->vehiculo->marca . ' ' . $cita->vehiculo->modelo,
                        'Fecha: ' . $cita->fecha->format('d/m/Y'),
                        'Hora: ' . substr($cita->hora_inicio, 0, 5) . ' - ' . substr($cita->hora_fin, 0, 5),
                    ]
                ])
            @endforeach
        @else
            <p style="color: var(--muted);">Aún no tienes citas registradas.</p>
        @endif
    </section>

    @include('clientes.components.modal-editar')

@endsection
