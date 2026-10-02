<?php

/*
    Les conditions de l'acompte.

    TEXTE DE DEPART, a faire valider par l'eleveuse avant la mise en ligne.
    Il est ecrit a partir de ce que le site annonce deja dans le parcours
    d'adoption, pour que les deux ne se contredisent jamais : douze semaines au
    plus tot, un contrat ecrit, un acompte, des nouvelles chaque semaine.

    Il se modifie depuis le back-office, dans Le site > Reglages : c'est une
    valeur de reglage et non un texte fige dans une vue, precisement pour que
    l'eleveuse puisse le reprendre avec son veterinaire ou son conseil sans
    demander une intervention.

    Deux points meritent une relecture attentive avant publication :
    l'acompte acquis en cas de renoncement, et le droit de retractation d'une
    vente a distance.

    ATTENTION. Ce texte a ete ecrit quand le parcours imposait une visite avant
    toute reservation : c'est ce deplacement qui devait empecher la vente d'etre
    conclue a distance. L'elevage ne recoit plus de visite avant le depart, pour
    une raison sanitaire tenant aux chatons non vaccines. La reservation et
    l'acompte se nouent donc desormais sans que personne se soit deplace.

    La question du droit de retractation de quatorze jours se pose donc
    franchement, et elle n'est pas theorique : elle porte sur un chaton retire de
    la vente pendant des semaines. Elle est a trancher par un professionnel du
    droit avant la mise en ligne, pas apres.
*/

return <<<'TEXTE'
L’acompte réserve un chaton précis au nom d’une famille précise. Dès son encaissement, le chaton est retiré de la vente et n’est plus proposé à personne d’autre.

**Montant et imputation.** Le montant est fixé au moment de la réservation et figure sur la page de paiement. Il vient en déduction du prix du chaton : ce n’est pas un supplément. Le solde est réglé le jour du départ.

**Délai.** L’acompte est à verser avant la date indiquée sur la page de réservation. Passé ce délai sans règlement, la réservation devient caduque et le chaton est de nouveau proposé.

**Si la famille renonce.** L’acompte reste acquis à l’élevage. Il couvre les semaines pendant lesquelles le chaton n’a été proposé à personne d’autre, ainsi que les frais déjà engagés pour lui.

**Si l’élevage ne peut pas céder le chaton.** En cas de maladie, de décès ou de toute cause tenant à l’élevage, l’acompte est intégralement remboursé sous quinze jours. La famille peut aussi choisir de le reporter sur une portée suivante.

**Entre la réservation et le départ.** Vous recevez des nouvelles et des photos chaque semaine, et nous sommes joignables autant que vous le souhaitez, en visio si vous préférez. Les chatons ne reçoivent pas de visite tant qu’ils ne sont pas vaccinés : c’est le jour du départ que vous rencontrez le vôtre.

**Le départ.** Le chaton part à douze semaines au plus tôt, identifié, primo-vacciné et rappelé, vermifugé, avec son certificat vétérinaire de bonne santé, son pedigree LOOF et un contrat de cession signé.

Ces conditions s’ajoutent au contrat de cession, remis et signé au moment de la réservation.
TEXTE;
