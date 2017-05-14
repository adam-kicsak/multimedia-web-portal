-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Hoszt: localhost
-- L�trehoz�s ideje: 2011. m�j. 09. 15:33
-- Szerver verzi�: 6.0.0
-- PHP verzi�: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- T�bla szerkezet: `album`
--

CREATE TABLE IF NOT EXISTS `album` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Els�dleges azonos�t�',
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'L�trehoz� neve',
  `title` varchar(100) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'C�m',
  `description` text COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Le�r�s',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'L�trehoz�s id�pontja',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `list` (`user_id`(10),`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- T�bla szerkezet: `album_content`
--

CREATE TABLE IF NOT EXISTS `album_content` (
  `media_id` int(11) unsigned NOT NULL COMMENT 'M�dia azonos�t�',
  `album_id` int(11) unsigned NOT NULL COMMENT 'Album azonos�t�',
  `order` int(11) unsigned NOT NULL COMMENT 'Sorrend',
  PRIMARY KEY (`media_id`,`album_id`),
  KEY `media_id` (`media_id`),
  KEY `list` (`album_id`,`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Album tartalam';

-- --------------------------------------------------------

--
-- T�bla szerkezet: `convert_state`
--

CREATE TABLE IF NOT EXISTS `convert_state` (
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Konvert�l�st ind�t� felhaszn�l� neve',
  `file` varchar(255) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'File neve',
  `type` enum('audio','video','image') COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Konvert�lt m�dia t�pusa',
  `phase` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Konvert�l�s f�zisa',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Konvert�l�sok �llapota';

-- --------------------------------------------------------

--
-- T�bla szerkezet: `favorite`
--

CREATE TABLE IF NOT EXISTS `favorite` (
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Felhaszn�l� azonos�t�',
  `media_id` int(11) unsigned NOT NULL COMMENT 'M�dia azonos�t�',
  `adding` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hozz�ad�s d�pontja',
  PRIMARY KEY (`user_id`,`media_id`),
  KEY `media_id` (`media_id`),
  KEY `user_id` (`user_id`),
  KEY `list` (`user_id`(10),`adding`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Kedvencek';

-- --------------------------------------------------------

--
-- T�bla szerkezet: `media`
--

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Egyedi azonos�t�',
  `type` enum('audio','video','image') COLLATE utf8_hungarian_ci NOT NULL COMMENT 'M�diat�pus',
  `title` varchar(100) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'C�m',
  `description` text COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Le�r�s',
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'A felt�lt� felhaszn�l�',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'L�trehoz�s id�pontja',
  `removed` enum('no','author','moderator') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'no' COMMENT 'Elt�vol�t�st jelz� mez�',
  `searchable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `search` (`searchable`,`type`,`removed`),
  KEY `list` (`user_id`(10),`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- T�bla szerkezet: `media_comment`
--

CREATE TABLE IF NOT EXISTS `media_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Elsodleges azonosito',
  `media_id` int(11) unsigned NOT NULL COMMENT 'Media azonosito',
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Felhasznalo azonosito',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Letrehozas idopontja',
  `removed` enum('no','author','moderator') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'no' COMMENT 'Eltavolitas allapota',
  `comment` text COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Megjegyzes tartalma',
  PRIMARY KEY (`id`),
  KEY `list` (`media_id`,`created`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Media megjegyzesei';

-- --------------------------------------------------------

--
-- T�bla szerkezet: `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Els�dleges azonos�t�, egyben felhaszn�l�n�v',
  `email` varchar(255) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'E-mail c�m',
  `secret` char(40) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Jelsz� SHA-1 lenyomata',
  `joined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Regisztr�ci� id�pontja',
  `role` enum('user','moderator','administrator') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'user' COMMENT 'Felhaszn�l� rangja',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `login` (`email`(10),`secret`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Felhaszn�l�k';

-- --------------------------------------------------------

--
-- T�bla szerkezet: `user_note`
--

CREATE TABLE IF NOT EXISTS `user_note` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Els�dleges kulcs',
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztetett felhaszn�l�',
  `author_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztet�s szerz�je',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'L�trehoz�s id�pontja',
  `type` enum('note','warn','ban') COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztet�s t�pusa',
  `reason` text COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztet�s oka',
  `expire` timestamp NULL DEFAULT NULL COMMENT 'Lej�rat',
  PRIMARY KEY (`id`),
  KEY `login` (`user_id`(10),`expire`),
  KEY `user_id` (`user_id`),
  KEY `author_id` (`author_id`),
  KEY `oreder` (`user_id`(10),`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Felhaszn�l� figylmeztet�se';

--
-- Megk�t�sek a ki�rt t�bl�khoz
--

--
-- Megk�t�sek a t�bl�hoz `album`
--
ALTER TABLE `album`
  ADD CONSTRAINT `album_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megk�t�sek a t�bl�hoz `album_content`
--
ALTER TABLE `album_content`
  ADD CONSTRAINT `album_content_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `album_content_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megk�t�sek a t�bl�hoz `convert_state`
--
ALTER TABLE `convert_state`
  ADD CONSTRAINT `convert_state_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megk�t�sek a t�bl�hoz `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `favorite_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `favorite_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megk�t�sek a t�bl�hoz `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megk�t�sek a t�bl�hoz `media_comment`
--
ALTER TABLE `media_comment`
  ADD CONSTRAINT `media_comment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `media_comment_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megk�t�sek a t�bl�hoz `user_note`
--
ALTER TABLE `user_note`
  ADD CONSTRAINT `user_note_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `user_note_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
