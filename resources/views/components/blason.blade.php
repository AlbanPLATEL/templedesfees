@props(['taille' => 42, 'lien' => false, 'urgent' => false])

{{--
    Le blason : la tête de Maine Coon du logo, reprise dans la dorure de la
    charte par scripts/blason.php.

    Le fichier servi suit la taille demandée. Un blason de 44 px dans le
    bandeau n'a aucune raison de faire télécharger une planche de 512 px, et
    c'est la première image de la page : elle se charge avant tout le reste,
    pas en différé.

    Tant que le fichier n'est pas là, le composant ne rend rien. Un cadre vide
    ou une image cassée dans le bandeau se voit bien plus qu'une absence, et
    le site doit rester présentable à toutes les étapes.
--}}
@php
    $source = match (true) {
        $taille <= 48  => 'images/blason-96.webp',
        $taille <= 110 => 'images/blason-192.webp',
        default        => 'images/blason.webp',
    };
@endphp

@if(file_exists(public_path($source)))
    @if($lien)
        <a class="blason-lien" href="{{ route('home') }}"
           aria-label="Accueil — La Chatterie du Temple des Fées">
            <img class="blason" src="{{ asset($source) }}" alt=""
                 width="{{ $taille }}" height="{{ $taille }}"
                 loading="{{ $urgent ? 'eager' : 'lazy' }}"
                 @if($urgent) fetchpriority="high" @endif decoding="async">
        </a>
    @else
        <img {{ $attributes->merge(['class' => 'blason']) }}
             src="{{ asset($source) }}" alt="La Chatterie du Temple des Fées"
             width="{{ $taille }}" height="{{ $taille }}"
             loading="{{ $urgent ? 'eager' : 'lazy' }}"
             @if($urgent) fetchpriority="high" @endif decoding="async">
    @endif
@endif
