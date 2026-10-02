<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessage extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objet'     => ['required', Rule::in(array_keys(ContactMessage::OBJETS_PROPOSES))],
            'prenom'    => ['required', 'string', 'max:80'],
            'nom'       => ['nullable', 'string', 'max:80'],
            'email'     => ['required', 'email:rfc', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'message'   => ['required', 'string', 'min:10', 'max:4000'],
            'rgpd'      => ['accepted'],
            'site'      => ['prohibited'],   // piege a robots
        ];
    }

    public function messages(): array
    {
        return [
            'prenom.required'  => 'Indiquez votre prénom pour que nous sachions à qui répondre.',
            'email.required'   => 'Nous avons besoin de votre email pour vous répondre.',
            'email.email'      => 'Cette adresse email ne semble pas valide.',
            'message.required' => 'Écrivez-nous quelques mots, même brefs.',
            'message.min'      => 'Votre message est un peu court pour qu’on puisse vous aider.',
            'rgpd.accepted'    => 'Cochez l’accord sur les données personnelles pour pouvoir envoyer le message.',
            'site.prohibited'  => 'Votre message n’a pas pu être envoyé.',
        ];
    }
}
