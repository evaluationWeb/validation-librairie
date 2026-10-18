---
title: Apercu
---

# Apercu de Mithridatem Validation

La librairie Mithridatem Validation propose une validation basee sur les attributs natifs de PHP 8.2 et plus. Il suffit d'annoter vos entites ou DTO avec les contraintes puis d'executer le `Validator`. Le validateur retourne toutes les erreurs dans un tableau `array<string, list<string>>` indexé par propriété, ou `[]` si l'objet est valide.

## Contenu de la librairie

- **Validator** : inspecte les attributs des proprietes et declenche chaque contrainte rencontree.
- **Contraintes** : `NotBlank`, `Length`, `Email`, `Pattern`, `Negative`, `NegativeOrZero`, `Positive` et `PositiveOrZero` sont fournies. Il est possible d'ajouter vos propres contraintes en implementant `Mithridatem\Validation\Contracts\PropertyConstraint`.
- **Erreurs** : messages regroupés par propriété, sans interruption à la première contrainte échouée.

Toutes les classes se trouvent dans l'espace de noms `Mithridatem\Validation`.

## Installation

```bash
composer require mithridatem/validation
```

## Fonctionnement

1. Creez une classe metier (entite ou DTO) et ajoutez les attributs de contraintes sur ses proprietes.
2. Instanciez `Mithridatem\Validation\Validator`.
3. Appelez `validate($objet)`.
4. Récupérez le tableau retourné et affichez les messages de chaque propriété.

```php
use Mithridatem\Validation\Validator;
use Mithridatem\Validation\Attributes\NotBlank;
use Mithridatem\Validation\Attributes\Length;

final class Utilisateur
{
    #[NotBlank]
    #[Length(min: 3, max: 40)]
    private ?string $prenom = null;
}

$validator = new Validator();
$errors = $validator->validate(new Utilisateur());
// ['prenom' => ['La propriete "prenom" ne peut pas etre nulle.']]
```

## Reference des contraintes

| Contrainte | Arguments | Description |
|------------|-----------|-------------|
| `NotBlank` | aucun     | Refuse les valeurs `null` ou les chaines vides |
| `Length`   | `?int $min`, `?int $max` | Verifie que la longueur de la chaine respecte les bornes |
| `Email`    | aucun     | Valide le format via `FILTER_VALIDATE_EMAIL` |

### Creer une contrainte personnalisee

Implémentez `PropertyConstraint::validate(): array` et retournez une liste de messages, ou `[]` en cas de succès.

```php
use Attribute;
use Mithridatem\Validation\Contracts\PropertyConstraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class NombrePositif implements PropertyConstraint
{
    public function validate(string $property, mixed $value): array
    {
        if ($value !== null && (!is_numeric($value) || $value <= 0)) {
            return [sprintf('%s doit etre positif.', $property)];
        }

        return [];
    }
}
```

## Tests

Une suite PHPUnit est fournie pour verifier le comportement :

```bash
composer test
```

## Checklist de publication

1. Mettre a jour `CHANGELOG.md` (si present) et documenter les ruptures de compatibilité. La version est déterminée par le tag Git.
2. Executer `composer test`.
3. Creer un tag (`git tag v0.x.x && git push origin v0.x.x`).
4. Verifier que Packagist (ou votre registre prive) a bien recupere le tag.

## Pour aller plus loin

- [Documentation officielle des attributs PHP](https://www.php.net/manual/en/language.attributes.overview.php)
- [Documentation PHPUnit](https://phpunit.de/documentation.html)
