# Plan de développement (robuste) – Réalité Augmentée “Quinzaine Zen”

> Stack : **Symfony 7**, **Stimulus**, **Webpack Encore**, **TypeScript** (optionnel), **MindAR (image tracking)** via **A‑Frame**, **PostgreSQL**.
>
> Objectif : déployer un mini‑module RA réutilisable pour d’autres ateliers, avec persistance DB (scènes créées par les participants) et un flux MindAR propre (cibles d’images -> .mind + scènes A‑Frame).

---

## Chapitre 0 — Arborescence cible & dépendances

### 0.1 Arborescence (proposée)
```
assets/
  controllers/
    ar/
      mindar_demo_controller.js
      mindar_create_controller.js
      ar_audio_controller.js
  styles/
    ar.scss
  mindar/
    targets/            # .mind générés
public/
  models/
    lotus.glb
    rock.glb
    bamboo.glb
  audio/
    water.mp3
    forest.mp3
  ar/
    markers/            # si mix AR.js
src/
  Controller/ArController.php
  Controller/Api/ArSceneController.php
  Entity/ArScene.php
  Repository/ArSceneRepository.php
  Form/ArSceneType.php
  Service/MindArTargetBuilder.php (optionnel)
  Security/ (si besoin)
templates/
  ar/
    intro.html.twig
    mindar_demo.html.twig
    mindar_create.html.twig
    partials/
      consent_camera.html.twig
migrations/
```

### 0.2 Dépendances npm
```bash
npm i aframe mind-ar --save
# (Optionnel pour mixte)
npm i ar.js --save

# Utilitaires
npm i file-saver --save
```
> **Remarque** : `mind-ar` apporte `mindar-image-aframe.prod.js` (image-tracking). On l’importera côté page Twig via Encore (copyFiles) ou CDN (on privilégie **Encore**).

### 0.3 Webpack Encore (extrait)
```js
// webpack.config.js (extraits)
Encore
  .addStyleEntry('ar', './assets/styles/ar.scss')
  .addEntry('mindar_demo', './assets/controllers/ar/mindar_demo_controller.js')
  .addEntry('mindar_create', './assets/controllers/ar/mindar_create_controller.js')
  .copyFiles({from: './node_modules/mind-ar/dist/', to: 'mindar/[path][name].[ext]'})
  .copyFiles({from: './public/models', to: 'models/[path][name].[ext]'})
  .copyFiles({from: './public/audio', to: 'audio/[path][name].[ext]'})
;
```

---

## Chapitre 1 — Modèle de données & migration

### 1.1 Entité `ArScene`
```php
<?php
// src/Entity/ArScene.php
namespace App\Entity;

use App\Repository\ArSceneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArSceneRepository::class)]
#[ORM\Table(name: 'ar_scene')]
class ArScene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $title;

    // Fichier MindAR généré pour l’image-cible (.mind) stocké/publie
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mindTargetPath = null;

    // Index du target dans le .mind (0 par défaut)
    #[ORM\Column(type: 'integer')]
    private int $targetIndex = 0;

    // Modèle 3D associé (URL relative /models/lotus.glb)
    #[ORM\Column(length: 255)]
    private string $modelUrl;

    // Son optionnel (/audio/water.mp3)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $soundUrl = null;

    // Appartenance participant (id user / null si public)
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ownerId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
    // getters/setters ...
}
```

### 1.2 Migration
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

---

## Chapitre 2 — Routes & contrôleurs (serveur)

### 2.1 `ArController` (pages Twig)
```php
<?php
// src/Controller/ArController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ArController extends AbstractController
{
    #[Route('/ra/intro', name: 'ar_intro')]
    public function intro(): Response
    {
        return $this->render('ar/intro.html.twig');
    }

    #[Route('/ra/mindar/demo', name: 'ar_mindar_demo')]
    public function demo(): Response
    {
        return $this->render('ar/demo.html.twig');
    }

    #[Route('/ra/mindar/create', name: 'ar_mindar_create')]
    public function create(): Response
    {
        return $this->render('ar/create.html.twig');
    }
}
```

### 2.2 API simple pour persister les scènes
```php
<?php
// src/Controller/Api/ArSceneController.php
namespace App\Controller\Api;

use App\Entity\ArScene;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ar/scenes')]
class ArSceneController extends AbstractController
{
    #[Route('', name: 'api_ar_scene_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $scene = new ArScene();
        $scene->setTitle($data['title'] ?? 'Sans titre');
        $scene->setMindTargetPath($data['mindTargetPath'] ?? null);
        $scene->setTargetIndex((int)($data['targetIndex'] ?? 0));
        $scene->setModelUrl($data['modelUrl']);
        $scene->setSoundUrl($data['soundUrl'] ?? null);
        $scene->setOwnerId($this->getUser()?->getId());
        $em->persist($scene);
        $em->flush();
        return $this->json(['id' => $scene->getId()], 201);
    }

    #[Route('', name: 'api_ar_scene_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $scenes = $em->getRepository(ArScene::class)->findBy([], ['createdAt' => 'DESC'], 50);
        return $this->json(array_map(fn(ArScene $s) => [
            'id' => $s->getId(),
            'title' => $s->getTitle(),
            'mindTargetPath' => $s->getMindTargetPath(),
            'targetIndex' => $s->getTargetIndex(),
            'modelUrl' => $s->getModelUrl(),
            'soundUrl' => $s->getSoundUrl(),
        ], $scenes));
    }
}
```

> **Sécurité** : ajoute un `IsGranted('ROLE_USER')` sur `create` si tu veux limiter aux inscrits ; laisse `list` public pour l’expo.

---

## Chapitre 3 — Pages Twig (MindAR)

### 3.1 `templates/ar/intro.html.twig`
```twig
{% extends 'base.html.twig' %}
{% block title %}Découvrir la RA{% endblock %}
{% block body %}
<section class="container mx-auto p-6 prose">
  <h1>Réalité augmentée : comment ça marche ?</h1>
  <p>Caméra → détection d’une image-cible → ancrage d’un objet 3D → rendu en temps réel. Tout se passe dans le navigateur.</p>
  {% include 'ar/partials/consent_camera.html.twig' %}
  <ul>
    <li>HTTPS requis</li>
    <li>Bonne lumière et cibles contrastées</li>
    <li>Vie privée : flux non enregistré</li>
  </ul>
  <p><a class="btn" href="{{ path('ar_mindar_demo') }}">Lancer la démo</a></p>
</section>
{% endblock %}
```

### 3.2 `templates/ar/partials/consent_camera.html.twig`
```twig
<div class="rounded bg-yellow-50 border p-3 text-sm">
  <strong>Autorisation caméra :</strong> si demandé par le navigateur, acceptez l’accès pour afficher l’expérience RA.
</div>
```

### 3.3 `templates/ar/mindar_demo.html.twig`
```twig
{% extends 'base.html.twig' %}
{% block stylesheets %}{{ parent() }}{{ encore_entry_link_tags('ar') }}{% endblock %}
{% block javascripts %}
  {{ parent() }}
  {{ encore_entry_script_tags('mindar_demo') }}
{% endblock %}
{% block body %}
<section class="container mx-auto p-4">
  <h1>Démo MindAR – Lotus Respire</h1>
  <p>Scannez l’image-cible fournie pendant l’atelier.</p>
</section>

<!-- A-Frame + MindAR (servis depuis /build/mindar/ grâce à copyFiles) -->
<script src="/build/mindar/mindar-image-aframe.prod.js"></script>
<script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>

<a-scene mindar-image="imageTargetSrc: /build/mindar/targets/demo.mind; filterMinCF:0.0001;"
         color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights"
         vr-mode-ui="enabled: false"
         device-orientation-permission-ui="enabled: true">
  <a-assets>
    <a-asset-item id="lotus" src="/build/models/lotus.glb"></a-asset-item>
    <audio id="ambience" src="/build/audio/forest.mp3"></audio>
  </a-assets>

  <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>

  <a-entity mindar-image-target="targetIndex: 0">
    <a-gltf-model src="#lotus" position="0 0 0" scale="0.5 0.5 0.5"
       animation__breath="property=scale; dir=alternate; dur=4000; loop=true; to=0.6 0.6 0.6"></a-gltf-model>
    <a-entity sound="src: #ambience; autoplay: false; loop: true"></a-entity>
  </a-entity>
</a-scene>
{% endblock %}
```

### 3.4 `templates/ar/mindar_create.html.twig`
```twig
{% extends 'base.html.twig' %}
{% block stylesheets %}{{ parent() }}{{ encore_entry_link_tags('ar') }}{% endblock %}
{% block javascripts %}
  {{ parent() }}
  {{ encore_entry_script_tags('mindar_create') }}
{% endblock %}
{% block body %}
<section class="container mx-auto p-4" data-controller="mindar-create">
  <h1>Créateur Jardin Zen</h1>

  <form class="space-y-4">
    <label class="block">Modèle 3D</label>
    <select data-mindar-create-target="model">
      <option value="/build/models/lotus.glb">Lotus</option>
      <option value="/build/models/rock.glb">Pierre</option>
      <option value="/build/models/bamboo.glb">Bambou</option>
    </select>

    <label class="block">Son</label>
    <select data-mindar-create-target="sound">
      <option value="">(aucun)</option>
      <option value="/build/audio/forest.mp3">Forêt</option>
      <option value="/build/audio/water.mp3">Eau</option>
    </select>

    <label class="block">Fichier cible (.mind)</label>
    <input type="file" accept=".mind" data-mindar-create-target="mindfile" />

    <button type="button" data-action="mindar-create#preview" class="btn">Prévisualiser</button>
    <button type="button" data-action="mindar-create#save" class="btn btn-primary">Enregistrer la scène</button>
  </form>

  <hr class="my-6" />

  <div id="preview" class="border rounded p-2">
    <!-- La scène A‑Frame de prévisualisation est injectée par Stimulus -->
  </div>
</section>
{% endblock %}
```

---

## Chapitre 4 — Contrôleurs Stimulus

### 4.1 Démo (facultatif) – `mindar_demo_controller.js`
> Utilisé si tu veux piloter audio / UI depuis JS. Optionnel, car la page de démo peut fonctionner “sans JS applicatif”.
```js
// assets/controllers/ar/mindar_demo_controller.js
import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
  connect(){ /* hooks UI si besoin */ }
}
```

### 4.2 Créateur – `mindar_create_controller.js`
```js
// assets/controllers/ar/mindar_create_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['model','sound','mindfile'];

  preview(){
    const container = document.getElementById('preview');
    container.innerHTML = '';

    const model = this.modelTarget.value;
    const sound = this.soundTarget.value;

    const file = this.mindfileTarget.files?.[0];
    if (!file) { alert('Importe un fichier .mind'); return; }
    const url = URL.createObjectURL(file);

    container.insertAdjacentHTML('beforeend', `
      <script src="/build/mindar/mindar-image-aframe.prod.js"></script>
      <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
      <a-scene mindar-image="imageTargetSrc: ${url};" vr-mode-ui="enabled:false" renderer="colorManagement:true">
        <a-assets>
          <a-asset-item id="model" src="${model}"></a-asset-item>
          ${sound ? `<audio id="sfx" src="${sound}"></audio>` : ''}
        </a-assets>
        <a-camera position="0 0 0" look-controls="enabled:false"></a-camera>
        <a-entity mindar-image-target="targetIndex: 0">
          <a-gltf-model src="#model" scale="0.5 0.5 0.5"
            animation__spin="property=rotation; to=0 360 0; loop:true; dur:12000"></a-gltf-model>
          ${sound ? `<a-entity sound="src:#sfx; autoplay:false; loop:true"></a-entity>` : ''}
        </a-entity>
      </a-scene>
    `);
  }

  async save(){
    const file = this.mindfileTarget.files?.[0];
    if (!file) { alert('Ajoute un .mind pour sauvegarder'); return; }

    // Stockage du .mind : pour aller vite, on envoie le nom relatif supposé (déjà uploadé) ou on bascule sur un endpoint upload.
    // Ici, on suppose que tu as une étape d’upload séparée (à ajouter si besoin). On enregistre seulement les URLs.

    const payload = {
      title: 'Scène zen',
      mindTargetPath: '/uploads/mind/'+ file.name, // à adapter si tu fais l’upload réel
      targetIndex: 0,
      modelUrl: this.modelTarget.value,
      soundUrl: this.soundTarget.value || null,
    };

    const res = await fetch('/api/ar/scenes', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      const data = await res.json();
      alert('Scène sauvegardée (id '+data.id+")");
    } else {
      alert('Erreur de sauvegarde');
    }
  }
}
```

### 4.3 Audio toggle (réutilisable) – `ar_audio_controller.js`
```js
import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
  static values = { selector: String };
  toggle(){
    const el = document.querySelector(this.selectorValue || '[sound]');
    const sound = el?.components?.sound;
    if (sound) (sound.isPlaying ? sound.stopSound() : sound.playSound());
  }
}
```

---

## Chapitre 5 — Génération des fichiers `.mind` (flux atelier)

**MindAR** nécessite de convertir une ou plusieurs images “cibles” en un fichier **`.mind`**. Deux options :

1. **Pré‑généré** : tu fournis 1–3 `.mind` prêts (ex : `demo.mind`, `mandala.mind`). Parfait pour la démo et pour limiter la friction en atelier.
2. **Atelier créatif** : tu laisses les participants choisir une image parmi un **catalogue** (galets, feuilles, calligraphies) et tu as **pré‑construit** les `.mind` correspondants, ou tu utilises un **petit service interne** (CLI/Node) pour convertir leurs images en direct.

> Pour un atelier fluide, je recommande **pré‑générés** + un petit nombre de variations. Ensuite, pour une version avancée, on code un **endpoint upload** et on déclenche un **processus CLI** qui génère le `.mind` côté serveur (service `MindArTargetBuilder`).

### 5.1 Service (esquisse) `MindArTargetBuilder`
```php
<?php
// src/Service/MindArTargetBuilder.php
namespace App\Service;

class MindArTargetBuilder
{
    public function buildFromImage(string $sourcePath, string $destMindPath): bool
    {
        // Idée : appeler un script Node/CLI (mindar-image-cli) via symfony/process
        // ou utiliser un conteneur Docker prêt. Ici, on retourne true en attendant l’intégration.
        return true;
    }
}
```

---

## Chapitre 6 — Upload des fichiers & sécurité (optionnel à activer)

- Crée un **endpoint d’upload** pour `.mind` (et éventuellement images brutes) avec **limites** de taille, **mime** whitelist, et stockage dans `public/uploads/mind/`.
- Active une **CSP** adaptée (scripts `aframe` et `/build/mindar/...`).
- Forcer **HTTPS** (caméra).

---

## Chapitre 7 — Checklist d’accessibilité & UX

- Bouton **“Permettre la caméra”** (iOS demande une interaction utilisateur).
- **Fallback** : si `navigator.mediaDevices` indisponible → vidéo démo + viewer 3D.
- Contrastes & boutons larges.
- Indication **luminosité** et **distance** pour mieux accrocher la cible.

---

## Chapitre 8 — Tests & QA

- Mobiles Android Chrome, iOS Safari.
- Débit réseaux (précharger modèles via `<a-assets>`).
- Temps de première détection cible (< 2–3 s en bonne lumière).

---

## Chapitre 9 — Prochaines évolutions

- Multi‑targets dans un seul `.mind` (index 0..n) → mini‑parcours zen.
- Galerie publique des scènes (route `/ra/gallery`).
- Ajout d’un **éditeur** (position, scale, rotation drag‑n‑drop) et **persist** des transform.

---

### Fin — À faire maintenant (ordre pratique)
1) Ajouter dépendances npm + Encore copyFiles.
2) Créer entité `ArScene` + migration.
3) Poser `ArController` + pages Twig (intro, demo, create).
4) Ajouter Stimulus `mindar_create` (preview + save).
5) Déposer au moins un `.mind` (ex `demo.mind`) et 2–3 modèles glb + audios.
6) Tester mobile HTTPS.

