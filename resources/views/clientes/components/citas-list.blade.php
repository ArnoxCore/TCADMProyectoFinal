<div class="citas-list">
    @foreach($citas as $cita)
        @include('clientes.components.cita-card', [
        'citaId' => $cita->id,
        'titulo' => optional($cita->servicios->first())->nombre ?? 'Servicio',
        'estado' => $cita->estatus_css,
        'estadoTexto' => $cita->estatus_texto,
        'detalles' => [
            $cita->vehiculo->marca . ' ' . $cita->vehiculo->modelo,
            'Mecánico: ' . ($cita->mecanico->nombre ?? 'Sin asignar'),
            'Fecha: ' . $cita->fecha->format('d/m/Y'),
            'Hora: ' . substr($cita->hora_inicio, 0, 5) . ' - ' . substr($cita->hora_fin, 0, 5),
        ],
        'notas' => $cita->observaciones_cliente,
    ])
    @endforeach
</div>
