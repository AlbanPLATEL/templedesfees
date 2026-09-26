{{--
    La feuille : ce qui est commun au contrat et à la facture.

    Un document, pas une page de site. Il n'a ni menu, ni pied de page, ni
    bandeau : on y arrive par un lien, on le lit, on l'imprime, on le range.
    La barre du haut est le seul élément d'écran, et elle ne s'imprime pas.

    Pas de génération de PDF côté serveur : la page EST le document. Tous les
    navigateurs savent l'enregistrer en PDF depuis leur propre dialogue
    d'impression, et le fichier qui en sort reste sélectionnable, cherchable et
    net à toutes les tailles — ce qu'une image collée dans un PDF ne serait pas.
--}}
@php
    use App\Models\Setting;

    $elevage = [
        'nom'         => Setting::get('elevage.nom', 'La Chatterie du Temple des Fées'),
        'adresse'     => Setting::get('elevage.adresse'),
        'code_postal' => Setting::get('elevage.code_postal'),
        'ville'       => Setting::get('elevage.ville'),
        'telephone'   => Setting::get('contact.telephone'),
        'email'       => Setting::get('contact.email'),
        'siren'       => Setting::get('legal.siren'),
        'certificat'  => Setting::get('legal.certificat'),
    ];

    // Ce qui manque encore pour que le document soit valable. On le dit à
    // l'écran, à l'éleveuse, et on ne l'imprime pas : sur le papier, les
    // mentions absentes se signalent déjà toutes seules, en or, à leur place.
    $manques = collect([
        'le numéro SIREN'                 => blank($elevage['siren']),
        'le numéro d’ACACED' => blank($elevage['certificat']),
        'l’adresse postale'               => blank($elevage['adresse']),
    ])->filter()->keys();

    /*
        Le retour. Les documents de réservation ramènent à leur page ; un
        autre document dirait où il veut. On ne suppose pas qu'il existe
        toujours une réservation.
    */
    $retour = $retour ?? [
        'url'     => isset($reservation) ? route('reservation.montrer', ['jeton' => $reservation->jeton]) : url('/'),
        'libelle' => isset($reservation) ? 'Revenir à la réservation' : 'Revenir au site',
    ];

    // Une facture porte les mentions obligatoires ; tout document n'y est
    // pas tenu.
    $mentions = $mentions ?? true;
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('titre') — {{ $elevage['nom'] }}</title>

    {{-- Un document privé, atteint par un lien envoyé à une famille précise. --}}
    <meta name="robots" content="noindex, nofollow">

    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    @vite('resources/css/document.css')
</head>
<body>

<div class="barre">
    <a href="{{ $retour['url'] }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m0 0 6-6m-6 6 6 6"/></svg>
        {{ $retour['libelle'] }}
    </a>

    <button type="button" class="pousse" onclick="window.print()">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/>
            <path d="M6 14h12v7H6z"/>
        </svg>
        Imprimer ou enregistrer en PDF
    </button>
</div>

{{-- Seule l'éleveuse voit ce rappel : elle est la seule à être connectée,
     et la seule qui puisse y faire quelque chose. --}}
@if($mentions && $manques->isNotEmpty() && auth()->check())
    <p class="manques">
        <b>Ce document n’est pas encore complet.</b>
        Il manque {{ $manques->join(', ', ' et ') }} — à renseigner dans
        Le site › Réglages avant de l’envoyer à une famille.
    </p>
@endif

<main class="feuille">

    <header class="tete">
        <div class="maison">
            @if(file_exists(public_path('images/blason-192.png')))
                <img class="blason" src="{{ asset('images/blason-192.png') }}" alt="">
            @endif
            <div>
                <h1>{{ $elevage['nom'] }}</h1>
                <address>
                    @if($elevage['adresse']){{ $elevage['adresse'] }}<br>@endif
                    {{ $elevage['code_postal'] }} {{ $elevage['ville'] }}<br>
                    @if($elevage['telephone']){{ $elevage['telephone'] }} · @endif
                    {{ $elevage['email'] }}
                </address>
            </div>
        </div>

        <div class="timbre">
            <span class="genre">@yield('genre')</span>
            <span class="numero">@yield('numero')</span>
            <span class="date">@yield('date')</span>
        </div>
    </header>

    @yield('document')

    <footer class="pied">
        {{ $elevage['nom'] }}@if($elevage['adresse']) · {{ $elevage['adresse'] }}@endif ·
        {{ $elevage['code_postal'] }} {{ $elevage['ville'] }}<br>

        @if($mentions)
            SIREN {!! $elevage['siren']
                ? e($elevage['siren'])
                : '<span class="a-completer">à compléter</span>' !!}
            · ACACED {!! $elevage['certificat']
                ? e($elevage['certificat'])
                : '<span class="a-completer">à compléter</span>' !!}
        @endif

        @hasSection('pied')
            <br>@yield('pied')
        @endif
    </footer>

</main>

</body>
</html>
