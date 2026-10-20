# Contribuer a Mithridatem Validation

Merci de votre interet pour l'amelioration de cette librairie !

## Configuration du projet

```bash
git clone https://github.com/evaluationWeb/validation-librairie.git
cd validation-librairie
composer install
```

## Routines de developpement

- Ecrire des tests PHPUnit pour toute nouvelle fonctionnalite ou correction.
- Lancer `composer test` avant de proposer une modification.
- Pour une contrainte personnalisée, retourner une liste d’objets `ValidationError` ou `[]` avec des codes explicites et vérifier les propriétés, codes, messages, paramètres et le cumul des erreurs dans les tests. Voir [le guide](docs/overview.md).
- Respecter le style de code existant (PSR-12).

## Proposer une contribution

1. Forkez le projet puis creez une branche.
2. Effectuez vos modifications et ajoutez des tests.
3. Mettez a jour la documentation si necessaire.
4. Soumettez une Pull Request detaillee.

## Signalement d'anomalies

Ouvrez une issue en precisant :

- La version utilisee.
- Les etapes pour reproduire.
- Le message d'erreur complet.

## Licence

En contribuant, vous acceptez que votre code soit publie sous licence MIT.
