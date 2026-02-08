-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 22 avr. 2025 à 00:37
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet1`
--

-- --------------------------------------------------------

--
-- Structure de la table `annee_scolaires`
--

CREATE TABLE `annee_scolaires` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `annee_scolaires`
--

INSERT INTO `annee_scolaires` (`id`, `libelle`, `active`, `created_at`, `updated_at`) VALUES
(1, '2024-2025', 0, '2025-04-16 10:33:30', '2025-04-16 10:33:30'),
(2, '2025-2026', 0, '2025-04-16 20:36:48', '2025-04-16 20:36:48'),
(3, '2026-2027', 0, '2025-04-17 16:30:00', '2025-04-17 16:30:00');

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `cycle_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `nom`, `cycle_id`, `created_at`, `updated_at`) VALUES
(131, 'CI', 1, '2025-04-21 18:19:58', '2025-04-21 18:19:58'),
(132, 'CP', 1, '2025-04-21 18:19:58', '2025-04-21 18:19:58'),
(133, 'CE1', 1, '2025-04-21 18:19:58', '2025-04-21 18:19:58'),
(134, 'CE2', 1, '2025-04-21 18:19:58', '2025-04-21 18:19:58'),
(135, 'CM1', 1, '2025-04-21 18:19:58', '2025-04-21 18:19:58'),
(136, 'CM2', 1, '2025-04-21 18:19:58', '2025-04-21 18:19:58'),
(137, '6eme', 2, '2025-04-21 18:20:15', '2025-04-21 18:20:15'),
(138, '5eme', 2, '2025-04-21 18:20:15', '2025-04-21 18:20:15'),
(139, '4eme', 2, '2025-04-21 18:20:15', '2025-04-21 18:20:15'),
(140, '3eme', 2, '2025-04-21 18:20:15', '2025-04-21 18:20:15'),
(141, '2nde', 2, '2025-04-21 18:20:15', '2025-04-21 18:20:15'),
(142, '1ere', 2, '2025-04-21 18:20:15', '2025-04-21 18:20:15'),
(143, 'Terminal', 2, '2025-04-21 18:20:15', '2025-04-21 18:20:15');

-- --------------------------------------------------------

--
-- Structure de la table `cycles`
--

CREATE TABLE `cycles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cycles`
--

INSERT INTO `cycles` (`id`, `nom`, `created_at`, `updated_at`) VALUES
(1, 'Primaire', '2025-04-21 18:19:42', '2025-04-21 18:19:42'),
(2, 'Secondaire', '2025-04-21 18:19:42', '2025-04-21 18:19:42'),
(17, 'Primaire', '2025-04-21 18:17:06', '2025-04-21 18:17:06'),
(18, 'Secondaire', '2025-04-21 18:17:06', '2025-04-21 18:17:06');

-- --------------------------------------------------------

--
-- Structure de la table `eleves`
--

CREATE TABLE `eleves` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `sexe` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `matricule` varchar(255) DEFAULT NULL,
  `parent_tuteur_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `lieu_naissance` varchar(100) NOT NULL,
  `niveau` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `eleves`
--

INSERT INTO `eleves` (`id`, `nom`, `prenom`, `date_naissance`, `sexe`, `photo`, `matricule`, `parent_tuteur_id`, `created_at`, `updated_at`, `lieu_naissance`, `niveau`) VALUES
(11, 'GLELE', 'Jefferson', '2025-04-19', 'M', NULL, NULL, 3, '2025-04-21 18:02:55', '2025-04-21 18:02:55', 'Cotonou', ''),
(12, 'GLELE', 'Jefferson', '2025-04-16', 'M', NULL, NULL, 3, '2025-04-21 18:08:46', '2025-04-21 18:08:46', 'Cotonou', '4eme'),
(13, 'GLELE', 'Jefferson', '2025-04-10', 'M', NULL, NULL, 3, '2025-04-21 18:10:32', '2025-04-21 18:10:32', 'Cotonou', 'CE2'),
(14, 'GLELE', 'Jefferson', '2016-07-07', 'M', NULL, NULL, 3, '2025-04-21 18:21:41', '2025-04-21 18:21:41', 'Cotonou', 'CP'),
(15, 'GLELE', 'Ola', '2007-05-11', 'F', NULL, NULL, 3, '2025-04-21 18:48:39', '2025-04-21 18:48:39', 'Cotonou', '2nde');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eleve_id` bigint(20) UNSIGNED NOT NULL,
  `classe_id` bigint(20) UNSIGNED NOT NULL,
  `annee_scolaire_id` bigint(20) UNSIGNED NOT NULL,
  `date_inscription` date NOT NULL DEFAULT '2025-04-16',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inscriptions`
--

INSERT INTO `inscriptions` (`id`, `eleve_id`, `classe_id`, `annee_scolaire_id`, `date_inscription`, `created_at`, `updated_at`) VALUES
(14, 14, 132, 3, '2025-04-21', '2025-04-21 18:21:41', '2025-04-21 18:21:41'),
(15, 15, 141, 3, '2025-04-21', '2025-04-21 18:48:39', '2025-04-21 18:48:39');

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eleve_id` bigint(20) UNSIGNED NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_paiement` date NOT NULL,
  `mode_paiement` varchar(50) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type_paiement` enum('total','tranche') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `eleve_id`, `montant`, `date_paiement`, `mode_paiement`, `reference`, `description`, `created_at`, `updated_at`, `type_paiement`) VALUES
(1, 14, 50000.00, '2025-04-21', 'MomoPay', NULL, NULL, '2025-04-21 18:30:13', '2025-04-21 18:30:13', 'total'),
(2, 14, 85000.00, '2025-04-21', 'CeltiisCash', NULL, NULL, '2025-04-21 18:41:51', '2025-04-21 18:41:51', 'total');

-- --------------------------------------------------------

--
-- Structure de la table `parent_tuteurs`
--

CREATE TABLE `parent_tuteurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `parent_tuteurs`
--

INSERT INTO `parent_tuteurs` (`id`, `user_id`, `nom`, `prenom`, `telephone`, `email`, `password`, `created_at`, `updated_at`) VALUES
(3, 2, 'Madina AKADI', '', '0164780067', 'micheeglele@gmail.com', NULL, '2025-04-21 17:55:14', '2025-04-21 17:55:14');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('micheeglele@gmail.com', '1798424ece221f1387a8c6e6443fd5c6801b043d7a38d4cc62152519185bac59', '2025-04-21 21:02:19');

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eleve_id` bigint(20) UNSIGNED NOT NULL,
  `montant` int(11) NOT NULL,
  `type_paiement` varchar(20) NOT NULL,
  `mode_paiement` varchar(20) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`id`, `eleve_id`, `montant`, `type_paiement`, `mode_paiement`, `transaction_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 15, 130000, 'total', 'carte', '315892', 'pending', '2025-04-21 22:12:53', NULL),
(2, 15, 43334, 'tranche', 'carte', '315904', 'pending', '2025-04-21 22:32:30', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `telephone`, `adresse`) VALUES
(2, 'Madina AKADI', 'micheeglele@gmail.com', NULL, '$2y$10$jB1aH2mDl060eFlhbCiFV.BepyYKDdkjFLe3djBWeg.x2XNDtzlcO', NULL, '2025-04-21 17:15:26', '2025-04-21 17:15:26', '0164780067', 'COTONOU');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `annee_scolaires`
--
ALTER TABLE `annee_scolaires`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classes_cycle_id_foreign` (`cycle_id`);

--
-- Index pour la table `cycles`
--
ALTER TABLE `cycles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `eleves`
--
ALTER TABLE `eleves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleves_parent_tuteur_id_foreign` (`parent_tuteur_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscriptions_eleve_id_foreign` (`eleve_id`),
  ADD KEY `inscriptions_classe_id_foreign` (`classe_id`),
  ADD KEY `inscriptions_annee_scolaire_id_foreign` (`annee_scolaire_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paiements_eleve_id_foreign` (`eleve_id`);

--
-- Index pour la table `parent_tuteurs`
--
ALTER TABLE `parent_tuteurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parent_tuteurs_email_unique` (`email`),
  ADD KEY `parent_tuteurs_user_id_foreign` (`user_id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleve_id` (`eleve_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `annee_scolaires`
--
ALTER TABLE `annee_scolaires`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT pour la table `cycles`
--
ALTER TABLE `cycles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `eleves`
--
ALTER TABLE `eleves`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `parent_tuteurs`
--
ALTER TABLE `parent_tuteurs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `eleves`
--
ALTER TABLE `eleves`
  ADD CONSTRAINT `eleves_parent_tuteur_id_foreign` FOREIGN KEY (`parent_tuteur_id`) REFERENCES `parent_tuteurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_annee_scolaire_id_foreign` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaires` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_classe_id_foreign` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_eleve_id_foreign` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_eleve_id_foreign` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `parent_tuteurs`
--
ALTER TABLE `parent_tuteurs`
  ADD CONSTRAINT `parent_tuteurs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
