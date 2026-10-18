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
$errors = $validator->validate(new Utilisateur());

if ($errors !== []) {
    foreach ($errors as $property => $messages) {
        foreach ($messages as $message) {
            echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<br>';
        }
    }
}
```

`validate()` retourne toutes les erreurs regroupées par propriété :

```php
[
    'prenom' => ['La propriete "prenom" ne peut pas etre nulle.'],
]
```

Une propriété peut contenir plusieurs messages : toutes ses contraintes sont exécutées.
Un objet valide retourne `[]`. Les propriétés sans erreur sont absentes du tableau.
Les erreurs ne sont pas conservées entre deux appels.

## Migration depuis les exceptions

- Remplacez les blocs `try/catch (ValidationException)` par la récupération du tableau retourné par `validate()` et un test `$errors !== []`.
- Les contraintes personnalisées doivent désormais implémenter `validate(string $property, mixed $value): array` et retourner une liste de messages (`list<string>`), ou `[]` si la valeur est valide.
- Les contraintes fournies ne lèvent plus de `ValidationException`. Cette classe reste disponible, mais est dépréciée.
- Les erreurs de configuration (bornes de `Length` invalides, expression régulière invalide) continuent de lever `InvalidArgumentException`.
- `null` reste accepté par toutes les contraintes sauf `NotBlank`. Une propriété typée non initialisée est traitée comme `null`.

Ce changement du contrat public nécessite d'adapter les applications et les contraintes personnalisées avant la mise à jour.

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
