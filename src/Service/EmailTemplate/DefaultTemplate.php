<?php

namespace App\Service\EmailTemplate;

class DefaultTemplate
{
    public const CONVOCATION = "Bonjour #civilite# #nom# #prenom#, </br>
</br>
Vous êtes invité(e) au #typeseance# en date du #dateseance# à #heureseance# qui se déroulera #lieuseance#.</br>
</br>
Merci de télécharger les documents de séance et d'indiquer si vous serez présent(e) ou non via l'adresse suivante : <a href='https://#urlseance#'>https://#urlseance#</a></br>
</br>
Cordialement,";

    public const INVITATION = "Bonjour #civilite# #nom# #prenom#, </br>
</br>
Vous êtes invité(e) au #typeseance# en date du #dateseance# à #heureseance# qui se déroulera #lieuseance#.</br>
</br>
Pour vous connecter à la séance, veuillez vous rendre à l'adresse suivante : <a href='https://#urlseance#'>https://#urlseance#</a></br>
</br>
Cordialement,";

    public const RECAPITULATIF = 'Bonjour #civilite# #nom# #prenom#, </br>
</br>
Ce mail est un récapitulatif des présents/absents pour les différentes séances en cours.</br>
</br>
#recapitulatif#
</br>
</br>                   
Cordialement,';
}
