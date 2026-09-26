@extends('layouts.app')

@section('title', "Mentions légales et protection des données")
@section('description', "Mentions légales de la Chatterie du Temple des Fées, conditions de cession des chatons et traitement des données personnelles.")

@section('content')

<section class="bande">
    <div class="wrap" style="max-width:840px">
        <x-section-head
            class="monte"
            niveau="1"
            eyebrow="Informations légales"
            titre="Mentions légales &amp; confidentialité"
            lede="Les informations que tout site d'élevage doit publier. Les champs marqués « à compléter » attendent les numéros officiels." />

        <div class="pile monte" style="gap:clamp(20px,2.6vw,28px)">

            <x-record titre="Éditeur du site">
                <table>
                    <tr><th>Dénomination</th><td>{{ \App\Models\Setting::get('elevage.nom') }}</td></tr>
                    <tr><th>Adresse</th><td>{{ \App\Models\Setting::get('elevage.ville') }} ({{ \App\Models\Setting::get('elevage.code_postal') }}), {{ \App\Models\Setting::get('elevage.departement') }}</td></tr>
                    <tr><th>SIREN / SIRET</th><td><x-legal-value cle="legal.siren" /></td></tr>
                    <tr><th>ACACED / certificat de capacité</th><td><x-legal-value cle="legal.certificat" /></td></tr>
                    <tr><th>Enregistrement</th><td>Chambre d'agriculture</td></tr>
                    <tr><th>Directeur de la publication</th><td><x-legal-value cle="legal.directeur" /></td></tr>
                    <tr><th>Hébergeur</th><td><x-legal-value cle="legal.hebergeur" /></td></tr>
                </table>
            </x-record>

            <x-record titre="Cession de chatons"
                      note="Chaque annonce de cession affiche le numéro d'identification du chaton, le numéro de portée LOOF, l'âge et le nombre d'animaux de la portée, conformément à la réglementation applicable aux cessions d'animaux de compagnie.">
                <table>
                    <tr><th>Âge minimum</th><td>{{ \App\Models\Litter::SEMAINES_AVANT_CESSION }} semaines révolues</td></tr>
                    <tr><th>Identification</th><td>Puce électronique enregistrée à l'ICAD avant toute cession</td></tr>
                    <tr><th>Inscription</th><td>LOOF — pedigree remis à la famille, jamais en option</td></tr>
                    <tr><th>Certificat d'engagement</th><td>Remis et signé au minimum 7 jours avant la cession. Ce délai de réflexion est incompressible : aucun chaton ne part avant son terme.</td></tr>
                    <tr><th>Documents remis</th><td>Certificat vétérinaire de bonne santé, carnet de vaccination, contrat de cession, document d'information sur les besoins de l'espèce</td></tr>
                    <tr><th>Reprise</th><td>Prévue au contrat, sans limite d'âge</td></tr>
                </table>
            </x-record>

            @php($acompte = \App\Models\Setting::get('legal.acompte'))

            @if(filled($acompte))
                {{-- Les conditions de l'acompte figurent aussi sur la page de
                     reservation, juste au-dessus de la case a cocher. Ici
                     elles sont consultables par n'importe qui, avant meme
                     d'avoir recu un lien. --}}
                <x-record titre="L’acompte et la réservation">
                    <x-texte-riche :texte="$acompte" />
                </x-record>
            @endif

            <x-record titre="Données personnelles"
                      note="Les statuts « réservé » et « adopté » sont affichés sans aucune donnée nominative sur la famille concernée. Les témoignages ne sont publiés qu'avec accord écrit, sous le prénom seul.">
                <table>
                    <tr><th>Données collectées</th><td>Identité, coordonnées et informations sur le foyer, uniquement via le formulaire de pré-réservation</td></tr>
                    <tr><th>Finalité</th><td>Traiter la demande d'adoption et assurer le suivi du chaton</td></tr>
                    <tr><th>Base légale</th><td>Consentement, recueilli explicitement au dépôt de la demande</td></tr>
                    <tr><th>Durée de conservation</th><td>{{ \App\Models\AdoptionRequest::MOIS_CONSERVATION }} mois après le dépôt de la demande, puis suppression automatique</td></tr>
                    <tr><th>Destinataires</th><td>La Chatterie du Temple des Fées uniquement — aucune transmission à un tiers, aucune revente</td></tr>
                    <tr><th>Vos droits</th><td>Accès, rectification, effacement et opposition sur simple demande à {{ \App\Models\Setting::get('contact.email') }}</td></tr>
                    <tr><th>Publication des noms</th><td>Aucun nom ni prénom d'adoptant n'est publié sur ce site</td></tr>
                </table>
            </x-record>

            <x-record titre="Cookies">
                <table>
                    <tr><th>Cookies déposés</th><td>Aucun cookie de mesure d'audience ni de publicité</td></tr>
                    <tr><th>Cookies techniques</th><td>Uniquement ceux nécessaires au fonctionnement du formulaire (session et jeton CSRF)</td></tr>
                </table>
            </x-record>

        </div>
    </div>
</section>

@endsection
