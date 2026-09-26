@extends('layouts.app')

@section('title', $article->titre)
@section('description', \Illuminate\Support\Str::limit(strip_tags($article->chapeau), 155))
@section('og_image', asset($article->photo_principale ?: 'images/cats/uriana.webp'))

@push('head')
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="{{ $article->date_publication->toIso8601String() }}">
@endpush

@push('schema')
    <x-fil-ariane :etapes="[
        ['nom' => 'Accueil',  'url' => route('home')],
        ['nom' => 'Articles', 'url' => route('articles.index')],
        ['nom' => $article->titre, 'url' => route('articles.show', $article)],
    ]" />

{{-- Le tableau est construit dans un bloc php, et non dans l'expression
     d'affichage : Blade compile la directive de contexte meme au milieu d'un
     tableau PHP, et la cle arobase-context sortirait remplacee par du code
     compile. --}}
@php
    $schema = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $article->titre,
    'description'   => $article->chapeau,
    'datePublished' => $article->date_publication->toIso8601String(),
    'dateModified'  => $article->updated_at?->toIso8601String(),
    'image'         => asset($article->photo_principale ?: 'images/cats/uriana.webp'),
    'mainEntityOfPage' => route('articles.show', $article),
    'author' => [
        '@type' => 'Organization',
        'name'  => \App\Models\Setting::get('elevage.nom', 'La Chatterie du Temple des Fées'),
        'url'   => route('home'),
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name'  => \App\Models\Setting::get('elevage.nom', 'La Chatterie du Temple des Fées'),
        'url'   => route('home'),
    ],
];
@endphp
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')

<section class="bande">
    <div class="wrap">
        <div class="chapitre monte" style="max-width:780px;margin-inline:auto">
            <span class="rubrique">
                <a href="{{ route('articles.index') }}">← Articles</a>
                @if($article->categorie) · {{ $article->categorie }} @endif
            </span>
            <h1>{{ $article->titre }}</h1>
            <p class="lede">{{ $article->chapeau }}</p>
            <span class="signe">
                {{ $article->date_publication->translatedFormat('j F Y') }}
                · {{ $article->minutesDeLecture() }} min de lecture
            </span>
        </div>

        @if($article->photo_principale)
            <figure class="vue monte" style="max-width:900px;margin-inline:auto">
                <x-img :src="$article->photo_principale" :alt="$article->titre"
                       sizes="(max-width:960px) 92vw, 900px"
                       :largeur="1200" :hauteur="1714" />
            </figure>
        @endif

        <div class="texte-long monte">
            {!! $article->corpsEnHtml() !!}
        </div>
    </div>
</section>

@if($autres->isNotEmpty())
<section class="bande creuse">
    <div class="wrap">
        <x-section-head class="monte" eyebrow="À lire aussi" titre="Les autres articles" />
        <div class="articles monte">
            @foreach($autres as $autre)
                <article class="billet">
                    <a class="arche petite" href="{{ route('articles.show', $autre) }}" tabindex="-1" aria-hidden="true">
                        <i><u>
                            <x-img :src="$autre->photo_principale ?: 'images/cats/uriana.webp'"
                                   alt="" sizes="200px" :largeur="1200" :hauteur="1714" />
                        </u></i>
                    </a>
                    <div class="bd">
                        @if($autre->categorie)<span class="rubrique">{{ $autre->categorie }}</span>@endif
                        <h2><a href="{{ route('articles.show', $autre) }}">{{ $autre->titre }}</a></h2>
                        <p>{{ $autre->chapeau }}</p>
                        <span class="signe">{{ $autre->date_publication->translatedFormat('j F Y') }}</span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
