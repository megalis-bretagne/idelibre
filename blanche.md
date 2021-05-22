Préambule
=========

Ce document décrit la procédure de personnalisation en "marque blanche" de comélus en utilisant
les variables d'environement et en montant des fichiers dans le docker-compose.yml


Title
=======
Le title est géré par la variable d'environement PRODUCT_NAME



Text du footer
=========

Le texte du footer ce décompose en 3 parties  
{Nom du produit} {version du produit} (c) {nom de l'éditeur}

Les variables d'environement associées sont
-  PRODUCT_NAME
-  VERSION
-  PRODUCT_EDITOR


Css personalisé
========
Il est possible de personnalisé le css en écrasant les valeurs par de nouvelles, pour cela il faut monter un fichier contenant les nouvelles règles
/var/www/comelus/public/assets/override.css

Images personalisée
========
De la meme maniere que le css on peut remplacer les images. les chemins sont les suivant

- Logo Libriciel page login :
  /var/www/comelus/public/assets/img/logo_Libriciel.png

- Logo comelus page login
  /var/www/comelus/public/assets/img/comelus.svg

- Logo libriciel blanc footer
  /var/www/comelus/public/assets/img/Libriciel_white_h24px.png

- Logo comelus header
  /var/www/comelus/public/assets/img/ce-200px-blanc.png


Favicon
======
Encore une fois il faut monter le nouveau favicon au path suivant
/var/www/comelus/public/favicon
