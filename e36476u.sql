-- phpMyAdmin SQL Dump
-- version 5.2.3-1.el9
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : sam. 15 nov. 2025 à 12:30
-- Version du serveur : 10.5.29-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `e36476u`
--

-- --------------------------------------------------------

--
-- Structure de la table `ApourGenre`
--

CREATE TABLE `ApourGenre` (
  `id` int(11) NOT NULL,
  `idGenre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `ApourGenre`
--

INSERT INTO `ApourGenre` (`id`, `idGenre`) VALUES
(2, 2),
(2, 4),
(3, 3),
(3, 4),
(4, 4),
(6, 2);

-- --------------------------------------------------------

--
-- Structure de la table `ApourPublic`
--

CREATE TABLE `ApourPublic` (
  `id` int(11) NOT NULL,
  `idPublic` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `ApourPublic`
--

INSERT INTO `ApourPublic` (`id`, `idPublic`) VALUES
(3, 1),
(4, 2),
(6, 3);

-- --------------------------------------------------------

--
-- Structure de la table `episode`
--

CREATE TABLE `episode` (
  `codeEpisode` int(11) NOT NULL,
  `numero` int(11) NOT NULL DEFAULT 1,
  `titre` varchar(128) NOT NULL,
  `resume` text DEFAULT NULL,
  `duree` int(11) NOT NULL DEFAULT 0,
  `file` varchar(256) DEFAULT NULL,
  `serie_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `episode`
--

INSERT INTO `episode` (`codeEpisode`, `numero`, `titre`, `resume`, `duree`, `file`, `serie_id`) VALUES
(1, 1, 'Le lac', 'Le lac se révolte ', 8, 'lake.mp4', 1),
(2, 2, 'Le lac : les mystères de l\'eau trouble', 'Un grand mystère, l\'eau du lac est trouble. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(3, 1, 'Le lac : les mystères de l\'eau sale', 'Un grand mystère, l\'eau du lac est sale. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(4, 2, 'Le lac : les mystères de l\'eau chaude', 'Un grand mystère, l\'eau du lac est chaude. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(5, 3, 'Le lac : les mystères de l\'eau froide', 'Un grand mystère, l\'eau du lac est froide. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(6, 1, 'Eau calme', 'L\'eau coule tranquillement au fil du temps.', 15, 'water.mp4', 2),
(7, 2, 'Eau calme 2', 'Le temps a passé, l\'eau coule toujours tranquillement.', 15, 'water.mp4', 2),
(8, 3, 'Eau moins calme', 'Le temps des tourments est pour bientôt, l\'eau s\'agite et le temps passe.', 15, 'water.mp4', 2),
(9, 4, 'la tempête', 'C\'est la tempête, l\'eau est en pleine agitation. Le temps passe mais rien n\'y fait. Jack trouvera-t-il la solution ?', 15, 'water.mp4', 2),
(10, 5, 'Le calme après la tempête', 'La tempête est passée, l\'eau retrouve son calme. Le temps passe et Jack part en vacances.', 15, 'water.mp4', 2),
(11, 1, 'les chevaux s\'amusent', 'Les chevaux s\'amusent bien, ils ont apportés les raquettes pour faire un tournoi de badmington.', 7, 'horses.mp4', 3),
(12, 2, 'les chevals fous', '- Oh regarde, des beaux chevals !!\r\n- non, des chevaux, des CHEVAUX !\r\n- oh, bin ça alors, ça ressemble drôlement à des chevals ?!!?', 7, 'horses.mp4', 3),
(13, 3, 'les chevaux de l\'étoile noire', 'Les chevaux de l\'Etoile Noire débrquent sur terre et mangent toute l\'herbe !', 7, 'horses.mp4', 3),
(14, 1, 'Tous à la plage', 'C\'est l\'été, tous à la plage pour profiter du soleil et de la mer.', 18, 'beach.mp4', 4),
(15, 2, 'La plage le soir', 'A la plage le soir, il n\'y a personne, c\'est tout calme', 18, 'beach.mp4', 4),
(16, 3, 'La plage le matin', 'A la plage le matin, il n\'y a personne non plus, c\'est tout calme et le jour se lève.', 18, 'beach.mp4', 4),
(17, 1, 'champion de surf', 'Jack fait du surf le matin, le midi le soir, même la nuit. C\'est un pro.', 11, 'surf.mp4', 5),
(18, 2, 'surf détective', 'Une planche de surf a été volée. Jack mène l\'enquête. Parviendra-t-il à confondre le brigand ?', 11, 'surf.mp4', 5),
(19, 3, 'surf amitié', 'En fait la planche n\'avait pas été volée, c\'est Jim, le meilleur ami de Jack, qui lui avait fait une blague. Les deux amis partagent une menthe à l\'eau pour célébrer leur amitié sans faille.', 11, 'surf.mp4', 5),
(20, 1, 'Ça roule, ça roule', 'Ça roule, ça roule toute la nuit. Jack fonce dans sa camionnette pour rejoindre le spot de surf.', 27, 'cars-by-night.mp4', 6),
(21, 2, 'Ça roule, ça roule toujours', 'Ça roule la nuit, comme chaque nuit. Jim fonce avec son taxi, pour rejoindre Jack à la plage. De l\'eau a coulé sous les ponts. Le mystère du Lac trouve sa solution alors que les chevaux sont de retour après une virée sur l\'Etoile Noire.', 27, 'cars-by-night.mp4', 6);

-- --------------------------------------------------------

--
-- Structure de la table `Genre`
--

CREATE TABLE `Genre` (
  `idGenre` int(11) NOT NULL,
  `libelle` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Genre`
--

INSERT INTO `Genre` (`idGenre`, `libelle`) VALUES
(1, 'Action'),
(2, 'Horreur'),
(3, 'Thriller'),
(4, 'Romance'),
(5, 'Anime');

-- --------------------------------------------------------

--
-- Structure de la table `GenrePrefere`
--

CREATE TABLE `GenrePrefere` (
  `emailUser` varchar(50) NOT NULL,
  `idGenre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `GenrePrefere`
--

INSERT INTO `GenrePrefere` (`emailUser`, `idGenre`) VALUES
('06tainor@gmail.com', 3),
('06tainor@gmail.com', 5),
('jordan2.0yt@gmail.com', 5),
('user2@gmail.com', 1),
('user2@gmail.com', 3);

-- --------------------------------------------------------

--
-- Structure de la table `PhotoProfil`
--

CREATE TABLE `PhotoProfil` (
  `idPhoto` int(11) NOT NULL,
  `img` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `PhotoProfil`
--

INSERT INTO `PhotoProfil` (`idPhoto`, `img`) VALUES
(1, 'profile1.png'),
(2, 'profile2.png'),
(3, 'profile3.png'),
(4, 'profile4.png'),
(5, 'profile5.png'),
(6, 'profile6.png'),
(7, 'profile7.png'),
(8, 'profile8.png'),
(9, 'profile9.png'),
(10, 'profile10.png'),
(11, 'profile11.png'),
(12, 'profile12.png'),
(13, 'profile13.png'),
(14, 'profile14.png'),
(15, 'profile15.png'),
(16, 'profile16.png');

-- --------------------------------------------------------

--
-- Structure de la table `Public`
--

CREATE TABLE `Public` (
  `idPublic` int(11) NOT NULL,
  `typePublic` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Public`
--

INSERT INTO `Public` (`idPublic`, `typePublic`) VALUES
(1, 'Tout le monde'),
(2, 'Enfant'),
(3, 'Adulte');

-- --------------------------------------------------------

--
-- Structure de la table `serie`
--

CREATE TABLE `serie` (
  `id` int(11) NOT NULL,
  `titre` varchar(128) NOT NULL,
  `descriptif` text NOT NULL,
  `img` varchar(256) NOT NULL,
  `annee` int(11) NOT NULL,
  `date_ajout` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `serie`
--

INSERT INTO `serie` (`id`, `titre`, `descriptif`, `img`, `annee`, `date_ajout`) VALUES
(1, 'Le lac aux mystères', 'C\'est l\'histoire d\'un lac mystérieux et plein de surprises. La série, bluffante et haletante, nous entraine dans un labyrinthe d\'intrigues époustouflantes. A ne rater sous aucun prétexte !', 'lac.png', 2020, '2022-10-30'),
(2, 'L\'eau a coulé', 'Une série nostalgique qui nous invite à revisiter notre passé et à se remémorer tout ce qui s\'est passé depuis que tant d\'eau a coulé sous les ponts.', 'eau.png', 1907, '2022-10-29'),
(3, 'Chevaux fous', 'Une série sur la vie des chevals sauvages en liberté. Décoiffante.', 'cheval.png', 2017, '2022-10-31'),
(4, 'A la plage', 'Le succès de l\'été 2021, à regarder sans modération et entre amis.', 'plage.png', 2021, '2022-11-04'),
(5, 'Champion', 'La vie trépidante de deux champions de surf, passionnés dès leur plus jeune age. Ils consacrent leur vie à ce sport. ', 'champion.png', 2022, '2022-11-03'),
(6, 'Une ville la nuit', 'C\'est beau une ville la nuit, avec toutes ces voitures qui passent et qui repassent. La série suit un livreur, un chauffeur de taxi, et un insomniaque. Tous parcourent la grande ville une fois la nuit venue, au volant de leur véhicule.', 'ville.png', 2017, '2022-10-31');

-- --------------------------------------------------------

--
-- Structure de la table `StatutSerie`
--

CREATE TABLE `StatutSerie` (
  `id` int(11) NOT NULL,
  `mailUser` varchar(50) NOT NULL,
  `commentaire` varchar(100) DEFAULT NULL,
  `datecommentaire` date DEFAULT NULL,
  `favori` int(1) DEFAULT NULL,
  `statut` varchar(30) DEFAULT NULL,
  `note` int(1) DEFAULT NULL,
  `codeEpisode` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `StatutSerie`
--

INSERT INTO `StatutSerie` (`id`, `mailUser`, `commentaire`, `datecommentaire`, `favori`, `statut`, `note`, `codeEpisode`) VALUES
(1, '06tainor@gmail.com', 'Le vrai mystère est comment les gens peuvent aimer cette série', NULL, 1, 'en cours', 2, 2),
(1, 'jordan2.0yt@gmail.com', NULL, NULL, 0, 'en cours', NULL, 3),
(1, 'saget.logan@gmail.com', NULL, NULL, NULL, 'en cours', NULL, 5),
(2, '06tainor@gmail.com', NULL, NULL, 1, 'en cours', NULL, 9),
(2, 'jean-eude@gmail.com', NULL, NULL, 1, NULL, NULL, NULL),
(2, 'jordan2.0yt@gmail.com', 'L\'eau a belle est bien coulé ', NULL, 1, 'fini', 4, 10),
(2, 'saget.logan@gmail.com', NULL, NULL, 1, NULL, NULL, NULL),
(2, 'user2@gmail.com', NULL, NULL, 1, NULL, NULL, NULL),
(3, '06tainor@gmail.com', 'La mort de Super-Cheval à la fin était très émouvante', '2025-11-05', 0, 'en cours', 5, 12),
(3, 'jean-eude@gmail.com', NULL, NULL, 1, NULL, NULL, NULL),
(3, 'jordan2.0yt@gmail.com', 'Mais ils sont fous, ces chevaux', '2025-11-05', 1, 'fini', 4, 13),
(3, 'saget.logan@gmail.com', NULL, NULL, NULL, 'en cours', NULL, 13),
(3, 'user2@gmail.com', 'Super série je suis fan', NULL, 1, 'en cours', 4, 12),
(4, '06tainor@gmail.com', 'J\'aime la plage', '2025-11-05', 0, 'en cours', 3, 15),
(4, 'jordan2.0yt@gmail.com', NULL, NULL, 0, 'fini', NULL, NULL),
(4, 'saget.logan@gmail.com', NULL, NULL, NULL, 'fini', NULL, 15),
(4, 'user2@gmail.com', NULL, NULL, 1, NULL, NULL, NULL),
(5, '06tainor@gmail.com', 'En tant que fan de surf cette série je trouve la série très mauvaise', NULL, 1, 'en cours', 1, NULL),
(5, 'jordan2.0yt@gmail.com', NULL, NULL, 0, 'en cours', NULL, 18),
(5, 'saget.logan@gmail.com', NULL, NULL, 1, 'fini', NULL, 19),
(5, 'user2@gmail.com', NULL, NULL, NULL, 'en cours', NULL, 18),
(6, '06tainor@gmail.com', NULL, NULL, 1, 'fini', NULL, NULL),
(6, 'jordan2.0yt@gmail.com', 'Pas terrible', NULL, 0, 'en cours', 2, 21),
(6, 'saget.logan@gmail.com', NULL, NULL, 1, 'fini', NULL, NULL),
(6, 'user2@gmail.com', NULL, NULL, 1, 'fini', NULL, 21);

-- --------------------------------------------------------

--
-- Structure de la table `Token`
--

CREATE TABLE `Token` (
  `mailUser` varchar(50) NOT NULL,
  `token` varchar(100) NOT NULL,
  `valider` int(1) DEFAULT NULL,
  `dateExpi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Token`
--

INSERT INTO `Token` (`mailUser`, `token`, `valider`, `dateExpi`) VALUES
('06tainor@gmail.com', 'aa', 1, '2025-11-05'),
('jean-eude@gmail.com', '9b49141e6c05d2bb4b1cf001a1140e54501d5c871c9fa948905bfe364c9ab836', 1, '2025-11-14'),
('jordan2.0yt@gmail.com', 'aa', 1, '2025-11-05'),
('saget.logan@gmail.com', 'aa', 1, '2025-11-05'),
('user1@gmail.com', '1e431cea374c0dbdba5b71417b3d0457662aef20818d38c8b54b7ceda420adbf', 0, '2025-11-15'),
('user2@gmail.com', '469cc2e95fa25b8a52a0a9cb9667a2aa93406b47d61e8d60bc4e8cc913a4bdea', 0, '2025-11-15');

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `mailUser` varchar(50) NOT NULL,
  `nomUser` varchar(30) DEFAULT NULL,
  `prenomUser` varchar(30) DEFAULT NULL,
  `passwd` varchar(100) DEFAULT NULL,
  `numeroCarte` varchar(100) DEFAULT NULL,
  `role` varchar(30) DEFAULT NULL,
  `idPhoto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Utilisateur`
--

INSERT INTO `Utilisateur` (`mailUser`, `nomUser`, `prenomUser`, `passwd`, `numeroCarte`, `role`, `idPhoto`) VALUES
('06tainor@gmail.com', 'Hermann', 'Taïno', '$2y$12$wl30Pc/OEuNnUJ8sz.5OL.eFhl.N3ieEVpnu8L1MaLuy7C/cMC3ne', '$2y$10$J32e/Z8DaleCFq8QGKdhmOFawGBcvGOQWQqL67f.2t0.4TXkp.QX6', '1', 8),
('jean-eude@gmail.com', 'Zuckenberg', 'Marc', '$2y$12$tKgQcTUatZGr/rzgL3VSNurSDSMv1BxBFttyjMxbN7kCY8quib1lG', '$2y$10$9m4dRCS3avVpzVlAaqntM.UMRW1dvCuDSAYUlwc1a0ZCMKMlBUmaO', '1', 8),
('jordan2.0yt@gmail.com', 'Vigneulle', 'Jordan', '$2y$12$T3jQzs4wDKHG/98g94yniulQ7NTvWi2AwciCLlrJQ72qssK4Lgm7G', '$2y$10$BGqrcP6.nin.xXlFMU5z1O3m42Ayn4tuvZEg0Fp2RlSRANGFElhnS', '1', 3),
('saget.logan@gmail.com', 'Saget', 'Logan', '$2y$12$0LY6R7qkS4/WXRW0TKLLGeCsAeGAG4uYZU1krFKB4KpjAiIFV/mLe', '$2y$10$3yGeJ/aHY6HSKsWprgw5z.00mfVL9LqCOMNsIJU88S8LkGBb/ZqUW', '1', 8),
('user1@gmail.com', 'Cruise', 'Tom', '$2y$12$gKigw.hdfJpr9fo3W.Ghp.9XTB87dpVpWhLhhfglHRcUFhIj93Gxm', '$2y$10$Vx1AyxaG6syhS.CulkKc6Oz1g.lIzFN0oF/DxSTsxeDlSNRmQ9CdC', '1', NULL),
('user2@gmail.com', 'Dujardin', 'Jean', '$2y$12$2oAwLMzNx16bUwyqrR3RReJR946Z37mTbxRdWZh8UlltjtxcL2Anq', '$2y$10$cwjCLcui5Gg63WgeZ3H6kefRLCBgTAnc0cDfsQ08cfnBs68xO/PM.', '1', 15);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ApourGenre`
--
ALTER TABLE `ApourGenre`
  ADD PRIMARY KEY (`id`,`idGenre`),
  ADD KEY `idGenre` (`idGenre`);

--
-- Index pour la table `ApourPublic`
--
ALTER TABLE `ApourPublic`
  ADD PRIMARY KEY (`id`,`idPublic`),
  ADD KEY `idPublic` (`idPublic`);

--
-- Index pour la table `episode`
--
ALTER TABLE `episode`
  ADD PRIMARY KEY (`codeEpisode`),
  ADD KEY `serie_id` (`serie_id`);

--
-- Index pour la table `Genre`
--
ALTER TABLE `Genre`
  ADD PRIMARY KEY (`idGenre`);

--
-- Index pour la table `GenrePrefere`
--
ALTER TABLE `GenrePrefere`
  ADD PRIMARY KEY (`emailUser`,`idGenre`),
  ADD KEY `FK_genre` (`idGenre`);

--
-- Index pour la table `PhotoProfil`
--
ALTER TABLE `PhotoProfil`
  ADD PRIMARY KEY (`idPhoto`);

--
-- Index pour la table `Public`
--
ALTER TABLE `Public`
  ADD PRIMARY KEY (`idPublic`);

--
-- Index pour la table `serie`
--
ALTER TABLE `serie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `StatutSerie`
--
ALTER TABLE `StatutSerie`
  ADD PRIMARY KEY (`id`,`mailUser`),
  ADD KEY `mailUser` (`mailUser`),
  ADD KEY `codeEpisode` (`codeEpisode`);

--
-- Index pour la table `Token`
--
ALTER TABLE `Token`
  ADD PRIMARY KEY (`mailUser`) USING BTREE;

--
-- Index pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD PRIMARY KEY (`mailUser`),
  ADD KEY `Fk_photo` (`idPhoto`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `episode`
--
ALTER TABLE `episode`
  MODIFY `codeEpisode` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `serie`
--
ALTER TABLE `serie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `ApourGenre`
--
ALTER TABLE `ApourGenre`
  ADD CONSTRAINT `ApourGenre_ibfk_1` FOREIGN KEY (`id`) REFERENCES `serie` (`id`),
  ADD CONSTRAINT `ApourGenre_ibfk_2` FOREIGN KEY (`idGenre`) REFERENCES `Genre` (`idGenre`);

--
-- Contraintes pour la table `ApourPublic`
--
ALTER TABLE `ApourPublic`
  ADD CONSTRAINT `ApourPublic_ibfk_1` FOREIGN KEY (`id`) REFERENCES `serie` (`id`),
  ADD CONSTRAINT `ApourPublic_ibfk_2` FOREIGN KEY (`idPublic`) REFERENCES `Public` (`idPublic`);

--
-- Contraintes pour la table `episode`
--
ALTER TABLE `episode`
  ADD CONSTRAINT `episode_ibfk_1` FOREIGN KEY (`serie_id`) REFERENCES `serie` (`id`);

--
-- Contraintes pour la table `GenrePrefere`
--
ALTER TABLE `GenrePrefere`
  ADD CONSTRAINT `FK_genre` FOREIGN KEY (`idGenre`) REFERENCES `Genre` (`idGenre`),
  ADD CONSTRAINT `Fk_mail` FOREIGN KEY (`emailUser`) REFERENCES `Utilisateur` (`mailUser`);

--
-- Contraintes pour la table `StatutSerie`
--
ALTER TABLE `StatutSerie`
  ADD CONSTRAINT `StatutSerie_ibfk_1` FOREIGN KEY (`id`) REFERENCES `serie` (`id`),
  ADD CONSTRAINT `StatutSerie_ibfk_2` FOREIGN KEY (`mailUser`) REFERENCES `Utilisateur` (`mailUser`),
  ADD CONSTRAINT `StatutSerie_ibfk_3` FOREIGN KEY (`codeEpisode`) REFERENCES `episode` (`codeEpisode`);

--
-- Contraintes pour la table `Token`
--
ALTER TABLE `Token`
  ADD CONSTRAINT `Token_ibfk_1` FOREIGN KEY (`mailUser`) REFERENCES `Utilisateur` (`mailUser`);

--
-- Contraintes pour la table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD CONSTRAINT `Fk_photo` FOREIGN KEY (`idPhoto`) REFERENCES `PhotoProfil` (`idPhoto`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
