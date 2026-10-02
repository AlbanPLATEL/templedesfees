@props([
    'url',
    'type'   => 'instagram',   {{-- instagram | facebook | tiktok | mail | tel --}}
    'handle' => '@templedesfees',
])

{{--
    Icône seule, sans libellé. Au survol, l'anneau s'allume : aux couleurs de la
    marque pour Instagram, en bronze pour le courriel et le téléphone.

    Le pictogramme Instagram est le glyphe officiel, repris sans modification de
    son tracé : les règles de marque de Meta interdisent de le redessiner, de le
    recolorer autrement qu'en aplat uni, de l'incliner ou de le déformer.
    L'enveloppe et le combiné viennent de Heroicons (MIT), intégrés ici plutôt
    qu'appelés au paquet : celui-ci n'est installé que comme dépendance du
    back-office, et le site public ne doit pas en dépendre.

    L'anneau tourne au survol, le glyphe est contre-pivoté dans app.css pour
    rester droit — ne pas supprimer cette contre-rotation.
--}}

@php
    $externe = in_array($type, ['instagram', 'facebook', 'tiktok'], true);

    [$titre, $intitule] = match ($type) {
        'mail' => ["Email — {$handle}",     "Écrire à {$handle}"],
        'tel'  => ["Téléphone — {$handle}", "Appeler le {$handle}"],
        'facebook' => ["Facebook — {$handle}", "Facebook {$handle} (nouvelle fenêtre)"],
        'tiktok'   => ["TikTok — {$handle}", "TikTok {$handle} (nouvelle fenêtre)"],
        default => ["Instagram — {$handle}", "Instagram {$handle} (nouvelle fenêtre)"],
    };
@endphp

<a {{ $attributes->merge(['class' => 'social social--'.$type]) }}
   href="{{ $url }}"
   @if($externe) target="_blank" rel="noopener noreferrer" @endif
   title="{{ $titre }}"
   aria-label="{{ $intitule }}">
    <span class="social-mark" aria-hidden="true">
        @switch($type)
            @case('mail')
                <svg viewBox="0 0 24 24" fill="currentColor" focusable="false">
                    <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z"/>
                    <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z"/>
                </svg>
                @break

            @case('tel')
                <svg viewBox="0 0 24 24" fill="currentColor" focusable="false">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z"/>
                </svg>
                @break

            @case('facebook')
                <svg viewBox="0 0 24 24" fill="currentColor" focusable="false">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12S0 5.446 0 12.073c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073Z"/>
                </svg>
                @break

            {{-- Le glyphe officiel de TikTok, repris sans retouche de son
                 tracé. Comme pour Instagram, il n’est pas redessiné : seul
                 le disque derrière lui porte les couleurs de la marque. --}}
            @case('tiktok')
                <svg viewBox="0 0 24 24" fill="currentColor" focusable="false">
                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                </svg>
                @break

            @default
                <svg viewBox="0 0 24 24" fill="currentColor" focusable="false">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
        @endswitch
    </span>
</a>
