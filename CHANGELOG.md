# Changelog

Toutes les modifications notables de ce projet seront documentees dans ce fichier.

## [Unreleased]

### Objets d’erreur de validation

- Ajout de `ValidationError`, objet `final readonly` contenant `property`, `message`, `code` et `parameters`.
- **Rupture de compatibilité** : les contraintes retournent désormais `list<ValidationError>` au lieu de `list<string>` ; le validateur conserve le regroupement par propriété et retourne `array<string, list<ValidationError>>`.
- Ajout de codes d'erreur pour les huit contraintes et de paramètres pour les bornes de `Length` et l'expression de `Pattern`.
- Conservation des messages existants, du retour `[]` en cas de succès et des exceptions de configuration.
- README, guide, exemples de formulaire/API, migration et consignes de contribution adaptés au nouveau contrat.
- Tests des objets, des codes, des paramètres, de la sérialisation JSON et du cumul des erreurs.

### Changed

- **Rupture de compatibilité** : `Validator::validate()` retourne toutes les erreurs dans un tableau `array<string, list<ValidationError>>`, regroupées par propriété, ou `[]` en cas de succès.
- **Rupture de compatibilité** : `PropertyConstraint::validate()` et les huit contraintes fournies retournent une liste d'objets `ValidationError` au lieu de lever `ValidationException`.
- Toutes les contraintes sont exécutées, y compris plusieurs contraintes sur une même propriété.
- Les erreurs de configuration continuent de lever `InvalidArgumentException`.
- Documentation et tests adaptés au nouveau contrat ; ajout de tests de cumul et d'absence d'état entre deux validations.
- Déclaration de la dépendance `ext-mbstring`, déjà utilisée par `Length`.

### Deprecated

- `ValidationException` reste disponible pour faciliter la migration mais n'est plus utilisée par les contraintes fournies.

## [0.1.0] - 2025-10-19

- Premiere extraction de la librairie `mithridatem/validation` depuis le projet Chocoblast.
- Ajout des contraintes `NotBlank`, `Length`, `Email` et du validateur generique.
- Publication des tests PHPUnit couvrant les cas principaux.
- Ajout de la documentation (`README`, `docs/overview.md`) et de la configuration Composer.

---

Format inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).
