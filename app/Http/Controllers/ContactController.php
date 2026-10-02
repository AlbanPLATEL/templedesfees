<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessage;
use App\Models\ContactMessage;
use App\Models\Review;
use App\Models\Setting;

class ContactController extends Controller
{
    public function show()
    {
        return view('pages.contact', [
            'objets' => ContactMessage::OBJETS_PROPOSES,
            'points' => config('chatterie.carte'),
            'avis'   => Review::publies()->get(),
            'itineraire' => self::itineraires(),
        ]);
    }

    /**
     * Les liens d'itineraire, un par application de navigation.
     *
     * Tous visent la COMMUNE et non l'adresse exacte : la page annonce que
     * l'adresse est communiquee au rendez-vous, et un itineraire porte-a-porte
     * la publierait d'un clic. Chaque lien peut etre remplace par un reglage,
     * par exemple par la fiche Google de l'elevage.
     *
     * @return array<string,string>
     */
    private static function itineraires(): array
    {
        $commune = trim(Setting::get('elevage.ville', 'Lapeyrouse-Mornay').' '
            .Setting::get('elevage.code_postal', '26210').' France');

        return [
            'commune' => $commune,
            'google'  => Setting::get('contact.itineraire_google')
                ?: 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($commune),
            'waze'    => Setting::get('contact.itineraire_waze')
                ?: 'https://www.waze.com/ul?navigate=yes&q='.urlencode($commune),
        ];
    }

    public function store(StoreContactMessage $request)
    {
        $message = ContactMessage::create($request->safe()->except('rgpd', 'site'));

        // TODO brancher la notification a l'elevage une fois le SMTP configure.

        return redirect()
            ->route('contact')
            ->with('succes', "Merci {$message->prenom}, votre message est bien arrivé. Nous vous répondons sous 48 heures.")
            ->withFragment('formulaire');
    }
}
