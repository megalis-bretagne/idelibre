# Changelog
All notable changes to this project will be documented in this file.


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

