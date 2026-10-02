@php
    $tel   = \App\Models\Setting::get('contact.telephone', '06 77 35 45 87');
    $mail  = \App\Models\Setting::get('contact.email', 'letempledesfees@outlook.fr');
    $insta = \App\Models\Setting::get('contact.instagram');
    $fb    = \App\Models\Setting::get('contact.facebook');
    $tt    = \App\Models\Setting::get('contact.tiktok');
    $siren = \App\Models\Setting::get('legal.siren');
    $nom   = \App\Models\Setting::get('elevage.nom', 'La Chatterie du Temple des Fées');
    $telLien = \Illuminate\Support\Str::of($tel)->replace(' ', '')->replaceFirst('0', '+33');
@endphp

<footer>
    <div class="wrap">
        <div class="fgrille">
            <div class="fsignature">
                <h4>{{ $nom }}</h4>
                <p class="petit resume">
                    Élevage familial de Maine Coon à Lapeyrouse-Mornay (26210), dans la Drôme
                    des collines. Une à deux portées par an, parents dépistés, résultats publiés.
                </p>
                {{-- Le blason remplace le fleuron des qu'il existe : au pied
                     d'une page, l'embleme vaut mieux qu'un ornement. --}}
                @if(file_exists(public_path('images/blason.png')))
                    <x-blason class="blason-pied" :taille="66" />
                @else
                    <x-fleuron taille="petit" style="margin-top:20px;color:var(--or-mat)" />
                @endif
            </div>

            {{-- Deux groupes de quatre, et des intitulés qui disent vrai.
                 Il y en avait six d'un côté et quatre de l'autre, sous des
                 titres qui ne tenaient pas : la galerie et le Maine Coon ne
                 sont pas « l'élevage », et les mentions légales ne sont pas
                 « adopter ». Dix liens gris de même poids sous des titres
                 approximatifs ne font pas deux listes, ils font un bloc.

                 Les mentions légales descendent au bas de page, auprès du
                 numéro de SIREN : c'est là qu'on les cherche. --}}
            <div class="fliens">
                <h4>Adopter</h4>
                <ul>
                    <li><a href="{{ route('kittens.index') }}">Chatons disponibles</a></li>
                    <li><a href="{{ route('cats.index') }}">Nos reproducteurs</a></li>
                    <li><a href="{{ route('adoption.create') }}">Le parcours</a></li>
                    <li><a href="{{ route('adoption.create') }}#couverture">Ce que couvre l'adoption</a></li>
                </ul>
            </div>

            <div class="fliens">
                <h4>Découvrir</h4>
                <ul>
                    <li><a href="{{ route('breed') }}">Le Maine Coon</a></li>
                    <li><a href="{{ route('articles.index') }}">Articles</a></li>
                    <li><a href="{{ route('gallery') }}">Galerie</a></li>
                    <li><a href="{{ route('hommage') }}">En mémoire d'Olimpia</a></li>
                </ul>
            </div>

            <div class="fjoindre">
                <h4>Nous joindre</h4>

                {{-- Des pictogrammes plutôt que des lignes de texte. Le libellé
                     complet reste dans l'intitulé accessible de chaque lien :
                     rien n'est perdu pour un lecteur d'écran.

                     Deux rangées, et pas une file : ce qui nous joint
                     directement d'un côté — le téléphone et le courriel — les
                     réseaux où l'on nous suit de l'autre. Ce ne sont pas les
                     mêmes gestes. Et comme chaque réseau peut être absent, le
                     groupe se resserre tout seul au lieu de laisser un trou. --}}
                <div class="socials">
                    <div class="socials-rang">
                        <x-social-link type="tel"  :url="'tel:'.$telLien" :handle="$tel" />
                        <x-social-link type="mail" :url="'mailto:'.$mail" :handle="$mail" />
                    </div>

                    @if($insta || $fb || $tt)
                        <div class="socials-rang">
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
                    @endif
                </div>

                <ul>
                    <li><a href="{{ route('contact') }}">Venir nous voir</a></li>
                    <li><a href="{{ route('faq') }}">Questions fréquentes</a></li>
                </ul>
            </div>
        </div>

        <div class="fbas">
            <span>© {{ date('Y') }} {{ $nom }} — Certificat de capacité · SIREN {{ $siren ?: 'à compléter' }}
                · <a href="{{ route('legal') }}">Mentions légales &amp; RGPD</a></span>
            <span>24 chemin Saint-Charles · 26210 Lapeyrouse-Mornay</span>
        </div>
    </div>
</footer>
