@extends('layouts.app')

@section('title', $chat->nom.' — '.$chat->role->libelle().' Maine Coon')
@section('description', \Illuminate\Support\Str::limit(strip_tags($chat->description), 150))
@section('og_image', asset($chat->photo_principale))

@push('schema')
    <x-fil-ariane :etapes="[
        ['nom' => 'Accueil',   'url' => route('home')],
        ['nom' => 'Nos chats', 'url' => route('cats.index')],
        ['nom' => $chat->nom,  'url' => route('cats.show', $chat)],
    ]" />
@endpush

@section('content')

<section class="bande">
    <div class="wrap">
        <div class="chapitre gauche monte" style="margin-bottom:clamp(26px,3.4vw,40px)">
            <span class="rubrique">
                <a href="{{ route('cats.index') }}">← Nos chats</a> · {{ $chat->role->libelle() }}
            </span>
            <h1>{{ $chat->nom }}</h1>
            <p class="lede">{{ $chat->description }}</p>
        </div>

        <div class="duo-texte haut monte">
            <div class="pile" style="gap:14px">
                <x-photo-viewer :photos="$chat->galerie()" />
            </div>

            <div class="pile" style="gap:clamp(18px,2.4vw,26px)">
                <x-liasse>
                <x-record titre="Identité" meta="{{ $chat->role->libelle() }}">
                    <table>
                        <tr><th>Sexe</th><td>{{ $chat->sexeLibelle() }}</td></tr>
                        <tr><th>Année de naissance</th><td>{{ $chat->annee_naissance }}</td></tr>
                        <tr><th>Robe</th><td>{{ $chat->robe }}</td></tr>
                        <tr>
                            <th>Pedigree LOOF</th>
                            <td>
                                <span @class(['verdict', 'attente' => blank($chat->loof_numero)])>{{ $chat->loof_numero ?: 'À compléter' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Identification</th>
                            <td>
                                <span @class(['verdict', 'attente' => blank($chat->icad_numero)])>{{ $chat->icad_numero ?: 'Non publiée' }}</span>
                                @unless($chat->icad_numero)
                                    <small>Le numéro de puce d’un reproducteur n’a pas à être public : l’obligation d’affichage porte sur les annonces de cession, donc sur les chatons.</small>
                                @endunless
                            </td>
                        </tr>
                        <tr>
                            <th>Mise à la reproduction</th>
                            <td>
                                @if($chat->role->peutReproduire())
                                    <span class="verdict">En reproduction</span>
                                @else
                                    <span class="verdict attente">Non — {{ \Illuminate\Support\Str::lower($chat->role->libelle()) }}</span>
                                    <small>Un Maine Coon met trois à quatre ans à finir de grandir. Aucune saillie avant que la croissance soit terminée et le bilan complet.</small>
                                @endif
                            </td>
                        </tr>
                    </table>
                </x-record>

                <x-health-table :chat="$chat" />

                @if($portees->isNotEmpty())
                    <x-record titre="Portées" meta="{{ $portees->count() }}">
                        <table>
                            @foreach($portees as $portee)
                                <tr>
                                    <th>{{ $portee->code }}</th>
                                    <td>
                                        <span class="verdict" style="font-size:18px">{{ $portee->kittens_count }} chaton{{ $portee->kittens_count > 1 ? 's' : '' }}</span>
                                        <small>{{ $portee->date_naissance?->translatedFormat('F Y') ?? '' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </x-record>
                @endif
                </x-liasse>

                <div class="btnrow">
                    <a class="btn creux" href="{{ route('kittens.index') }}">La portée en cours</a>
                    <a class="btn creux" href="{{ route('contact') }}">Poser une question</a>
                </div>
            </div>
        </div>
    </div>
</section>

@if($autres->isNotEmpty())
<section class="bande creuse">
    <div class="wrap">
        <x-section-head class="monte" eyebrow="La lignée" titre="Les autres chats de l’élevage" />
        <div class="fiches monte">
            @foreach($autres->take(4) as $autre)
                <x-cat-card :chat="$autre" />
            @endforeach
        </div>
        @if($autres->count() > 4)
            <div style="display:flex;justify-content:center;margin-top:clamp(28px,3.4vw,40px)">
                <a class="lien" href="{{ route('cats.index') }}">Les {{ $autres->count() + 1 }} chats de l’élevage</a>
            </div>
        @endif
    </div>
</section>
@endif

@endsection
