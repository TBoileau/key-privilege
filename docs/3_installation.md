# Installation

[Retour au sommaire](index.md)

## Récupérer les sources du projet
Pensez à `fork` le projet, pensez à lire le [guide de contribution](/CONTRIBUTING.md).
```
git clone https://github.com/<your-username>/<repo-name>
```

## Pré-requis
* PHP >= 8.0
* Extensions PHP :
    * ctype
    * iconv
    * json
    * xml
    * intl
    * mbstring
* composer
* MySQL/MariaDB
* NodeJS >= 14.4
* npm >= 6.14

## Installer les dépendances
Dans un premier temps, positionnez vous dans le dossier du projet :
```
cd <repo-name>
```

Exectuer la commande suivante
```
make install
```

## Environnements
Pour faire fonctionner le projet sur votre machine, pensez à configurer les différentes environnements. Une documentation sur ce sujet est présent [ici](4_environnements.md).

## Initialiser les base de données
En commençant par l'environnement `test`
```
make database-test
```

Puis l'environnement `dev`:
```
make database-dev
```

Si vous le souhaitez, vous pouvez aussi injecter des fixtures en plus de mettre en place la base de données :

Pour l'environnement `test`
```
make fixtures-test
```

Puis l'environnement `dev`:
```
make fixtures-dev
```

## Lancer le serveur en local
Il est nécessaire d'avoir installé le [binaire de symfony](https://symfony.com/download).
```
symfony serve
```

## Gestion des ressources externes (css, js)
Compilez une seule fois les fichiers en environnement de développement :
```
npm run dev
```

Activez la compilation automatique :
```
npm run watch
```

Compilez les fichiers pour la production :
```
npm run build
```
