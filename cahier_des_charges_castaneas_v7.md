# Cahier des Charges Fonctionnel & Design System - Application Castaneas

## 1. Contexte & Objectifs
L'application Castaneas est une solution web dédiée à la gestion globale d'une station fruitière. Elle a pour but de digitaliser l'ensemble de la chaîne d'approvisionnement, du dépôt initial des fruits par les producteurs jusqu'à la préparation des commandes destinées aux clients finals.

L'objectif principal est d'assurer une traçabilité sans faille (norme GGN, suivi par numéro de palox) et de fluidifier le travail des opérateurs en atelier grâce à un système simple d'impression d'étiquettes et de mise à jour automatique des stocks.

---

## 2. Charte Graphique & UI/UX Design

L'ensemble de l'application métier doit respecter une **identité visuelle homogène**, alignée avec la charte graphique de la marque et du site institutionnel **castaneas.fr**.

### 2.1 Typographie
* **Police principale :** **Fraunces** (Google Fonts).
  * Utilisée pour les titres de sections, en-têtes de modules, éléments de mise en valeur et numéros de cartes.
* **Police d'accompagnement / Interface (Lisibilité données) :** Une typographie sans-serif propre (ex: Inter / System Sans-Serif) peut être associée en corps de texte de tableau et de formulaires complexes si besoin pour garantir une lisibilité optimale sur écran d'atelier.

### 2.2 Palette de Couleurs & Identité Visuelle
* **Couleurs primaires :** Inspirées de la châtaigne et de l'univers naturel de Castaneas (tons marron chaud, bordeaux/châtaigne, crème/écru).
* **Code couleur UI / Statuts métier :**
  * **Disponible / Conforme :** Vert doux / Sauge.
  * **Non Conforme / Alerte :** Rouge terre cuite / Brique.
  * **Partiellement utilisé / En cours :** Ambre / Ocre.
* **Bordures & Cartes :** Arrondis doux (rounded corners), contrastes nets pour les boutons d'action et grande clarté des tableaux de données.

---

## 3. Découpage des Modules Métiers

L'application est structurée autour de 5 modules principaux :

```
+-----------------------------------------------------------------------------------+
|                               APPLICATION CASTANEAS                               |
+-----------------+-----------------+-----------------+---------------+-------------+
|   1. RÉCEPTION  |   2. CALIBRAGE  |    3. STOCK     |  4. COMMANDES |5. BACKOFFICE|
+-----------------+-----------------+-----------------+---------------+-------------+
```

---

### MODULE 1 : RÉCEPTION

Le module **Réception** permet d'enregistrer l'arrivée des fruits livrés par les fournisseurs.

* **Saisie des informations fournisseur :**
  L'opérateur sélectionne le fournisseur dans une liste. Le système associe et affiche immédiatement le code GGN (numéro d'identification obligatoire) qui lui est rattaché.

* **Saisie du lot :**
  L'opérateur renseigne le type de fruit, la variété et le poids brut global mesuré lors de la livraison.

* **Contrôle qualité & Conformité :**
  En cas de problème sur le lot (fruits abîmés, défauts d'aspect), l'opérateur peut cocher l'option "Non conforme". Un champ de texte apparaît alors pour saisir l'explication du défaut.

* **Étiquetage de réception :**
  À la validation, le système génère un numéro de réception unique et déclenche l'impression d'une étiquette récapitulative (Date, N° de réception, Fournisseur, Code GGN, Fruit, Variété, Poids brut, et mention "Non conforme" si applicable).

* **Traçabilité Opérateur :**
  Chaque enregistrement de réception capture automatiquement l'identifiant de l'opérateur connecté et l'horodatage précis de la saisie.

---

### MODULE 2 : CALIBRAGE

Le module **Calibrage** intervient lors du tri des fruits pour préparer les caisses de stock (appelées palox).

* **Recherche du lot :**
  L'opérateur saisit ou scanne le numéro de réception. L'application charge automatiquement le nom du fournisseur, la variété et le code GGN associé.

* **Qualification des fruits :**
  L'opérateur sélectionne le calibre obtenu, le type de tare (poids du palox vide), et saisit le poids net de fruits calibrés ainsi que le poids de déchet écarté lors du tri.

* **Création du Palox :**
  La validation génère automatiquement un numéro de palox unique et imprime l'étiquette définitive à coller sur le palox. Le produit est alors transféré directement dans l'inventaire actif.

* **Traçabilité Opérateur :**
  L'opérateur effectuant le calibrage et la création du palox est enregistré dans l'historique du palox.

---

### MODULE 3 : STOCK

Le module **Stock** offre une vue d'ensemble sur le parc de palox disponibles dans l'entrepôt et permet d'en gérer le suivi.

* **Affichage sous forme de tableau :**
  La liste présente l'ensemble des palox enregistrés avec leurs informations clés : Fruit, Fournisseur, Variété, N° de Palox, Calibre, Poids net restant, Contrat / En cours, et Statut de disponibilité.

* **Organisation par onglets :**
  * **Stock Général :** Affiche l'ensemble des palox calibrés et prêts à être vendus.
  * **Non-Conformes :** Onglet dédié regroupant les réceptions marquées non conformes qui n'ont pas encore été calibrées ou traitées, afin de ne pas les confondre avec le stock marchand.

* **Menu d'actions sur chaque palox :**
  Chaque ligne du tableau propose un menu permettant :
  * De **réimprimer l'étiquette** du palox si celle-ci est endommagée.
  * De **modifier le poids** restant si un réajustement manuel est nécessaire.
  * De **consulter l'historique de traçabilité** : une fenêtre affiche l'origine complète du palox (réception, fournisseur, GGN) ainsi que la liste de toutes les commandes auxquelles il a contribué.

---

### MODULE 4 : COMMANDES

Le module **Commandes** gère la préparation des expéditions clients et l'affectation du stock.

* **Création d'une commande :**
  Lors de la création d'une nouvelle commande, l'opérateur remplit les champs suivants :
  1. **Nom du client**
  2. **Numéro de commande** (ex: saisie ou génération du N° de commande/bon de commande client)
  3. **Choix et attribution des Palox :**
     * Affichage de la liste du stock **filtrée par variété**.
     * **Filtre strict de disponibilité :** Seuls les palox ayant le statut **"Disponible"** (ou utilisables) sont affichés et sélectionnables.
     * Pour chaque palox sélectionné, l'opérateur saisit le poids net prélevé.

* **Décrémentation automatique du stock :**
  Dès qu'un palox est associé à la commande avec un poids prélevé, le poids net restant sur le palox est automatiquement diminué. Si la totalité du poids est utilisée, son statut bascule automatiquement hors de la liste des palox disponibles.

* **Vue détaillée et historique d'une commande :**
  Une fiche récapitulative permet de voir l'ensemble des commandes enregistrées, et dans le détail de chacune :
  * Quels palox y ont été attribués.
  * Quel poids a été prélevé palox par palox.
  * L'opérateur qui a créé ou préparé la commande.

---

### MODULE 5 : BACKOFFICE SUPERADMIN (Gestion des Référentiels & Logs d'Activité)

Le module **Backoffice Superadmin** est un espace sécurisé réservé aux administrateurs pour piloter l'ensemble des référentiels de données de l'application et superviser l'activité des utilisateurs. Tout le paramétrage métier y est entièrement éditable (ajout, modification, désactivation).

* **Gestion des Fruits & Variétés :**
  * Ajout / Édition / Suppression de types de fruits (ex: Châtaigne, Cerise, Marron).
  * Création et association des **variétés** rattachées à chaque fruit.

* **Gestion des Fournisseurs & Codes GGN :**
  * Ajout / Édition des fournisseurs avec saisie/mise à jour obligatoire du **Code GGN**.

* **Gestion des Calibres & Tares :**
  * Paramétrage des calibres autorisés par type de fruit.
  * Définition des types de tares pré-enregistrées (poids des caisses/palox).

* **Gestion des Utilisateurs & Rôles :**
  * Gestion des comptes opérateurs d'atelier et des accès Superadmin.

* **Journal d'Audit & Traçabilité Opérateurs (Accès Exclusif Superadmin) :**
  * **Enregistrement automatique systématique :** Toutes les actions effectuées dans l'application (création de réception, calibrage, modification de poids, création de commande, réimpression d'étiquette, etc.) sont systématiquement associées à l'opérateur connecté et horodatées.
  * **Visibilité restreinte :** L'accès à l'historique détaillé des actions ("Qui a fait quoi et quand") est **exclusivement réservé au profil Superadmin** dans le Backoffice. Les opérateurs standards n'ont pas accès à ces journaux d'audit.

---

## 4. Arbitrages de Cadrage Validés

Les points suivants sont considérés comme validés pour la première version de l'application.

### 4.1 Authentification
* **Type d'authentification :** login interne simple pour le moment.
* **Profils minimums :**
  * **Opérateur :** accès aux modules métier selon ses droits.
  * **Superadmin :** accès au backoffice complet et au journal d'audit détaillé.

### 4.2 Gestion du Stock
* **Règle de stock restant :** le poids net restant d'un palox est géré avec une règle de **0 exact**.
* **Disponibilité :** dès que le poids net restant atteint **0**, le palox sort automatiquement de la liste des palox disponibles.
* **Contrôle de saisie :** l'application doit empêcher toute affectation d'un poids supérieur au poids net restant disponible.

### 4.3 Réceptions Non Conformes
* Une réception marquée **Non conforme** est enregistrée dans le stock avec un **statut Non conforme**.
* Ces éléments doivent apparaître dans un **sous-onglet séparé** du module Stock, distinct du stock marchand disponible.
* Ils ne doivent **jamais être proposés** dans la sélection des palox lors de la création d'une commande client.

### 4.4 Impression
* **Format d'impression :** format **étiquette**.
* Cette contrainte s'applique aux impressions de réception ainsi qu'aux impressions des étiquettes palox.

### 4.5 Filtres & Recherche
* Des **filtres doivent être disponibles dans tous les modules** comportant des listes, tableaux ou historiques.
* Les filtres devront être adaptés au contexte métier, par exemple : fournisseur, fruit, variété, statut, date, numéro de réception, numéro de palox, client, numéro de commande, opérateur.

### 4.6 Modification des Commandes
* Une commande peut être **modifiée uniquement sur son numéro de commande**.
* Les autres informations d'une commande validée, en particulier les palox affectés et les poids prélevés, doivent être considérées comme non modifiables via l'interface standard.
