<section class="section dashboard-tabs">

    <div class="tabs">
        <button class="tab active" data-tab="proximas">Próximas Citas</button>
        <button class="tab" data-tab="historial">Historial</button>

        <button
            class="button primary small add-cita-btn"
            id="openModal"
            data-perfil="{{ $perfilCompleto ? 1 : 0 }}"
        >
            + Agendar Nueva Cita
        </button>
    </div>

    <div id="proximas" class="tab-content active">
        {!! $proximas ?? '' !!}
    </div>

    <div id="historial" class="tab-content">
        {!! $historial ?? '' !!}
    </div>

</section>
