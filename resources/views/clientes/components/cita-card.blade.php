<article class="appointment">
    <header>
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

    {{-- FOOTER SOLO SI LA CITA NO ESTÁ CANCELADA --}}
    @if (strtolower($estadoTexto) !== 'cancelada')
        <footer>

            {{-- MODIFICAR --}}
            <button
                type="button"
                class="button ghost small btn-modificar-cita"
                data-id="{{ $citaId }}"
                data-fecha="{{ $fecha }}"
                data-hora="{{ $hora }}"
            >
                Modificar
            </button>

            {{-- CANCELAR --}}
            <button
                type="button"
                class="button ghost small btn-cancelar-cita"
                data-cita-id="{{ $citaId }}"
                data-cancel-url="{{ route('cliente.citas.cancelar', $citaId) }}"
            >
                Cancelar
            </button>

        </footer>
    @endif
</article>
