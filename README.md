# Mithridatem Validation

Librairie de validation basée sur les attributs PHP. Annotez vos entités ou DTO, puis récupérez **toutes les erreurs dans un tableau regroupé par propriété**, sans interruption à la première erreur.

## Prérequis et installation

- PHP 8.2 ou supérieur ;
- extension PHP `mbstring` ;
- Composer.

```bash
composer require mithridatem/validation
```

## Démarrage rapide

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Mithridatem\Validation\Attributes\Email;
use Mithridatem\Validation\Attributes\Length;
use Mithridatem\Validation\Attributes\NotBlank;
use Mithridatem\Validation\Validator;

final class Utilisateur
{
    #[NotBlank]
    #[Length(min: 3, max: 50)]
    public ?string $prenom = '';

    #[NotBlank]
    #[Email]
    public ?string $email = 'adresse-invalide';
}

$utilisateur = new Utilisateur();
$validator = new Validator();
$errors = $validator->validate($utilisateur);
```

Résultat :

```php
[
    'prenom' => [
        'La propriete "prenom" ne peut pas etre vide.',
        'La propriete "prenom" doit contenir au moins 3 caracteres.',
    ],
    'email' => [
        'La propriete "email" doit contenir une adresse email valide.',
    ],
]
```

Chaque propriété peut contenir plusieurs messages. Les propriétés valides sont absentes du tableau.

Après correction des valeurs, le même validateur retourne un tableau vide :

```php
$utilisateur->prenom = 'Mathieu';
$utilisateur->email = 'mathieu@example.com';

$errors = $validator->validate($utilisateur); // []
```

Les erreurs d'un appel précédent ne sont pas conservées.

## Exploiter les erreurs

```php
$errors = $validator->validate($utilisateur);

if ($errors === []) {
    // Poursuivre le traitement : enregistrement, appel d'un service, etc.
} else {
    foreach ($errors as $property => $messages) {
        foreach ($messages as $message) {
            echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br>';
        }
    }
}
```

Pour afficher uniquement les erreurs d'un champ, utilisez `$errors['email'] ?? []`.

## Contraintes disponibles

Toutes les classes d'attributs se trouvent dans `Mithridatem\Validation\Attributes`.

| Contrainte | Exemple | Règle |
| --- | --- | --- |
| `NotBlank` | `#[NotBlank]` | Refuse `null`, une chaîne vide ou composée uniquement d'espaces |
| `Length` | `#[Length(min: 3, max: 50)]` | Vérifie la longueur ; chaque borne est facultative et inclusive |
| `Email` | `#[Email]` | Vérifie le format avec `FILTER_VALIDATE_EMAIL` |
| `Pattern` | `#[Pattern(pattern: '/^[A-Z]{3}$/')]` | Exige une chaîne correspondant à l'expression régulière |
| `Negative` | `#[Negative]` | Exige une valeur numérique strictement inférieure à zéro |
| `NegativeOrZero` | `#[NegativeOrZero]` | Exige une valeur numérique inférieure ou égale à zéro |
| `Positive` | `#[Positive]` | Exige une valeur numérique strictement supérieure à zéro |
| `PositiveOrZero` | `#[PositiveOrZero]` | Exige une valeur numérique supérieure ou égale à zéro |

À l'exception de `NotBlank`, toutes les contraintes acceptent `null`. Ajoutez `#[NotBlank]` pour rendre une propriété obligatoire. Une propriété typée non initialisée est traitée comme `null`. Les propriétés privées et protégées sont également inspectées.

## Migration depuis les exceptions

Cette évolution modifie le contrat public :

- `Validator::validate(object $entity): array` retourne `array<string, list<string>>` ;
- remplacez les blocs `try/catch (ValidationException)` par la récupération du tableau et un test `$errors !== []` ;
- adaptez vos contraintes personnalisées : `validate(string $property, mixed $value): array` retourne une liste de messages ou `[]` ;
- `ValidationException` reste disponible, mais est dépréciée et n'est plus levée par les contraintes fournies ;
- une configuration invalide de `Length` ou de `Pattern` continue de lever `InvalidArgumentException`.

Consultez le [guide détaillé](docs/overview.md) pour les exemples de formulaire, de réponse JSON et de contrainte personnalisée.

## Développement

```bash
git clone https://github.com/evaluationWeb/validation-librairie.git
cd validation-librairie
composer install
composer test
```

Voir le [guide de contribution](CONTRIBUTING.md) et le [changelog](CHANGELOG.md).

## Licence

Le projet est distribué sous licence MIT. Voir [LICENSE](LICENSE).
