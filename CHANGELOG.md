# Changelog
All notable changes to this project will be documented in this file.

## [4.2.0] - 2023-xx-xx

### Evolution
- Docker passage  en Ubuntu 22:04Ajout
- Node 12 -> 16
- php 8 -> php8.1
- Concatenation -> pdftk to pdfunite
- Gestion des fichiers 'encrypted' avec qpdf
- Mise en place d'un système permettant de bloquer le brut force
- Mise en place du calcul d'entropie sur les mot de passes
- Un mail d'initialisation du mot de passe est envoyé lors de la création d'un compte superadmin et admin de groupe.
- Délai de purge de donnée configurable par entité (#290)
- Lors de la création d'un utilisateur dans une structure, il existe deux possiblité concernant le mot de passe :
  - soit un mail d'initialisation du mot de passe est envoyé
  - soit il est possible de définir le mot de passe
- Mise en place d'envoie d'un mail de réinitialisation du mot de passe.
- Mise en place de l'invalidation des mots de passe de tous les utilisateurs de la structure.

### Correction
- la suppression d'une structure supprime bien tout ce qui lui est associée


### Suppression
- Lors de la création / modification d'un admin de group ou d'un superadmind, il n'est plus possible de définir le mot de passe du nouveau utilisateur.
- Lors de la modification d'un admin de group ou d'un superadmind, il n'est plus possible de changer l'adresse e-mail de l'utilisateur.

## [4.1.4] - 2022-08-31

### Evolution
- symfony 5.4.12
- possibilité de cacher la bannière de gauche de l'écran de login

### Correction
- Erreur d'adresse et de siret de l'éditeur dans la notice rgpd


## [4.1.3] - 2022-07-22

### Evolution
- symfony 5.4.10

### Correction
- Erreur minimum.sql lors d'une fresh install

## [4.1.2] - 2022-03-23

### Evolution
- symfony 5.4.6
- Fonctionnalité de stats iso 3.x


## [4.1.1] - 2022-03-18

### Evolution
- Script de purge
- script de creation des repertoires de token post migration
- Récupération de l'api32


### Correction
 - Envoi de sms par lot via lsmessage (#264) 

## [4.1] - 2022-03-01
### Evolution
- nouveau systeme d'authentification
- Php8
- Dans modele de mail replacer la denomination balises par variables (#165)
- Les utilisateurs personnel administratif et invité peuvent etre crée via csv (#162)
- La civilité peut etre renseignée via csv (#161)
- Un admin de groupe ne peut plus supprimer une structure (#160)
- Depuis le client web Nommage plus explicite  du fichier zip de la séance (#154)
- Ajout de la reecriture de l'host dans le vhost
- Suppression de la limite du nombre de carractere dans la description du connecteur comelus (#176)
- Champs de publipostage disponible dans la description du connecteur comelus (#164)
- Client web, nouveau logo idelibre
- Afficher / masquer le mot de passe (#84)
- Alerte si jamais le navigateur ne permet pas de télécharger les fichiers (#172)
- Politique de confidentialité configurable par structure (#113)
- Assignation de bon onwer sur le repertoire file monté sur le docker si il n'est pas correcte (#178)
- Renommage des zip et pdf complet de la seance au format : nomSeance_DateSeance (#192)
- [Technique] remplacement de swift_mailer par mailer (#194)

### Nouveautés
- Fichier de rendez vous avec l'envoi du mail de convocation (#152)
- Jeton d'horodatage lors de l'ajout de document dans une séance déja envoyée (#156)
- Apiv2 (avec recupération des jetons d'horodatage)
- Gestion des clés d'api
- Ajout des thèmes via csv (#179)
- Mot de passe oublié client web (#158)


### Correction  
- Format de la date lors de la création et modification d'une séance (#167)   
- mise à jour du nom complet des sous themes lors de la mise à jour du nom d'un theme (#159)
- Docker : Creation des repertoires au demarage s'ils n'existent pas (#178)
- supression de l'ancienne orthographe d'idelibre (#247)
- Reception du mail de convocation 2 fois (#246)
- Dupplicata d'accusé de réception (#244)
- Ordre chronologique navigateur (#235)
- tout Type de séance visible pour gestionnaire dans le tableau de bord (#232)
- desactivation du connecteur comelus (#232)
- Enregistrement de la liste de difusion comelus (#225)
- Connecteur ls message : message d'erreur si champs expéditeur supérieur à 11 caractères (#210)

## [4.0.6] - 2021-10-04
### Evolution
- Nombre de fichiers maximum par envoi est maintenant de 600 (#151)
- Temps maximum d'execution d'un script est de 300s
- symfony 5.3.9 (conservation de doctrine orm en 2.9 pour eviter erreur sur les arbres)

### Correction
- Impossibilité d'ajouter 2 fois de suite le meme fichier sous chrome et edge (#150)
- correction de la source du fichier d'initialisation des certificats

## [4.0.5] - 2021-09-16
### Evolution
- Connecteur legacy : accepte les array et les string pour la clé acteurs-convoqués
- Connecteur legacy : Si un username généré existe déja on considère que c'est le même élu
- Redirections des anciennes routes vers les nouvelles pour eviter les 404 en favoris (srvusers/login et idelibre_client3)  



## [4.0.4] - 2021-09-06
### Correction
- L'aperçu de l'email de template en format text echappe maintenant les balises html (#132)
- Correction de l'impossibilité de supprimer un acteur s'il a une annotation (#137)
- Selectionner tous les élus dans l'association au type (#136)
- Suppression par lot (#135)
- Fautes d'orthographes (#133)
- Les balises html sont visibles dans l'appercu des templates d'email si on est en mode texte (#132)


### Evolutions
- Déarchiver une séance (#138)
- Si on ajoute une invitation apres l'enregistrement cela ajoute bien les administratifs et invites par defaut (#130)
- Label des champs fichiers dans la création d'une séance (#134)
- Mise à jour symfony 5.3.7


## [4.0.3] - 2021-08-27
### Evolutions
- mise en place de sentry
- enlever le suffix dans le select de l'association des utilisateurs aux types (#125)
- rendre configurable si un groupe peut creer ou non des structures (#127)
- rendre le connecteur WD non snessible à la casse et aux espaces en périphérie (#126)



## [4.0.2] - 2021-08-25

### Corrections
- impossibilité pour un gestionnaire de seance de creer une seance (erreur 500)
- impossibilité de supprimer un utilisateur rapporteur d'un projet (erreur 500)   

### Evolutions
- Mise à jour symfony 5.3.6

## [4.0.1] - 2021-08-24

### Evolutions
- Mise à jour du docker-compose

### Corrections
- Correction du calcul du hash du legacy password coté client (nodejs)

