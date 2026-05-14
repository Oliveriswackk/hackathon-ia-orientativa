@extends('layouts.app')

@section('title', 'Listo · ¿Y qué hago?')

@push('head')
    <style>
        .navbar, footer { display: none; }
        .page-main { padding-top: 0; }
    </style>
@endpush

@section('content')
    <div class="ov-screen ov-bg">
        <div class="ov-shell ov-center">
            <div class="ov-hero-icon">
                <img class="ov-lupin-img" src="{{ asset('images/lupin-mascot.png') }}" width="88" height="88" alt="Lupin, tu orientador">
            </div>
            <h1 class="ov-h2">Listo!!!</h1>
            <p class="ov-p">Ya puedes comenzar a usar OrientaVox. La mejor decisión es la que tomas estando informado.</p>

            <a class="ov-btn ov-btn-primary" href="{{ route('home') }}">Comenzar</a>

            <div class="ov-dots" aria-label="Progreso">
                <span class="ov-dot" aria-hidden="true"></span>
                <span class="ov-dot" aria-hidden="true"></span>
                <span class="ov-dot ov-dot--active" aria-hidden="true"></span>
            </div>
        </div>
    </div>
@endsection

