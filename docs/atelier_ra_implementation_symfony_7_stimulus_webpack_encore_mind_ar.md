# Atelier réalité augmentée – Implémentation Symfony 7 + Stimulus + Webpack Encore + MindAR

## Objectif pédagogique
- Permettre aux médiateurs de configurer rapidement une scène MindAR mêlant modèle 3D et ambiance sonore.
- Donner aux participants un parcours complet : découverte, création de cible, prévisualisation et démonstration audio.
- Capitaliser sur Symfony 7 et Stimulus pour industrialiser les ateliers RA au sein du portail Potins Numériques.

## Cartographie du code
- **Routage & contrôleur** : `src/Controller/Game/Ar/ArController.php` expose les pages `/ra/intro`, `/ra/mindar/demo`, `/ra/mindar/create` et injecte la liste des packs MindAR via `MindArPackLocator`.
- **API** : `src/Controller/Api/UploadMindController.php` (upload des fichiers `.mind`) et `src/Controller/Api/ArSceneController.php` (création/lecture des scènes) stockent les métadonnées en base (`src/Entity/Games/ArScene.php`).
- **Stimulus** :
    - `assets/controllers/mindar_create_controller.js` gère la prévisualisation/sauvegarde des scènes.
    - `assets/controllers/mindar_demo_controller.js` orchestre l’activation du son et le suivi `targetFound/targetLost` côté A‑Frame.
    - `assets/controllers/ar/mindar_build_controller.js` compile les images en `.mind` et déclenche l’upload.
- **Templates Twig** :
    - `templates/pwa/ar/intro.html.twig` : introduction et consentement caméra.
    - `templates/pwa/ar/mindar_demo.html.twig` : démo audio « Lotus Respire » avec bouton d’activation du son.
    - `templates/pwa/ar/mindar_create.html.twig` : atelier de configuration + bibliothèque de packs + compilateur navigateur.
- **Build front** : `webpack.config.js` copie `mind-ar`, les modèles 3D et les pistes audio vers `public/build/`.
- **Outils** : `tools/mindar/build-mind.js` (CLI Node) pour générer un `.mind` à partir d’images locales.

## Dépendances et prérequis
Les dépendances PHP/JS sont listées dans `composer.json` et `package.json`. Après clonage :
```bash
composer install
npm install # ou yarn install
```
Webpack Encore copie automatiquement les assets MindAR (`mindar-image-aframe.prod.js`, `mindar-image-compiler.prod.js`…), les modèles (`public/models`) et les pistes audio (`public/audio`).

## Processus atelier
1. **Préparer la cible**
    - Option navigateur : dans `/ra/mindar/create`, la section « Générer un fichier .mind » (controller `ar--mindar-build`) accepte des images contrastées, calcule les scores et propose l’upload vers `/api/upload/mind`.
    - Option CLI : `node tools/mindar/build-mind.js chemin/vers/image.jpg public/mindar/packs/demo/targets.mind`.
2. **Prévisualiser la scène**
    - Sélectionner un modèle 3D, une ambiance sonore et le fichier `.mind`.
    - Cliquer sur « Prévisualiser » : `mindar_create_controller` injecte dynamiquement A‑Frame et MindAR pour tester la scène directement depuis le navigateur.
3. **Sauvegarder**
    - « Enregistrer la scène » envoie les métadonnées vers `/api/ar/scenes` et crée une entrée `ArScene` (titre, cible, modèle, son, propriétaire).
4. **Tester l’expérience audio**
    - Rendez-vous sur `/ra/mindar/demo` : `mindar_demo_controller` fournit un bouton d’activation conforme aux contraintes d’autoplay. Lorsque la cible est détectée (`targetFound`), le son est déclenché ; il s’arrête lors du `targetLost`.
5. **Partager/Intégrer**
    - Les packs listés dans `public/mindar/packs/*/targets.json` sont remontés dans l’interface pour guider les ateliers ultérieurs.

## Bonnes pratiques
- Toujours tester l’expérience en HTTPS réel ou via un tunnel (WebRTC + accès caméra).
- Préférer des images contrastées (>300 points) pour garantir le tracking MindAR.
- Vérifier le poids des modèles `.glb` (<3 Mo) et des sons (<1 Mo) pour éviter un chargement trop long en mobilité.
- Documenter chaque pack dans `targets.json` (champ `name`, liste `items[]` avec `label`/`description`) afin de le rendre exploitable depuis l’interface.
