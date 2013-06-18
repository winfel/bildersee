
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Tabellenstruktur für Tabelle `authors`
--

CREATE TABLE IF NOT EXISTS `authors` (
  `tag` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `licence_public` varchar(20) NOT NULL,
  `licence_protected` varchar(20) NOT NULL,
  `licence_private` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `autotags`
--

CREATE TABLE IF NOT EXISTS `autotags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pathpart` varchar(255) NOT NULL,
  `tag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=66 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `key` varchar(2000) DEFAULT NULL,
  `filename` varchar(2000) DEFAULT NULL,
  `sortstring` varchar(2000) DEFAULT NULL,
  `tags` varchar(2000) DEFAULT NULL,
  `folder` varchar(2000) DEFAULT NULL,
  `category` varchar(2000) DEFAULT NULL,
  `copyright` varchar(2000) DEFAULT NULL,
  `subfolder` varchar(2000) DEFAULT NULL,
  `status` varchar(2000) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `files_backup`
--

CREATE TABLE IF NOT EXISTS `files_backup` (
  `key` varchar(2000) DEFAULT NULL,
  `filename` varchar(2000) DEFAULT NULL,
  `sortstring` varchar(2000) DEFAULT NULL,
  `tags` varchar(2000) DEFAULT NULL,
  `folder` varchar(2000) DEFAULT NULL,
  `category` varchar(2000) DEFAULT NULL,
  `copyright` varchar(2000) DEFAULT NULL,
  `subfolder` varchar(2000) DEFAULT NULL,
  `status` varchar(2000) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `filetags`
--

CREATE TABLE IF NOT EXISTS `filetags` (
  `image` varchar(255) NOT NULL,
  `tags` text NOT NULL,
  UNIQUE KEY `image` (`image`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `people`
--

CREATE TABLE IF NOT EXISTS `people` (
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `birthday` int(11) NOT NULL DEFAULT '0',
  `dead` int(11) NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tags_implied`
--

CREATE TABLE IF NOT EXISTS `tags_implied` (
  `tag` varchar(255) NOT NULL,
  `implied` varchar(255) NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(20) NOT NULL,
  `password` varchar(50) NOT NULL,
  `userquery` varchar(255) NOT NULL DEFAULT '1=1',
  `clearname` varchar(50) NOT NULL,
  `uploadpath` varchar(255) NOT NULL,
  `isAdmin` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



-- Admin user

INSERT INTO `users` (`username`, `password`, `userquery`, `clearname`, `uploadpath`, `isAdmin`) VALUES
('admin', 'af504c1a3837f66eb2f9cee2a1651c4a', '1=1', 'Admin', '', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
