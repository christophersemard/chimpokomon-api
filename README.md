<h1 align="center">🐵 Chimpokomon API</h1>

<p align="center">
  Une API REST pédagogique pour gérer un bestiaire de créatures, leurs statistiques et leurs médias.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/Symfony-7.1-000000?style=flat-square&logo=symfony&logoColor=white" alt="Symfony 7.1" />
  <img src="https://img.shields.io/badge/Doctrine-ORM-FC6A31?style=flat-square&logo=doctrine&logoColor=white" alt="Doctrine ORM" />
  <img src="https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat-square&logo=postgresql&logoColor=white" alt="PostgreSQL 16" />
  <img src="https://img.shields.io/badge/JWT-Authentication-000000?style=flat-square&logo=jsonwebtokens&logoColor=white" alt="JWT" />
</p>

## À propos

Ce projet de formation explore la conception d'une API avec Symfony : routes REST, sérialisation, validation, persistance Doctrine, authentification JWT, cache et documentation OpenAPI. Le domaine s'inspire avec humour des Chinpokomon de *South Park*.

## Fonctionnalités

- gestion des entrées du Chimpokodex et des créatures associées ;
- création, lecture, mise à jour et suppression via une API REST ;
- suppression logique de certaines ressources grâce à leur statut ;
- authentification par jetons JWT et renouvellement des jetons ;
- contrôle d'accès par rôles ;
- cache tagué et invalidation après écriture ;
- téléversement et référencement de médias ;
- documentation interactive OpenAPI avec NelmioApiDocBundle ;
- jeu de données local généré avec les fixtures Doctrine.

## Lancer le projet en local

### Prérequis

- PHP 8.2 ou version ultérieure ;
- Composer ;
- Node.js et npm ;
- Docker, pour PostgreSQL ;
- Symfony CLI, recommandé pour le serveur local.

```bash
git clone https://github.com/christophersemard/chimpokomon-api.git
cd chimpokomon-api
composer install
npm install
docker compose up -d database
```

Créer ensuite un fichier `.env.local` et y définir au minimum des valeurs personnelles pour `APP_SECRET` et `JWT_PASSPHRASE`, puis générer les clés JWT :

```bash
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:schema:create
php bin/console doctrine:fixtures:load
npm run build
symfony server:start
```

L'interface est alors accessible sur `https://127.0.0.1:8000` et la documentation OpenAPI sur `https://127.0.0.1:8000/api/doc`.

## Principaux endpoints

| Méthode | Route | Rôle |
| --- | --- | --- |
| `POST` | `/api/login_check` | Obtenir un jeton JWT |
| `POST` | `/api/token/refresh` | Renouveler un jeton |
| `GET`, `POST` | `/api/chimpokomons` | Lister ou créer des créatures |
| `GET`, `PUT`, `DELETE` | `/api/chimpokomons/{id}` | Consulter, modifier ou supprimer une créature |
| `GET`, `POST` | `/api/chimpokodexs` | Lister ou créer des espèces |
| `GET`, `PUT`, `DELETE` | `/api/chimpokodexs/{id}` | Gérer une espèce |
| `POST` | `/api/media` | Ajouter un média |

Les routes métier sont protégées par JWT. La documentation donne le détail des schémas et des réponses disponibles.

## État du projet

Le dépôt représente un travail de formation conservé comme démonstration technique. Il n'est pas destiné à la production : la couverture de tests reste à compléter, le schéma est initialisé localement sans migrations versionnées et les valeurs du `.env` sont uniquement des exemples de développement.

> Projet mis en avant dans la vitrine GitHub ; documentation revue en août 2026.

## Auteur

Projet réalisé par [Christopher Semard](https://github.com/christophersemard) dans le cadre de sa formation en développement web.
