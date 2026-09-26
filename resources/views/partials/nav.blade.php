@php
    $tel   = \App\Models\Setting::get('contact.telephone', '06 77 35 45 87');
    $mail  = \App\Models\Setting::get('contact.email', 'letempledesfees@outlook.fr');
    $insta = \App\Models\Setting::get('contact.instagram');
    $fb    = \App\Models\Setting::get('contact.facebook');
    $telLien = \Illuminate\Support\Str::of($tel)->replace(' ', '')->replaceFirst('0', '+33');
@endphp

{{--
    La marque à gauche, les rubriques et les quatre moyens de nous joindre à
    droite.

    Le fronton centré a été essayé : avec sept rubriques il ne tient pas — la
    marque n'est jamais au milieu et vient buter dans le menu. Trois zones
    alignées valent mieux qu'une symétrie qui ne tombe juste sur aucune largeur.
--}}
<header class="bandeau" id="bandeau">

    <a class="marque" href="{{ route('home') }}">
        {{-- Le nom complet, en deux registres : l’article et le nom commun
             au-dessus, le nom propre en or. D’une seule ligne il tiendrait
             près de 480 px de capitales espacées et viendrait buter dans le
             menu — le bandeau a déjà débordé une fois pour bien moins. --}}
        <span class="marque-sus">La Chatterie du</span>
        <b class="or">Temple des Fées</b>
        <small>Maine Coon · Drôme</small>
    </a>

    <div class="barre-droite">
        <nav id="menu" aria-label="Navigation principale">
            {{-- Les deux intitulés et le fleuron n'existent que dans le menu
                 déplié : sur un bandeau, une rubrique ne s'annonce pas. --}}
            <span class="menu-titre" aria-hidden="true">Le site</span>

            <a href="{{ route('kittens.index') }}" @if(request()->routeIs('kittens.*')) aria-current="page" @endif>Nos chatons</a>
            <a href="{{ route('cats.index') }}"    @if(request()->routeIs('cats.*'))    aria-current="page" @endif>Nos chats</a>
            <a href="{{ route('breed') }}"         @if(request()->routeIs('breed'))     aria-current="page" @endif>Le Maine Coon</a>
            <a href="{{ route('adoption.create') }}" @if(request()->routeIs('adoption.*')) aria-current="page" @endif>Adopter</a>
            <a href="{{ route('articles.index') }}" @if(request()->routeIs('articles.*')) aria-current="page" @endif>Articles</a>
            <a href="{{ route('gallery') }}"       @if(request()->routeIs('gallery'))   aria-current="page" @endif>Galerie</a>
            <a href="{{ route('faq') }}"           @if(request()->routeIs('faq'))       aria-current="page" @endif>Questions</a>
            <a href="{{ route('contact') }}"       @if(request()->routeIs('contact'))   aria-current="page" @endif>Contact</a>

            {{-- Dans le menu déplié, les icônes ne suffisent plus : on écrit le
                 numéro et l'adresse en toutes lettres, doigt oblige. Les deux
                 réseaux suivent le même sort — une pastille de 36 px au bas
                 d'un menu plein écran, personne ne la vise. --}}
            <span class="menu-titre" aria-hidden="true">Nous joindre</span>

            <a class="menu-tel" href="tel:{{ $telLien }}">{{ $tel }}</a>
            <a class="menu-tel" href="mailto:{{ $mail }}">{{ $mail }}</a>
            @if($insta)
                <a class="menu-tel" href="{{ $insta }}" target="_blank" rel="noopener">Instagram</a>
            @endif
            @if($fb)
                <a class="menu-tel" href="{{ $fb }}" target="_blank" rel="noopener">Facebook</a>
            @endif

            <x-fleuron taille="petit" class="menu-sceau" />
        </nav>

        {{-- Téléphone, courriel, Instagram, Facebook : quatre pictogrammes
             plutôt qu'un numéro écrit. Chacun garde son intitulé complet pour
             les lecteurs d'écran. --}}
        <div class="socials barre-socials">
            <x-social-link type="tel"  :url="'tel:'.$telLien" :handle="$tel" />
            <x-social-link type="mail" :url="'mailto:'.$mail" :handle="$mail" />
            @if($insta)
                <x-social-link type="instagram" :url="$insta" handle="chatteriedutempledesfees" />
            @endif
            @if($fb)
                <x-social-link type="facebook" :url="$fb" handle="Chatterie du Temple des Fées" />
            @endif
        </div>

        {{-- Le blason ferme le bandeau a droite : la marque ecrite d'un cote,
             l'embleme de l'autre. Il ne s'affiche que lorsque le fichier
             existe, ce qui evite un cadre vide en attendant. --}}
        <x-blason class="blason-bandeau" :taille="44" :lien="true" :urgent="true" />

        <button class="cle" id="cle" type="button" aria-expanded="false" aria-controls="menu">Menu</button>
    </div>

    {{-- Jauge de lecture : un filet d'or sur le bord bas du bandeau, rempli par
         la position de la page elle-même. Ornement pur, absent des navigateurs
         qui ne connaissent pas animation-timeline. --}}
    <span id="jauge" aria-hidden="true"></span>
</header>
