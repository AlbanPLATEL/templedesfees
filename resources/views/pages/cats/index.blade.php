@extends('layouts.app')

@section('title', "Nos chats — les reproducteurs de la chatterie")
@section('description', "Les onze Maine Coon de la Chatterie du Temple des Fées : robe, pedigree, rôle et résultats de dépistage HCM, SMA, PK-Def et dysplasie pour chacun.")

@section('content')

<section class="bande">
    <div class="wrap">
        <div class="frise" style="margin-bottom:clamp(30px,4vw,50px)"><x-fleuron taille="grand" /></div>

        <x-section-head
            class="monte"
            niveau="1"
            eyebrow="L'élevage"
            titre="Ceux qui vivent ici"
            lede="Onze Maine Coon, tous à la maison. Chaque fiche porte la robe, le pedigree et les résultats de dépistage, datés — y compris ceux qui manquent encore." />

        {{-- Les filtres passent par l'URL : une vue filtrée se partage et se met
             en favori, là où un filtre en JavaScript ne laisse aucune trace. --}}
        @php
            $lien = fn (array $p) => request()->fullUrlWithQuery($p);
        @endphp
        <nav class="filtres monte" aria-label="Filtrer les chats" data-filtre-barre>
            <a href="{{ route('cats.index') }}" @if(! $role && ! $sexe) aria-current="true" @endif>Tous ({{ $total }})</a>
            @foreach([\App\Enums\CatRole::Etalon, \App\Enums\CatRole::Reproductrice, \App\Enums\CatRole::Observation, \App\Enums\CatRole::Retraite] as $r)
                @if(($parRole[$r->value] ?? 0) > 0)
                    <a href="{{ $lien(['role' => $r->value, 'sexe' => null]) }}"
                       @if($role === $r) aria-current="true" @endif>
                        {{ $r->libelle() }} ({{ $parRole[$r->value] }})
                    </a>
                @endif
            @endforeach
            @foreach(['male' => 'Mâles', 'femelle' => 'Femelles'] as $valeur => $libelle)
                @if(($parSexe[$valeur] ?? 0) > 0)
                    <a href="{{ $lien(['sexe' => $valeur, 'role' => null]) }}"
                       @if($sexe === $valeur) aria-current="true" @endif>
                        {{ $libelle }} ({{ $parSexe[$valeur] }})
                    </a>
                @endif
            @endforeach
        </nav>

        @if($chats->isEmpty())
            <p class="lede monte" style="text-align:center;margin-inline:auto">
                Aucun chat ne correspond à ce filtre pour l'instant.
                <a class="lien" href="{{ route('cats.index') }}" style="margin-left:10px">Tout voir</a>
            </p>
        @else
            <div class="fiches monte" data-filtre="role sexe"
                 data-filtre-vide="Aucun chat ne correspond à ce filtre.">
                @foreach($chats as $chat)
                    <x-cat-card :chat="$chat" />
                @endforeach
            </div>
        @endif
    </div>
</section>

<figure class="bande-photo" style="--h:42vh">
    <img src="{{ asset('images/cats/boonie.webp') }}"
         alt="Arbre à chat devant la fenêtre, à la chatterie" loading="lazy">
    <span class="voile" aria-hidden="true"></span>
    <figcaption>Une à deux portées par an, pas davantage</figcaption>
</figure>

<section class="bande creuse">
    <div class="wrap">
        <div class="duo-texte monte">
            <div class="pile">
                <span class="rubrique">Notre façon de faire</span>
                <h2>Peu de portées,<br>et jamais sur commande</h2>
                <p class="lede lettrine">
                    Le rythme est calé sur la récupération des femelles, jamais sur la demande.
                    Une chatte ne porte pas deux fois dans l'année parce qu'il y a une liste
                    d'attente.
                </p>
                <p class="lede">
                    Entre deux portées, la maison redevient une maison. C'est ce qui explique
                    que nos chatons arrivent chez vous déjà propres, habitués au bruit, à
                    l'aspirateur et aux allées et venues : ils n'ont jamais connu autre chose
                    qu'un salon.
                </p>
                <p class="lede">
                    Les retraitées restent ici jusqu'au bout. Un chat né à la chatterie qui ne
                    peut plus rester dans sa famille y revient aussi, à n'importe quel âge.
                </p>
            </div>

            <div class="faits">
                <div class="fait"><b data-compte="{{ $total }}">0</b><span>chats à l'élevage</span></div>
                <div class="fait"><b data-compte="2">0</b><span>portées par an au maximum</span></div>
                <div class="fait"><b data-compte="{{ \App\Models\Litter::SEMAINES_AVANT_CESSION }}">0</b><span>semaines avant le départ</span></div>
                <div class="fait"><b data-compte="6">0</b><span>dépistages par reproducteur</span></div>
            </div>
        </div>

        {{-- Olimpia. Elle n'est ni dans la grille des reproducteurs ni dans le
             menu : elle n'est plus de l'élevage, elle en est l'origine. Un seul
             bloc, en fin de page, qui ne crie pas. --}}
        <a class="entree-hommage monte" href="{{ route('hommage') }}">
            <span class="portrait">
                <x-img :src="\App\Support\PhotosOlimpia::principale()" alt="Olimpia" sizes="104px" />
            </span>
            <span class="dit">
                <span class="rubrique">En mémoire</span>
                <b>Olimpia</b>
                <p>
                    Elle a brillé sur les podiums, elle arrivait en courant à un seul mot,
                    et elle a laissé une fille qui lui ressemble en tout. Son histoire est ici.
                </p>
            </span>
        </a>

        <div class="btnrow monte" style="justify-content:center;margin-top:clamp(30px,4vw,44px)">
            <a class="btn" href="{{ route('kittens.index') }}">Voir les chatons</a>
            <a class="btn creux" href="{{ route('contact') }}">Nous écrire</a>
        </div>
    </div>
</section>

@endsection
