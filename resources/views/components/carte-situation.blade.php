@props(['itineraire'])

@php
    /*
        La carte de situation, dessinée.

        Le fond de plan précédent venait de tuiles CARTO, qui réclament
        désormais une clé : la carte s'affichait barrée de « API KEY
        REQUIRED ». Plutôt que d'aller chercher un autre fournisseur, elle est
        tracée ici — un SVG, aucune requête vers l'extérieur, aucun traceur,
        aucun filigrane, et 160 ko de Leaflet en moins sur la page.

        Rien n'est placé à la main : les villes sont projetées depuis leurs
        vraies coordonnées (config/chatterie.php). Corriger une latitude là-bas
        déplace le point ici.
    */

    $zone    = config('chatterie.carte.zone');
    $reperes = config('chatterie.carte.reperes');
    $lieux   = array_merge([$zone], $reperes);

    $lats = array_column($lieux, 'lat');
    $lngs = array_column($lieux, 'lng');

    // Projection équirectangulaire : à cette latitude un degré de longitude
    // vaut 0,70 degré de latitude. Sans ce facteur la région serait étirée.
    $kx = cos(deg2rad((min($lats) + max($lats)) / 2));

    /* La marge ouest est la plus large : c'est de ce cote que partent les
       etiquettes des villes de la moitie droite du cadre, dont la plus longue,
       Saint-Rambert-d'Albon. Sans cette reserve, elle sortirait de la planche. */
    $latMin = min($lats) - 0.13;  $latMax = max($lats) + 0.13;
    $lngMin = min($lngs) - 0.30;  $lngMax = max($lngs) + 0.16;

    $spanX = ($lngMax - $lngMin) * $kx;
    $spanY = $latMax - $latMin;

    // Le cadre épouse l'emprise des données : pas de vide sur les côtés.
    $H = 800.0;
    $W = round($H * $spanX / $spanY, 1);
    $echelle = $H / $spanY;

    $proj = fn (float $lat, float $lng): array => [
        round(($lng - $lngMin) * $kx * $echelle, 1),
        round(($latMax - $lat) * $echelle, 1),
    ];

    // Un degré de latitude fait 111,2 km : de quoi graduer l'échelle.
    $uParKm = $echelle / 111.2;

    /*
        Le Rhône, relevé grossièrement de Lyon à Valence. Il ne sert pas à
        naviguer : c'est le repère qui fait reconnaître la région d'un coup
        d'oeil, et l'élevage se lit aussitôt comme étant sur sa rive gauche.
    */
    $fleuve = [
        [45.78, 4.83], [45.70, 4.81], [45.62, 4.77], [45.53, 4.87], [45.46, 4.79],
        [45.38, 4.76], [45.31, 4.79], [45.24, 4.81], [45.16, 4.83], [45.08, 4.85],
        [44.98, 4.88],
    ];

    // Lissage quadratique : chaque sommet devient un point de contrôle, les
    // milieux de segments deviennent les points de passage.
    $pts = array_map(fn ($p) => $proj($p[0], $p[1]), $fleuve);
    $d = 'M'.$pts[0][0].' '.$pts[0][1];
    for ($i = 1; $i < count($pts) - 1; $i++) {
        $mx = round(($pts[$i][0] + $pts[$i + 1][0]) / 2, 1);
        $my = round(($pts[$i][1] + $pts[$i + 1][1]) / 2, 1);
        $d .= ' Q'.$pts[$i][0].' '.$pts[$i][1].' '.$mx.' '.$my;
    }
    $d .= ' L'.end($pts)[0].' '.end($pts)[1];

    [$ex, $ey] = $proj($zone['lat'], $zone['lng']);

    // Le disque marque la commune, pas la maison : deux kilomètres de rayon,
    // à l'échelle du plan.
    $rayonCommune = round(2.2 * $uParKm, 1);

    // Les villes, avec de quoi poser l'étiquette du bon côté : à gauche du
    // point quand il est dans la moitié droite du cadre, sinon à droite.
    $villes = collect($reperes)->map(function ($r) use ($proj, $W) {
        [$x, $y] = $proj($r['lat'], $r['lng']);
        $aDroite = $x > $W * 0.52;

        return [
            'x' => $x, 'y' => $y,
            'titre' => $r['titre'], 'detail' => $r['detail'],
            'ancre' => $aDroite ? 'end' : 'start',
            'dx'    => $aDroite ? -16 : 16,
        ];
    })->values();

    $nord = $proj($latMax - 0.04, $lngMin + 0.05);
@endphp

<figure {{ $attributes->merge(['class' => 'carte-lieu']) }}>
    <span class="eq" aria-hidden="true"></span><span class="eq" aria-hidden="true"></span>
    <span class="eq" aria-hidden="true"></span><span class="eq" aria-hidden="true"></span>

    <div class="carte-plan">
        <svg viewBox="0 0 {{ $W }} {{ $H }}" role="img"
             aria-label="Carte de situation : l'élevage est à Lapeyrouse-Mornay, au nord de la Drôme, entre Lyon, Saint-Étienne et Valence.">
            <defs>
                <radialGradient id="halo-elevage">
                    <stop offset="0%"   stop-color="#D9B26A" stop-opacity=".34"/>
                    <stop offset="55%"  stop-color="#D9B26A" stop-opacity=".10"/>
                    <stop offset="100%" stop-color="#D9B26A" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="cours-rhone" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#7EA8A0" stop-opacity=".12"/>
                    <stop offset="38%"  stop-color="#7EA8A0" stop-opacity=".40"/>
                    <stop offset="100%" stop-color="#7EA8A0" stop-opacity=".12"/>
                </linearGradient>
                <pattern id="trame" width="64" height="64" patternUnits="userSpaceOnUse">
                    <path d="M64 0H0v64" fill="none" stroke="#F5F0E6" stroke-opacity=".045" stroke-width="1"/>
                </pattern>
            </defs>

            {{-- Le carroyage : il donne l'échelle du regard sans rien affirmer. --}}
            <rect width="100%" height="100%" fill="url(#trame)"/>

            {{-- Le Rhône. --}}
            <path class="rhone" d="{{ $d }}" fill="none" stroke="url(#cours-rhone)"
                  stroke-width="9" stroke-linecap="round"/>
            <path class="rhone-fil" d="{{ $d }}" fill="none" stroke="#9BC4BB" stroke-opacity=".30"
                  stroke-width="1.4" stroke-linecap="round"/>
            <text class="hydronyme" x="{{ $pts[8][0] - 12 }}" y="{{ $pts[8][1] }}"
                  text-anchor="end">Le Rhône</text>

            {{-- Les liaisons, tracées depuis l'élevage. Chacune appartient à sa
                 ville : survoler l'une allume l'autre. --}}
            <g class="lieux">
                @foreach($villes as $i => $v)
                    <g class="ville" style="--rang:{{ $i }}">
                        <line class="liaison" x1="{{ $ex }}" y1="{{ $ey }}"
                              x2="{{ $v['x'] }}" y2="{{ $v['y'] }}"/>
                        <circle class="halo-ville" cx="{{ $v['x'] }}" cy="{{ $v['y'] }}" r="16"/>
                        <circle class="point" cx="{{ $v['x'] }}" cy="{{ $v['y'] }}" r="5.5"/>
                        <text class="nom" x="{{ $v['x'] + $v['dx'] }}" y="{{ $v['y'] - 2 }}"
                              text-anchor="{{ $v['ancre'] }}">{{ Str::upper($v['titre']) }}</text>
                        <text class="duree" x="{{ $v['x'] + $v['dx'] }}" y="{{ $v['y'] + 20 }}"
                              text-anchor="{{ $v['ancre'] }}">{{ $v['detail'] }}</text>
                    </g>
                @endforeach
            </g>

            {{-- L'élevage. Le disque marque la commune, pas la maison :
                 l'adresse exacte se donne au moment du depart. --}}
            <g class="elevage">
                <circle cx="{{ $ex }}" cy="{{ $ey }}" r="{{ round(6 * $uParKm, 1) }}"
                        fill="url(#halo-elevage)"/>
                <circle class="onde" cx="{{ $ex }}" cy="{{ $ey }}" r="{{ $rayonCommune }}"/>
                <circle class="commune" cx="{{ $ex }}" cy="{{ $ey }}" r="{{ $rayonCommune }}"/>
                <circle class="coeur" cx="{{ $ex }}" cy="{{ $ey }}" r="7"/>

                {{-- L'élevage est le point le plus à l'est : ses étiquettes partent
                     vers l'intérieur du cadre, et au-dessus du disque pour ne pas
                     tomber sur Saint-Rambert-d'Albon, tout proche au sud. --}}
                <text class="mention" x="{{ $ex - $rayonCommune - 14 }}" y="{{ $ey - $rayonCommune - 32 }}"
                      text-anchor="end">L’élevage</text>
                <text class="nom" x="{{ $ex - $rayonCommune - 14 }}" y="{{ $ey - $rayonCommune - 8 }}"
                      text-anchor="end">Lapeyrouse-Mornay</text>
            </g>

            {{-- La rose des vents, réduite à ce qui sert. --}}
            <g class="rose" transform="translate({{ $nord[0] }}, {{ $nord[1] }})">
                <path d="M0 26V-14" stroke-width="1"/>
                <path d="m-6-8 6-10 6 10" fill="none" stroke-width="1.2"/>
                <text x="0" y="42" text-anchor="middle">N</text>
            </g>

            {{-- L'échelle, graduée sur la projection elle-même. --}}
            <g class="echelle" transform="translate(30, {{ $H - 34 }})">
                <path d="M0 0h{{ round(20 * $uParKm, 1) }}" stroke-width="1.4"/>
                <path d="M0-5v10M{{ round(20 * $uParKm, 1) }}-5v10" stroke-width="1.4"/>
                <text x="{{ round(10 * $uParKm, 1) }}" y="-12" text-anchor="middle">20 km</text>
            </g>
        </svg>
    </div>

    <figcaption class="carte-dire">
        <span class="rubrique">Venir jusqu’à nous</span>
        <h3>Au nord de la Drôme,<br>sur la rive gauche du Rhône</h3>

        <p>
            L’élevage est à <strong>Lapeyrouse-Mornay</strong>, à dix minutes de la sortie
            de Saint-Rambert-d’Albon et à une heure de Lyon comme de Saint-Étienne.
            L’adresse exacte vous est communiquée au moment du départ du chaton.
        </p>
        <p>Nous pouvons venir vous chercher à la gare de La Verpillière.</p>

        {{-- Visent la commune, pas l'adresse exacte : un itinéraire
             porte-à-porte la publierait. --}}
        <p class="carte-itineraire">
            <a href="{{ $itineraire['google'] }}" target="_blank" rel="noopener noreferrer"
               aria-label="Itinéraire vers {{ $itineraire['commune'] }} sur Google Maps (nouvelle fenêtre)">
                Tracer l’itinéraire
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h13M13 6l6 6-6 6"/>
                </svg>
            </a>
            <a class="second" href="{{ $itineraire['waze'] }}" target="_blank" rel="noopener noreferrer"
               aria-label="Itinéraire vers {{ $itineraire['commune'] }} sur Waze (nouvelle fenêtre)">ou dans Waze</a>
        </p>

        <p class="petit">
            Carte dessinée pour ce site : aucune tuile, aucun script et aucun traceur
            extérieur n’est chargé ici. Les liens d’itinéraire ouvrent l’application
            de navigation dans un nouvel onglet.
        </p>
    </figcaption>
</figure>
