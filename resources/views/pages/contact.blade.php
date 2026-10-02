@extends('layouts.app')

@section('title', "Contact — écrivez-nous")
@section('description', "Écrivez-nous ou appelez la Chatterie du Temple des Fées à Lapeyrouse-Mornay (26), dans la Drôme des collines. Réponse sous 48 heures.")

@php
    $tel    = \App\Models\Setting::get('contact.telephone');
    $telRaw = \Illuminate\Support\Str::of($tel)->replace(' ', '')->replaceFirst('0', '+33');
    $mail   = \App\Models\Setting::get('contact.email');
    $insta  = \App\Models\Setting::get('contact.instagram');
    $fb     = \App\Models\Setting::get('contact.facebook');
    $tt     = \App\Models\Setting::get('contact.tiktok');
@endphp

@section('content')

<section class="bande">
    <div class="wrap">
        <x-section-head
            class="monte"
            niveau="1"
            eyebrow="Contact"
            titre="Écrivez-nous, ou appelez"
            lede="Un appel vaut souvent mieux qu'un long formulaire — nous décrochons en soirée et le week-end. Si vous préférez écrire, tout est ci-dessous : réponse sous 48 heures." />

        <div class="duo-texte decale" style="align-items:start">

            {{-- ---------------- formulaire ---------------- --}}
            <div id="formulaire">

                @if(session('succes'))
                    <p class="retour">{{ session('succes') }}</p>
                @endif

                @if($errors->any())
                    <div class="retour erreur">
                        Votre message n'a pas pu être envoyé :
                        <ul>
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(config('chatterie.apercu_statique'))
                    <x-note-apercu quoi="Ce formulaire" />
                @endif

                <form class="demo " method="POST" action="{{ route('contact.store') }}"
                      @if(config('chatterie.apercu_statique')) data-apercu @endif>
                    @csrf

                    {{-- Piege a robots : invisible pour un humain. --}}
                    <div style="position:absolute;left:-9999px" aria-hidden="true">
                        <label for="c-site">Site</label>
                        <input type="text" id="c-site" name="site" tabindex="-1" autocomplete="off">
                    </div>

                    <fieldset class="champ plein" style="border:0;padding:0;margin:0">
                        <legend style="padding:0;margin-bottom:11px"
                                class="mono" >Votre demande</legend>
                        <div class="objets">
                            @foreach($objets as $cle => $libelle)
                                <label for="c-objet-{{ $cle }}">
                                    <input type="radio" id="c-objet-{{ $cle }}" name="objet" value="{{ $cle }}"
                                           @checked(old('objet', 'adoption') === $cle)>
                                    <span>{{ $libelle }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="champ">
                        <label for="c-prenom">Prénom</label>
                        <input id="c-prenom" name="prenom" type="text" autocomplete="given-name"
                               value="{{ old('prenom') }}" required>
                    </div>
                    <div class="champ">
                        <label for="c-nom">Nom</label>
                        <input id="c-nom" name="nom" type="text" autocomplete="family-name" value="{{ old('nom') }}">
                    </div>
                    <div class="champ">
                        <label for="c-email">Email</label>
                        <input id="c-email" name="email" type="email" autocomplete="email"
                               value="{{ old('email') }}" required>
                    </div>
                    <div class="champ">
                        <label for="c-tel">Téléphone</label>
                        <input id="c-tel" name="telephone" type="tel" autocomplete="tel" value="{{ old('telephone') }}">
                    </div>

                    <div class="champ plein">
                        <label for="c-message">Votre message</label>
                        <textarea id="c-message" name="message" required
                                  placeholder="Dites-nous ce qui vous amène : un chaton en particulier, une question sur la race…">{{ old('message') }}</textarea>
                    </div>

                    <label class="consent" for="c-rgpd">
                        <input type="checkbox" id="c-rgpd" name="rgpd" value="1" @checked(old('rgpd'))>
                        <span>
                            J'accepte que La Chatterie du Temple des Fées conserve ces informations pour répondre à mon
                            message. Elles ne sont jamais transmises à un tiers et sont supprimées au bout
                            de {{ \App\Models\ContactMessage::MOIS_CONSERVATION }} mois.
                            <a href="{{ route('legal') }}">Politique de confidentialité</a>
                        </span>
                    </label>

                    <div class="full"><button class="btn" type="submit">Envoyer le message</button></div>
                </form>
            </div>

            {{-- ---------------- coordonnées ---------------- --}}
            <div class="pile" style="gap:20px">
                <x-record titre="Nous joindre directement">
                    <table>
                        <tr><th>Téléphone</th><td><a href="tel:{{ $telRaw }}" style="color:var(--bronze-lt);text-decoration:none">{{ $tel }}</a></td></tr>
                        <tr><th>Email</th><td><a href="mailto:{{ $mail }}" style="color:var(--bronze-lt);text-decoration:none">{{ $mail }}</a></td></tr>
                        <tr><th>Les chatons</th><td>Pas de visite avant le départ — pour les protéger tant qu’ils ne sont pas vaccinés</td></tr>
                        <tr><th>Réponse</th><td>Sous 48 heures maximum</td></tr>
                    </table>
                </x-record>

                {{-- Le meme ordre que dans le bandeau et le pied de page :
                     joindre d'abord, suivre ensuite. --}}
                <div class="socials">
                    <x-social-link type="tel" :url="'tel:'.$telRaw" :handle="$tel" />
                    <x-social-link type="mail" :url="'mailto:'.$mail" :handle="$mail" />
                    @if($insta)
                        <x-social-link type="instagram" :url="$insta" handle="chatteriedutempledesfees" />
                    @endif
                    @if($fb)
                        <x-social-link type="facebook" :url="$fb" handle="Chatterie du Temple des Fées" />
                    @endif
                    @if($tt)
                        <x-social-link type="tiktok" :url="$tt" handle="@templedesfees" />
                    @endif
                </div>

                <div class="btnrow">
                    <a class="btn" href="tel:{{ $telRaw }}">Appeler l'élevage</a>
                    <a class="btn creux" href="{{ route('adoption.create') }}">Faire une demande d’adoption</a>
                </div>

                <figure class="vue" style="margin:0">
                    <img src="{{ asset('images/cats/kora.webp') }}"
                         alt="Maine Coon de la Chatterie du Temple des Fées" loading="lazy">
                    <figcaption>Fin de journée à la maison</figcaption>
                </figure>
            </div>
        </div>
    </div>
</section>

{{-- ---------------- avis ---------------- --}}
{{--
    Temoignages de familles adoptantes, saisis dans le back-office ou deposes
    ici par les visiteurs. Trois regles tenues :
    - prenom seul, jamais de nom de famille, comme l'annonce la page Mentions
      legales — la table n'a d'ailleurs pas de colonne pour un nom ;
    - un avis depose arrive en attente et ne parait qu'apres relecture ;
    - pas de balisage schema.org Review : les regles de Google reservent les
      donnees structurees aux avis collectes par le site lui-meme, et un
      AggregateRating auto-declare se paie d'une penalite plutot que d'etoiles.
--}}
<section class="bande creuse serree" id="avis">
    <div class="wrap">
        <x-section-head
            class="monte"
            eyebrow="Ils sont passés par là"
            titre="Ce que disent les familles"
            lede="Témoignages de familles adoptantes, publiés avec leur accord. Prénom seul — aucun nom de famille n'est publié sur ce site." />

        @if($avis->isNotEmpty())
            <div class="cellules">
                @foreach($avis as $a)
                    <div class="cellule">
                        <span class="n">{{ $a->etoiles() }}</span>
                        <p>{{ $a->texte }}</p>
                        <span class="n" style="margin-top:auto">{{ $a->prenom }}@if($a->publie_le) · {{ $a->publie_le->translatedFormat('F Y') }}@endif</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if(session('succes_avis'))
            <p class="retour" style="margin-top:26px">{{ session('succes_avis') }}</p>
        @endif

        @if($errors->avis->any())
            <div class="retour erreur" style="margin-top:26px">
                Votre avis n'a pas pu être envoyé :
                <ul>
                    @foreach($errors->avis->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <details class="depot" @if(session('succes_avis') || $errors->avis->any()) open @endif>
            <summary>Vous avez adopté chez nous ? Laissez votre avis</summary>

            @if(config('chatterie.apercu_statique'))
                <x-note-apercu quoi="Ce depot d’avis" />
            @endif

            <form class="demande" method="POST" action="{{ route('reviews.store') }}"
                  @if(config('chatterie.apercu_statique')) data-apercu @endif>
                @csrf

                {{-- Piege a robots : invisible pour un humain. --}}
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label for="a-site">Site</label>
                    <input type="text" id="a-site" name="site" tabindex="-1" autocomplete="off">
                </div>

                <div class="champ">
                    <label for="a-prenom">Prénom</label>
                    <input id="a-prenom" name="prenom" type="text" autocomplete="given-name"
                           value="{{ old('prenom') }}" maxlength="80" required>
                </div>

                <div class="champ">
                    <label for="a-note">Note</label>
                    <select id="a-note" name="note" required>
                        @foreach([5 => '★★★★★', 4 => '★★★★☆', 3 => '★★★☆☆', 2 => '★★☆☆☆', 1 => '★☆☆☆☆'] as $v => $libelle)
                            <option value="{{ $v }}" @selected((int) old('note', 5) === $v)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="champ plein" style="grid-column:1/-1">
                    <label for="a-email">Email <span style="text-transform:none;letter-spacing:0">— facultatif, jamais publié</span></label>
                    <input id="a-email" name="email" type="email" autocomplete="email"
                           value="{{ old('email') }}" maxlength="150"
                           placeholder="Pour vous recontacter si nous avons une question">
                </div>

                <div class="champ plein" style="grid-column:1/-1">
                    <label for="a-texte">Votre avis</label>
                    <textarea id="a-texte" name="texte" maxlength="1500" required
                              placeholder="Votre expérience avec l'élevage : la préparation, les échanges, l'arrivée du chaton chez vous.">{{ old('texte') }}</textarea>
                </div>

                <label class="consent" for="a-rgpd">
                    <input type="checkbox" id="a-rgpd" name="rgpd" value="1" @checked(old('rgpd'))>
                    <span>
                        J'accepte que mon avis soit publié sur ce site sous mon prénom seul, et que
                        La Chatterie du Temple des Fées conserve mon adresse email si je l'ai renseignée, uniquement pour
                        me recontacter. Mon avis est relu avant publication.
                        <a href="{{ route('legal') }}">Politique de confidentialité</a>
                    </span>
                </label>

                <div class="btnrow" style="grid-column:1/-1">
                    <button class="btn" type="submit">Envoyer mon avis</button>
                </div>
            </form>
        </details>

        @if($avisGoogle = \App\Models\Setting::get('contact.avis_google'))
            <p style="margin-top:24px">
                <a class="lien" href="{{ $avisGoogle }}" target="_blank" rel="noopener noreferrer">
                    Voir tous les avis sur Google
                </a>
            </p>
        @endif
    </div>
</section>

{{-- ---------------- carte ---------------- --}}
<section class="bande creuse serree">
    <div class="wrap">
        <x-carte-situation class="monte" :itineraire="$itineraire" />
    </div>
</section>

@endsection
