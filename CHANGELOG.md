# Changelog
All notable changes to this project will be documented in this file.

## [4.0.7] - 2021-??-??
### Evolution
- symfony 5.3.12
- Script pour regeneré tous les zip et pdf complet d'une structure. (#169)

### Correction
- Crash si plusieur fois un nouveau type dans le csv utilisateur (#188)
- A la création d'une structure Erreur 500 si meme suffix (#187)


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

