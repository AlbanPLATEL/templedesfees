@extends('layouts.app')

@section('title', $chaton->nom.' — chaton Maine Coon '.$chaton->statut->libelle())
@section('description', \Illuminate\Support\Str::limit(strip_tags($chaton->description), 150))
@section('og_image', asset($chaton->photo_principale))

@push('schema')
    <x-fil-ariane :etapes="[
        ['nom' => 'Accueil',     'url' => route('home')],
        ['nom' => 'Nos chatons', 'url' => route('kittens.index')],
        ['nom' => $chaton->nom,  'url' => route('kittens.show', $chaton)],
    ]" />
@endpush

@section('content')

<section class="bande">
    <div class="wrap">
        <div class="chapitre gauche monte" style="margin-bottom:clamp(26px,3.4vw,40px)">
            <span class="rubrique">
                <a href="{{ route('kittens.index') }}">← {{ $portee->code }}</a> · Fiche {{ $chaton->reference }}
            </span>
            <h1 style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
                {{ $chaton->nom }} <x-chip :statut="$chaton->statut" />
            </h1>
            <p class="lede">{{ $chaton->description }}</p>
        </div>

        <div class="duo-texte haut fiche-detail monte">
            <div class="pile" style="gap:14px">
                <x-photo-viewer :photos="$chaton->galerie()" />

                @if($fratrie->isNotEmpty())
                    <div>
                        <p class="rubrique" style="margin-bottom:12px">Ses frères et sœurs</p>
                        <div class="rail">
                            @foreach($fratrie as $frere)
                                <a class="vignette" style="opacity:1;width:clamp(64px,8vw,82px)"
                                   href="{{ route('kittens.show', $frere) }}"
                                   aria-label="{{ $frere->nom }}, frère ou sœur de {{ $chaton->nom }}">
                                    <img src="{{ asset($frere->photo_principale) }}"
                                         alt="{{ $frere->nom }}, frère ou sœur de {{ $chaton->nom }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="pile" style="gap:clamp(18px,2.4vw,26px)">

                <x-liasse>
                <x-record titre="Identité" meta="{{ $chaton->reference }}"
                          note="{{ $chaton->mentionsCompletes()
                                    ? 'Identification complète : numéro de portée LOOF et puce ICAD enregistrés.'
                                    : 'Identification en cours. Un chaton n’est pucé qu’à quelques semaines, et le numéro de portée revient du LOOF ensuite : les deux apparaissent ici dès qu’ils arrivent. Aucun départ ne se fait avant.' }}">
                    <table>
                        <tr><th>Sexe</th><td>{{ $chaton->sexeLibelle() }}</td></tr>
                        <tr><th>Date de naissance</th><td>{{ $portee->date_naissance?->translatedFormat('j F Y') ?? 'à compléter' }}</td></tr>
                        <tr><th>Âge</th><td>{{ $chaton->ageEnSemaines() ? $chaton->ageEnSemaines().' semaines' : 'à compléter' }}</td></tr>
                        <tr><th>Robe</th><td>{{ $chaton->robe }}</td></tr>
                        @if($chaton->poidsFormate())
                            <tr>
                                <th>Poids au dernier contrôle</th>
                                <td style="font-variant-numeric:tabular-nums">{{ $chaton->poidsFormate() }}</td>
                            </tr>
                        @endif
                        <tr><th>Parents</th><td>{{ $portee->pere?->nom }} × {{ $portee->mere?->nom }}</td></tr>
                        <tr>
                            <th>N° de portée LOOF</th>
                            <td><span @class(['verdict', 'attente' => blank($portee->loof_portee_numero)])>{{ $portee->loof_portee_numero ?: 'À compléter' }}</span></td>
                        </tr>
                        <tr>
                            <th>Identification ICAD</th>
                            <td><span @class(['verdict', 'attente' => blank($chaton->icad_numero)])>{{ $chaton->icad_numero ?: 'À compléter' }}</span></td>
                        </tr>
                        <tr>
                            <th>Disponible à partir du</th>
                            <td>{{ $portee->date_disponibilite?->translatedFormat('j F Y') }}</td>
                        </tr>
                    </table>
                </x-record>

                <x-record titre="Pedigree" meta="3 générations"
                          note="Les générations précédentes se reprennent du pedigree LOOF. Elles permettent d’afficher le taux de consanguinité de la portée.">
                    <div class="arbre">
                        @foreach([['Père', $portee->pere], ['Mère', $portee->mere]] as [$role, $parent])
                            <div class="gen">
                                @if($parent)
                                    <a class="case" href="{{ route('cats.show', $parent) }}">
                                        <em>{{ $role }}</em>
                                        <strong>{{ $parent->nom }}</strong>
                                        <span>{{ $parent->robe }}</span>
                                    </a>
                                @else
                                    <div class="case vide"><em>{{ $role }}</em><strong>À compléter</strong></div>
                                @endif
                                <div class="sous">
                                    <div class="case vide">
                                        <em>Grand-père {{ $role === 'Père' ? 'paternel' : 'maternel' }}</em>
                                        <strong>À compléter</strong>
                                    </div>
                                    <div class="case vide">
                                        <em>Grand-mère {{ $role === 'Père' ? 'paternelle' : 'maternelle' }}</em>
                                        <strong>À compléter</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-record>

                @if($portee->pere)<x-health-table :chat="$portee->pere" jalon="Santé du père" />@endif
                @if($portee->mere)<x-health-table :chat="$portee->mere" jalon="Santé de la mère" />@endif

                @if($portee->events->isNotEmpty())
                    <x-record titre="Suivi de la portée" meta="{{ $portee->code }}">
                        <div style="padding-top:18px"><x-timeline :events="$portee->events" /></div>
                    </x-record>
                @endif
                </x-liasse>

                @unless($chaton->estDisponible())
                    <div class="registre">
                        <span class="eq" aria-hidden="true"></span><span class="eq" aria-hidden="true"></span>
                        <span class="eq" aria-hidden="true"></span><span class="eq" aria-hidden="true"></span>
                        <p class="note" style="border-top:0;margin-top:0;padding-top:0">
                            @if($chaton->statut === \App\Enums\KittenStatus::Reserve)
                                {{ $chaton->nom }} est réservé. Vous pouvez demander à être prévenu en
                                priorité de la prochaine portée.
                            @else
                                {{ $chaton->nom }} a rejoint sa famille. Les fiches restent en ligne
                                pour retracer le travail de l’élevage.
                            @endif
                        </p>
                    </div>
                    <div class="btnrow">
                        <a class="btn creux" href="{{ route('kittens.index', ['statut' => 'disponible']) }}">Voir les chatons disponibles</a>
                    </div>
                @endunless
            </div>
        </div>
    </div>
</section>

<div class="bande serree">
    <x-photo-strip titre="La portée en images" />
</div>

@if($chaton->estDisponible())
<div class="barre-chaton">
    <div class="wrap in">
        <span class="nm">{{ $chaton->nom }}</span>
        <x-chip :statut="$chaton->statut" />
        <span class="petit">{{ $chaton->sexeLibelle() }} · {{ $chaton->robe }}</span>
        <a class="btn" href="{{ route('adoption.create', ['chaton' => $chaton->id]) }}">Pré-réserver {{ $chaton->nom }}</a>
    </div>
</div>
@endif

@endsection
