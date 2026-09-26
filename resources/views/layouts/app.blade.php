<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>@yield('title', "La Chatterie du Temple des Fées") — Élevage de Maine Coon dans la Drôme</title>
    <meta name="description" content="@yield('description', "Chatterie familiale de Maine Coon à Lapeyrouse-Mornay (26), Drôme des collines. Chatons inscrits au LOOF, parents dépistés HCM, SMA et PK-Def, résultats publiés.")">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Les icones sont tirees du logo par scripts/blason.php. Tant qu'elles
         n'existent pas, rien n'est declare et le favicon.ico de la racine
         sert de secours. --}}
    @if(file_exists(public_path('images/blason-32.png')))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/blason-32.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/blason-192.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/blason-180.png') }}">
    @endif
    @if(config('chatterie.apercu_statique'))
        {{-- L'apercu ne doit pas se retrouver indexe a cote du vrai site :
             deux fois le meme contenu, et les deux y perdent. --}}
        <meta name="robots" content="noindex, nofollow">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="La Chatterie du Temple des Fées">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="@yield('title', "La Chatterie du Temple des Fées")">
    <meta property="og:description" content="@yield('description', "Élevage familial de Maine Coon dans la Drôme. Parents dépistés, résultats publiés.")">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/cats/karrington.webp'))">
    <meta name="twitter:card" content="summary_large_image">

    {{--
        Polices auto-hebergees dans public/fonts. Aucun appel a
        fonts.googleapis.com ni fonts.gstatic.com : la typographie tient sans
        reseau tiers, et aucun visiteur n'est trace au passage. Les trois
        fichiers precharges sont ceux du premier ecran ; crossorigin est
        obligatoire meme en same-origin, une police etant toujours recuperee
        en mode CORS.
    --}}
    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="{{ asset('fonts/cormorant-garamond-normal-300-latin.woff2') }}">
    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="{{ asset('fonts/cinzel-normal-400-600-latin.woff2') }}">
    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="{{ asset('fonts/jost-normal-300-500-latin.woff2') }}">
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @stack('schema')
</head>
<body>

{{-- Le grain de la page : une tuile de bruit fixe, posee au-dessus du fond et
     sous tout le reste. C'est elle qui empeche les aplats sombres de paraitre
     plats. --}}
<div id="grain" aria-hidden="true"></div>

@include('partials.nav')

<main id="app">
    @yield('content')
</main>

@include('partials.footer')

@include('partials.seuil-olimpia')

@stack('scripts')
</body>
</html>
