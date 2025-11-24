<article class="appointment appointment--with-car">

    @php
        /** @var \App\Models\Cita|null $citaModel */
        $citaModel = $cita ?? null;

        $citaIdFinal = $citaId
            ?? ($citaModel->id ?? null);

        $fechaFinal = $fecha
            ?? (
                $citaModel && $citaModel->fecha
                    ? (\Carbon\Carbon::parse($citaModel->fecha)->format('Y-m-d'))
                    : ''
            );

        $horaFinal = $hora
            ?? (
                $citaModel && $citaModel->hora_inicio
                    ? substr($citaModel->hora_inicio, 0, 5)
                    : ''
            );

        // ==============================
        //   LÓGICA DE BOTONES
        // ==============================
        $hoy = \Carbon\Carbon::today('America/Mexico_City');

        // Si hay modelo, calculamos si la fecha es pasada
        $esPasada = false;
        if ($citaModel && $citaModel->fecha) {
            $fechaCarbon = $citaModel->fecha instanceof \Carbon\Carbon
                ? $citaModel->fecha
                : \Carbon\Carbon::parse($citaModel->fecha);

            $esPasada = $fechaCarbon->lt($hoy);
        }

        // Normalizamos texto de estado
        $estadoTextoLower = strtolower($estadoTexto ?? '');

        // Estados donde NO debe haber botones
        $estadoBloqueado = in_array($estadoTextoLower, ['cancelada', 'completada']);

        // Solo mostramos botones si:
        // - NO es pasada
        // - NO está cancelada ni completada
        $mostrarBotones = !$esPasada && !$estadoBloqueado;
    @endphp

    {{-- Columna izquierda: texto --}}
    <div class="appointment-main">
        <header class="appointment-header appointment-header--stacked">
            <h3>{{ $titulo }}</h3>
            <span class="badge {{ $estado }}">{{ $estadoTexto }}</span>
        </header>

        <ul class="appointment-info">
            @foreach($detalles as $detalle)
                <li>{{ $detalle }}</li>
            @endforeach
        </ul>

        @isset($notas)
            <div class="appointment-notes">
                <strong>Observaciones:</strong>
                <span>{{ $notas }}</span>
            </div>
        @endisset

        {{-- FOOTER: SOLO SI SE PUEDE EDITAR / CANCELAR --}}
        @if ($mostrarBotones)
            <footer>
                {{-- MODIFICAR --}}
                <button
                    type="button"
                    class="button ghost small btn-modificar-cita"
                    data-cita-id="{{ $citaIdFinal }}"
                    data-fecha="{{ $fechaFinal }}"
                    data-hora="{{ $horaFinal }}"
                >
                    Modificar
                </button>

                {{-- CANCELAR --}}
                <button
                    type="button"
                    class="button ghost small btn-cancelar-cita"
                    data-cita-id="{{ $citaIdFinal }}"
                    data-cancel-url="{{ route('cliente.citas.cancelar', $citaIdFinal) }}"
                >
                    Cancelar
                </button>
            </footer>
        @endif
    </div>

    {{-- Columna derecha: imagen del vehículo --}}
    @if($citaModel && $citaModel->vehiculo)
        <div class="appointment-car">
            <div class="car-img-wrapper">
                <div class="car-skeleton"></div>

                <img
                    class="car-photo"
                    data-marca="{{ $citaModel->vehiculo->marca }}"
                    data-modelo="{{ $citaModel->vehiculo->modelo }}"
                    data-ano="{{ $citaModel->vehiculo->ano }}"
                    alt="Foto del vehículo"
                    style="display:none;"
                >
            </div>
        </div>
    @endif
</article>
