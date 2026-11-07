# Baseline de sécurité — Mithridatem Validation

Cette note décrit la première passe d'audit et les tests
`tests/Security/SecurityBaselineTest.php`. Ils caractérisent l'API actuelle ;
ils ne constituent **pas** une certification ni la preuve de l'absence de faille.

## Frontières de confiance et comportements

| Sujet | Comportement actuel | Responsabilité de l'application |
| --- | --- | --- |
| Valeurs `null` | `Email`, `Length`, `Pattern` et les contraintes numériques acceptent `null` | Ajouter `#[NotBlank]` lorsqu'un champ est obligatoire |
| Propriétés typées non initialisées | `Validator` les traite comme `null` | Initialiser les DTO correctement et ne pas considérer `null` comme une validation réussie d'un champ obligatoire |
| Valeurs numériques | Les contraintes utilisent `is_numeric()` ; les chaînes numériques sont acceptées | Valider le type métier, les bornes, les montants, la précision et la finitude en amont ou avec une contrainte spécifique |
| `Pattern` | Exécute une expression régulière définie par le développeur | Ne pas construire le pattern à partir d'une entrée HTTP ; choisir des expressions bornées pour éviter le backtracking coûteux |
| `Length` | Convertit sa valeur en chaîne avant `mb_strlen()` | Normaliser et vérifier les types de données de formulaire avant validation ; un tableau ou objet non convertible n'est pas une entrée texte prise en charge |
| Erreurs structurées | Les contraintes fournies ne réinjectent pas les valeurs soumises dans leurs messages ni leurs paramètres | Échapper les messages affichés en HTML, vérifier les métadonnées des contraintes personnalisées et éviter de révéler des détails sensibles |
| Validation des propriétés | Reflection lit les propriétés privées/protégées et instancie les attributs `PropertyConstraint` | Valider uniquement des classes et contraintes applicatives fiables ; les attributs personnalisés peuvent exécuter du code |

## Exemples

Une adresse électronique obligatoire nécessite les deux contraintes :

```php
#[\Mithridatem\Validation\Attributes\NotBlank]
#[\Mithridatem\Validation\Attributes\Email]
public ?string $email = null;
```

Une entrée `$_POST['email']` peut être un tableau. La transformer en
chaîne sans vérification n'est pas une validation de type. L'application
doit contrôler la forme de sa requête avant d'hydrater son DTO :

```php
$raw = $_POST['email'] ?? null;
$dto->email = is_string($raw) ? trim($raw) : null;
```

Affichage des erreurs :

```php
<?= htmlspecialchars($error->message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
```

## Points de vigilance nécessitant des tests supplémentaires

- `Pattern` : erreurs d'exécution PCRE (dont limites de retour arrière,
  chaînes invalides selon le modificateur utilisé), temps de traitement
  et éventuelle dégradation de service avec des regex mal conçues.
- `Length` : valeurs atypiques ou tableaux transmis à une propriété
  non typée, avec éventuels avertissements ou exceptions PHP.
- Contraintes numériques : flottants non finis, chaînes exponentielles,
  limites et exigences de précision des montants financiers.
- Contraintes personnalisées : évaluation d'effets de bord et de la
  sérialisation de métadonnées contenant des données sensibles.

Ces sujets sont à caractériser **avant** de décider d'une modification de
production. La présente PR ne change aucune API, règle ni comportement de
validation.


## Deuxième passe — comportements adversariaux des contraintes

Les tests de `tests/Security/AdversarialConstraintsTest.php` consignent
des comportements de la version actuelle, sans demander de changement
immédiat de contrat.

### `Pattern` et erreurs PCRE

`Pattern::validate()` utilise `!preg_match(...)`. Cela confond
actuellement la valeur `0` (**aucune correspondance**) et `false`
(**erreur du moteur PCRE**) :

- dépassement d'une limite locale de backtracking ;
- séquence UTF-8 invalide lorsque le modificateur `u` est utilisé.

Dans les deux cas, `pattern.no_match` est retourné. Ce comportement
est à prendre en compte pour l'observabilité des erreurs et la distinction
entre donnée incorrecte et erreur d'exécution. Les expressions régulières
proviennent normalement du code de l'application ; ne pas construire
un pattern libre à partir d'une requête HTTP et limiter la taille des
entrées si les expressions sont complexes. Cette caractérisation ne prouve
pas une vulnérabilité exploitable en usage conforme.

### `Length` et entrées non textuelles

`Length` convertit la valeur en `string` avant de compter les caractères.
Un tableau provoque actuellement un avertissement PHP « Array to string
conversion » et est traité comme la chaîne `Array`. Un objet dépourvu
de méthode `__toString()` provoque une erreur PHP. La validation
des types des DTO et des tableaux `$_POST` est donc nécessaire avant
d'appliquer cette contrainte.

Les caractères UTF-8 sont comptés avec `mb_strlen()`, et non en octets.

### Valeurs numériques non finies

Les contraintes `Positive`, `PositiveOrZero`, `Negative` et
`NegativeOrZero` utilisent `is_numeric()` puis une comparaison de signe.
Dans la version actuelle :

- `INF` est accepté par les contraintes positives ;
- `-INF` est accepté par les contraintes négatives ;
- `NAN` est accepté par les **quatre** contraintes, puisque ses
  comparaisons avec zéro sont fausses ;
- une chaîne numérique en notation exponentielle, telle que `1e309`,
  peut être acceptée.

Ces contraintes expriment uniquement un **signe**, pas la finitude,
la précision décimale, la plage autorisée ou l'aptitude au calcul.
Pour un prix, une quantité ou un montant, imposer un format et des
règles métier adaptés en amont.

**Décision à ce stade :** documenter et tester avant tout changement.
Une évolution du comportement des contraintes ferait l'objet d'une
analyse d'impact et d'une PR distincte, si un défaut de sécurité
intrinsèque et reproductible était démontré.
