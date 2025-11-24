@extends('clientes.layouts.cliente')

@section('content')

    @include('clientes.components.modal-editar')

    @include('clientes.components.stats-cards')

    @include('clientes.components.dashboard-tabs', [
        'proximas' => view('clientes.components.citas-list', ['citas' => $proximas ?? []])->render(),
        'historial' => view('clientes.components.citas-list', ['citas' => $historial ?? []])->render()
    ])

    @include('clientes.components.modal-crear')

@endsection
