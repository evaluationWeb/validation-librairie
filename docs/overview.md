---
title: Guide de validation
---

# Guide de Mithridatem Validation

La librairie utilise les attributs PHP pour valider les propriétés d'une entité ou d'un DTO. Le validateur retourne toutes les erreurs sous forme d'objets immuables `ValidationError`, regroupés par propriété.

## Installation et premier exemple

PHP 8.2+, l'extension `mbstring` et Composer sont nécessaires.

```bash
composer require mithridatem/validation
```

Le [README](../README.md) contient un exemple complet avec chargement de l'autoloader et plusieurs contraintes.

## Contrat du validateur

```php
/** @return array<string, list<ValidationError>> */
public function validate(object $entity): array;
```

Pour chaque propriété inspectée, le validateur lit les attributs implémentant `PropertyConstraint`, instancie les contraintes et rassemble leurs objets `ValidationError`.

- Toutes les contraintes sont exécutées tant qu'aucune erreur de configuration ou de programmation n'interrompt l'exécution.
- Plusieurs erreurs d'une même propriété sont ajoutées à sa liste d'objets.
- Une propriété sans erreur n'apparaît pas dans le résultat.
- Un objet valide, ou sans contrainte, retourne `[]`.
- Les erreurs ne sont pas conservées entre deux appels.
- Les propriétés publiques, protégées et privées peuvent être validées.
- Une propriété typée non initialisée est traitée comme `null`.

La validation porte sur les propriétés inspectées : elle ne valide pas récursivement les objets ou les collections qu'elles contiennent.

Exemple de résultat :

```php
[
    'prenom' => [
        new \Mithridatem\Validation\ValidationError('prenom', 'La propriete "prenom" ne peut pas etre vide.', 'not_blank.blank'),
        new \Mithridatem\Validation\ValidationError('prenom', 'La propriete "prenom" doit contenir au moins 3 caracteres.', 'length.too_short', ['min' => 3]),
    ],
    'email' => [
        new \Mithridatem\Validation\ValidationError('email', 'La propriete "email" doit contenir une adresse email valide.', 'email.invalid'),
    ],
]
```

## Objet ValidationError et codes d’erreur

`Mithridatem\Validation\ValidationError` est une classe `final readonly`, avec quatre propriétés publiques :

```php
public function __construct(
    public string $property,
    public string $message,
    public string $code,
    public array $parameters = [],
);
```

`parameters` contient les métadonnées nécessaires au traitement de l'erreur. Les contraintes fournies n'y ajoutent pas la valeur saisie. Les codes suivants permettent d'identifier le motif de l'échec indépendamment du message affiché :

| Contrainte | Code | Paramètres |
| --- | --- | --- |
| `NotBlank` | `not_blank.null` | Aucun |
| `NotBlank` | `not_blank.blank` | Aucun |
| `Length` | `length.too_short` | `min` |
| `Length` | `length.too_long` | `max` |
| `Email` | `email.invalid` | Aucun |
| `Pattern` | `pattern.invalid_type` | Aucun |
| `Pattern` | `pattern.no_match` | `pattern` |
| `Negative` | `negative.not_numeric` | Aucun |
| `Negative` | `negative.not_negative` | Aucun |
| `NegativeOrZero` | `negative_or_zero.not_numeric` | Aucun |
| `NegativeOrZero` | `negative_or_zero.not_negative_or_zero` | Aucun |
| `Positive` | `positive.not_numeric` | Aucun |
| `Positive` | `positive.not_positive` | Aucun |
| `PositiveOrZero` | `positive_or_zero.not_numeric` | Aucun |
| `PositiveOrZero` | `positive_or_zero.not_positive_or_zero` | Aucun |

La librairie fournit le message ; la traduction reste à la charge de l'application. Les paramètres des contraintes fournies sont des scalaires. Pour vos contraintes personnalisées, préférez également des valeurs scalaires afin de conserver des métadonnées simples et sérialisables.

## Référence des contraintes

Importez les contraintes depuis `Mithridatem\Validation\Attributes`.

| Contrainte | Arguments du constructeur | Comportement |
| --- | --- | --- |
| `NotBlank` | Aucun | Refuse `null` et les chaînes vides après `trim()` |
| `Length` | `?int $min = null, ?int $max = null` | Mesure la longueur avec `mb_strlen((string) $value)` ; bornes inclusives |
| `Email` | Aucun | Vérifie le format avec `FILTER_VALIDATE_EMAIL` |
| `Pattern` | `string $pattern` | Refuse les valeurs non textuelles et vérifie la correspondance avec `preg_match()` |
| `Negative` | Aucun | Valeur numérique strictement inférieure à zéro |
| `NegativeOrZero` | Aucun | Valeur numérique inférieure ou égale à zéro |
| `Positive` | Aucun | Valeur numérique strictement supérieure à zéro |
| `PositiveOrZero` | Aucun | Valeur numérique supérieure ou égale à zéro |

Les quatre contraintes numériques utilisent `is_numeric()` : elles acceptent les entiers, les nombres décimaux et les chaînes numériques.

`NotBlank` ne vérifie pas le type de la valeur : `0` et `false` sont acceptés. Il ne s'agit donc pas d'une contrainte « case à cocher obligatoire ».

### Valeurs facultatives et obligatoires

Toutes les contraintes, sauf `NotBlank`, acceptent `null`. Une chaîne vide n'est pas équivalente à `null` : elle échoue notamment à `Email`.

```php
use Mithridatem\Validation\Attributes\Email;
use Mithridatem\Validation\Attributes\NotBlank;

final class Contact
{
    #[Email]
    public ?string $emailSecondaire = null;

    #[NotBlank]
    #[Email]
    public ?string $emailPrincipal = null;
}
```

Avec ces valeurs, seul `emailPrincipal` produit une erreur. Combinez les contraintes pour exprimer séparément le caractère obligatoire et le format attendu.

### Erreurs de configuration

Les erreurs de données sont retournées dans le tableau. Une configuration incorrecte continue de lever `InvalidArgumentException` :

- `Length` : borne négative ou minimum supérieur au maximum ;
- `Pattern` : expression vide ou expression régulière invalide.

Ces erreurs peuvent survenir lors de l'appel à `validate()`, au moment où les attributs sont instanciés. Elles interrompent alors la validation et ne sont pas converties en messages de champ.

## Utilisation dans un formulaire PHP

Cet exemple suppose que le DTO `Utilisateur` du README est disponible et que l'autoloader Composer a été chargé.

```php
$utilisateur = new Utilisateur();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = $_POST['prenom'] ?? null;
    $email = $_POST['email'] ?? null;

    $utilisateur->prenom = is_string($prenom) ? trim($prenom) : null;
    $utilisateur->email = is_string($email) ? trim($email) : null;

    $errors = (new Mithridatem\Validation\Validator())->validate($utilisateur);

    if ($errors === []) {
        // Enregistrer les données puis rediriger, selon votre application.
    }
}
```

Dans le template, affichez les erreurs à côté du champ :

```php
<?php foreach ($errors['email'] ?? [] as $error): ?>
    <p class="error">
        <?= htmlspecialchars($error->message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </p>
<?php endforeach; ?>
```

## Réponse JSON pour une API

L'exemple suivant suppose que `$utilisateur` a été construit à partir des données reçues. Il doit être exécuté avant toute sortie HTTP.

```php
$errors = (new Mithridatem\Validation\Validator())->validate($utilisateur);

if ($errors !== []) {
    http_response_code(422);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        ['errors' => $errors],
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );
    exit;
}

// Poursuivre le traitement de la requête valide.
```

Les propriétés publiques de `ValidationError` sont sérialisées nativement. Par exemple, une erreur de longueur produit :

```json
{
  "errors": {
    "prenom": [
      {
        "property": "prenom",
        "message": "La propriete \"prenom\" doit contenir au moins 3 caracteres.",
        "code": "length.too_short",
        "parameters": {"min": 3}
      }
    ]
  }
}
```

Sans métadonnées, `parameters` vaut `[]` et est sérialisé comme un tableau JSON vide.

Le code HTTP et l'enveloppe `errors` sont des choix de l'application. La librairie retourne uniquement le tableau PHP.

## Créer une contrainte personnalisée

Une contrainte est un attribut de propriété qui implémente `Mithridatem\Validation\Contracts\PropertyConstraint`.

```php
<?php

use Mithridatem\Validation\Contracts\PropertyConstraint;
use Mithridatem\Validation\ValidationError;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class CodePostal implements PropertyConstraint
{
    /** @return list<ValidationError> */
    public function validate(string $property, mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (!is_string($value) || preg_match('/^[0-9]{5}$/', $value) !== 1) {
            return [new ValidationError(
                property: $property,
                message: sprintf(
                    'La propriété "%s" doit contenir un code postal de 5 chiffres.',
                    $property
                ),
                code: 'code_postal.invalid',
            )];
        }

        return [];
    }
}
```

Utilisation, une fois la classe chargée :

```php
final class Adresse
{
    #[CodePostal]
    public ?string $codePostal = '7500';
}

$errors = (new Mithridatem\Validation\Validator())->validate(new Adresse());
// $errors['codePostal'][0]->code === 'code_postal.invalid'
// $errors['codePostal'][0]->property === 'codePostal'
```

Une contrainte peut retourner plusieurs erreurs. Retournez toujours une liste d'objets `ValidationError` : le regroupement par propriété est effectué par le validateur.

## Migrer depuis les tableaux de messages

Le tableau extérieur conserve les mêmes clés. Ses listes contiennent désormais des objets, ce qui modifie le contrat public.

| Avant | Maintenant |
| --- | --- |
| `array<string, list<string>>` | `array<string, list<ValidationError>>` |
| `$message` dans la boucle d'affichage | `$error->message` |
| `return ['Message'];` dans une contrainte | `return [new ValidationError($property, 'Message', 'custom.invalid')];` |

Adaptez également les tests qui comparaient des chaînes et les consommateurs JSON qui recevaient des listes de textes. Une contrainte doit renseigner `property` avec le nom fourni par le validateur. Le code d'erreur est indépendant du message et permet de sélectionner une traduction sans analyser le texte.

## Migrer depuis la validation par exception

### Avant

```php
try {
    $validator->validate($utilisateur);
    // Traitement des données valides.
} catch (Mithridatem\Validation\Exception\ValidationException $exception) {
    $message = $exception->getMessage();
}
```

### Maintenant

```php
$errors = $validator->validate($utilisateur);

if ($errors === []) {
    // Traitement des données valides.
} else {
    // Parcourir $errors ou transmettre le tableau à la vue.
}
```

Les contraintes personnalisées doivent également passer du retour `void` au retour `array`, remplacer les exceptions de validation par des listes d'objets `ValidationError` et retourner `[]` en cas de succès.

`ValidationException` est conservée mais dépréciée. Les contraintes fournies ne la lèvent plus. Le validateur ne transforme pas les exceptions d'une contrainte personnalisée en messages : adaptez son implémentation au nouveau contrat.

## Développement et publication

```bash
git clone https://github.com/evaluationWeb/validation-librairie.git
cd validation-librairie
composer install
composer test
```

Avant une publication, mettez à jour le changelog, vérifiez la documentation et exécutez les tests. Créez ensuite un nouveau tag adapté aux changements du contrat public ; la version publiée est déterminée par le tag Git.

Voir le [guide de contribution](../CONTRIBUTING.md) et le [changelog](../CHANGELOG.md).
