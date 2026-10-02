@extends('layouts.app')

@section('title', "Adopter un chaton Maine Coon — parcours et pré-réservation")
@section('description', "Le parcours d'adoption à la Chatterie du Temple des Fées en quatre étapes, pourquoi les chatons ne reçoivent pas de visite, et le formulaire de pré-réservation.")

@section('content')

<section class="bande">
    <div class="wrap">
        <x-section-head
            class="monte"
            niveau="1"
            eyebrow="Adopter"
            titre="Le parcours d'adoption"
            lede="Quatre étapes, aucune surprise. La demande ne vous engage à rien : elle ouvre la discussion." />

        <div class="cellules">
            <div class="cellule"><span class="n">ÉTAPE 01</span><h3>Vous nous écrivez</h3><p>Le formulaire plus bas, ou un appel. Parlez-nous de votre foyer, de vos autres animaux, de votre rythme de vie. Réponse sous 48 heures.</p></div>
            <div class="cellule"><span class="n">ÉTAPE 02</span><h3>On apprend à se connaître</h3><p>Photos, nouvelles, appels, visio si vous le souhaitez. <strong>Les chatons ne reçoivent pas de visite</strong> : tant qu'ils ne sont pas vaccinés, ce qu'un visiteur rapporte sous ses chaussures peut les emporter. Nous ne faisons pas d'exception.</p></div>
            <div class="cellule"><span class="n">ÉTAPE 03</span><h3>Réservation et contrat</h3><p>Nous vous envoyons le contrat de réservation et un lien privé qui récapitule tout : le chaton, le montant de l’acompte et la façon de le verser. Dès qu'il est réglé, le chaton est retiré de la vente — et vous recevez des nouvelles chaque semaine jusqu'au départ.</p></div>
            <div class="cellule"><span class="n">ÉTAPE 04</span><h3>Le grand jour</h3><p>C'est ce jour-là que vous le rencontrez. À {{ \App\Models\Litter::SEMAINES_AVANT_CESSION }} semaines minimum : pedigree LOOF, carnet de santé, certificat vétérinaire, puce ICAD, contrat et kit d'alimentation.</p></div>
        </div>
    </div>
</section>

<section class="bande" id="couverture">
    <div class="wrap">
        <x-section-head
            class="monte"
            eyebrow="La question du tarif"
            titre="Ce que couvre l'adoption"
            lede="Il faut le dire clairement, parce que c'est la question qui fâche : ce que vous réglez ne paie pas le chat. Voici, ligne par ligne, ce qu'il y a derrière un chaton qui arrive chez vous." />

        <div class="releve">
            @foreach(config('chatterie.couverture') as $ligne)
                <div class="ligne">
                    <span class="mk">{{ $ligne['numero'] }}</span>
                    <span class="ttl">{{ $ligne['titre'] }}<small>{{ $ligne['detail'] }}</small></span>
                    <span class="quand">{{ $ligne['quand'] }}</span>
                </div>
            @endforeach
            <div class="foot">
                <p class="citation-texte">« Derrière chaque chaton, il y a plusieurs mois de présence et de travail. »</p>
                <p class="lede">
                    C'est cette prise en charge globale qui représente un coût — bien davantage que le
                    chat lui-même. Si le budget est ce qui vous retient, parlez-nous-en : on trouve
                    souvent une solution, notamment en échelonnant.
                </p>
            </div>
        </div>
    </div>
</section>

<x-photo-band image="images/cats/helios.webp"
              legende="Douze semaines ensemble avant le grand départ"
              hauteur="42vh" />

<section class="bande">
    <div class="wrap">
        <x-section-head
            class="monte"
            eyebrow="Demande de pré-réservation"
            titre="Parlez-nous de votre foyer"
            lede="Plus vous nous en dites, plus nous pourrons vous orienter vers le chaton qui vous correspond vraiment. Réponse sous 48 heures." />

        <div style="max-width:880px">

            @if(session('succes'))
                <div class="record" style="margin-bottom:26px">
                    <p class="note" style="border-top:0;color:var(--ok)">{{ session('succes') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="record" style="margin-bottom:26px">
                    <div class="hd"><span class="mono">Demande incomplète</span></div>
                    <p class="note" style="border-top:0">
                        @foreach($errors->all() as $erreur)
                            {{ $erreur }}<br>
                        @endforeach
                    </p>
                </div>
            @endif

            @if(config('chatterie.apercu_statique'))
                <x-note-apercu quoi="Ce formulaire" />
            @endif

            <form class="demande" method="POST" action="{{ route('adoption.store') }}"
                  @if(config('chatterie.apercu_statique')) data-apercu @endif>
                @csrf

                {{-- Piege a robots : invisible pour un humain, rempli par les bots. --}}
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label for="f-site">Site</label>
                    <input type="text" id="f-site" name="site" tabindex="-1" autocomplete="off">
                </div>

                <div class="champ"><label for="f-prenom">Prénom</label>
                    <input id="f-prenom" name="prenom" type="text" autocomplete="given-name" value="{{ old('prenom') }}" required></div>
                <div class="champ"><label for="f-nom">Nom</label>
                    <input id="f-nom" name="nom" type="text" autocomplete="family-name" value="{{ old('nom') }}"></div>
                <div class="champ"><label for="f-email">Email</label>
                    <input id="f-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required></div>
                <div class="champ"><label for="f-tel">Téléphone</label>
                    <input id="f-tel" name="telephone" type="tel" autocomplete="tel" value="{{ old('telephone') }}"></div>
                <div class="champ"><label for="f-cp">Code postal</label>
                    <input id="f-cp" name="code_postal" type="text" inputmode="numeric" autocomplete="postal-code" value="{{ old('code_postal') }}"></div>

                <div class="champ"><label for="f-chaton">Chaton souhaité</label>
                    <select id="f-chaton" name="kitten_id">
                        <option value="">Sans préférence</option>
                        @foreach($disponibles as $chaton)
                            <option value="{{ $chaton->id }}"
                                @selected(old('kitten_id', request('chaton')) == $chaton->id)>
                                {{ $chaton->nom }} — {{ $chaton->sexeLibelle() }} — {{ $chaton->robe }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="champ"><label for="f-logement">Votre logement</label>
                    <select id="f-logement" name="logement">
                        @foreach(['Appartement', 'Maison avec jardin', 'Maison sans jardin'] as $choix)
                            <option @selected(old('logement') === $choix)>{{ $choix }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="champ"><label for="f-animaux">Autres animaux</label>
                    <select id="f-animaux" name="autres_animaux">
                        @foreach(['Aucun', 'Un chat', 'Plusieurs chats', 'Un chien', 'Chien(s) et chat(s)'] as $choix)
                            <option @selected(old('autres_animaux') === $choix)>{{ $choix }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="champ"><label for="f-presence">Présence à la maison</label>
                    <select id="f-presence" name="presence">
                        @foreach(['Quelqu\'un est là la journée', 'Absent la journée en semaine', 'Télétravail partiel'] as $choix)
                            <option @selected(old('presence') === $choix)>{{ $choix }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="champ"><label for="f-exp">Expérience avec les chats</label>
                    <select id="f-exp" name="experience">
                        @foreach(['Premier chat', 'J\'ai déjà eu des chats', 'J\'ai déjà eu un Maine Coon'] as $choix)
                            <option @selected(old('experience') === $choix)>{{ $choix }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="champ plein"><label for="f-msg">Votre message</label>
                    <textarea id="f-msg" name="message" placeholder="Parlez-nous de votre rythme de vie, de ce que vous attendez d'un Maine Coon, de vos questions…">{{ old('message') }}</textarea></div>

                <label class="consent" for="f-rgpd">
                    <input type="checkbox" id="f-rgpd" name="rgpd" value="1" @checked(old('rgpd'))>
                    <span>
                        J'accepte que La Chatterie du Temple des Fées conserve ces informations pour traiter ma demande
                        d'adoption. Elles ne sont jamais transmises à un tiers et sont supprimées au bout
                        de {{ \App\Models\AdoptionRequest::MOIS_CONSERVATION }} mois.
                        <a href="{{ route('legal') }}" style="color:var(--bronze-dim)">Politique de confidentialité</a>
                    </span>
                </label>

                <div class="full"><button class="btn" type="submit">Envoyer ma demande</button></div>
            </form>
        </div>
    </div>
</section>

@endsection
