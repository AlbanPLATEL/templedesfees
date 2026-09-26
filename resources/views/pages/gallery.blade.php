@extends('layouts.app')

@section('title', "Galerie — la vie à l'élevage")
@section('description', "Photos des Maine Coon de l'élevage : chatons, adultes et vie quotidienne à la maison, prises au fil des mois.")

@section('content')

@php
    $vues = $photos->when(request('categorie'), fn ($c) => $c->where('categorie', request('categorie')))->values();

    // Les planches d'un ouvrage se numérotent en chiffres romains. Au-delà de
    // douze on repasse en chiffres arabes : XIII sur une vignette ne se lit plus.
    $romain = fn (int $n) => ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$n - 1] ?? (string) $n;
@endphp

<section class="bande">
    <div class="wrap">
        <x-section-head
            class="monte"
            niveau="1"
            eyebrow="Galerie"
            titre="La planche de portraits"
            lede="Des photos prises à la maison au fil des mois, pas une séance de studio. Chaque cliché porte le nom du chat et sa robe. Cliquez pour l’agrandir." />

        {{-- Un seul intitulé ne se filtre pas : la barre ne s'affiche qu'à
             partir de deux catégories, sinon elle propose de choisir entre
             tout et tout. --}}
        @if($categories->count() > 1)
            <nav class="filtres monte" aria-label="Filtrer les photos" data-filtre-barre>
                <a href="{{ route('gallery') }}"
                   @if(! request('categorie')) aria-current="true" @endif>Tout ({{ $photos->count() }})</a>
                @foreach($categories as $categorie)
                    <a href="{{ route('gallery', ['categorie' => $categorie]) }}"
                       @if(request('categorie') === $categorie) aria-current="true" @endif>
                        {{ \Illuminate\Support\Str::ucfirst($categorie) }} ({{ $photos->where('categorie', $categorie)->count() }})
                    </a>
                @endforeach
            </nav>
        @endif

        @if($vues->isEmpty())
            <p class="lede monte" style="text-align:center">
                Aucune photo dans cette catégorie pour le moment.
            </p>
        @else
            <div class="planche-tete monte">
                <x-fleuron taille="petit" style="color:var(--or-mat)" />
                <p class="petit">
                    {{ $vues->count() }} clichés · La Chatterie du Temple des Fées · Lapeyrouse-Mornay
                </p>
            </div>

            {{-- data-lightbox et data-full sont le contrat de la visionneuse :
                 elle lit la liste sur le conteneur et la source sur chaque
                 figure. Ne pas les retirer en changeant la mise en page. --}}
            <div class="planche-photos monte" data-lightbox data-filtre="categorie"
                 data-filtre-vide="Aucune photo dans cette catégorie pour le moment.">
                @foreach($vues as $i => $photo)
                    @php
                        // La légende du seeder tient sur « Nom, robe » : on la
                        // coupe pour composer le nom et la robe séparément.
                        [$nom, $robe] = array_pad(explode(',', $photo->legende ?? '', 2), 2, null);
                    @endphp

                    <figure class="cliche" tabindex="0" role="button"
                            data-categorie="{{ $photo->categorie }}"
                            aria-label="Agrandir : {{ $photo->legende ?: $photo->alt }}"
                            data-full="{{ asset($photo->chemin) }}"
                            data-legende="{{ $photo->legende }}">
                        <div class="vitre">
                            <x-img :src="$photo->chemin" :alt="$photo->alt"
                                   sizes="(max-width:560px) 88vw, (max-width:900px) 44vw, 30vw"
                                   :urgent="$i < 3" />
                            <span class="planche-numero" aria-hidden="true">{{ $romain($i + 1) }}</span>
                        </div>

                        <figcaption>
                            <b>{{ trim($nom ?: $photo->alt) }}</b>
                            @if($robe)<span>{{ trim($robe) }}</span>@endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
