@extends('clientes.layouts.cliente')

@section('content')

    <section class="section">
        <h2 style="margin-bottom: 1rem; font-weight: 600;">Mis Citas</h2>

        @if($citas->count() > 0)

            <div class="citas-list">
                @foreach($citas as $cita)
                    @include('clientes.components.cita-card', [
                        // ID para editar / cancelar
                        'citaId'      => $cita->id,

                        // Título de la tarjeta
                        'titulo'      => optional($cita->servicios->first())->nombre ?? 'Servicio',

                        // Estado de la cita
                        'estado'      => $cita->estatus_css,
                        'estadoTexto' => $cita->estatus_texto,

                        // Texto que se muestra en la lista
                        'detalles'    => [
                            $cita->vehiculo->marca . ' ' . $cita->vehiculo->modelo,
                            'Fecha: ' . $cita->fecha->format('d/m/Y'),
                            'Hora: ' . substr($cita->hora_inicio, 0, 5) . ' - ' . substr($cita->hora_fin, 0, 5),
                        ],

                        // Observaciones
                        'notas' => $cita->observaciones_cliente,

                        // Datos crudos para el modal de editar
                        'fecha' => $cita->fecha->format('Y-m-d'),
                        'hora'  => substr($cita->hora_inicio, 0, 5),
                    ])
                @endforeach
            </div>

        @else
            <p style="color: var(--muted);">Aún no tienes citas registradas.</p>
        @endif
    </section>

    @include('clientes.components.modal-editar')

@endsection
