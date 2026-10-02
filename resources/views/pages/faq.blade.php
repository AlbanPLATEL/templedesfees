@extends('layouts.app')

@section('title', "Questions fréquentes sur l'adoption d'un Maine Coon")
@section('description', "Âge de départ, tarif, compatibilité avec les enfants et les chiens, vie en appartement, LOOF, allergies : les réponses complètes avant d'adopter un Maine Coon.")

@push('schema')
{{-- Le tableau est construit dans un bloc php ci-dessous, et non directement
     dans l'expression d'affichage. Blade compile la directive de contexte meme
     au milieu d'un tableau PHP : la cle arobase-context etait remplacee par du
     code compile, et le JSON-LD sortait inexploitable. Les blocs php sont mis
     de cote avant la compilation des directives, donc la cle y survit.
     Ne pas remettre ce tableau dans l'expression d'affichage.
     Et ne pas ecrire de directive Blade dans ce commentaire : elle serait
     compilee elle aussi. --}}
@php
    $schema = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($f) => [
        '@type' => 'Question',
        'name'  => $f->question,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f->reponse)],
    ])->values(),
];
@endphp
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')

<section class="bande">
    <div class="wrap">
        <x-section-head
            class="monte"
            niveau="1"
            eyebrow="Questions fréquentes"
            titre="Ce qu'on nous demande le plus"
            lede="Les réponses complètes, y compris celles qui pourraient vous faire renoncer. On préfère que vous renonciez avant qu'après." />

        <div class="duo-texte haut colonne-aside monte">
            <div>
                {{-- L'attribut name rend le groupe exclusif : ouvrir une
                     question referme la precedente, sans une ligne de script.
                     Un repli de secours existe dans app.js pour les
                     navigateurs qui ne connaissent pas encore ce comportement. --}}
                <div class="questions">
                    @foreach($faqs as $faq)
                        <details name="questions" @if($loop->first) open @endif>
                            <summary>{{ $faq->question }}</summary>
                            <div class="reponse">{!! $faq->reponse !!}</div>
                        </details>
                    @endforeach
                </div>
                <div class="btnrow" style="margin-top:40px">
                    <a class="btn" href="{{ route('adoption.create') }}">Poser une autre question</a>
                    <a class="btn creux" href="tel:+33677354587">{{ \App\Models\Setting::get('contact.telephone') }}</a>
                </div>
            </div>
            <aside class="aparte">
                <x-fleuron taille="petit" style="color:var(--or-mat)" />
                <h4>Votre question n'y est pas ?</h4>
                <p>Appelez-nous. On répond plus volontiers au téléphone qu'en trois lignes, surtout quand il s'agit de savoir si un Maine Coon est fait pour vous.</p>
                <a class="btn" href="tel:+33677354587" style="justify-content:center">{{ \App\Models\Setting::get('contact.telephone') }}</a>
                <a class="lien" href="{{ route('adoption.create') }}">Faire une demande d’adoption</a>
            </aside>
        </div>
    </div>
</section>

<div class="bande serree">
    <x-photo-strip titre="L'élevage en images" />
</div>

@endsection
