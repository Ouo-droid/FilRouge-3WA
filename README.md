# FilRouge — Application de gestion de projets

Application web PHP MVC développée dans le cadre du fil rouge Kentec / AzTech. Elle permet de gérer des projets, des tâches, des clients, des équipes et des utilisateurs.

---

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP 8.2 (MVC maison, sans framework) |
| Base de données | PostgreSQL 17 |
| Frontend | SCSS + Bootstrap 5 + Webpack |
| Serveur | Apache 2 (mod_php, mpm_prefork) |
| Conteneurisation | Docker + Docker Compose |
| Déploiement | Railway |

---

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et démarré
- [Node.js](https://nodejs.org/) (pour compiler les assets en dev)
- [Composer](https://getcomposer.org/) (si vous travaillez sans Docker)

---

## Lancer le projet en local (Docker)

### 1. Cloner le dépôt

```bash
git clone https://github.com/VotreNom/FilRouge.git
cd FilRouge
```

### 2. Configurer le fichier `.env`

Copiez le fichier d'exemple et remplissez vos valeurs :

```bash
cp .env.example .env
```

Variables requises :

```env
# Base de données PostgreSQL
POSTGRES_HOST=postgres
POSTGRES_PORT=5432
POSTGRES_DB=filrouge
POSTGRES_USER=votre_utilisateur
POSTGRES_PASSWORD=votre_mot_de_passe

# Namespaces PHP
CONTROLLER_NAMESPACE=Kentec\App\Controller\
MODEL_NAMESPACE=Kentec\App\Model\

# Debug
DEBUG=true
```

> **Important :** `POSTGRES_HOST` doit valoir `postgres` (nom du service Docker) et non `localhost`.

### 3. Démarrer les conteneurs

```bash
docker compose up --build
```

L'application est accessible sur **http://localhost:8080**

Pour stopper :

```bash
docker compose down
```

Pour repartir de zéro (base de données incluse) :

```bash
docker compose down -v
docker compose up --build
```

---

## Services Docker

| Service | URL / Port | Description |
|---|---|---|
| `php_app` | http://localhost:8080 | Application PHP + Apache |
| `postgres_db` | `localhost:5432` | Base de données PostgreSQL |
| `pgadmin` | http://localhost:5050 | Interface pgAdmin (admin / admin) |

---

## Compiler les assets (SCSS / JS)

Les assets se trouvent dans `assets/` et sont compilés vers `public/build/` via Webpack.

```bash
# Installer les dépendances Node
npm install

# Compilation unique
npm run dev

# Surveillance automatique (mode développement)
npm run watch
```

> En développement, lancez `npm run watch` en parallèle de Docker pour que les modifications SCSS/JS soient prises en compte à la volée.

---

## Architecture du projet

```
FilRouge/
├── assets/                  # Sources frontend
│   ├── app.js               # Point d'entrée JS
│   └── styles/
│       ├── abstracts/       # Tokens de couleurs, mixins, variables
│       ├── base/            # Reset, layout global
│       ├── components/      # Appbar, modals, forms, cards, dropdown…
│       └── pages/           # Styles par page (login, projects, tasks…)
│
├── docker/
│   ├── apache/
│   │   └── 000-default.conf # VirtualHost Apache → DocumentRoot public/
│   └── php/
│       └── conf.d/
│           └── xdebug.ini   # Config Xdebug pour VS Code
│
├── public/                  # Point d'entrée web (index.php + assets compilés)
│   └── build/               # Fichiers compilés par Webpack
│
├── src/
│   ├── Controller/          # Contrôleurs de l'application
│   ├── Model/               # Modèles (entités)
│   └── Views/               # Vues PHP (HTML + PHP)
│
├── Kernel/                  # Cœur du micro-framework
│   ├── Router.php
│   ├── AbstractController.php
│   ├── Repository.php
│   ├── SqlBuilder.php
│   ├── Hydrator.php
│   └── Database.php
│
├── Dockerfile               # Image PHP 8.2 Apache
├── docker-compose.yml       # Orchestration locale
├── entrypoint.sh            # Script de démarrage du conteneur
├── routes.php               # Définition des routes
├── webpack.config.js        # Config Webpack
└── GenDBWithFewDatas.sql    # Seed initial de la base de données
```

---

## Ajouter une page (rappel MVC)

### 1. Contrôleur — `src/Controller/ExempleController.php`

```php
<?php

namespace Kentec\App\Controller;

use Kentec\Kernel\Utils\AbstractController;

class ExempleController extends AbstractController
{
    public function index(): void
    {
        $this->render('exemple/index.php', ['title' => 'Exemple']);
    }
}
```

### 2. Vue — `src/Views/exemple/index.php`

```php
<h1><?= $title ?></h1>
<p>Contenu de la page.</p>
```

### 3. Route — `routes.php`

```php
'/exemple' => [
    'HTTP_METHODS' => ['GET'],
    'CONTROLLER'   => 'ExempleController',
    'METHOD'       => 'index',
],
```

---

## Tokens de couleurs SCSS

Toutes les couleurs de l'application sont centralisées dans `assets/styles/abstracts/_token.scss`. Pour changer la couleur principale, modifiez uniquement ce fichier.

```scss
// Couleur principale
$clr-indigo:        #6366f1;
$clr-indigo-dark:   #4f46e5;
$clr-indigo-darker: #4338ca;

// Couleurs utilitaires
$clr-blue:          #3b82f6;
$clr-blue-light:    #dbeafe;
$clr-green:         #22c55e;
$clr-red:           #dc2626;
$clr-red-light:     #fef2f2;
$clr-text:          #0f172a;
```

---

## Workflow Git

- La branche principale est `develop`
- Créez une branche par fonctionnalité : `feature/nom-de-la-feature`
- Une fois terminée, ouvrez une Pull Request vers `develop`
- Ne poussez jamais directement sur `main`

```bash
git checkout -b feature/ma-fonctionnalite
# ... travail ...
git add fichier1 fichier2
git commit -m "feat: description de la fonctionnalité"
git push origin feature/ma-fonctionnalite
```
