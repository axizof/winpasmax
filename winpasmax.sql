-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 29 nov. 2024 à 15:56
-- Version du serveur : 10.6.20-MariaDB
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `peyo5877_winpasmax`
--

-- --------------------------------------------------------

--
-- Structure de la table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `equipe1` varchar(50) NOT NULL,
  `equipe2` varchar(50) NOT NULL,
  `scoreEquipe1` int(11) DEFAULT 0,
  `scoreEquipe2` int(11) DEFAULT 0,
  `cote1` float NOT NULL DEFAULT 0,
  `cote2` float NOT NULL DEFAULT 0,
  `coteNul` float NOT NULL DEFAULT 0,
  `statut` varchar(50) NOT NULL,
  `datfin` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `matches`
--

INSERT INTO `matches` (`id`, `equipe1`, `equipe2`, `scoreEquipe1`, `scoreEquipe2`, `cote1`, `cote2`, `coteNul`, `statut`, `datfin`) VALUES
(1, 'Arsenal', 'Aston Villa', 0, 0, 2, 4, 0, 'pariable', '2024-11-29 14:55:28'),
(2, 'Liverpool', 'Manchester City', 0, 0, 1.5, 1.1, 2, 'pariable', '2024-11-29 14:55:31'),
(3, 'Barcelona', 'Girona', 1, 2, 1, 1.5, 2, 'resultat', '2024-11-27 13:50:57'),
(4, 'Barcelona', 'Girona', 1, 2, 1.2, 1.5, 2, 'resultat', '2024-11-27 13:50:57'),
(5, 'Real Madrid', 'Atlético de Madrid', 0, 0, 1.4, 2.2, 1.9, 'pariable', '2024-11-27 15:54:37');

-- --------------------------------------------------------

--
-- Structure de la table `parier`
--

CREATE TABLE `parier` (
  `id` int(11) NOT NULL,
  `coteActuel` float NOT NULL,
  `equipe` varchar(50) NOT NULL,
  `id_match` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ticket`
--

CREATE TABLE `ticket` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `montantParis` float NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(10000) NOT NULL,
  `role` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `balance` double(10,2) NOT NULL DEFAULT 0.00,
  `email` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`, `balance`, `email`) VALUES
(1, 'test', '$2y$10$lDCaTJyFfRyWo0wQkAdaPehE9GS6MgwUe3aSyLnpYGP7OwJ3JTqcG', 'bookmaker', '2024-11-27 12:58:48', 10.00, 'test@test.com'),
(2, 'luc', '$2y$10$g/bYnRzeu6HIXb6Jis7a2.4fQ1IJxbLGRMzxhvU8giXznBVC2GuRK', 'user', '2024-11-27 14:28:30', 0.00, 'luckas@mail.com');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `parier`
--
ALTER TABLE `parier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parier_match_FK` (`id_match`),
  ADD KEY `parier_ticket_FK` (`id_ticket`);

--
-- Index pour la table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_user_FK` (`id_user`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `parier`
--
ALTER TABLE `parier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `parier`
--
ALTER TABLE `parier`
  ADD CONSTRAINT `parier_match_FK` FOREIGN KEY (`id_match`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parier_ticket_FK` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_user_FK` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
