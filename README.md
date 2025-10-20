# Mithridatem Validation

Librairie de validation basee sur les attributs PHP 8.2+. Elle permet d'annoter vos entites avec des contraintes et de les verifier via la classe `Validator`.

## Installation

```bash
composer require mithridatem/validation
```

## Utilisation

```php
use Mithridatem\Validation\Validator;
use Mithridatem\Validation\Attributes\NotBlank;
use Mithridatem\Validation\Attributes\Length;

class Utilisateur
{
    #[NotBlank]
    #[Length(min: 3, max: 50)]
    private string $prenom;
}

$validator = new Validator();
$validator->validate(new Utilisateur());
```

En cas d'echec, le `Validator` leve une `Mithridatem\Validation\Exception\ValidationException`. Interceptez cette exception pour afficher ou journaliser le message.

## Contraintes disponibles

- `NotBlank` : interdit les valeurs nulles ou les chaines vides
- `Length` : impose une longueur minimale et/ou maximale
- `Email` : valide une adresse electronique avec `FILTER_VALIDATE_EMAIL`
- `Pattern` : impose un pattern regex à une string
- `Negative` : impose une valeur négative à un entier  
- `NegativeOrZero` : impose une valeur négative ou égale à zéro à un entier  
- `Positive` : impose une valeur positive à un entier  
- `PositiveOrZero` : impose une valeur positive ou égale à zéro à un entier  

## Developpement

```bash
composer install
composer test
```

## Licence

Le projet est distribue sous licence MIT. Voir [LICENSE](LICENSE).
