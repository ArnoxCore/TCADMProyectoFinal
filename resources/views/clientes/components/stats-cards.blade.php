<section class="cards-3">

    {{-- Próximas Citas --}}
    <article class="card">
        <div class="head"><span class="title">Próximas Citas</span></div>
        <div class="body">
            <div class="kpi">{{ $proximasCitasCount }}</div>
            <div class="sub">Citas agendadas</div>
        </div>
    </article>

    {{-- Servicios Completados --}}
    <article class="card">
        <div class="head"><span class="title">Servicios Completados</span></div>
        <div class="body">
            <div class="kpi">{{ $serviciosCompletadosCount }}</div>
            <div class="sub">Total de servicios</div>
        </div>
    </article>

    {{-- Próximo Servicio --}}
    <article class="card">
        <div class="head"><span class="title">Próximo Servicio</span></div>
        <div class="body">

            @if($proximaCita)
                <div class="kpi">{{ $proximaCita->fecha->format('d/m/Y') }}</div>
                <div class="sub">
                    {{ substr($proximaCita->hora_inicio,0,5) }}
                </div>
            @else
                <div class="kpi">—</div>
                <div class="sub">Sin citas próximas</div>
            @endif

        </div>
    </article>

</section>
