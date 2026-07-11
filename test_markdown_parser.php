<?php
// Script de test pour le parseur Markdown
// À exécuter avec PHP en ligne de commande

require_once __DIR__ . '/includes/markdown_parser.php';

echo "=== Test du Parseur Markdown ===\n\n";

// Test 1: liste2.md
$content1 = "# Cohésion
Liste exemple pour barbecue
lieu - date

## qui amènes :
- Boisson soft
- Boisson pas soft
- Salade
- Homard thermidor à la truffe
- Tarte salée
- Tarte sucrée
- Dessert
- Rien (je suis un rat!)
- Je ne sais pas encore ;)
## pour la cuisson
- barbecue
- charbon
- de l'allume feu et un briquet
- une grille spéciale saucisse (ou pas)
- un voire deux gants ignifugés
- une pince, un grattoir, des piques…
- un extincteur
## A cuire
- Saucisses
- Merguez
- Légumes
- Poisson
- Pain
- Autres
## Covoit
- aller-conducteur
- retour-conducteur
- aller-passager
- retour-passage
";

$result1 = parseMarkdownList($content1);
echo "Test 1: liste2.md\n";
echo "Nom: " . $result1['name'] . "\n";
echo "Description: " . $result1['description'] . "\n";
echo "Nombre de colonnes: " . count($result1['columns']) . "\n";
echo "Premières colonnes:\n";
foreach (array_slice($result1['columns'], 0, 5) as $col) {
    echo "  - " . $col . "\n";
}
echo "\n";

// Test 2: Match simple.md
$content2 = "# Match simple
Liste de base pour les matchs simples
lieu - date

## management
- Mascotte
- Echauffement
## technique
- Chargement matos et montage
- Son
- Lumière
- Photo
## Accueil
- HelloAsso
- Récupération préventes
- Préventes/invitations
- Vente sur place
- MC
## Manger-boire
- Pizza
- Pain + Ti Coop
";

$result2 = parseMarkdownList($content2);
echo "Test 2: Match simple.md\n";
echo "Nom: " . $result2['name'] . "\n";
echo "Description: " . $result2['description'] . "\n";
echo "Nombre de colonnes: " . count($result2['columns']) . "\n";
echo "Colonnes:\n";
foreach ($result2['columns'] as $col) {
    echo "  - " . $col . "\n";
}
echo "\n";

// Test 3: Markdown simple sans catégories
$content3 = "# Liste Simple
Une liste sans catégories

- Élément 1
- Élément 2
- Élément 3
";

$result3 = parseMarkdownList($content3);
echo "Test 3: Liste simple\n";
echo "Nom: " . $result3['name'] . "\n";
echo "Description: " . $result3['description'] . "\n";
echo "Colonnes:\n";
foreach ($result3['columns'] as $col) {
    echo "  - " . $col . "\n";
}
echo "\n";

echo "=== Tests terminés ===\n";
