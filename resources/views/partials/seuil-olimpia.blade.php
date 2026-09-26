{{--
    Le seuil : Olimpia, avant d'entrer.

    TROIS PISTES, le temps de choisir. Elles ne se ressemblent pas : les trois
    premières versions étaient la même idée retouchée, et c'est sans doute pour
    ça qu'aucune n'allait.

        ?seuil=a   LE MOT        — presque tout noir. « le poulette » en or,
                                   énorme, et rien d'autre. Elle n'apparaît
                                   qu'en filigrane, très sombre.
        ?seuil=b   LE PLEIN CADRE — celui d'aujourd'hui : sa photo prend tout
                                   l'écran, le texte tient la moitié gauche.
        ?seuil=c   PAS DE VOILE   — rien ne s'ouvre par-dessus le site. Elle
                                   devient le premier écran de l'accueil, et
                                   on descend dans le site normalement.

    La troisième n'est pas une variante de mise en page : c'est la question de
    savoir si ce qui gêne n'est pas le fait même qu'une fenêtre s'ouvre.

    Trois façons de sortir des deux premières : la croix, le bouton « Entrer
    sur le site », et la touche Échap. Aucune n'est cachée.

    Le seuil ne paraît ni sur la page d'Olimpia — on n'annonce pas à quelqu'un
    ce qu'il est déjà en train de lire — ni sur les pages de réservation : une
    famille en train de verser un acompte n'a pas à voir surgir autre chose.
--}}
@php
    use App\Models\Setting;
    use App\Support\PhotosOlimpia;

    $seuilTexte = Setting::get('hommage.seuil');
    $seuilDates = Setting::get('hommage.dates');
    $seuilMot   = Setting::get('hommage.mot');

    $photos     = PhotosOlimpia::toutes();
    $principale = PhotosOlimpia::principale();

    $forme = in_array(request('seuil'), ['a', 'b', 'c'], true) ? request('seuil') : 'b';
@endphp

{{-- La forme « c » ne pose rien par-dessus le site : elle vit dans la page
     d'accueil, pas ici. --}}
@unless($forme === 'c' || ! $principale
        || request()->routeIs('hommage') || request()->routeIs('reservation.*'))

    <div class="seuil-olimpia seuil--{{ $forme }}" id="seuil-olimpia" hidden
         role="dialog" aria-modal="true" aria-labelledby="seuil-olimpia-nom">

        @if($forme === 'b')
            <div class="fonds" aria-hidden="true">
                @foreach($photos as $i => $photo)
                    <x-img :class="$i === 0 ? 'fond vue' : 'fond'" :src="$photo" alt=""
                           sizes="100vw" :urgent="$i === 0" :differe="$i > 0" />
                @endforeach
            </div>
            <span class="fondu" aria-hidden="true"></span>
            <span class="grain" aria-hidden="true"></span>
        @else
            {{-- ═══ A — le mot ═══
                 Elle est là, mais à peine : le noir a presque tout mangé. Ce
                 qu'on voit, c'est le mot. --}}
            <x-img class="ombre" :src="$principale" alt="" sizes="100vw" :urgent="true" />
            <span class="fondu" aria-hidden="true"></span>
        @endif

        <div class="feuille">

            <span class="maison">
                @if(file_exists(public_path('images/blason-192.png')))
                    <img src="{{ asset('images/blason-192.png') }}" alt="" width="28" height="28">
                @endif
                <b>{{ \App\Models\Setting::get('elevage.nom', 'La Chatterie du Temple des Fées') }}</b>
            </span>

            <button class="fermer" type="button" aria-label="Fermer et entrer sur le site">
                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor"
                     stroke-width="1.3" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>

            <div class="dedans">

                @if($forme === 'a')
                    <span class="rubrique">En mémoire d’Olimpia</span>

                    <p class="avant">Il suffisait d’un mot.</p>

                    <p class="le-mot" id="seuil-olimpia-nom">«&nbsp;{{ $seuilMot }}&nbsp;»</p>

                    <p class="apres">Et elle arrivait en courant.</p>
                @else
                    <span class="rubrique">En mémoire</span>

                    <h2 class="nom" id="seuil-olimpia-nom">
                        <span class="sous-pinceau">Olimpia<x-pinceau /></span>
                    </h2>

                    <p class="complet">Olimpia Maryliss&nbsp;Country</p>

                    @if($seuilDates)
                        <p class="dates">{{ $seuilDates }}</p>
                    @endif

                    @if(filled($seuilTexte))
                        <x-texte-riche class="mots" :texte="$seuilTexte" />
                    @endif
                @endif

                <div class="pied">
                    <button class="btn entrer" type="button">
                        Entrer sur le site
                        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor"
                             stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14m0 0-6-6m6 6-6 6"/>
                        </svg>
                    </button>

                    <div class="menus">
                        <a class="lire" href="{{ route('hommage') }}">Lire son histoire</a>
                        <span class="sep" aria-hidden="true">·</span>
                        <label class="plus">
                            <input type="checkbox" id="seuil-olimpia-jamais">
                            <span>Ne plus afficher</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endunless
