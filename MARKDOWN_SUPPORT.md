# Support Markdown pour les Listes

Ce document décrit les modifications apportées pour supporter l'import de listes depuis des fichiers Markdown.

## Fonctionnalités Ajoutées

### 1. Import Markdown
Les administrateurs peuvent maintenant importer des listes depuis des fichiers Markdown (`.md` ou `.markdown`) en plus des formats CSV et TXT existants.

### 2. Structure Markdown Supportée

Format attendu pour les fichiers Markdown :

```markdown
# Titre de la liste
Description de la liste (optionnelle)
Ligne supplémentaire de description

## Catégorie 1
- Élément 1
- Élément 2
- Élément 3

## Catégorie 2
- Élément A
- Élément B
```

**Règles de parsing :**
- `#` au début d'une ligne : Titre de la liste (obligatoire)
- Les lignes suivant le titre (avant le premier `##`) : Description (optionnelle)
- `##` au début d'une ligne : Nom de la catégorie
- `-` au début d'une ligne : Élément de la liste
- Si une catégorie est active, les éléments sont préfixés par "Catégorie - Élément"

### 3. Exemples

Deux exemples sont fournis dans `assets/examples/` :
- [`Match simple.md`](assets/examples/Match%20simple.md) - Liste simple pour des matchs
- [`liste2.md`](assets/examples/liste2.md) - Liste complète avec plusieurs catégories

### 4. Affichage des Descriptions

Les descriptions des listes sont maintenant affichées :
- Dans l'interface utilisateur (page d'accueil)
- Dans l'interface administrateur (gestion des listes)

## Fichiers Modifiés

### Nouveaux Fichiers
1. **`includes/markdown_parser.php`**
   - `parseMarkdownList($content)` : Parse le contenu Markdown et extrait le nom, la description et les colonnes
   - `importListFromMarkdown($filePath)` : Import une liste depuis un fichier Markdown

### Fichiers Modifiés

1. **`includes/functions.php`**
   - Ajout du paramètre `$description` dans `addList()` et `updateList()`
   - Les listes stockent maintenant une description

2. **`admin/import.php`**
   - Acceptation des extensions `.md` et `.markdown`
   - Appel à `importListFromMarkdown()` pour les fichiers Markdown
   - Documentation mise à jour avec des exemples Markdown
   - Liens vers les fichiers d'exemple

3. **`admin/manage.php`**
   - Ajout d'un champ "Description" dans les formulaires de création/modification
   - Affichage de la description dans le tableau des listes
   - Lien vers la page d'import

4. **`user/index.php`**
   - Affichage de la description pour chaque liste
   - Meilleure mise en page des cartes de liste

5. **`assets/css/style.css`**
   - Styles pour `.list-description` et `.list-meta`
   - Amélioration de l'affichage des cartes

## Utilisation

### Pour les Administrateurs

1. **Créer une liste avec description** :
   - Allez dans "Gérer les listes"
   - Remplissez le champ "Description" pour ajouter une description à votre liste

2. **Importer une liste Markdown** :
   - Allez dans "Importer"
   - Sélectionnez un fichier `.md` ou `.markdown`
   - Le système extraira automatiquement le titre, la description et les colonnes

### Pour les Utilisateurs

- Les descriptions des listes sont affichées sur la page d'accueil
- Les listes sont présentées sous forme de cartes avec leur description

## Format CSV (inchangé)

Le format CSV existant continue de fonctionner :

```csv
Nom de la liste,Nom1,Nom2,Nom3
Exemple,Ligne 1,Ligne 2,Ligne 3
```

## Compatibilité

- **PHP 7.4+** requis (déjà requis par le projet)
- Pas de nouvelles dépendances
- Fonctionne avec les fichiers existants
- Les listes existantes sans description continueront à fonctionner

## Tests

Un script de test est disponible : `test_markdown_parser.php`

Exécutez-le avec :
```bash
php test_markdown_parser.php
```

## Optimisations Possibles

### 1. Amélioration du Parsing Markdown
- Supporter les titres de niveau 3 (`###`) pour des sous-catégories
- Gérer les listes numérotées
- Ignorer les commentaires HTML dans le Markdown

### 2. Export Markdown
- Ajouter une fonction d'export vers Markdown
- Permettre aux administrateurs de télécharger une liste au format Markdown

### 3. Éditeur Markdown
- Ajouter un éditeur WYSIWYG pour créer des listes au format Markdown
- Prévisualisation en temps réel

### 4. Catégories comme Groupes
- Au lieu de préfixer les colonnes, créer des groupes de colonnes
- Afficher les catégories comme des en-têtes de groupe dans le tableau

### 5. Validation Améliorée
- Vérifier que les noms de colonnes sont uniques
- Limiter la longueur des descriptions
- Valider le format des fichiers avant import

### 6. Internationalisation
- Supporter les fichiers Markdown en différentes langues
- Gérer les encodages spéciaux

### 7. Performance
- Cache des listes parsées
- Optimisation pour les grands fichiers Markdown

## Sécurité

- Les fichiers Markdown sont lus et parsés de manière sécurisée
- Pas d'exécution de code arbitraire
- Les descriptions sont échappées avant affichage (htmlspecialchars)
- Les noms de fichiers sont validés

## Licence

Ces modifications sont sous la même licence MIT que le projet original.
