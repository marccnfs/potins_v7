# Plan détaillé pour l'escape par équipe (branche `game/escape-team`)

## 1. Architecture générale et données
- **Modèles** : `EscapeTeamRun` (session globale lancée par l'admin, lien d'inscription unique, statut, temps max commun, image d'accueil), `EscapeTeam` (nom d'équipe unique par run, avatar/couleur, horodatage), `EscapeTeamMember` (pseudo + avatar prédéfini rattaché à une équipe), `EscapeTeamSession` (progrès d'équipe + chronos + indices consommés + codes finaux).
- **Relations** : `EscapeTeamRun` ↔ `EscapeGame` (base de l'escape), `EscapeTeamRun` ↔ `Participant` (maître du jeu/owner), `EscapeTeamRun` ↔ `EscapeTeam` (max 10 équipes, nom unique), `EscapeTeam` ↔ `EscapeTeamMember` (inscriptions individuelles), `EscapeTeam` ↔ `EscapeTeamSession` (1:1 pour suivre la progression et le scoring).
- **Persistance** : tables dédiées (aff_escape_team_run, aff_escape_team, aff_escape_team_member, aff_escape_team_session) avec contraintes d'unicité (slug du run, nom d'équipe par run, session unique par équipe) et FK vers `aff_escapegame`/`aff_participant`.
- **Réemploi** : factoriser la logique existante de la section escape (états, timers, scoring) pour la rendre multi-équipe. Prévoir migrations.

## 2. Épreuves (5 étapes)
- **Étapes 1, 2, 5** (papier → mot secret) : préconfigurer 3 énigmes `cryptex` avec saisie d'un mot (placeholders `MOT_ETAPE_1/2/5`, validation auto, hash côté client), indices et fragment final à collecter. Étape 5 réutilise le puzzle papier final.
- **Étape 3** (logique en 3 parties) : puzzle `logic_form` avec trois questions indépendantes (intrus, vrai/faux, suite logique) devant toutes être correctes pour débloquer l'étape 4 ; messages OK/erreur dédiés, indices multiples et fragment final.
- **Étape 4** (QR) : puzzle `qr_geo` en mode `qr_only` (scan sans GPS) avec message de validation, QR non expirant par défaut, indices et fragment final ; réutilise la génération de QR existante.
- **Indices** : chaque puzzle fournit un tableau d'indices non vides (fallback générique) et un `finalClue` à consommer dans la phrase finale ; suivi côté session (hintsUsed) inchangé.

## 3. Création/gestion des équipes
- **Inscription en ligne** via lien unique (ou QR) affiché sur la page d'accueil projetée ; pseudo + avatar prédéfini, nom d'équipe obligatoire, max 10 équipes (enregistré sur `EscapeTeamRun::maxTeams`).
- **Validation** : unicité du nom, avatar requis, au moins un membre ; édition/suppression avant lancement si besoin.
- **Services** : `EscapeTeamAvatarCatalog` liste les avatars utilisables côté team & membres ; `EscapeTeamRegistrationService` centralise création/mise à jour/suppression avec contrôles (limite d'équipes, statut du run "draft/registration" uniquement, création automatique de la session d'équipe et du step courant = 1).

## 4. Page d'accueil et lancement par l'administrateur
- **Accueil projeté** : titre de l'escape, image de l'univers, affichage du lien unique/QR pour s'inscrire (slug généré automatiquement via `EscapeTeamRun::ensureShareSlug(...)`).
- **Back-office admin** : liste des équipes inscrites, paramétrage du temps maximum commun, bouton « Lancer le jeu » (verrouille les inscriptions, démarre timers, synchronise les sessions d'équipe en step 1 + horodatage `startedAt`).
- **Service** : `EscapeTeamRunAdminService` prépare le run (titre, image, max équipes, temps limite), ouvre les inscriptions (status `registration`, date d'ouverture) et lance le jeu (status `running`, démarre `startedAt` pour le run + chaque session d'équipe, bloque les inscriptions).
- **Rôles** : le créateur/admin est maître du jeu (authz dédiée, pas d'inscription en équipe).

## 5. Progression et temps réel
- **Projection live** : page de progression (barre ou liste) mise à jour automatiquement à chaque étape réussie ; code couleur/avatars pour distinguer les équipes. Snapshot fourni par `EscapeTeamProgressService::buildLiveProgress` (statut du run, stepCount, équipes triées par progression puis durée).
- **Par équipe** : suivi de l'étape active, temps écoulé, indices utilisés, pénalités et horodatage d'activité ; réemploi du mécanisme temps réel existant (websocket/poll) alimenté par le snapshot.
- **Enregistrement** : `EscapeTeamProgressService::recordStepCompletion` persiste la fin d'une étape (horodatage, durée facultative, delta d'indices, métadonnées). Le service détecte la complétion finale (étape > totalSteps), clôt la session (endedAt, completed=true) et avance `currentStep` sinon.
- **Indices** : `consumeHint` incrémente un compteur d'indices consommés par équipe et rafraîchit l'activité pour déclencher une mise à jour du flux temps réel.

## 6. Logique de progression
- **Flux** : `EscapeTeamProgressService::recordStepCompletion` gère l'avancement (temps, indices, next step). `recordLogicPartCompletion` ajoute le suivi des 3 parties de l'étape 3 avant validation complète.
- **Finale** : `submitFinalAnswer` stocke la phrase et le code final, clôt la session (currentStep null, endedAt) et bascule le run en `ended` si toutes les équipes ont terminé.
- **Verrouillage** : après lancement, inscriptions fermées ; timers démarrés pour toutes les équipes.
- **Reprise** : journalisation dans `stepStates` + `lastActivityAt` pour reconstruire une projection live et reprendre en cas de déconnexion.

## 7. Classement final et fin de jeu
- **Reconstruction finale** : phrase à reconstituer ; sa validation révèle un code secret et clôt la session via `submitFinalAnswer`.
- **Classement** : `computeLeaderboard` renvoie un tableau trié (terminés puis progression, durée, pénalités, indices) avec avatar, nom, progression et temps total.
- **Projection** : une vue publique `escape_team_leaderboard` affiche le tableau final ou en cours.

## 8. UX et onboarding
- **Messages d'état** : templates Twig dédiés (landing, register, leaderboard) affichent statut, limites (10 équipes) et accès direct aux pages clés.
- **Guides** : landing projetable (titre, visuel, lien unique), page d'inscription avec avatar picker prédéfini et verrouillage automatique après lancement, leaderboard pour l'affichage final.

## 9. Tests et données
- **Tests back** : progression multi-équipe, validation des étapes (y compris 3 parties de l'étape 3), consommation d'indices, calcul du classement, permissions admin.
- **Tests front** : flux d'inscription, passage des étapes, projection live, écran final.
- **Seeds** : 5 épreuves configurées et avatars prédéfinis pour démos.

## 10. Points à confirmer avant dev
- Règle exacte de classement (temps vs score, pénalité d'indices).
- Limites de taille min/max par équipe.
- Set final des avatars (emoji vs images internes).
- Méthode de temps réel retenue (websocket/poll) et contraintes de perf.
- Gestion de reprise d'une session interrompue.
