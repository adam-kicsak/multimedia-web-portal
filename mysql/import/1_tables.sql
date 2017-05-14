-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Hoszt: localhost
-- Létrehozás ideje: 2011. máj. 09. 15:33
-- Szerver verzió: 6.0.0
-- PHP verzió: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Tábla szerkezet: `album`
--

CREATE TABLE IF NOT EXISTS `album` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Elsõdleges azonosító',
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Létrehozó neve',
  `title` varchar(100) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Cím',
  `description` text COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Leírás',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Létrehozás idõpontja',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `list` (`user_id`(10),`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet: `album_content`
--

CREATE TABLE IF NOT EXISTS `album_content` (
  `media_id` int(11) unsigned NOT NULL COMMENT 'Média azonosító',
  `album_id` int(11) unsigned NOT NULL COMMENT 'Album azonosító',
  `order` int(11) unsigned NOT NULL COMMENT 'Sorrend',
  PRIMARY KEY (`media_id`,`album_id`),
  KEY `media_id` (`media_id`),
  KEY `list` (`album_id`,`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Album tartalam';

-- --------------------------------------------------------

--
-- Tábla szerkezet: `convert_state`
--

CREATE TABLE IF NOT EXISTS `convert_state` (
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Konvertálást indító felhasználó neve',
  `file` varchar(255) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'File neve',
  `type` enum('audio','video','image') COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Konvertált média típusa',
  `phase` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Konvertálás fázisa',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Konvertálások állapota';

-- --------------------------------------------------------

--
-- Tábla szerkezet: `favorite`
--

CREATE TABLE IF NOT EXISTS `favorite` (
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Felhasználó azonosító',
  `media_id` int(11) unsigned NOT NULL COMMENT 'Média azonosító',
  `adding` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hozzáadás dõpontja',
  PRIMARY KEY (`user_id`,`media_id`),
  KEY `media_id` (`media_id`),
  KEY `user_id` (`user_id`),
  KEY `list` (`user_id`(10),`adding`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Kedvencek';

-- --------------------------------------------------------

--
-- Tábla szerkezet: `media`
--

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Egyedi azonosító',
  `type` enum('audio','video','image') COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Médiatípus',
  `title` varchar(100) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Cím',
  `description` text COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Leírás',
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'A feltöltõ felhasználó',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Létrehozás idõpontja',
  `removed` enum('no','author','moderator') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'no' COMMENT 'Eltávolítást jelzõ mezõ',
  `searchable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `search` (`searchable`,`type`,`removed`),
  KEY `list` (`user_id`(10),`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet: `media_comment`
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
-- Tábla szerkezet: `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Elsõdleges azonosító, egyben felhasználónév',
  `email` varchar(255) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'E-mail cím',
  `secret` char(40) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Jelszó SHA-1 lenyomata',
  `joined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Regisztráció idõpontja',
  `role` enum('user','moderator','administrator') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'user' COMMENT 'Felhasználó rangja',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `login` (`email`(10),`secret`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Felhasználók';

-- --------------------------------------------------------

--
-- Tábla szerkezet: `user_note`
--

CREATE TABLE IF NOT EXISTS `user_note` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Elsõdleges kulcs',
  `user_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztetett felhasználó',
  `author_id` varchar(30) COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztetés szerzõje',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Létrehozás idõpontja',
  `type` enum('note','warn','ban') COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztetés típusa',
  `reason` text COLLATE utf8_hungarian_ci NOT NULL COMMENT 'Figyelmeztetés oka',
  `expire` timestamp NULL DEFAULT NULL COMMENT 'Lejárat',
  PRIMARY KEY (`id`),
  KEY `login` (`user_id`(10),`expire`),
  KEY `user_id` (`user_id`),
  KEY `author_id` (`author_id`),
  KEY `oreder` (`user_id`(10),`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci COMMENT='Felhasználó figylmeztetése';

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `album`
--
ALTER TABLE `album`
  ADD CONSTRAINT `album_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megkötések a táblához `album_content`
--
ALTER TABLE `album_content`
  ADD CONSTRAINT `album_content_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `album_content_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megkötések a táblához `convert_state`
--
ALTER TABLE `convert_state`
  ADD CONSTRAINT `convert_state_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megkötések a táblához `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `favorite_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `favorite_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megkötések a táblához `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megkötések a táblához `media_comment`
--
ALTER TABLE `media_comment`
  ADD CONSTRAINT `media_comment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `media_comment_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Megkötések a táblához `user_note`
--
ALTER TABLE `user_note`
  ADD CONSTRAINT `user_note_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `user_note_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
