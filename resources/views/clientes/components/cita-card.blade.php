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

    <footer>
        <button class="button ghost small">Modificar</button>
        <button class="button ghost small">Cancelar</button>
    </footer>
</article>
