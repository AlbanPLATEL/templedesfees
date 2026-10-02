@extends('layouts.app')

@section('title', \App\Models\Setting::get('elevage.nom', 'La Chatterie du Temple des Fées'))
@section('description', "Chatterie familiale de Maine Coon à Lapeyrouse-Mornay (26). Une à deux portées par an, parents dépistés HCM, SMA et PK-Def, résultats publiés sur chaque fiche.")

@push('schema')
{{--
    Fiche d'identite de l'elevage pour les moteurs. Elle compte double ici :
    le site actuel de la chatterie n'expose aucune donnee structuree, et ses
    concurrents directs en ont. Ces donnees sont le seul signal structure dont
    disposent les moteurs pour situer l'etablissement.

    Pas de Product ni d'Offer sur les fiches chaton : un resultat enrichi
    Product exige un prix, que ce site ne publie pas — et marquer un chaton
    comme un produit contredirait la page Adopter.
--}}
{{-- Le tableau est construit dans un bloc php, et non dans l'expression
     d'affichage : Blade compile la directive de contexte meme au milieu d'un
     tableau PHP, et la cle arobase-context sortirait remplacee par du code
     compile. Les blocs php sont mis de cote avant cette compilation. --}}
@php
    $schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'LocalBusiness',
    '@id'      => route('home').'#elevage',
    'name'     => \App\Models\Setting::get('elevage.nom', 'La Chatterie du Temple des Fées'),
    'description' => "Élevage familial de Maine Coon à Lapeyrouse-Mornay, dans la Drôme des collines.",
    'url'      => route('home'),
    'image'    => asset('images/cats/karrington.webp'),
    'telephone' => \App\Models\Setting::get('contact.telephone'),
    'email'     => \App\Models\Setting::get('contact.email'),
    'address'  => [
        '@type' => 'PostalAddress',
        'streetAddress'   => '24 chemin Saint-Charles',
        'addressLocality' => \App\Models\Setting::get('elevage.ville'),
        'postalCode'      => \App\Models\Setting::get('elevage.code_postal'),
        'addressRegion'   => \App\Models\Setting::get('elevage.departement'),
        'addressCountry'  => 'FR',
    ],
    'geo' => [
        '@type'     => 'GeoCoordinates',
        'latitude'  => config('chatterie.carte.zone.lat'),
        'longitude' => config('chatterie.carte.zone.lng'),
    ],
    'areaServed' => [
        ['@type' => 'AdministrativeArea', 'name' => 'Drôme'],
        ['@type' => 'AdministrativeArea', 'name' => 'Auvergne-Rhône-Alpes'],
    ],
    'sameAs' => array_values(array_filter([
        \App\Models\Setting::get('contact.instagram'),
    ])),
    'availableLanguage' => 'fr',
];
@endphp
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')

{{-- ═══ ouverture ═══ --}}
{{-- ═══ Olimpia, en tête de l'accueil ═══

     La troisième piste du seuil : rien ne s'ouvre par-dessus le site, elle est
     simplement le premier écran, et on descend. Aucune fenêtre à refermer,
     aucun geste demandé — c'est la différence, et c'est tout le sujet.

     Pour l'instant sur ?seuil=c seulement, le temps de comparer. --}}
@if(request('seuil') === 'c' && \App\Support\PhotosOlimpia::principale())
    @php($olimpia = \App\Support\PhotosOlimpia::principale())
    @php($olimpiaDates = \App\Models\Setting::get('hommage.dates'))

    <section class="olimpia-ouverture">
        <x-img class="fond" :src="$olimpia" alt="" sizes="100vw" :urgent="true" />
        <div class="voile" aria-hidden="true"></div>

        <div class="dit">
            <span class="rubrique">En mémoire</span>
            <h2>Olimpia</h2>
            <p class="complet">Olimpia Maryliss&nbsp;Country</p>
            @if($olimpiaDates)
                <p class="dates">{{ $olimpiaDates }}</p>
            @endif

            <div class="btnrow" style="margin-top:clamp(20px,3vh,30px)">
                <a class="btn creux" href="{{ route('hommage') }}">Lire son histoire</a>
            </div>
        </div>

        <a class="descendre" href="#la-maison">
            <span class="mot">Entrer sur le site</span>
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor"
                 stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14m0 0-6-6m6 6 6-6"/>
            </svg>
        </a>
    </section>
@endif

<section class="hero" id="la-maison">
    <div class="halo" aria-hidden="true"></div>

    <div class="frise"><x-fleuron taille="moyen" /></div>

    <div class="duo">
        <div class="portail arche">
            <i><u>
                {{-- L’arche a sa propre photo, qui ne dépend d’aucune fiche :
                     prendre celle d’un reproducteur revenait à ce qu’une
                     dépublication emporte la page d’accueil avec elle.

                     Elle passe par <x-img> comme les autres. Elle n’avait
                     aucun srcset et se téléchargeait en pleine taille jusque
                     dans un cadre de 322 px — c’est la plus grosse image de
                     la page, et la première que le visiteur attend. --}}
                <x-img src="images/cats/accueil.webp"
                       alt="Maine Coon de la Chatterie du Temple des Fées, installé à la maison"
                       sizes="(max-width:900px) 322px, 434px"
                       :largeur="800" :hauteur="1143" :urgent="true" />
            </u></i>
        </div>

        <div class="texte">
        <span class="rubrique">{{ \App\Models\Setting::get('elevage.nom', 'La Chatterie du Temple des Fées') }} · Lapeyrouse-Mornay (26)</span>
        <h1 class="or">
            <span class="leve"><span>Le sanctuaire</span></span>
            <span class="leve"><span><em>des géants doux</em></span></span>
        </h1>
        <p class="lede">
            Un élevage familial de Maine Coon dans la Drôme des collines. Une à deux portées
            par an, nées au milieu de la maison. Chaque parent dépisté, chaque résultat publié.
        </p>
        <div class="btnrow">
            <a class="btn" href="{{ route('kittens.index') }}">
                @if($nbDispo > 0) Les {{ $nbDispo }} chatons disponibles @else Voir la portée en cours @endif
            </a>
            <a class="btn creux" href="{{ route('adoption.create') }}">Le parcours d'adoption</a>
        </div>
        </div>
    </div>

    <p class="signature">À la maison · Hiver 2026</p>
</section>

{{-- ═══ le fil vivant ═══ --}}
@if($portee)
<div class="vivant">
    <div class="wrap in">
        <span class="pouls" aria-hidden="true"></span>
        <span class="rubrique" style="letter-spacing:.24em">{{ $portee->code }} · {{ $portee->pere?->nom }} × {{ $portee->mere?->nom }}</span>
        <span>
            <strong>{{ $nbDispo }} chaton{{ $nbDispo > 1 ? 's' : '' }} disponible{{ $nbDispo > 1 ? 's' : '' }}</strong>
            <span style="color:var(--ivoire-dim)">— né{{ $portee->nb_chatons > 1 ? 's' : '' }} le {{ $portee->date_naissance->translatedFormat('j F Y') }}@if($portee->phraseDisponibilite()), {{ $portee->phraseDisponibilite() }}@endif</span>
        </span>
        <a class="lien" href="{{ route('kittens.index') }}">Voir la portée</a>
    </div>
</div>
@endif

{{-- ═══ chapitre premier ═══ --}}
@if($chats->isNotEmpty())
<section class="bande">
    <div class="wrap">
        <div class="frise" style="margin-bottom:clamp(34px,5vw,54px)"><x-fleuron taille="grand" /></div>

        <div class="chapitre monte">
            <span class="numero">Chapitre premier</span>
            <h2>Ceux qui vivent ici</h2>
            <p class="lede">
                Onze Maine Coon, tous à la maison — pas en cage, pas en box. Les reproductrices
                mettent bas dans le salon, et les retraitées restent jusqu'au bout.
            </p>
        </div>

        <div class="retable">
            @foreach($chats->take(3) as $chat)
                <a class="portrait monte" href="{{ route('cats.show', $chat) }}">
                    <div class="arche petite">
                        <i><u>
                            <img src="{{ asset($chat->photo_principale) }}"
                                 alt="{{ $chat->nom }}, Maine Coon {{ \Illuminate\Support\Str::lower($chat->robe) }}"
                                 loading="lazy" width="900" height="1255">
                        </u></i>
                    </div>
                    <b>{{ $chat->nom }}</b>
                    <small>{{ $chat->robe }} · {{ $chat->role->libelle() }}</small>
                </a>
            @endforeach
        </div>

        @if($chats->count() > 3)
            <div style="display:flex;justify-content:center;margin-top:clamp(32px,4vw,48px)">
                <a class="lien" href="{{ route('cats.index') }}">Les {{ $chats->count() }} chats de l'élevage</a>
            </div>
        @endif
    </div>
</section>
@endif

{{-- ═══ chapitre second ═══ --}}
@if($vitrine)
<section class="bande creuse encadree">
    <div class="wrap">
        <div class="registre monte">
            <span class="eq" aria-hidden="true"></span><span class="eq" aria-hidden="true"></span>
            <span class="eq" aria-hidden="true"></span><span class="eq" aria-hidden="true"></span>

            <div style="display:flex;gap:clamp(24px,4vw,56px);align-items:flex-start;flex-wrap:wrap">
                <div style="width:min(380px,100%);display:flex;flex-direction:column;gap:16px">
                    <span class="numero" style="font-family:var(--pierre);font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:var(--or-mat)">Chapitre second</span>
                    <h2 style="font-size:clamp(1.9rem,3.6vw,3rem)">Le registre<br>de santé</h2>
                    <p style="font-size:15px;line-height:1.78;color:#B3AB99">
                        La cardiomyopathie hypertrophique est la maladie de la race. Elle se dépiste
                        de deux façons qui ne se remplacent pas : le test ADN cherche les mutations
                        connues, l'échographie regarde le cœur tel qu'il est aujourd'hui. Les deux
                        figurent ici, avec leur date.
                    </p>
                    <a class="lien" href="{{ route('cats.show', $vitrine) }}">La fiche complète de {{ $vitrine->nom }}</a>
                </div>

                <div style="flex:1 1 420px;min-width:0">
                    <div class="entete">
                        <b>{{ $vitrine->nom }}</b>
                        <span>{{ $vitrine->loof_numero ? 'Pedigree LOOF '.$vitrine->loof_numero : 'Pedigree LOOF à compléter' }}</span>
                    </div>
                    <table>
                        @foreach($vitrine->healthTests as $test)
                            <tr>
                                <th>{{ $test->type->libelle() }}</th>
                                <td>
                                    <span @class(['verdict', 'attente' => $test->estEnAttente()])>
                                        {{ $test->resultat ?: 'À programmer' }}
                                    </span>
                                    <small>
                                        @if($test->date_examen)
                                            {{ $test->laboratoire ? $test->laboratoire.' · ' : '' }}{{ $test->date_examen->translatedFormat('j F Y') }}
                                        @else
                                            {{ $test->type->methode() }}
                                        @endif
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <p class="note">
                        Un test ADN se fait une fois pour la vie : le génome ne change pas. Une
                        échocardiographie ne vaut que pour le jour où elle a été faite, et se
                        renouvelle tant que le chat reproduit. Une ligne encore vide s'affiche
                        telle quelle, en or — nous ne masquons pas ce qui manque.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══ chapitre troisième ═══ --}}
@if($chatons->isNotEmpty())
<section class="bande">
    <div class="wrap">
        <div class="chapitre monte">
            <span class="numero">Chapitre troisième</span>
            <h2>La portée en cours</h2>
            <p class="lede">
                Ils sont nés dans le salon, au bruit de la maison. Chaque fiche porte le numéro
                d'identification du chaton et le numéro de portée LOOF — sans eux, elle reste
                en brouillon et n'est jamais publiée.
            </p>
        </div>

        <div class="fiches defilante">
            @foreach($chatons as $chaton)
                <a class="fiche monte @if($chaton->statut === \App\Enums\KittenStatus::Adopte) partie @endif"
                   href="{{ route('kittens.show', $chaton) }}">
                    <div class="arche petite">
                        <span class="pastille {{ $chaton->statut->value }}">{{ $chaton->statut->libelle() }}</span>
                        <i><u>
                            <img src="{{ asset($chaton->photo_principale) }}"
                                 alt="{{ $chaton->nom }}, chaton Maine Coon {{ \Illuminate\Support\Str::lower($chaton->robe) }}"
                                 loading="lazy" width="900" height="1125">
                        </u></i>
                    </div>
                    <span class="bd">
                        <b>{{ $chaton->nom }}</b>
                        <small>{{ $chaton->robe }} · {{ \Illuminate\Support\Str::lower($chaton->sexeLibelle()) }}</small>
                    </span>
                </a>
            @endforeach
        </div>

        @if($chatons->count() > 1)
            <p class="souffle glisser">Faites glisser pour les voir tous.</p>
        @endif
    </div>
</section>
@endif

{{-- ═══ chapitre quatrième ═══ --}}
{{-- La bande de parchemin : une page claire au milieu du livre. C'est le
     chapitre du départ, celui qu'on lit en entier — il gagne à être posé sur
     du papier plutôt que dans la nuit. --}}
<section class="bande parchemin" id="le-depart">
    <div class="wrap">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(30px,5vw,68px);align-items:start">
            <div class="chapitre gauche monte" style="margin-bottom:0">
                <span class="numero">Chapitre quatrième</span>
                <h2>Le départ</h2>
                <p class="lede">
                    Un chaton ne se commande pas. Il part à douze semaines au plus tôt, identifié,
                    vacciné, vermifugé, avec son pedigree et son contrat. Entre le premier
                    message et le jour du départ, il se passe trois mois.
                </p>
                <div class="btnrow" style="margin-top:8px">
                    <a class="btn creux" href="{{ route('adoption.create') }}">Le parcours en détail</a>
                </div>
            </div>

            <ol class="chrono monte">
                <li class="faite">
                    <span class="pt" aria-hidden="true"></span>
                    <span><span class="quand">Premier contact</span>
                    <span class="quoi">Un message, puis un appel. Nous parlons de votre foyer, de vos horaires, de vos autres animaux. Il n'y a pas de mauvaise réponse, mais il y a des mauvais moments.</span></span>
                </li>
                <li class="faite">
                    <span class="pt" aria-hidden="true"></span>
                    <span><span class="quand">On apprend à se connaître</span>
                    <span class="quoi">Par téléphone, par photos, en visio si vous voulez. Les chatons ne reçoivent pas de visite : tant qu'ils ne sont pas vaccinés, ils n'ont aucune défense contre ce qu'on rapporte sous ses chaussures.</span></span>
                </li>
                <li class="encours">
                    <span class="pt" aria-hidden="true"></span>
                    <span><span class="quand">La réservation</span>
                    <span class="quoi">Un contrat écrit, un acompte, et le chaton vous est réservé. Vous recevez des photos toutes les semaines jusqu'au départ.</span></span>
                </li>
                <li>
                    <span class="pt" aria-hidden="true"></span>
                    <span><span class="quand">Douze semaines</span>
                    <span class="quoi">Identifié, primo-vacciné et rappelé, vermifugé, testé, pedigree LOOF en main. Pas un jour avant. C'est ce jour-là que vous le rencontrez, et que vous repartez avec lui.</span></span>
                </li>
                <li>
                    <span class="pt" aria-hidden="true"></span>
                    <span><span class="quand">Après</span>
                    <span class="quoi">Nous restons joignables. Un chat né ici qui ne peut plus rester chez vous revient ici, à n'importe quel âge.</span></span>
                </li>
            </ol>
        </div>
    </div>
</section>

{{-- ═══ le livre ═══ --}}
<section class="bande creuse" id="le-livre">
    <div class="wrap">
        <div class="chapitre monte">
            <span class="rubrique">Ce qui ne se négocie pas</span>
            <h2 class="sous-pinceau">
                <x-pinceau />
                Le livre de la maison
            </h2>
            <p class="lede">
                Six pages. Appuyez sur la page de droite pour tourner, sur celle de gauche
                pour revenir en arrière.
            </p>
        </div>

        <x-livre :pages="config('chatterie.livre')"
                 legende="Le livre de la maison"
                 class="monte" />
    </div>
</section>

{{-- ═══ envoi ═══ --}}
<section class="bande">
    <div class="wrap citation monte">
        <x-fleuron taille="petit" style="color:var(--or-mat)" />
        <p>Le prix d'un chaton ne paie pas l'animal. Il paie les neuf mois qui le précèdent.</p>
        <cite>La chatterie</cite>
    </div>
</section>

@endsection
