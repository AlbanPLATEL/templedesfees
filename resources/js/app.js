/* La Chatterie du Temple des Fées — interactions du site public.
   Sans dépendance : menu, bandeau, apparition au défilement, vue plein
   écran, visionneuse de fiche, transitions de page. */

const reduit = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---------- menu ---------- */
const cle = document.getElementById('cle');
const menu = document.getElementById('menu');

/* Le meme seuil que la charte, et pas un voisin.

   Il etait ecrit 1120 ici et 1180 la-bas. Entre les deux, la charte posait
   deja le panneau en plein ecran tandis que le script le declarait visible :
   le menu restait donc ouvert en permanence, 838 px par-dessus le haut de
   chaque page, sur toutes les fenetres de 1121 a 1180 px. Une media query
   lue par le script ne peut plus diverger de celle de la feuille. */
const menuEtroit = window.matchMedia('(max-width: 1180px)');

/* Ouvrir le menu fige la page dessous : sans cela le doigt fait defiler le
   site derriere le panneau, qui a l'air casse. */
const poserMenu = (ouvert) => {
    cle.setAttribute('aria-expanded', String(ouvert));
    menu.hidden = !ouvert;
    cle.textContent = ouvert ? 'Fermer' : 'Menu';
    document.body.style.overflow = ouvert ? 'hidden' : '';
};

const accorderMenu = () => {
    if (!cle || !menu) return;
    if (!menuEtroit.matches) {
        menu.hidden = false;
        cle.setAttribute('aria-expanded', 'false');
        cle.textContent = 'Menu';
        document.body.style.overflow = '';
    } else if (cle.getAttribute('aria-expanded') !== 'true') {
        poserMenu(false);
    }
};

cle?.addEventListener('click', () => {
    poserMenu(cle.getAttribute('aria-expanded') !== 'true');
});

/* Echap referme et rend la main au bouton : un menu plein ecran sans sortie
   au clavier enferme celui qui n'a pas de souris. */
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (cle?.getAttribute('aria-expanded') !== 'true') return;
    poserMenu(false);
    cle.focus();
});

menuEtroit.addEventListener('change', accorderMenu);
window.addEventListener('resize', accorderMenu);
accorderMenu();

/* ---------- les filtres d'une liste ----------

   Chats, chatons, photos, articles : quatre listes qui passent leur filtre
   par l'adresse, et c'est le serveur qui trie. L'apercu publie sur GitHub
   Pages n'a pas de serveur — ?role=etalon y renvoyait la page entiere, avec
   tous les chats et la pastille doree restee sur « Tous ». Les filtres ne
   faisaient donc rien du tout, ni sur telephone ni sur ordinateur.

   Ce bloc refait le tri dans la page. Sur le site servi par Laravel il ne
   trouve rien a cacher, le serveur ayant deja trie, et ne change donc rien :
   c'est le meme filtre, applique deux fois au meme resultat.

   Le conteneur declare les parametres qu'il lit, chaque element porte sa
   valeur en data-, et le message de liste vide est ecrit la ou il doit
   l'etre : dans le gabarit, pas ici. */

document.querySelectorAll('[data-filtre]').forEach((liste) => {
    const clefs = liste.dataset.filtre.split(/\s+/).filter(Boolean);
    const demande = new URLSearchParams(window.location.search);
    const actifs = clefs
        .map((c) => [c, demande.get(c)])
        .filter(([, v]) => v !== null && v !== '');

    let montres = 0;
    [...liste.children].forEach((item) => {
        const garde = actifs.every(([c, v]) => item.dataset[c] === v);
        item.hidden = !garde;
        if (garde) montres += 1;
    });

    const zone = liste.closest('.wrap') ?? document;

    /* La pastille doree doit suivre : sans cela elle reste sur « Tous »
       alors que la liste est filtree, et plus rien ne dit ou l'on est. */
    zone.querySelectorAll('[data-filtre-barre] a').forEach((a) => {
        const sienne = new URL(a.href, window.location.href).searchParams;
        const pareil = clefs.every(
            (c) => (sienne.get(c) ?? '') === (demande.get(c) ?? ''),
        );
        if (pareil) a.setAttribute('aria-current', 'true');
        else a.removeAttribute('aria-current');
    });

    /* Aucun des filtres proposes ne mene a une liste vide — leurs comptes
       sont etablis sur l'ensemble. Une adresse tapee a la main, si : une
       page qui n'affiche alors rien du tout a l'air cassee. */
    if (montres > 0 || !liste.dataset.filtreVide) return;

    const mot = document.createElement('p');
    mot.className = 'lede';
    mot.style.textAlign = 'center';
    mot.style.marginInline = 'auto';
    mot.textContent = liste.dataset.filtreVide + ' ';

    const tout = document.createElement('a');
    tout.className = 'lien';
    tout.href = window.location.pathname;
    tout.textContent = 'Tout voir';
    mot.appendChild(tout);

    liste.after(mot);
});

/* ---------- bandeau ---------- */
const bandeau = document.getElementById('bandeau');
window.addEventListener(
    'scroll',
    () => bandeau?.classList.toggle('accroche', window.scrollY > 12),
    { passive: true },
);

/* ---------- apparition au défilement ----------

   Chaque chapitre monte en entrant dans le champ. Le filet de sécurité en
   bas du bloc découvre ce qui resterait masqué si l'observateur ne se
   déclenchait pas — une page qui n'affiche rien est pire qu'une page sans
   animation. */

if (!reduit()) {
    const io = new IntersectionObserver(
        (entrees) => {
            entrees.forEach((e) => {
                if (!e.isIntersecting) return;
                e.target.classList.add('vu');
                io.unobserve(e.target);
            });
        },
        { rootMargin: '0px 0px -7% 0px' },
    );

    document.querySelectorAll('.monte').forEach((el, i) => {
        el.style.transitionDelay = `${Math.min(i, 5) * 90}ms`;
        if (el.getBoundingClientRect().top > window.innerHeight * 1.02) io.observe(el);
        else el.classList.add('vu');
    });

    /*
     * Le filet de securite, deux fois.
     *
     * Une seule verification apres deux secondes ne couvrait que le haut de
     * la page : plus bas, si l'observateur ne se declenchait pas — onglet
     * d'arriere-plan, navigation par ancre, moteur qui bride les
     * observateurs — la section restait invisible pour de bon. Une page qui
     * n'affiche rien est pire qu'une page sans animation, donc le defilement
     * decouvre aussi ce qui est arrive dans le champ.
     */
    const decouvrir = () => {
        const restants = document.querySelectorAll('.monte:not(.vu)');
        restants.forEach((el) => {
            if (el.getBoundingClientRect().top < window.innerHeight * 1.2) el.classList.add('vu');
        });

        return restants.length;
    };

    let veille = false;
    const surveiller = () => {
        if (veille) return;
        veille = true;
        requestAnimationFrame(() => {
            veille = false;
            if (decouvrir() === 0) {
                window.removeEventListener('scroll', surveiller);
                window.removeEventListener('resize', surveiller);
            }
        });
    };

    window.addEventListener('scroll', surveiller, { passive: true });
    window.addEventListener('resize', surveiller);
    setTimeout(decouvrir, 2200);
} else {
    document.querySelectorAll('.monte').forEach((el) => el.classList.add('vu'));
}

/* ---------- vue plein écran ----------

   Un seul mécanisme pour deux usages : la mosaïque de la galerie, où chaque
   vignette est son propre déclencheur, et la visionneuse des fiches, où
   c'est la grande photo qui agrandit la vue courante. Dans les deux cas la
   liste est lue sur le conteneur marqué data-lightbox. */

let lb = null;
let lbListe = [];
let lbIndex = 0;

const peindre = () => {
    const f = lbListe[lbIndex];
    const img = lb.querySelector('img');
    img.src = f.dataset.full;
    img.alt = f.querySelector('img')?.alt ?? '';
    lb.querySelector('.cap').textContent =
        `${f.dataset.legende ?? ''}  ·  ${lbIndex + 1} / ${lbListe.length}`;
};

const deplacer = (d) => {
    lbIndex = (lbIndex + d + lbListe.length) % lbListe.length;
    peindre();
};

const fermer = () => {
    if (!lb) return;
    lb.hidden = true;
    document.body.style.overflow = '';
};

const ouvrir = (items, index) => {
    if (!items.length) return;
    lbListe = items;
    lbIndex = Math.max(0, index);

    if (!lb) {
        lb = document.createElement('div');
        lb.id = 'lb';
        lb.innerHTML =
            '<button class="x" type="button">Fermer</button><img alt=""><p class="cap"></p>' +
            '<div class="nav"><button type="button" data-d="-1">← Précédente</button>' +
            '<button type="button" data-d="1">Suivante →</button></div>';
        document.body.appendChild(lb);

        /* Le glissement du doigt, et la raison du drapeau.

           Un glissement horizontal sur le fond produit aussi un clic sur le
           fond, et le clic sur le fond ferme la vue. Sans ce drapeau, chaque
           passage a la photo suivante refermait la visionneuse.

           Meme geste et meme seuil que le livre : 44 px, et il faut que
           l'horizontale l'emporte nettement, sinon un defilement du pouce
           un peu de travers ferait tourner la photo. */
        let depart = null;
        let glisse = false;

        lb.addEventListener('click', (ev) => {
            if (glisse) {
                glisse = false;
                return;
            }
            if (ev.target === lb || ev.target.classList.contains('x')) return fermer();
            const b = ev.target.closest('[data-d]');
            if (b) deplacer(Number(b.dataset.d));
        });

        lb.addEventListener('pointerdown', (ev) => {
            if (ev.target.closest('button')) return;
            depart = { x: ev.clientX, y: ev.clientY };
        });

        lb.addEventListener('pointerup', (ev) => {
            if (!depart) return;
            const dx = ev.clientX - depart.x;
            const dy = ev.clientY - depart.y;
            depart = null;

            if (Math.abs(dx) > 44 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                glisse = true;
                deplacer(dx < 0 ? 1 : -1);
            }
        });
        document.addEventListener('keydown', (ev) => {
            if (!lb || lb.hidden) return;
            if (ev.key === 'Escape') fermer();
            if (ev.key === 'ArrowLeft') deplacer(-1);
            if (ev.key === 'ArrowRight') deplacer(1);
        });
    }

    lb.hidden = false;
    document.body.style.overflow = 'hidden';
    peindre();
};

const listeDe = (conteneur) => [...conteneur.querySelectorAll('[data-full]')];

document.addEventListener('click', (e) => {
    const vignette = e.target.closest('[data-lightbox] figure[data-full]');
    if (vignette) {
        const items = listeDe(vignette.closest('[data-lightbox]'));
        return ouvrir(items, items.indexOf(vignette));
    }
    const scene = e.target.closest('[data-zoom]');
    if (scene) {
        const conteneur = scene.closest('[data-lightbox]');
        if (!conteneur) return;
        const items = listeDe(conteneur);
        return ouvrir(items, items.findIndex((v) => v.getAttribute('aria-current') === 'true'));
    }
});

/* La scène d'une fiche et les clichés de la galerie sont annoncés comme des
   boutons : ils doivent répondre au clavier comme tels. */
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    const cible = e.target.closest?.('[data-zoom], [data-lightbox] figure[data-full]');
    if (!cible) return;
    e.preventDefault();
    cible.click();
});

/* ---------- visionneuse de fiche ---------- */

document.querySelectorAll('.viseur').forEach((viseur) => {
    const vues = [...viseur.querySelectorAll('.scene img')];
    const vignettes = [...viseur.querySelectorAll('.vignette')];
    const compteur = viseur.querySelector('.compteur b');
    let courant = 0;

    const montrer = (i, prendreLeFocus = false) => {
        courant = (i + vues.length) % vues.length;
        vues.forEach((img, n) => img.classList.toggle('visible', n === courant));
        vignettes.forEach((v, n) => {
            if (n === courant) v.setAttribute('aria-current', 'true');
            else v.removeAttribute('aria-current');
            v.tabIndex = n === courant ? 0 : -1;
        });
        if (compteur) compteur.textContent = courant + 1;

        const active = vignettes[courant];
        active?.scrollIntoView({
            block: 'nearest',
            inline: 'nearest',
            behavior: reduit() ? 'auto' : 'smooth',
        });
        if (prendreLeFocus) active?.focus();
    };

    vignettes.forEach((v, i) => v.addEventListener('click', () => montrer(i)));

    viseur.querySelector('.rail')?.addEventListener('keydown', (e) => {
        const pas = {
            ArrowRight: 1,
            ArrowLeft: -1,
            Home: -courant,
            End: vues.length - 1 - courant,
        }[e.key];
        if (pas === undefined) return;
        e.preventDefault();
        montrer(courant + pas, true);
    });

    /* Glissement au doigt. Le seuil de 40 px évite de changer de photo sur un
       défilement vertical un peu oblique ; au-delà de 340 px on considère que
       le doigt a balayé l'écran et non la photo. */
    const scene = viseur.querySelector('.scene');
    let depart = null;

    scene?.addEventListener('pointerdown', (e) => {
        depart = e.pointerType === 'touch' ? { x: e.clientX, y: e.clientY } : null;
    });

    scene?.addEventListener('pointerup', (e) => {
        if (!depart) return;
        const dx = e.clientX - depart.x;
        const dy = e.clientY - depart.y;
        depart = null;
        if (Math.abs(dx) < 40 || Math.abs(dx) > 340 || Math.abs(dy) > Math.abs(dx)) return;

        // Le pointerup sera suivi d'un click, qui ouvrirait la vue plein écran
        // par-dessus la photo qu'on vient de faire défiler. On le neutralise
        // une fois, en phase de capture.
        scene.addEventListener(
            'click',
            (ev) => {
                ev.stopPropagation();
                ev.preventDefault();
            },
            { capture: true, once: true },
        );

        montrer(courant + (dx < 0 ? 1 : -1));
    });
});

/* ---------- transitions de page ----------

   Le navigateur sait déjà fondre une page dans l'autre (règle
   @view-transition dans app.css). Ce bloc ajoute le seul détail qu'il ne
   peut pas deviner : quelle image de la liste correspond à la grande photo
   de la fiche. Une fois les deux nommées pareil, il déplace la même image
   d'un cadre à l'autre au lieu de la faire disparaître puis réapparaître.

   Le nom est posé au dernier moment et retiré dès la fin du trajet : deux
   éléments portant le même nom au même instant annulent la transition. */

const NOM_VT = 'photo-fiche';

/* Seule la grande photo d'une fiche a un homologue dans une liste.
   L'arche de l'accueil n'en a pas, et elle etait pourtant nommee : le
   selecteur retombait sur « .portail img », qui n'existe qu'a cet endroit.

   Une image nommee est extraite dans le calque de la transition, ou le
   overflow:hidden de son cadre ne s'applique plus. La photo de l'accueil
   ressortait donc en rectangle par-dessus l'arche, et la hauteur forcee sur
   l'instantane la faisait paraitre agrandie. */
const photoDeLaFiche = () => document.querySelector('.scene img.visible');

const photoDeLaCarte = (url) => {
    if (!url) return null;
    const chemin = new URL(url, location.href).pathname;
    const lien = [...document.querySelectorAll('a.fiche, a.portrait')].find(
        (a) => new URL(a.href, location.href).pathname === chemin,
    );
    return lien?.querySelector('img') ?? null;
};

const nommer = (img, transition) => {
    if (!img) return;
    img.style.viewTransitionName = NOM_VT;
    transition?.finished.finally(() => {
        img.style.viewTransitionName = '';
    });
};

window.addEventListener('pageswap', (e) => {
    if (!e.viewTransition) return;
    nommer(photoDeLaCarte(e.activation?.entry?.url) ?? photoDeLaFiche(), e.viewTransition);
});

/* Un rechargement n'a pas d'homologue : la page de depart est la page
   d'arrivee. Nommer une image pour rien la sort de son cadre le temps du
   fondu, pour un raccord qui ne relie rien. */
const estUnRechargement = () =>
    window.navigation?.activation?.navigationType === 'reload';

window.addEventListener('pagereveal', (e) => {
    if (!e.viewTransition || estUnRechargement()) return;
    // Sur une fiche c'est la grande photo ; sur une liste, la vignette d'où
    // l'on vient — le retour arrière replie l'image sur son cadre.
    nommer(
        photoDeLaFiche() ?? photoDeLaCarte(window.navigation?.activation?.from?.url),
        e.viewTransition,
    );
});


/* ---------- lecture d'un Maine Coon ----------

   Deux entrees pour la meme information : les reperes poses sur la photo, et
   la liste des traits a cote. Survoler ou cliquer l'un allume l'autre.

   Les six textes sont ecrits cote serveur et deplies par defaut : sans
   JavaScript la page reste complete, elle perd seulement le pliage. On ne les
   replie donc qu'une fois le script en place. */

const lecteur = document.getElementById('lecture');

if (lecteur) {
    const reperes = [...lecteur.querySelectorAll('.repere')];
    const traits = [...lecteur.querySelectorAll('.traits button')];

    const montrer = (i) => {
        reperes.forEach((r) => r.setAttribute('aria-pressed', String(Number(r.dataset.point) === i)));
        traits.forEach((t) => {
            if (Number(t.dataset.point) === i) t.setAttribute('aria-current', 'true');
            else t.removeAttribute('aria-current');
        });
    };

    const depuis = (e) => {
        const b = e.target.closest('[data-point]');
        if (b) montrer(Number(b.dataset.point));
    };

    lecteur.addEventListener('click', depuis);
    lecteur.addEventListener('mouseover', depuis);
    lecteur.addEventListener('focusin', depuis);

    montrer(0);
}


/* ---------- hauteur réelle du bandeau ----------

   Le sommaire collant et les ancres doivent se placer juste sous le bandeau.
   Sa hauteur change entre le bureau et le téléphone, et selon la longueur de
   la marque : on la mesure plutôt que de la deviner. */

const mesurerBandeau = () => {
    if (!bandeau) return;
    document.documentElement.style.setProperty('--h-bandeau', `${Math.round(bandeau.offsetHeight)}px`);
};
mesurerBandeau();
window.addEventListener('resize', mesurerBandeau);

/* ---------- sommaire : la section en cours s'allume ----------

   Calcul direct plutot qu'un IntersectionObserver : on cherche la derniere
   section dont le haut est deja passe sous le bandeau. C'est trois lignes de
   geometrie, ca ne depend d'aucune heuristique de fenetre d'observation, et
   ca donne le meme resultat sur un saut d'ancre que sur un defilement lent.

   L'appel est cale sur requestAnimationFrame : la position n'est lue qu'une
   fois par image, jamais a chaque evenement de defilement. */

const sommaire = document.querySelector('.sommaire');

if (sommaire) {
    const liens = [...sommaire.querySelectorAll('a[href^="#"]')];
    const sections = liens
        .map((a) => ({ lien: a, cible: document.getElementById(decodeURIComponent(a.getAttribute('href').slice(1))) }))
        .filter((s) => s.cible);

    if (sections.length) {
        let enAttente = false;
        let dernier = null;

        const placer = () => {
            enAttente = false;

            // La ligne de reference : juste sous le bandeau et le sommaire.
            const ligne = (bandeau?.offsetHeight ?? 74) + sommaire.offsetHeight + 8;

            let courante = sections[0];
            for (const s of sections) {
                if (s.cible.getBoundingClientRect().top <= ligne) courante = s;
            }

            // En bas de page, la derniere section gagne meme si son haut est
            // reste au-dessus de la ligne : sinon elle ne s'allume jamais.
            if (window.innerHeight + window.scrollY >= document.body.scrollHeight - 4) {
                courante = sections[sections.length - 1];
            }

            if (courante === dernier) return;
            dernier = courante;

            sections.forEach(({ lien }) => lien.removeAttribute('aria-current'));
            courante.lien.setAttribute('aria-current', 'true');

            const rail = sommaire.querySelector('.sommaire-in') ?? sommaire;
            if (rail.scrollWidth > rail.clientWidth) {
                courante.lien.scrollIntoView({
                    inline: 'center', block: 'nearest',
                    behavior: reduit() ? 'auto' : 'smooth',
                });
            }
        };

        const demander = () => {
            if (enAttente) return;
            enAttente = true;
            requestAnimationFrame(placer);
        };

        /* ── il sort du bandeau passe l'introduction ──

           Trois cents pixels : le titre de la page a eu le temps de partir.
           Avant cela le sommaire n'aurait rien a annoncer — on est encore
           dans l'introduction, et la premiere section n'a pas commence. */

        const deplier = () => sommaire.classList.toggle('deplie', window.scrollY > 300);

        deplier();
        window.addEventListener('scroll', deplier, { passive: true });

        window.addEventListener('scroll', demander, { passive: true });
        window.addEventListener('resize', demander);
        placer();
    }
}

/* ---------- la liasse ----------
   Plusieurs registres dans le même cadre, un seul à l'écran. On passe de
   l'un à l'autre par les flèches, par les jalons, au clavier ou d'un glissé
   du pouce.

   En dessous de deux fiches on ne fait rien : la pile ordinaire est déjà la
   bonne réponse, et une flèche qui ne mène nulle part serait un mensonge. */
document.querySelectorAll('[data-liasse]').forEach((liasse) => {
    const scene = liasse.querySelector('.liasse-scene');
    const barre = liasse.querySelector('.liasse-barre');
    const volets = [...scene.children];
    if (volets.length < 2 || !barre) return;

    const jalons = barre.querySelector('.jalons');
    let index = 0;

    /* Le nom de chaque fiche est lu sur la fiche elle-même : il n'est écrit
       qu'à un seul endroit, il ne peut donc pas diverger. */
    volets.forEach((volet, i) => {
        const nom = volet.dataset.jalon
            || volet.querySelector('.entete b')?.textContent.trim()
            || `Fiche ${i + 1}`;

        const bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.textContent = nom;
        bouton.addEventListener('click', () => aller(i));

        const li = document.createElement('li');
        li.append(bouton);
        jalons.append(li);
    });

    const boutons = [...jalons.querySelectorAll('button')];

    const mesurer = () => {
        scene.style.setProperty('--h-volet', `${volets[index].offsetHeight}px`);
    };

    const poser = (cible) => {
        index = cible;

        volets.forEach((volet, i) => {
            volet.dataset.etat = i === cible ? 'actif' : (i < cible ? 'avant' : 'apres');
            volet.toggleAttribute('inert', i !== cible);
            volet.setAttribute('aria-hidden', String(i !== cible));
        });

        boutons.forEach((b, i) => {
            if (i === cible) b.setAttribute('aria-current', 'true');
            else b.removeAttribute('aria-current');
        });

        mesurer();
    };

    /* Les flèches tournent en boucle : avec deux fiches, un bouton grisé une
       fois sur deux se remarque plus que le passage lui-même. */
    const aller = (vers) => {
        const cible = (vers + volets.length) % volets.length;
        if (cible !== index) poser(cible);
    };

    barre.querySelectorAll('[data-pas]').forEach((bouton) => {
        bouton.addEventListener('click', () => aller(index + Number(bouton.dataset.pas)));
    });

    liasse.addEventListener('keydown', (e) => {
        if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
        if (e.target.closest('input, textarea, select')) return;
        e.preventDefault();
        aller(index + (e.key === 'ArrowRight' ? 1 : -1));
    });

    /* Le glissé du pouce, au doigt seulement : à la souris, un cliqué-glissé
       sert à sélectionner du texte. */
    let depart = null;
    scene.addEventListener('pointerdown', (e) => {
        depart = e.pointerType === 'mouse' ? null : { x: e.clientX, y: e.clientY };
    });
    scene.addEventListener('pointerup', (e) => {
        if (!depart) return;
        const dx = e.clientX - depart.x;
        const dy = e.clientY - depart.y;
        depart = null;
        if (Math.abs(dx) > 48 && Math.abs(dx) > Math.abs(dy) * 1.6) {
            aller(index + (dx < 0 ? 1 : -1));
        }
    });

    liasse.dataset.feuillete = '';
    barre.hidden = false;
    poser(0);

    /* Les transitions n'arrivent qu'une fois les polices posées : mesurée
       avec la police de secours, une fiche ne fait pas la même hauteur, et
       la scène se verrait alors corriger sa taille en glissant. */
    const armer = () => requestAnimationFrame(() => { mesurer(); liasse.dataset.pret = ''; });
    if (document.fonts?.ready) document.fonts.ready.then(armer);
    else armer();

    /* Une fiche peut changer de hauteur sans que la fenêtre bouge : polices
       enfin chargées, tableau qui passe en colonne, texte replié. */
    if ('ResizeObserver' in window) {
        const veille = new ResizeObserver(mesurer);
        volets.forEach((volet) => veille.observe(volet));
    }
    window.addEventListener('resize', mesurer);
});

/* ---------- les chiffres qui montent ----------

   Chaque <b data-compte="5"> part de zéro et monte jusqu'à sa valeur en
   entrant dans le champ. Le texte écrit dans la balise reste « 0 » : si le
   script ne passe pas, la page affiche un zéro plutôt qu'un trou — mais le
   filet de sécurité plus bas pose la vraie valeur dans tous les cas. */
{
    const compteurs = [...document.querySelectorAll('[data-compte]')];

    if (compteurs.length) {
        const format = new Intl.NumberFormat('fr-FR');
        const valeur = (el) => Number(el.dataset.compte);

        const poser = (el) => {
            const cible = valeur(el);
            el.textContent = Number.isFinite(cible) ? format.format(cible) : el.textContent;
            el.dataset.compte = '';
        };

        const monter = (el) => {
            const cible = valeur(el);
            if (!Number.isFinite(cible)) return;
            if (reduit() || cible === 0) { poser(el, cible); return; }

            el.dataset.compte = '';
            const duree = 760 + Math.min(cible, 80) * 11;
            const depart = performance.now();

            const pas = (t) => {
                const p = Math.min(1, (t - depart) / duree);
                /* Le compte ralentit avant de s'arrêter : un chiffre qui
                   s'immobilise net donne l'impression d'un saut. */
                el.textContent = format.format(Math.round(cible * (1 - (1 - p) ** 3)));
                if (p < 1) requestAnimationFrame(pas);
            };

            requestAnimationFrame(pas);
        };

        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver(
                (entrees) => entrees.forEach((e) => {
                    if (!e.isIntersecting) return;
                    io.unobserve(e.target);
                    monter(e.target);
                }),
                { rootMargin: '0px 0px -12% 0px' },
            );
            compteurs.forEach((el) => io.observe(el));
        } else {
            compteurs.forEach(poser);
        }

        /* Un chiffre resté à zéro est un mensonge, pas une animation ratée. */
        setTimeout(() => {
            document.querySelectorAll('[data-compte]:not([data-compte=""])').forEach((el) => {
                if (el.getBoundingClientRect().top < window.innerHeight * 1.2) poser(el);
            });
        }, 2400);
    }
}

/* ---------- l'aperçu statique ----------
   Sur la version publiée sans serveur, les formulaires portent data-apercu.
   Le clic reste possible — c'est la maquette, elle doit se manipuler — mais
   l'envoi est retenu et la note d'explication reprend la main. */
document.querySelectorAll('form[data-apercu]').forEach((formulaire) => {
    formulaire.addEventListener('submit', (e) => {
        e.preventDefault();

        const note = formulaire.parentElement.querySelector('.note-apercu');
        if (!note) return;

        note.classList.remove('bat');
        void note.offsetWidth;          // on redémarre l'animation
        note.classList.add('bat');
        note.scrollIntoView({ block: 'center', behavior: reduit() ? 'auto' : 'smooth' });
    });
});

/* ---------- le livre ----------

   Deux lectures du même contenu. En large, un feuillet porte deux pages et
   pivote sur son bord intérieur : on voit 02 et 03 côte à côte, comme dans
   un livre ouvert. En étroit, deux pages côte à côte deviennent illisibles,
   alors on en montre une seule et c'est elle qui pivote.

   Le même geste dans les deux cas : on appuie sur la moitié droite pour
   avancer, sur la gauche pour revenir. */
document.querySelectorAll('[data-livre]').forEach((bloc) => {
    const livre = bloc.querySelector('.livre');
    const feuillets = [...bloc.querySelectorAll('.feuillet')];
    const faces = [...bloc.querySelectorAll('.face')];
    const ou = bloc.querySelector('.livre-ou');
    const boutons = [...bloc.querySelectorAll('[data-pas]')];
    if (!livre || !feuillets.length) return;

    /* Le seuil : en dessous, une page de livre ferait moins de 420 px de
       large, ou le texte ne tient plus une mesure lisible. */
    const etroit = window.matchMedia('(max-width: 860px)');
    let simple = etroit.matches;
    let etat = 0;                     // feuillets tournés, ou page courante

    const bornes = () => (simple ? faces.length - 1 : feuillets.length);

    /* L'étagement des feuillets : le dernier tourné couvre la pile de
       gauche, le prochain à tourner couvre celle de droite. */
    const etager = () => {
        feuillets.forEach((f, i) => {
            f.style.zIndex = i < etat ? i + 1 : feuillets.length - i;
        });
    };

    const peindre = (precedent = null) => {
        if (simple) {
            faces.forEach((f, i) => {
                f.toggleAttribute('data-active', i === etat);
                f.toggleAttribute('inert', i !== etat);
            });

            /* La page qu'on quitte pivote, puis s'efface. Elle n'est retirée
               qu'à la fin de l'animation, sinon on la verrait disparaître
               d'un coup au lieu de tourner. */
            if (precedent !== null && precedent < etat && !reduit()) {
                const sortante = faces[precedent];
                sortante.setAttribute('data-sort', '');
                sortante.addEventListener('animationend',
                    () => sortante.removeAttribute('data-sort'), { once: true });
            }

            if (ou) ou.textContent = `Page ${etat + 1} sur ${faces.length}`;
        } else {
            feuillets.forEach((f, i) => {
                const tourne = i < etat;
                f.toggleAttribute('data-tourne', tourne);
            });

            etager();

            /* La page qui tourne prend son élan au-dessus des deux piles.

               Sans cela elle recevait son étage d'arrivée dès le premier
               instant : elle passait donc SOUS la page suivante alors qu'elle
               survolait encore la moitié droite, disparaissait, puis
               réapparaissait à gauche. C'est ce saut qu'on voyait, pas le
               tour de page. */
            if (precedent !== null && precedent !== etat && !reduit()) {
                const i = Math.min(precedent, etat);
                const f = feuillets[i];
                if (f) {
                    f.style.zIndex = '60';
                    f.dataset.anime = etat > precedent ? 'avant' : 'arriere';

                    /* Le livre entier est prévenu : c'est lui qui porte
                       l'ombre projetée dans la reliure. */
                    livre.dataset.anime = f.dataset.anime;

                    const reposer = () => {
                        delete f.dataset.anime;
                        if (!bloc.querySelector('.feuillet[data-anime]')) {
                            delete livre.dataset.anime;
                            etager();
                        }
                    };

                    /* Le filet de sécurité : une animation lancée dans un
                       onglet qu'on quitte aussitôt peut ne jamais annoncer sa
                       fin, et la page resterait alors au-dessus de tout. */
                    f.addEventListener('animationend', reposer, { once: true });
                    setTimeout(reposer, 1500);
                }
            }

            faces.forEach((f, i) => {
                const feuillet = Math.floor(i / 2);
                const visible = i % 2 === 0 ? feuillet === etat : feuillet === etat - 1;
                f.toggleAttribute('inert', !visible);
            });

            if (ou) {
                ou.textContent = etat === 0
                    ? 'Ouvrir le livre'
                    : (etat === feuillets.length
                        ? 'Fin du livre'
                        : `Pages ${etat * 2} et ${etat * 2 + 1}`);
            }
        }

        boutons.forEach((b) => {
            const pas = Number(b.dataset.pas);
            b.disabled = pas < 0 ? etat === 0 : etat >= bornes();
        });
    };

    const aller = (pas) => {
        const cible = Math.min(bornes(), Math.max(0, etat + pas));
        if (cible === etat) return;
        const precedent = etat;
        etat = cible;
        peindre(precedent);
    };

    boutons.forEach((b) => b.addEventListener('click', () => aller(Number(b.dataset.pas))));

    /* Appuyer sur le livre : la moitié droite avance, la gauche revient. */
    livre.addEventListener('click', (e) => {
        if (e.target.closest('a, button')) return;
        const r = livre.getBoundingClientRect();
        aller(e.clientX - r.left > r.width / 2 ? 1 : -1);
    });

    bloc.addEventListener('keydown', (e) => {
        if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
        e.preventDefault();
        aller(e.key === 'ArrowRight' ? 1 : -1);
    });

    /* Le glissé du pouce, au doigt seulement. */
    let depart = null;
    livre.addEventListener('pointerdown', (e) => {
        depart = e.pointerType === 'mouse' ? null : { x: e.clientX, y: e.clientY };
    });
    livre.addEventListener('pointerup', (e) => {
        if (!depart) return;
        const dx = e.clientX - depart.x;
        const dy = e.clientY - depart.y;
        depart = null;
        if (Math.abs(dx) > 44 && Math.abs(dx) > Math.abs(dy) * 1.5) aller(dx < 0 ? 1 : -1);
    });

    /* Passer d'une lecture à l'autre : on repart de l'endroit équivalent
       plutôt que du début, pour ne pas perdre le lecteur en tournant
       l'appareil. */
    const relire = () => {
        const desormais = etroit.matches;
        if (desormais === simple) return;
        etat = desormais ? Math.min(faces.length - 1, etat * 2) : Math.floor(etat / 2);
        simple = desormais;
        livre.toggleAttribute('data-simple', simple);
        peindre();
    };

    livre.toggleAttribute('data-simple', simple);
    peindre();

    /* Le change d'une media query suffit en theorie. En pratique il n'arrive
       pas toujours — fenetre pilotee, affichage emule — alors que le
       redimensionnement, lui, arrive toujours. Les deux sont ecoutes, et
       relire() ne fait rien quand rien n'a change. */
    etroit.addEventListener('change', relire);
    window.addEventListener('resize', relire);
});

/* ---------- les questions, une à la fois ----------

   L'attribut name sur <details> rend un groupe exclusif : le navigateur
   referme la question précédente tout seul. Les versions antérieures à
   Chrome 120, Safari 17.2 et Firefox 130 l'ignorent — pour elles, et pour
   elles seules, on refait le travail à la main. */
if (!('name' in document.createElement('details'))) {
    document.querySelectorAll('.questions').forEach((groupe) => {
        const volets = [...groupe.querySelectorAll('details')];

        volets.forEach((volet) => {
            volet.addEventListener('toggle', () => {
                if (!volet.open) return;
                volets.forEach((autre) => {
                    if (autre !== volet) autre.open = false;
                });
            });
        });
    });
}
/* ---------- le seuil : Olimpia, avant d'entrer ----------

   Un voile plein écran posé par-dessus le site à l'arrivée. On la regarde, on
   lit deux phrases, on entre.

   Deux mémoires, et pas une seule :

   - sessionStorage : refermé, il ne revient pas de la visite. C'est la règle
     par défaut, et elle suffit dans l'immense majorité des cas.
   - localStorage : la case « Ne plus afficher » cochée, il ne revient jamais.

   Les deux peuvent manquer — navigation privée, cookies bloqués, navigateur
   d'entreprise. Chaque lecture et chaque écriture est donc protégée : au pire
   le seuil se represente, jamais une page qui casse.

   ?banniere=1 sur l'adresse le force, quelles que soient ces mémoires : c'est
   le lien qu'on envoie pour le montrer à quelqu'un. */

const seuil = document.getElementById('seuil-olimpia');

if (seuil) {
    const CLEF = 'olimpia.seuil';

    const forcer = new URLSearchParams(window.location.search).has('banniere');

    const lu = (magasin) => {
        try {
            return window[magasin].getItem(CLEF) !== null;
        } catch {
            return false;
        }
    };

    const ecrire = (magasin) => {
        try {
            window[magasin].setItem(CLEF, 'vu');
        } catch {
            /* tant pis : il se representera */
        }
    };

    const jamais = seuil.querySelector('#seuil-olimpia-jamais');
    let rendu = null;                       // à qui rendre le focus en sortant
    let relais = null;                      // le minuteur qui fait tourner ses photos

    const refermer = () => {
        if (relais) window.clearInterval(relais);
        seuil.classList.remove('ouvert');
        document.documentElement.classList.remove('seuil-ouvert');

        // Coché, il ne revient jamais ; sinon, il ne revient pas de la visite.
        ecrire(jamais?.checked ? 'localStorage' : 'sessionStorage');

        window.setTimeout(() => {
            seuil.hidden = true;
            rendu?.focus?.();
        }, reduit() ? 0 : 650);
    };

    const ouvrir = () => {
        rendu = document.activeElement;

        seuil.hidden = false;
        document.documentElement.classList.add('seuil-ouvert');

        /* Un souffle pour que le navigateur prenne l'état de départ avant
           d'animer. Un délai plutôt qu'une image d'animation : dans un onglet
           en arrière-plan, requestAnimationFrame ne se déclenche pas. */
        window.setTimeout(() => {
            seuil.classList.add('ouvert');
            seuil.querySelector('.fermer')?.focus({ preventScroll: true });
        }, 40);
    };

    if (forcer || !(lu('localStorage') || lu('sessionStorage'))) {
        /* Tout de suite : un seuil qui arrive après coup n'est plus un seuil,
           c'est une fenêtre qui surgit au milieu de la lecture. */
        ouvrir();
    }

    /* ── ses photos se relaient ──

       Six secondes de pose, le temps de la regarder ; le fondu dure deux
       secondes et demie et se fait tout seul, en CSS. On s'arrête dès que le
       seuil est refermé : une boucle qui tourne sur un écran que personne ne
       regarde ne sert qu'à chauffer la machine. */

    const fonds = [...seuil.querySelectorAll('.fond')];

    /* Aller chercher une photo differee. On le fait un tour a l'avance : elle
       a six secondes pour arriver, ce qui est large, et rien n'est telecharge
       pour une photo qu'on ne verra pas si le seuil se referme avant. */
    const charger = (img) => {
        if (!img || !img.dataset.src) return;

        if (img.dataset.srcset) {
            img.sizes = img.dataset.sizes ?? '100vw';
            img.srcset = img.dataset.srcset;
        }

        img.src = img.dataset.src;
        delete img.dataset.src;
    };

    if (fonds.length > 1 && !reduit()) {
        let courante = 0;
        let etage = 1;

        charger(fonds[1]);                  // la suivante, des maintenant

        relais = window.setInterval(() => {
            const precedente = courante;
            courante = (courante + 1) % fonds.length;

            /* La suivante monte d'un etage et se fond PAR-DESSUS la courante,
               qui reste opaque en dessous jusqu'au bout. Les faire se croiser
               a cinquante pour cent chacune laissait un quart de transparence
               au milieu du passage, et on voyait le site au travers. */
            fonds[courante].style.zIndex = ++etage;
            fonds[courante].classList.add('vue');

            window.setTimeout(() => fonds[precedente].classList.remove('vue'), 2600);

            charger(fonds[(courante + 1) % fonds.length]);
        }, 6000);
    }

    seuil.querySelector('.fermer')?.addEventListener('click', refermer);
    seuil.querySelector('.entrer')?.addEventListener('click', refermer);

    // Lire son histoire, c'est sortir aussi : on part sur sa page, le voile
    // n'a plus rien à faire derrière.
    seuil.querySelector('.lire')?.addEventListener('click', () => {
        ecrire(jamais?.checked ? 'localStorage' : 'sessionStorage');
    });

    // Cliquer à côté de la feuille referme, comme partout ailleurs.
    seuil.querySelector('.voile')?.addEventListener('click', refermer);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && seuil.classList.contains('ouvert')) refermer();
    });
}
