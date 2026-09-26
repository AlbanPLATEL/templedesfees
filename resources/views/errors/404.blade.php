@extends('layouts.app')

@section('title', "Cette page n'existe pas")
@section('description', "La page demandée n'existe pas ou plus sur le site de la Chatterie du Temple des Fées.")

@push('head')
    <meta name="robots" content="noindex">
@endpush

@php
    // Les anciennes adresses exactes sont redirigées en 301 par routes/web.php et
    // n'arrivent jamais ici. Restent les adresses PLUS PROFONDES du même site —
    // /reproducteurs-2/uzumaki, par exemple — qu'aucune redirection exacte ne
    // couvre. On suggère alors la bonne rubrique d'après le premier segment,
    // plutôt que de laisser le visiteur dans une impasse.
    $premier    = explode('/', trim(request()->path(), '/'))[0] ?? '';
    $suggestion = config('chatterie.anciennes_urls')[$premier] ?? null;
@endphp

@section('content')

<section class="bande">
    <div class="wrap" style="max-width:760px">
        <x-section-head
            class="monte"
            niveau="1"
            eyebrow="Erreur 404"
            titre="Cette page n’existe pas"
            lede="Le lien est peut-être ancien, ou l’adresse comporte une erreur. Voici par où reprendre." />

        @if($suggestion)
            <p class="retour" style="margin-bottom:30px">
                Vous cherchiez sans doute
                <a href="{{ route($suggestion) }}">cette page</a> — l’adresse a changé depuis l’ancien site.
            </p>
        @endif

        <div class="cellules monte">
            <a class="cellule" href="{{ route('kittens.index') }}" style="text-decoration:none">
                <span class="n">Nos chatons</span>
                <p>La portée en cours, les fiches détaillées et les disponibilités.</p>
            </a>
            <a class="cellule" href="{{ route('cats.index') }}" style="text-decoration:none">
                <span class="n">L’élevage</span>
                <p>Les reproducteurs, leurs pedigrees et leurs dépistages.</p>
            </a>
            <a class="cellule" href="{{ route('adoption.create') }}" style="text-decoration:none">
                <span class="n">Adopter</span>
                <p>Le parcours en quatre étapes et la demande de pré-réservation.</p>
            </a>
            <a class="cellule" href="{{ route('contact') }}" style="text-decoration:none">
                <span class="n">Nous joindre</span>
                <p>Par téléphone, par email, ou en venant nous voir sur rendez-vous.</p>
            </a>
        </div>

        <div class="btnrow" style="margin-top:34px">
            <a class="btn" href="{{ route('home') }}">Retour à l’accueil</a>
        </div>
    </div>
</section>

@endsection
