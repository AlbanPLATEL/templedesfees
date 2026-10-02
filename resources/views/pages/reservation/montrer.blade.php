@extends('layouts.app')

@section('title', "Réservation de {$chaton?->nom}")
@section('description', "Page de réservation privée.")

@push('head')
    {{-- Une page privee, atteinte par un lien envoye a une famille precise :
         elle n'a rien a faire dans un moteur de recherche. --}}
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')

@php
    use App\Enums\ReservationStatus;
    use App\Services\Caisse;

    $etat = $reservation->statut;
    $perimee = $reservation->estPerimee();
@endphp

<section class="bande">
    <div class="wrap" style="max-width:940px">

        <div class="chapitre monte">
            <span class="rubrique">Réservation · {{ $reservation->nomComplet() }}</span>
            <h1>{{ $chaton?->nom ?? 'Chaton' }}</h1>
            <p class="lede">
                @if($etat === ReservationStatus::Payee)
                    L’acompte est arrivé. {{ $chaton?->nom }} vous est réservé.
                @elseif($perimee || $etat === ReservationStatus::Expiree)
                    Le délai de cette réservation est passé.
                @elseif($etat === ReservationStatus::Annulee)
                    Cette réservation a été annulée.
                @elseif($etat === ReservationStatus::Remboursee)
                    L’acompte a été remboursé.
                @else
                    Voici ce qui vous est réservé, et ce que couvre l’acompte.
                    Prenez le temps de lire avant de valider.
                @endif
            </p>
        </div>

        @if(Caisse::enDemonstration() && $reservation->peutEtrePayee())
            {{-- Ce bandeau n'apparait que tant qu'aucune clef Stripe n'est
                 renseignee. Il doit etre impossible de le confondre avec un
                 vrai paiement. --}}
            <p class="note-apercu monte" style="border-color:var(--alerte)">
                <span class="sceau" aria-hidden="true" style="border-color:var(--alerte);color:var(--alerte)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                    </svg>
                </span>
                <span>
                    <b>Démonstration — aucun paiement réel</b>
                    Le compte de paiement n’est pas encore ouvert. Le bouton ci-dessous
                    marque la réservation comme payée pour montrer le parcours complet.
                    Aucune carte n’est demandée, aucune somme n’est prélevée.
                </span>
            </p>
        @endif

        @if(session('erreur'))
            <p class="note-apercu monte">
                <span class="sceau" aria-hidden="true">!</span>
                <span>{{ session('erreur') }}</span>
            </p>
        @endif

        <div class="duo-texte haut monte" style="align-items:start">

            {{-- ── le chaton ── --}}
            <div class="pile">
                @if($chaton?->photo_principale)
                    <div class="arche">
                        <i><u>
                            <x-img :src="$chaton->photo_principale"
                                   :alt="$chaton->nom.', chaton Maine Coon'"
                                   sizes="(max-width:980px) 90vw, 42vw" :urgent="true" />
                        </u></i>
                    </div>
                @endif
            </div>

            {{-- ── ce qu'on paie ── --}}
            <div class="pile">
                <x-record titre="Le chaton" meta="{{ $chaton?->reference }}">
                    <table>
                        <tr><th>Nom</th><td>{{ $chaton?->nom }}</td></tr>
                        <tr><th>Sexe</th><td>{{ $chaton?->sexeLibelle() ?: '—' }}</td></tr>
                        <tr><th>Robe</th><td>{{ $chaton?->robe }}</td></tr>
                        @if($chaton?->litter?->date_disponibilite)
                            <tr>
                                <th>Départ prévu</th>
                                <td>{{ $chaton->litter->date_disponibilite->translatedFormat('j F Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </x-record>

                <x-record titre="L’acompte" meta="{{ $etat->libelle() }}"
                          note="L’acompte est déduit du prix du chaton au moment du départ. Il n’est pas un supplément.">
                    <table>
                        <tr>
                            <th>Montant</th>
                            <td><span class="verdict">{{ $reservation->acompteFormate() }}</span></td>
                        </tr>
                        <tr>
                            <th>Ce qu’il engage</th>
                            <td>
                                <span class="verdict" style="font-size:16px">Le chaton vous est réservé</span>
                                <small>Il n’est plus proposé à personne d’autre, et vous recevez des
                                photos toutes les semaines jusqu’au départ.</small>
                            </td>
                        </tr>
                        @if($reservation->expire_le && $reservation->statut->attendUnPaiement())
                            <tr>
                                <th>À régler avant le</th>
                                <td>
                                    <span @class(['verdict', 'attente' => ! $perimee])>
                                        {{ $reservation->expire_le->translatedFormat('j F Y') }}
                                    </span>
                                    <small>Passé ce délai, le chaton est de nouveau proposé.</small>
                                </td>
                            </tr>
                        @endif
                        @if($reservation->paye_le)
                            <tr>
                                <th>Reçu le</th>
                                <td>{{ $reservation->paye_le->translatedFormat('j F Y à H\hi') }}</td>
                            </tr>
                        @endif
                    </table>
                </x-record>

                @php($conditions = \App\Models\Setting::get('legal.acompte'))

                @if(filled($conditions))
                    {{-- Les conditions sont sur la page, pas derriere un lien :
                         on ne fait pas cocher « j'ai lu » a quelqu'un qui
                         devrait ouvrir un autre onglet pour lire. --}}
                    <x-record titre="Les conditions" meta="À lire avant de valider">
                        <x-texte-riche :texte="$conditions" />
                    </x-record>
                @endif

                {{-- Les documents. Le contrat existe dès la création : c'est sur
                     lui que la famille se décide. La facture n'apparaît qu'une
                     fois l'acompte arrivé — elle atteste un versement reçu. --}}
                <x-record titre="Vos documents" meta="{{ $reservation->reference() }}"
                          note="Chaque document s'imprime, ou s'enregistre en PDF depuis la fenêtre d'impression de votre navigateur.">
                    <div class="btnrow">
                        <a class="btn creux" href="{{ route('reservation.contrat', ['jeton' => $reservation->jeton]) }}">
                            Le contrat de réservation
                        </a>
                        @if($reservation->aUneFacture())
                            <a class="btn creux" href="{{ route('reservation.facture', ['jeton' => $reservation->jeton]) }}">
                                La facture d’acompte n° {{ $reservation->facture_numero }}
                            </a>
                        @endif
                    </div>
                </x-record>

                @if($reservation->peutEtrePayee() && Caisse::horsLigne())
                    {{-- Le règlement hors ligne. Pas de bouton : on dit où
                         verser, et l’éleveur enregistre l’acompte à son arrivée
                         depuis son espace. Le reste de la page ne bouge pas —
                         le chaton, ce que couvre l’acompte, les conditions et le
                         contrat sont là de la même façon. --}}
                    <x-record titre="Comment verser l’acompte"
                              meta="{{ $reservation->acompteFormate() }}"
                              note="L’acompte bloque {{ $chaton?->nom }} dès qu’il nous parvient. Vous recevez alors votre facture, ici même.">
                        @php($commentPayer = \App\Models\Setting::get('paiement.instructions'))

                        @if($commentPayer)
                            <x-texte-riche :texte="$commentPayer" />
                        @else
                            <p>
                                Le versement se fait directement auprès de nous, par virement
                                ou de la main à la main lors de votre visite. Appelez-nous ou
                                écrivez-nous : nous vous indiquons la marche à suivre et nous
                                convenons du moment.
                            </p>
                        @endif

                        <div class="btnrow" style="margin-top:6px">
                            <a class="btn" href="{{ route('contact') }}">Nous joindre</a>
                            <a class="btn creux" href="{{ route('kittens.show', $chaton) }}">
                                Revoir la fiche de {{ $chaton?->nom }}
                            </a>
                        </div>
                    </x-record>
                @elseif($reservation->peutEtrePayee())
                    <form method="POST" action="{{ route('reservation.payer', ['jeton' => $reservation->jeton]) }}"
                          class="demande">
                        @csrf

                        <label class="consent plein">
                            <input type="checkbox" name="conditions" value="1" required
                                   @checked(old('conditions'))>
                            <span>
                                J’ai lu et j’accepte les conditions de l’acompte ci-dessus.
                                <a href="{{ route('legal') }}">Mentions légales</a>
                            </span>
                        </label>

                        @error('conditions')
                            <p class="erreur plein">{{ $message }}</p>
                        @enderror

                        <div class="btnrow plein" style="margin-top:6px">
                            <button class="btn" type="submit">
                                @if(Caisse::enDemonstration())
                                    Simuler le versement de {{ $reservation->acompteFormate() }}
                                @else
                                    Verser l’acompte de {{ $reservation->acompteFormate() }}
                                @endif
                            </button>
                        </div>

                        @unless(Caisse::enDemonstration())
                            <p class="petit plein" style="margin-top:4px">
                                Paiement par carte, traité par Stripe. Aucun numéro de carte
                                ne transite par ce site ni n’y est conservé.
                            </p>
                        @endunless
                    </form>
                @elseif($etat === ReservationStatus::Payee)
                    <div class="btnrow">
                        <a class="btn creux" href="{{ route('kittens.show', $chaton) }}">Revoir la fiche de {{ $chaton?->nom }}</a>
                        <a class="btn creux" href="{{ route('contact') }}">Nous écrire</a>
                    </div>
                @else
                    <p class="petit">
                        Si vous pensez que c’est une erreur, appelez-nous : nous reprendrons
                        la réservation ensemble.
                    </p>
                    <div class="btnrow">
                        <a class="btn creux" href="{{ route('contact') }}">Nous joindre</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection
