-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 12:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gotomarconi`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `ID` int(11) NOT NULL,
  `Mittente_ID` int(11) NOT NULL,
  `Destinatario_ID` int(11) NOT NULL,
  `Messaggio` text NOT NULL,
  `Timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`ID`, `Mittente_ID`, `Destinatario_ID`, `Messaggio`, `Timestamp`) VALUES
(1, 23, 24, 'Ciao', '2025-05-25 23:35:03'),
(2, 24, 23, 'ciao', '2025-05-25 23:38:22'),
(3, 23, 24, 'Negro', '2025-05-25 23:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `notifica`
--

CREATE TABLE `notifica` (
  `ID` int(11) NOT NULL,
  `ID_Utente` int(11) DEFAULT NULL,
  `Testo` text DEFAULT NULL,
  `Letta` tinyint(1) DEFAULT 0,
  `Timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prenotazione`
--

CREATE TABLE `prenotazione` (
  `ID` int(11) NOT NULL,
  `ID_Viaggio` int(11) DEFAULT NULL,
  `ID_Passeggero` int(11) DEFAULT NULL,
  `ID_Autista` int(11) DEFAULT NULL,
  `Stato` varchar(20) DEFAULT NULL,
  `Orario` time DEFAULT NULL,
  `Notificata` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prenotazione`
--

INSERT INTO `prenotazione` (`ID`, `ID_Viaggio`, `ID_Passeggero`, `ID_Autista`, `Stato`, `Orario`, `Notificata`) VALUES
(8, 29, 24, NULL, 'Accettata', NULL, 1),
(9, 31, 24, NULL, 'Accettata', NULL, 1),
(10, 30, 24, NULL, 'Accettata', NULL, 1),
(11, 32, 24, NULL, 'Accettata', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `recensione`
--

CREATE TABLE `recensione` (
  `ID` int(11) NOT NULL,
  `ID_Autore` int(11) DEFAULT NULL,
  `ID_Utente` int(11) DEFAULT NULL,
  `Voto` int(11) DEFAULT NULL CHECK (`Voto` >= 1 and `Voto` <= 5),
  `Commento` text DEFAULT NULL,
  `Data` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recensione`
--

INSERT INTO `recensione` (`ID`, `ID_Autore`, `ID_Utente`, `Voto`, `Commento`, `Data`) VALUES
(1, 24, 23, 5, 'Perchè guida bene', '2025-05-25 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `utente`
--

CREATE TABLE `utente` (
  `ID` int(11) NOT NULL,
  `Nome` varchar(50) DEFAULT NULL,
  `Cognome` varchar(50) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Ruolo` enum('Autista','Passeggero') DEFAULT 'Passeggero',
  `Citta` varchar(100) NOT NULL DEFAULT 'Roma',
  `FotoProfilo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utente`
--

INSERT INTO `utente` (`ID`, `Nome`, `Cognome`, `Email`, `Telefono`, `Password`, `Ruolo`, `Citta`, `FotoProfilo`) VALUES
(23, 'Alessandro', 'Arcenni', 'alessandro.arcenni@gmail.com', '1234567890', '$2y$10$r4l6tkT4oS8i5FOuQenI3e.z5fPJoxx6vsSs.2srWnrMqliHJGH6m', 'Autista', 'Ponsacco', NULL),
(24, 'Matteo Stefan', 'Ciuca', 'matteociuca@gmail.com', '3216549870', '$2y$10$L7r2X.8y8B5nELXkyOC2R.QDMRAGpN7m76GXrlJkZsLV6j3QE9ava', 'Passeggero', 'Ponsacco', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `veicolo`
--

CREATE TABLE `veicolo` (
  `Targa` varchar(10) NOT NULL,
  `Modello` varchar(50) DEFAULT NULL,
  `Colore` varchar(30) DEFAULT NULL,
  `Posti` int(11) DEFAULT NULL,
  `ID_Proprietario` int(11) DEFAULT NULL,
  `Marca` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `veicolo`
--

INSERT INTO `veicolo` (`Targa`, `Modello`, `Colore`, `Posti`, `ID_Proprietario`, `Marca`) VALUES
('AA340SD', 'M3', 'Nero', 4, 23, 'BMW');

-- --------------------------------------------------------

--
-- Table structure for table `viaggio`
--

CREATE TABLE `viaggio` (
  `ID` int(11) NOT NULL,
  `Partenza` varchar(100) DEFAULT NULL,
  `Data` datetime DEFAULT NULL,
  `Posti` int(11) DEFAULT NULL,
  `ID_Autista` int(11) DEFAULT NULL,
  `Note` text DEFAULT NULL,
  `Completato` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `viaggio`
--

INSERT INTO `viaggio` (`ID`, `Partenza`, `Data`, `Posti`, `ID_Autista`, `Note`, `Completato`) VALUES
(29, 'Casa → Lavoro', '2025-05-27 00:00:00', 2, 23, 'A', 1),
(30, 'Casa → Lavoro', '2025-05-27 00:00:00', 2, 23, 'e', 1),
(31, 'Lavoro → Casa', '2025-05-29 00:00:00', 1, 23, 'v', 1),
(32, 'Lavoro → Casa', '2025-05-31 00:00:00', 2, 23, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `viaggio_destinazione`
--

CREATE TABLE `viaggio_destinazione` (
  `ID` int(11) NOT NULL,
  `ID_Viaggio` int(11) NOT NULL,
  `Citta` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `viaggio_destinazione`
--

INSERT INTO `viaggio_destinazione` (`ID`, `ID_Viaggio`, `Citta`) VALUES
(6, 29, 'Ponsacco'),
(7, 30, 'Ponsacco'),
(8, 31, 'Ponsacco'),
(9, 32, 'Ponsacco');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Mittente_ID` (`Mittente_ID`),
  ADD KEY `Destinatario_ID` (`Destinatario_ID`);

--
-- Indexes for table `notifica`
--
ALTER TABLE `notifica`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `prenotazione`
--
ALTER TABLE `prenotazione`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Viaggio` (`ID_Viaggio`),
  ADD KEY `ID_Passeggero` (`ID_Passeggero`),
  ADD KEY `ID_Autista` (`ID_Autista`);

--
-- Indexes for table `recensione`
--
ALTER TABLE `recensione`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Autore` (`ID_Autore`),
  ADD KEY `ID_Utente` (`ID_Utente`);

--
-- Indexes for table `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `veicolo`
--
ALTER TABLE `veicolo`
  ADD PRIMARY KEY (`Targa`),
  ADD KEY `ID_Proprietario` (`ID_Proprietario`);

--
-- Indexes for table `viaggio`
--
ALTER TABLE `viaggio`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Autista` (`ID_Autista`);

--
-- Indexes for table `viaggio_destinazione`
--
ALTER TABLE `viaggio_destinazione`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Viaggio` (`ID_Viaggio`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifica`
--
ALTER TABLE `notifica`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prenotazione`
--
ALTER TABLE `prenotazione`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `recensione`
--
ALTER TABLE `recensione`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `utente`
--
ALTER TABLE `utente`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `viaggio`
--
ALTER TABLE `viaggio`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `viaggio_destinazione`
--
ALTER TABLE `viaggio_destinazione`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`Mittente_ID`) REFERENCES `utente` (`ID`),
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`Destinatario_ID`) REFERENCES `utente` (`ID`);

--
-- Constraints for table `prenotazione`
--
ALTER TABLE `prenotazione`
  ADD CONSTRAINT `prenotazione_ibfk_1` FOREIGN KEY (`ID_Viaggio`) REFERENCES `viaggio` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `prenotazione_ibfk_2` FOREIGN KEY (`ID_Passeggero`) REFERENCES `utente` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `prenotazione_ibfk_3` FOREIGN KEY (`ID_Autista`) REFERENCES `utente` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `recensione`
--
ALTER TABLE `recensione`
  ADD CONSTRAINT `recensione_ibfk_1` FOREIGN KEY (`ID_Autore`) REFERENCES `utente` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `recensione_ibfk_2` FOREIGN KEY (`ID_Utente`) REFERENCES `utente` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `veicolo`
--
ALTER TABLE `veicolo`
  ADD CONSTRAINT `veicolo_ibfk_1` FOREIGN KEY (`ID_Proprietario`) REFERENCES `utente` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `viaggio`
--
ALTER TABLE `viaggio`
  ADD CONSTRAINT `viaggio_ibfk_1` FOREIGN KEY (`ID_Autista`) REFERENCES `utente` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `viaggio_destinazione`
--
ALTER TABLE `viaggio_destinazione`
  ADD CONSTRAINT `viaggio_destinazione_ibfk_1` FOREIGN KEY (`ID_Viaggio`) REFERENCES `viaggio` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
