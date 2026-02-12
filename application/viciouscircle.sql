-- phpMyAdmin SQL Dump
-- version 4.1.14.6
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Jeu 12 Février 2026 à 23:54
-- Version du serveur :  5.1.73
-- Version de PHP :  5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `viciouscircle`
--

-- --------------------------------------------------------

--
-- Structure de la table `adresses`
--

CREATE TABLE IF NOT EXISTS `adresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL DEFAULT '0',
  `alias` varchar(255) NOT NULL,
  `company` varchar(64) DEFAULT NULL,
  `lastname` varchar(32) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `address1` varchar(128) NOT NULL,
  `address2` varchar(128) DEFAULT NULL,
  `postcode` varchar(12) DEFAULT NULL,
  `city` varchar(64) NOT NULL,
  `country_id` int(10) DEFAULT NULL,
  `other` text,
  `phone` varchar(32) DEFAULT NULL,
  `phone_mobile` varchar(32) DEFAULT NULL,
  `vat_number` varchar(32) DEFAULT NULL,
  `dni` varchar(16) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `address_customer` (`client_id`),
  KEY `country` (`country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6419 ;

-- --------------------------------------------------------

--
-- Structure de la table `albums`
--

CREATE TABLE IF NOT EXISTS `albums` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupe_id` int(11) unsigned DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `catalogue` varchar(100) DEFAULT NULL,
  `producteurs` varchar(255) DEFAULT NULL,
  `editeurs` varchar(255) DEFAULT NULL,
  `licence` varchar(255) DEFAULT NULL,
  `enreg_lieu` varchar(100) DEFAULT NULL,
  `enreg_date` date DEFAULT NULL,
  `copyright_c` varchar(50) DEFAULT NULL,
  `annee_p` varchar(4) DEFAULT NULL,
  `isrc` varchar(30) DEFAULT NULL,
  `upc` varchar(20) DEFAULT NULL,
  `upc_believe` varchar(20) DEFAULT NULL,
  `grid` varchar(50) DEFAULT NULL,
  `compilation` tinyint(4) NOT NULL DEFAULT '0',
  `date_preorder` date DEFAULT NULL,
  `date_sortie` date DEFAULT NULL,
  `prix_titre` float(4,2) DEFAULT NULL,
  `vente_titre` tinyint(4) NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  `description_uk` text,
  `explicit_lyrics` tinyint(4) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'offline',
  `encode_queue` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `groupe_id` (`groupe_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=188 ;

-- --------------------------------------------------------

--
-- Structure de la table `albums_albums`
--

CREATE TABLE IF NOT EXISTS `albums_albums` (
  `album_id` int(11) unsigned NOT NULL,
  `similar_id` int(11) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`album_id`,`similar_id`),
  KEY `similar_id` (`similar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `albums_artistes`
--

CREATE TABLE IF NOT EXISTS `albums_artistes` (
  `album_id` int(10) unsigned NOT NULL,
  `artiste_id` int(11) NOT NULL,
  `fonction` varchar(30) NOT NULL DEFAULT '',
  `position` smallint(6) unsigned DEFAULT '0',
  PRIMARY KEY (`album_id`,`artiste_id`,`fonction`),
  KEY `artiste_id` (`artiste_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `artistes`
--

CREATE TABLE IF NOT EXISTS `artistes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(256) NOT NULL COMMENT 'Nom de l''artiste',
  `prenom` varchar(255) NOT NULL,
  `adresse` text NOT NULL,
  `codepostal` varchar(30) NOT NULL,
  `ville` varchar(255) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Table de gestion des auteurs, compositeurs, interprètes, ...' AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Structure de la table `attributs`
--

CREATE TABLE IF NOT EXISTS `attributs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attributs_groupe_id` int(10) unsigned NOT NULL,
  `color` varchar(32) DEFAULT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attributs_groupe_id` (`attributs_groupe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `attributs_declinaisons`
--

CREATE TABLE IF NOT EXISTS `attributs_declinaisons` (
  `attribut_id` int(10) unsigned NOT NULL,
  `declinaison_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`attribut_id`,`declinaison_id`),
  KEY `id_product_attribute` (`declinaison_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `attributs_groupes`
--

CREATE TABLE IF NOT EXISTS `attributs_groupes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_color_group` tinyint(1) NOT NULL DEFAULT '0',
  `group_type` enum('select','radio','color') NOT NULL DEFAULT 'select',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `attributs_groupes_traductions`
--

CREATE TABLE IF NOT EXISTS `attributs_groupes_traductions` (
  `attributs_groupe_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(128) NOT NULL,
  `public_name` varchar(64) NOT NULL,
  PRIMARY KEY (`attributs_groupe_id`,`lang`),
  KEY `id_lang` (`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `attributs_impacts`
--

CREATE TABLE IF NOT EXISTS `attributs_impacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) unsigned NOT NULL,
  `attribut_id` int(11) unsigned NOT NULL,
  `weight` decimal(20,6) NOT NULL,
  `price` decimal(17,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_product` (`produit_id`,`attribut_id`),
  KEY `id_attribute` (`attribut_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `attributs_traductions`
--

CREATE TABLE IF NOT EXISTS `attributs_traductions` (
  `attribut_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`attribut_id`,`lang`),
  KEY `id_lang` (`lang`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `caracteristiques`
--

CREATE TABLE IF NOT EXISTS `caracteristiques` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `caracteristiques_traductions`
--

CREATE TABLE IF NOT EXISTS `caracteristiques_traductions` (
  `caracteristique_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`caracteristique_id`,`lang`),
  KEY `lang` (`lang`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `caracteristiques_valeurs`
--

CREATE TABLE IF NOT EXISTS `caracteristiques_valeurs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `caracteristique_id` int(10) unsigned NOT NULL,
  `custom` tinyint(3) unsigned DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caracteristique` (`caracteristique_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `caracteristiques_valeurs_produits`
--

CREATE TABLE IF NOT EXISTS `caracteristiques_valeurs_produits` (
  `caracteristiques_valeur_id` int(10) unsigned NOT NULL,
  `produit_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`caracteristiques_valeur_id`,`produit_id`),
  KEY `produit_id` (`produit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `caracteristiques_valeurs_traductions`
--

CREATE TABLE IF NOT EXISTS `caracteristiques_valeurs_traductions` (
  `caracteristiques_valeur_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`caracteristiques_valeur_id`,`lang`),
  KEY `lang` (`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `cartesmembres`
--

CREATE TABLE IF NOT EXISTS `cartesmembres` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(256) NOT NULL,
  `valeur` int(6) NOT NULL,
  `prix` int(6) unsigned NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL,
  `level_depth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `left_id` int(10) unsigned NOT NULL DEFAULT '0',
  `right_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tree_id` int(10) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `is_root_category` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_parent` (`parent_id`),
  KEY `nleftright` (`left_id`,`right_id`),
  KEY `nleftrightactive` (`left_id`,`right_id`,`active`),
  KEY `level_depth` (`level_depth`),
  KEY `nright` (`right_id`),
  KEY `nleft` (`left_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `categories_produits`
--

CREATE TABLE IF NOT EXISTS `categories_produits` (
  `category_id` int(10) unsigned NOT NULL,
  `produit_id` int(10) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`produit_id`),
  KEY `id_product` (`produit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `categories_traductions`
--

CREATE TABLE IF NOT EXISTS `categories_traductions` (
  `category_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` text,
  `slug` varchar(255) NOT NULL,
  `meta_title` varchar(128) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`category_id`,`lang`),
  KEY `category_name` (`name`),
  KEY `id_lang` (`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `chequescadeaux`
--

CREATE TABLE IF NOT EXISTS `chequescadeaux` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(256) NOT NULL,
  `prix` int(6) NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang` varchar(10) DEFAULT NULL,
  `default_group_id` int(10) unsigned NOT NULL DEFAULT '1',
  `genre` varchar(10) NOT NULL,
  `company` varchar(64) DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `ape` varchar(5) DEFAULT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `username` varchar(128) NOT NULL,
  `password` varchar(64) NOT NULL,
  `last_passwd_gen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `birthday` date DEFAULT NULL,
  `newsletter` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip_registration_newsletter` varchar(15) DEFAULT NULL,
  `newsletter_created_at` datetime DEFAULT NULL,
  `website` varchar(128) DEFAULT NULL,
  `secure_key` varchar(32) NOT NULL DEFAULT '-1',
  `note` text,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `promotion_id` int(10) unsigned DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0',
  `from_v1` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `group` int(11) NOT NULL,
  `last_login` int(11) DEFAULT NULL,
  `login_hash` varchar(255) NOT NULL,
  `profile_fields` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_email` (`username`),
  KEY `client_login` (`username`,`password`),
  KEY `client_passwd_id` (`id`,`password`),
  KEY `genre` (`genre`),
  KEY `promotion_id` (`promotion_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6156 ;

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE IF NOT EXISTS `commandes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(9) DEFAULT NULL,
  `fraisport_id` int(10) unsigned DEFAULT NULL,
  `lang` varchar(10) NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `panier_id` int(10) unsigned NOT NULL,
  `livraison_id` int(10) unsigned NOT NULL,
  `facturation_id` int(10) unsigned NOT NULL,
  `transaction_id` int(10) DEFAULT NULL,
  `current_state` int(10) unsigned NOT NULL,
  `secure_key` varchar(32) NOT NULL DEFAULT '-1',
  `module` varchar(255) DEFAULT NULL,
  `gift` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `gift_message` text,
  `shop_message` text,
  `shipping_number` varchar(32) DEFAULT NULL,
  `total_discounts_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_discounts_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_paid_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_paid_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_paid_shipping` decimal(17,2) NOT NULL,
  `total_paid_real` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_products_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_products_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_shipping_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_shipping_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_payment_tax` decimal(17,2) NOT NULL DEFAULT '0.00',
  `carrier_tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `invoice_number` int(10) unsigned NOT NULL DEFAULT '0',
  `delivery_number` int(10) unsigned NOT NULL DEFAULT '0',
  `invoice_date` datetime NOT NULL,
  `delivery_date` datetime NOT NULL,
  `download_zip` tinyint(1) NOT NULL DEFAULT '0',
  `valid` int(1) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `panier_id` (`panier_id`),
  KEY `invoice_number` (`invoice_number`),
  KEY `fraisport_id` (`fraisport_id`),
  KEY `lang` (`lang`),
  KEY `livraison_id` (`livraison_id`),
  KEY `facturation_id` (`facturation_id`),
  KEY `created_at` (`created_at`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6146 ;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_cartesmembres`
--

CREATE TABLE IF NOT EXISTS `commandes_cartesmembres` (
  `commande_id` int(11) NOT NULL,
  `cartemembre_id` int(6) unsigned NOT NULL,
  `prix` int(6) unsigned NOT NULL,
  `valeur` int(6) unsigned NOT NULL,
  PRIMARY KEY (`commande_id`,`cartemembre_id`),
  KEY `id_cartemembre` (`cartemembre_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_chequescadeaux`
--

CREATE TABLE IF NOT EXISTS `commandes_chequescadeaux` (
  `commande_id` int(11) NOT NULL,
  `chequecadeau_id` int(6) NOT NULL,
  `id_membre` int(11) NOT NULL,
  `prix` int(6) unsigned NOT NULL,
  `code` varchar(15) NOT NULL,
  `utilise` tinyint(1) NOT NULL,
  PRIMARY KEY (`commande_id`,`chequecadeau_id`),
  KEY `id_chequecadeau` (`chequecadeau_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_details`
--

CREATE TABLE IF NOT EXISTS `commandes_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `commande_id` int(10) unsigned NOT NULL,
  `produit_id` int(10) unsigned DEFAULT NULL,
  `declinaison_id` int(10) unsigned DEFAULT NULL,
  `format_id` int(10) unsigned DEFAULT NULL,
  `titre_id` int(11) unsigned DEFAULT NULL,
  `produit_name` varchar(255) NOT NULL,
  `produit_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `produit_quantity_in_stock` int(10) NOT NULL DEFAULT '0',
  `produit_quantity_refunded` int(10) unsigned NOT NULL DEFAULT '0',
  `produit_quantity_return` int(10) unsigned NOT NULL DEFAULT '0',
  `produit_quantity_reinjected` int(10) unsigned NOT NULL DEFAULT '0',
  `produit_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `produit_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `reduction_percent` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reduction_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `reduction_amount_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `reduction_amount_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `group_reduction` decimal(10,2) NOT NULL DEFAULT '0.00',
  `produit_quantity_discount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `produit_ean13` varchar(13) DEFAULT NULL,
  `produit_upc` varchar(12) DEFAULT NULL,
  `produit_reference` varchar(32) DEFAULT NULL,
  `produit_supplier_reference` varchar(32) DEFAULT NULL,
  `produit_weight` decimal(20,6) NOT NULL,
  `tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',
  `ecotax` decimal(21,6) NOT NULL DEFAULT '0.000000',
  `ecotax_tax_rate` decimal(5,3) NOT NULL DEFAULT '0.000',
  `discount_quantity_applied` tinyint(1) NOT NULL DEFAULT '0',
  `download_path` text,
  `download_hash` varchar(255) DEFAULT NULL,
  `download_nb` int(10) unsigned DEFAULT '0',
  `download_deadline` datetime DEFAULT NULL,
  `download_zip` tinyint(1) NOT NULL DEFAULT '0',
  `total_price_tax_incl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `total_price_tax_excl` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `original_produit_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `commandes_details_order` (`commande_id`),
  KEY `produit_id` (`produit_id`),
  KEY `declinaison_id` (`declinaison_id`),
  KEY `commandes_details_id` (`commande_id`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9074 ;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_details_taxes`
--

CREATE TABLE IF NOT EXISTS `commandes_details_taxes` (
  `detail_id` int(11) NOT NULL,
  `tax_id` int(11) NOT NULL,
  `amount` decimal(16,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`detail_id`),
  KEY `tax_id` (`tax_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_editions`
--

CREATE TABLE IF NOT EXISTS `commandes_editions` (
  `commande_id` int(11) NOT NULL,
  `edition_id` int(11) NOT NULL,
  `prix` int(6) unsigned NOT NULL,
  PRIMARY KEY (`commande_id`,`edition_id`),
  KEY `id_edition` (`edition_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_etats`
--

CREATE TABLE IF NOT EXISTS `commandes_etats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice` tinyint(1) unsigned DEFAULT '0',
  `send_email` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `logable` tinyint(1) NOT NULL DEFAULT '0',
  `delivery` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `shipped` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `paid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_etats_traductions`
--

CREATE TABLE IF NOT EXISTS `commandes_etats_traductions` (
  `etat_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(64) NOT NULL,
  `template` varchar(64) NOT NULL,
  PRIMARY KEY (`etat_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_historique`
--

CREATE TABLE IF NOT EXISTS `commandes_historique` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `client_id` int(10) unsigned DEFAULT NULL,
  `commande_id` int(10) unsigned NOT NULL,
  `etat_id` int(10) unsigned NOT NULL,
  `sent_email` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_history_order` (`commande_id`),
  KEY `user_id` (`user_id`),
  KEY `etat_id` (`etat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12021 ;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_messages`
--

CREATE TABLE IF NOT EXISTS `commandes_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_messages_traductions`
--

CREATE TABLE IF NOT EXISTS `commandes_messages_traductions` (
  `message_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(128) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`message_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_promotions`
--

CREATE TABLE IF NOT EXISTS `commandes_promotions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `commande_id` int(10) unsigned NOT NULL,
  `promotion_id` int(10) unsigned NOT NULL,
  `facture_id` int(10) unsigned DEFAULT '0',
  `name` varchar(254) NOT NULL,
  `value` decimal(17,2) NOT NULL DEFAULT '0.00',
  `value_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `free_shipping` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `commande_id` (`commande_id`),
  KEY `promotion_id` (`promotion_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1128 ;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_titres`
--

CREATE TABLE IF NOT EXISTS `commandes_titres` (
  `commande_id` int(11) NOT NULL,
  `titre_id` int(11) unsigned NOT NULL,
  `prix` int(6) unsigned NOT NULL,
  PRIMARY KEY (`commande_id`,`titre_id`),
  KEY `titre_id` (`titre_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `concerts`
--

CREATE TABLE IF NOT EXISTS `concerts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `heure` varchar(5) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `ville` varchar(255) NOT NULL,
  `organisateur` varchar(255) NOT NULL,
  `reservation` varchar(255) NOT NULL,
  `prix` varchar(40) NOT NULL,
  `annule` tinyint(4) NOT NULL,
  `complet` tinyint(4) NOT NULL,
  `status` varchar(30) NOT NULL,
  `groupe_id` int(11) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `groupe_id` (`groupe_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1748 ;

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `societe` varchar(255) NOT NULL,
  `fonction` varchar(255) NOT NULL,
  `adresse` text NOT NULL,
  `codepostal` varchar(10) NOT NULL,
  `ville` varchar(255) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `contacts_groupes`
--

CREATE TABLE IF NOT EXISTS `contacts_groupes` (
  `contact_id` int(11) unsigned NOT NULL,
  `groupe_id` int(11) unsigned NOT NULL,
  `position` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`contact_id`,`groupe_id`),
  KEY `id_groupe` (`groupe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tables des groupes concernés pour l''annonce d''un concert';

-- --------------------------------------------------------

--
-- Structure de la table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int(10) unsigned NOT NULL,
  `currency_id` int(10) unsigned NOT NULL DEFAULT '0',
  `iso_code` varchar(3) NOT NULL,
  `call_prefix` int(10) NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `contains_states` tinyint(1) NOT NULL DEFAULT '0',
  `need_identification_number` tinyint(1) NOT NULL DEFAULT '0',
  `need_zip_code` tinyint(1) NOT NULL DEFAULT '1',
  `zip_code_format` varchar(12) NOT NULL DEFAULT '',
  `display_tax_label` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country_iso_code` (`iso_code`),
  KEY `country_` (`zone_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=245 ;

-- --------------------------------------------------------

--
-- Structure de la table `countries_traductions`
--

CREATE TABLE IF NOT EXISTS `countries_traductions` (
  `country_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`country_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `declinaisons`
--

CREATE TABLE IF NOT EXISTS `declinaisons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `produit_id` int(10) unsigned NOT NULL,
  `reference` varchar(32) DEFAULT NULL,
  `supplier_reference` varchar(32) DEFAULT NULL,
  `location` varchar(64) DEFAULT NULL,
  `ean13` varchar(13) DEFAULT NULL,
  `upc` varchar(12) DEFAULT NULL,
  `wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `ecotax` decimal(17,6) NOT NULL DEFAULT '0.000000',
  `weight` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `default_on` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `minimal_quantity` int(10) unsigned NOT NULL DEFAULT '1',
  `available_date` date DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_attribute_product` (`produit_id`),
  KEY `reference` (`reference`),
  KEY `supplier_reference` (`supplier_reference`),
  KEY `product_default` (`produit_id`,`default_on`),
  KEY `id_product_id_product_attribute` (`id`,`produit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `digitals`
--

CREATE TABLE IF NOT EXISTS `digitals` (
  `id` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `prix_album` decimal(6,2) NOT NULL,
  `prix_titre` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

CREATE TABLE IF NOT EXISTS `factures` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `commande_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `delivery_number` int(11) NOT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `total_discount_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_discount_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_paid_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_paid_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_products_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_products_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_shipping_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_shipping_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `shipping_method` smallint(10) unsigned NOT NULL,
  `total_wrapping_tax_excl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_wrapping_tax_incl` decimal(17,2) NOT NULL DEFAULT '0.00',
  `note` text,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commande_id` (`commande_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5548 ;

-- --------------------------------------------------------

--
-- Structure de la table `factures_paiements`
--

CREATE TABLE IF NOT EXISTS `factures_paiements` (
  `facture_id` int(11) unsigned NOT NULL,
  `paiement_id` int(11) unsigned NOT NULL,
  `commande_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`facture_id`,`paiement_id`),
  KEY `order_payment` (`paiement_id`),
  KEY `commande_id` (`commande_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `factures_taxes`
--

CREATE TABLE IF NOT EXISTS `factures_taxes` (
  `facture_id` int(11) NOT NULL,
  `type` varchar(15) NOT NULL,
  `tax_id` int(11) NOT NULL,
  `amount` decimal(10,6) NOT NULL DEFAULT '0.000000'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `fichiers`
--

CREATE TABLE IF NOT EXISTS `fichiers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `alternatif` text NOT NULL,
  `legende` text NOT NULL,
  `description` text NOT NULL,
  `ext` varchar(15) NOT NULL,
  `category` varchar(50) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1679 ;

-- --------------------------------------------------------

--
-- Structure de la table `fichier_joint`
--

CREATE TABLE IF NOT EXISTS `fichier_joint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(50) NOT NULL,
  `id_in_table` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `preset` varchar(255) DEFAULT NULL,
  `ext` varchar(15) NOT NULL,
  `crop` text,
  `slug` varchar(255) NOT NULL,
  `fichier_id` int(10) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fichier_id` (`fichier_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5749 ;

-- --------------------------------------------------------

--
-- Structure de la table `formats`
--

CREATE TABLE IF NOT EXISTS `formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) unsigned NOT NULL,
  `support_id` int(11) unsigned NOT NULL,
  `tax_id` int(11) unsigned NOT NULL,
  `digital_offert` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `covers` tinyint(1) NOT NULL DEFAULT '0',
  `booklet` tinyint(1) NOT NULL DEFAULT '0',
  `titre` varchar(256) NOT NULL,
  `prix` float(6,2) unsigned NOT NULL DEFAULT '9.99',
  `pistes` smallint(6) unsigned NOT NULL,
  `status` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_album` (`album_id`),
  KEY `id_support` (`support_id`),
  KEY `user_id` (`user_id`),
  KEY `tax_id` (`tax_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=571 ;

-- --------------------------------------------------------

--
-- Structure de la table `formats_digitals`
--

CREATE TABLE IF NOT EXISTS `formats_digitals` (
  `format_id` int(11) NOT NULL,
  `digital_id` int(11) NOT NULL,
  PRIMARY KEY (`format_id`,`digital_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `formats_titres`
--

CREATE TABLE IF NOT EXISTS `formats_titres` (
  `format_id` int(11) NOT NULL,
  `titre_id` int(11) unsigned NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`format_id`,`titre_id`),
  KEY `titre_id` (`titre_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Table de stockage des titres présents dans une édition d''alb';

-- --------------------------------------------------------

--
-- Structure de la table `fraisports`
--

CREATE TABLE IF NOT EXISTS `fraisports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tax_id` int(10) unsigned DEFAULT '0',
  `name` varchar(64) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `shipping_handling` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_free` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `shipping_method` int(2) NOT NULL DEFAULT '0',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `max_width` int(10) DEFAULT '0',
  `max_height` int(10) DEFAULT '0',
  `max_depth` int(10) DEFAULT '0',
  `max_weight` decimal(20,6) DEFAULT '0.000000',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`,`active`),
  KEY `tax_id` (`tax_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Structure de la table `fraisports_countries`
--

CREATE TABLE IF NOT EXISTS `fraisports_countries` (
  `fraisport_id` int(10) unsigned NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fraisport_id`,`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `fraisports_poids`
--

CREATE TABLE IF NOT EXISTS `fraisports_poids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fraisport_id` int(10) unsigned NOT NULL,
  `delimiter` decimal(20,6) NOT NULL,
  `price` decimal(20,6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fraisport_id` (`fraisport_id`,`delimiter`,`price`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1683 ;

-- --------------------------------------------------------

--
-- Structure de la table `fraisports_prix`
--

CREATE TABLE IF NOT EXISTS `fraisports_prix` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fraisport_id` int(10) unsigned NOT NULL,
  `delimiter` decimal(20,6) NOT NULL,
  `price` decimal(20,6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fraisport_id` (`fraisport_id`,`delimiter`,`price`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `fraisports_traductions`
--

CREATE TABLE IF NOT EXISTS `fraisports_traductions` (
  `fraisport_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `delay` varchar(128) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`lang`,`fraisport_id`),
  KEY `fraisport_id` (`fraisport_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `groupes`
--

CREATE TABLE IF NOT EXISTS `groupes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL DEFAULT 'offline',
  `slug` varchar(255) NOT NULL,
  `existe` tinyint(4) NOT NULL DEFAULT '1',
  `nom` varchar(255) NOT NULL,
  `alphabet` char(255) NOT NULL,
  `referent` varchar(255) DEFAULT NULL,
  `fonction` varchar(255) DEFAULT NULL,
  `adresse1` varchar(255) DEFAULT NULL,
  `adresse2` varchar(255) DEFAULT NULL,
  `codepostal` varchar(5) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `google` varchar(255) DEFAULT NULL,
  `myspace` varchar(255) DEFAULT NULL,
  `picasa` varchar(255) DEFAULT NULL,
  `flickr` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `dailymotion` varchar(255) DEFAULT NULL,
  `vimeo` varchar(255) DEFAULT NULL,
  `soundclound` varchar(255) DEFAULT NULL,
  `bandcamp` varchar(255) DEFAULT NULL,
  `itunes` varchar(255) DEFAULT NULL,
  `deezer` varchar(255) DEFAULT NULL,
  `lastfm` varchar(255) DEFAULT NULL,
  `inrocks` varchar(255) DEFAULT NULL,
  `reverbnation` varchar(255) DEFAULT NULL,
  `believe` varchar(255) DEFAULT NULL,
  `order` smallint(5) unsigned NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slug` (`slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_artistes`
--

CREATE TABLE IF NOT EXISTS `groupes_artistes` (
  `groupe_id` int(11) unsigned NOT NULL,
  `artiste_id` int(11) NOT NULL,
  `fonction` varchar(255) NOT NULL,
  `position` smallint(6) unsigned DEFAULT '0',
  PRIMARY KEY (`groupe_id`,`artiste_id`),
  KEY `artiste_id` (`artiste_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_traductions`
--

CREATE TABLE IF NOT EXISTS `groupes_traductions` (
  `groupe_id` int(11) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `fiche` longtext,
  `fiche_publish` tinyint(4) DEFAULT '1',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`groupe_id`,`lang`),
  KEY `groupe_id` (`groupe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `guests`
--

CREATE TABLE IF NOT EXISTS `guests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `operating_system` varchar(255) DEFAULT NULL,
  `web_browser` varchar(255) DEFAULT NULL,
  `client_id` int(10) unsigned DEFAULT NULL,
  `javascript` tinyint(1) DEFAULT '0',
  `screen_resolution_x` smallint(5) unsigned DEFAULT NULL,
  `screen_resolution_y` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5041812 ;

-- --------------------------------------------------------

--
-- Structure de la table `marques`
--

CREATE TABLE IF NOT EXISTS `marques` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `menu` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `position` int(3) NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `menus`
--

CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL,
  `level_depth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `left_id` int(10) unsigned NOT NULL DEFAULT '0',
  `right_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tree_id` int(10) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `is_root_category` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `page_fixe` varchar(150) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_parent` (`parent_id`),
  KEY `nleftright` (`left_id`,`right_id`),
  KEY `nleftrightactive` (`left_id`,`right_id`,`slug`),
  KEY `level_depth` (`level_depth`),
  KEY `nright` (`right_id`),
  KEY `nleft` (`left_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Structure de la table `migration`
--

CREATE TABLE IF NOT EXISTS `migration` (
  `type` varchar(25) NOT NULL,
  `name` varchar(50) NOT NULL,
  `migration` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `video_iframe` text NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `guid` varchar(255) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'online',
  `user_id` int(11) NOT NULL,
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `pages_traductions`
--

CREATE TABLE IF NOT EXISTS `pages_traductions` (
  `page_id` int(11) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL DEFAULT 'fr',
  `title` varchar(255) NOT NULL,
  `excerpt` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`page_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commande_id` int(10) unsigned NOT NULL,
  `order_reference` varchar(9) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `transaction_id` varchar(254) DEFAULT NULL,
  `card_number` varchar(254) DEFAULT NULL,
  `card_brand` varchar(254) DEFAULT NULL,
  `card_expiration` char(7) DEFAULT NULL,
  `card_holder` varchar(254) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_reference` (`order_reference`),
  KEY `commande_id` (`commande_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5394 ;

-- --------------------------------------------------------

--
-- Structure de la table `paniers`
--

CREATE TABLE IF NOT EXISTS `paniers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fraisport_id` int(10) unsigned DEFAULT NULL,
  `delivery_option` text,
  `lang` varchar(10) NOT NULL,
  `livraison_id` int(10) unsigned DEFAULT NULL,
  `facturation_id` int(10) unsigned DEFAULT NULL,
  `client_id` int(10) unsigned DEFAULT NULL,
  `guest_id` int(10) unsigned NOT NULL,
  `secure_key` varchar(32) NOT NULL DEFAULT '-1',
  `gift` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `gift_message` text,
  `shop_message` text,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_customer` (`client_id`),
  KEY `livraison_id` (`livraison_id`),
  KEY `facturation_id` (`facturation_id`),
  KEY `lang` (`lang`),
  KEY `guest_id` (`guest_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5040273 ;

-- --------------------------------------------------------

--
-- Structure de la table `paniers_formats`
--

CREATE TABLE IF NOT EXISTS `paniers_formats` (
  `panier_id` int(10) unsigned NOT NULL,
  `format_id` int(10) unsigned NOT NULL,
  `address_delivery_id` int(10) unsigned DEFAULT '0',
  `quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL,
  KEY `paniers_produits_index` (`panier_id`,`format_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `paniers_produits`
--

CREATE TABLE IF NOT EXISTS `paniers_produits` (
  `panier_id` int(10) unsigned NOT NULL,
  `produit_id` int(10) unsigned NOT NULL,
  `address_delivery_id` int(10) unsigned DEFAULT '0',
  `declinaison_id` int(10) unsigned DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL,
  KEY `paniers_produits_index` (`panier_id`,`produit_id`),
  KEY `declinaison_id` (`declinaison_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `paniers_titres`
--

CREATE TABLE IF NOT EXISTS `paniers_titres` (
  `panier_id` int(10) unsigned NOT NULL,
  `titre_id` int(10) unsigned NOT NULL,
  `address_delivery_id` int(10) unsigned DEFAULT '0',
  `quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(10) NOT NULL,
  KEY `paniers_produits_index` (`panier_id`,`titre_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `photos`
--

CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `copyright` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupe_id` (`groupe_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=340 ;

-- --------------------------------------------------------

--
-- Structure de la table `playlists`
--

CREATE TABLE IF NOT EXISTS `playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `type` varchar(50) CHARACTER SET latin1 NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`titre_id`,`type`,`album_id`),
  KEY `album_id` (`album_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `video_iframe` text NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `guid` varchar(255) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'online',
  `groupe_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=296 ;

-- --------------------------------------------------------

--
-- Structure de la table `posts_traductions`
--

CREATE TABLE IF NOT EXISTS `posts_traductions` (
  `post_id` int(11) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL DEFAULT 'fr',
  `title` varchar(255) NOT NULL,
  `excerpt` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`post_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `preecoute`
--

CREATE TABLE IF NOT EXISTS `preecoute` (
  `ecou_id` int(10) NOT NULL AUTO_INCREMENT,
  `madate` datetime NOT NULL,
  `ipaddress` varchar(20) CHARACTER SET latin1 NOT NULL,
  `titr_id` int(10) NOT NULL,
  PRIMARY KEY (`ecou_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `prehomes`
--

CREATE TABLE IF NOT EXISTS `prehomes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `line1` varchar(255) NOT NULL,
  `line2` varchar(255) NOT NULL,
  `groupe_id` int(10) unsigned DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `target` varchar(30) NOT NULL DEFAULT '_self',
  `video_iframe` text NOT NULL,
  `video_url` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'offline',
  `lang` varchar(10) NOT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Structure de la table `prehomes_traductions`
--

CREATE TABLE IF NOT EXISTS `prehomes_traductions` (
  `prehome_id` int(11) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL DEFAULT 'fr',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`prehome_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE IF NOT EXISTS `produits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `marque_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `tax_id` int(11) unsigned NOT NULL,
  `on_sale` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `online_only` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ean13` varchar(13) DEFAULT NULL,
  `upc` varchar(30) DEFAULT NULL,
  `ecotax` decimal(17,6) NOT NULL DEFAULT '0.000000',
  `minimal_quantity` int(10) unsigned NOT NULL DEFAULT '1',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `wholesale_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `additional_shipping_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `instalment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reference` varchar(32) DEFAULT NULL,
  `supplier_reference` varchar(32) DEFAULT NULL,
  `width` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `height` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `depth` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `weight` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `out_of_stock` int(10) unsigned NOT NULL DEFAULT '2',
  `quantity_discount` tinyint(1) DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `available_for_order` tinyint(1) NOT NULL DEFAULT '1',
  `available_date` date NOT NULL,
  `condition` enum('new','used','refurbished') NOT NULL DEFAULT 'new',
  `warranty` tinyint(4) NOT NULL DEFAULT '0',
  `show_price` tinyint(1) NOT NULL DEFAULT '1',
  `indexed` tinyint(1) NOT NULL DEFAULT '0',
  `visibility` enum('both','catalog','search','none') NOT NULL DEFAULT 'both',
  `cache_default_attribute` int(10) unsigned DEFAULT NULL,
  `redirect_type` enum('','404','301','302') NOT NULL DEFAULT '',
  `id_product_redirected` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_manufacturer` (`marque_id`),
  KEY `id_category_default` (`category_id`),
  KEY `id_tax_rules_group` (`tax_id`),
  KEY `indexed` (`indexed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `produits_produits`
--

CREATE TABLE IF NOT EXISTS `produits_produits` (
  `produit1_id` int(10) unsigned NOT NULL,
  `produit2_id` int(10) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`produit1_id`,`produit2_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `produits_traductions`
--

CREATE TABLE IF NOT EXISTS `produits_traductions` (
  `produit_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `description` text,
  `description_short` text,
  `slug` varchar(128) NOT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_title` varchar(128) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `available_now` varchar(255) DEFAULT NULL,
  `available_later` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`lang`,`produit_id`),
  KEY `id_lang` (`lang`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `promotions`
--

CREATE TABLE IF NOT EXISTS `promotions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned DEFAULT NULL,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `description` text,
  `quantity` int(10) unsigned DEFAULT NULL,
  `quantity_per_user` int(10) unsigned DEFAULT NULL,
  `minimum_amount` decimal(17,2) DEFAULT NULL,
  `minimum_amount_tax` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_amount_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `newuser_restriction` tinyint(1) unsigned NOT NULL,
  `fraisport_restriction` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `produit_restriction` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `free_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `reduction_type` varchar(100) NOT NULL,
  `reduction_percent` decimal(5,2) DEFAULT NULL,
  `reduction_amount` decimal(17,2) DEFAULT NULL,
  `reduction_tax` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `reduction_product` int(10) NOT NULL DEFAULT '0',
  `gift_product` int(10) unsigned NOT NULL DEFAULT '0',
  `gift_product_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'offline',
  `created_at` int(10) DEFAULT NULL,
  `updated_at` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Structure de la table `promotions_attributions_albums`
--

CREATE TABLE IF NOT EXISTS `promotions_attributions_albums` (
  `promotion_id` int(10) unsigned NOT NULL,
  `album_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`promotion_id`,`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `promotions_attributions_formats`
--

CREATE TABLE IF NOT EXISTS `promotions_attributions_formats` (
  `promotion_id` int(10) unsigned NOT NULL,
  `format_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`promotion_id`,`format_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `promotions_restrictions_albums`
--

CREATE TABLE IF NOT EXISTS `promotions_restrictions_albums` (
  `promotion_id` int(10) unsigned NOT NULL,
  `album_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`promotion_id`,`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `promotions_restrictions_formats`
--

CREATE TABLE IF NOT EXISTS `promotions_restrictions_formats` (
  `promotion_id` int(10) unsigned NOT NULL,
  `format_id` int(10) unsigned NOT NULL,
  `quantity` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`promotion_id`,`format_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `promotions_traductions`
--

CREATE TABLE IF NOT EXISTS `promotions_traductions` (
  `promotion_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(254) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`promotion_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `selections`
--

CREATE TABLE IF NOT EXISTS `selections` (
  `id` int(11) unsigned NOT NULL,
  `type` varchar(50) CHARACTER SET latin1 NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `slides`
--

CREATE TABLE IF NOT EXISTS `slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(30) NOT NULL DEFAULT 'offline',
  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `publish_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=85 ;

-- --------------------------------------------------------

--
-- Structure de la table `slides_traductions`
--

CREATE TABLE IF NOT EXISTS `slides_traductions` (
  `slide_id` int(11) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `line1` varchar(255) NOT NULL,
  `line2` varchar(255) NOT NULL,
  `newline` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `button` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `target` varchar(30) NOT NULL DEFAULT '_self',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`slide_id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `stocks`
--

CREATE TABLE IF NOT EXISTS `stocks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `format_id` int(10) unsigned DEFAULT NULL,
  `produit_id` int(11) unsigned DEFAULT NULL,
  `declinaison_id` int(11) unsigned DEFAULT NULL,
  `quantity` int(10) NOT NULL DEFAULT '0',
  `out_of_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_sqlstock` (`produit_id`,`declinaison_id`),
  KEY `produit_id` (`produit_id`),
  KEY `declinaison_id` (`declinaison_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=404 ;

-- --------------------------------------------------------

--
-- Structure de la table `stocks_mvts`
--

CREATE TABLE IF NOT EXISTS `stocks_mvts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `stocks_mvts_reason_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `lastname` varchar(32) DEFAULT '',
  `firstname` varchar(32) DEFAULT '',
  `physical_quantity` int(11) unsigned NOT NULL,
  `sign` tinyint(1) NOT NULL DEFAULT '1',
  `price_te` decimal(20,6) DEFAULT '0.000000',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_id` (`stock_id`),
  KEY `stock_mvt_reason_id` (`stocks_mvts_reason_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9434 ;

-- --------------------------------------------------------

--
-- Structure de la table `stocks_mvts_reasons`
--

CREATE TABLE IF NOT EXISTS `stocks_mvts_reasons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sign` tinyint(1) NOT NULL DEFAULT '1',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Structure de la table `stocks_mvts_reasons_traductions`
--

CREATE TABLE IF NOT EXISTS `stocks_mvts_reasons_traductions` (
  `stocks_mvts_reason_id` int(11) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`stocks_mvts_reason_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `styles`
--

CREATE TABLE IF NOT EXISTS `styles` (
  `id` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) NOT NULL,
  `createur` int(11) NOT NULL,
  `modificateur` int(11) NOT NULL,
  `creation` datetime NOT NULL,
  `modification` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `supports`
--

CREATE TABLE IF NOT EXISTS `supports` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `digital_id` smallint(11) unsigned DEFAULT NULL,
  `libelle` varchar(100) NOT NULL,
  `autocreate` tinyint(1) NOT NULL DEFAULT '1',
  `prix` decimal(6,2) DEFAULT NULL,
  `poids` decimal(6,2) DEFAULT NULL,
  `tare` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=77 ;

-- --------------------------------------------------------

--
-- Structure de la table `taches_albums`
--

CREATE TABLE IF NOT EXISTS `taches_albums` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_echeance` int(11) NOT NULL,
  `date_validation` int(11) NOT NULL,
  `actif` tinyint(4) NOT NULL,
  `user_id` int(11) NOT NULL,
  `album_id` int(10) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `taches_labels`
--

CREATE TABLE IF NOT EXISTS `taches_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `delai` varchar(255) NOT NULL COMMENT 'Release strftime (ex:+1 month)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Liste des taches prédéfinies pour un label' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(256) NOT NULL,
  `lien` varchar(256) NOT NULL,
  `type` enum('','groupe','lieu','organisateur','evenement') NOT NULL,
  `createur` int(11) NOT NULL,
  `modificateur` int(11) NOT NULL,
  `creation` datetime NOT NULL,
  `modification` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `taxes`
--

CREATE TABLE IF NOT EXISTS `taxes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rate` decimal(10,3) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `default_format` tinyint(1) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `taxes_traductions`
--

CREATE TABLE IF NOT EXISTS `taxes_traductions` (
  `tax_id` int(10) unsigned NOT NULL,
  `lang` varchar(10) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`tax_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `titres`
--

CREATE TABLE IF NOT EXISTS `titres` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `titre1` varchar(255) NOT NULL,
  `titre2` varchar(255) DEFAULT NULL,
  `duration` smallint(5) unsigned DEFAULT NULL,
  `groupe` varchar(255) NOT NULL,
  `upload` varchar(255) DEFAULT NULL,
  `prix` float(4,2) NOT NULL,
  `single` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isrc` varchar(30) DEFAULT NULL,
  `instrumental` tinyint(4) NOT NULL DEFAULT '0',
  `paroles` text,
  `editeurs` varchar(255) DEFAULT NULL,
  `annee_p` varchar(4) DEFAULT NULL,
  `producteurs` varchar(255) DEFAULT NULL,
  `encoded` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1745 ;

-- --------------------------------------------------------

--
-- Structure de la table `titres_albums`
--

CREATE TABLE IF NOT EXISTS `titres_albums` (
  `titre_id` int(11) unsigned NOT NULL,
  `album_id` int(11) unsigned NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`titre_id`,`album_id`),
  KEY `album_id` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `titres_artistes`
--

CREATE TABLE IF NOT EXISTS `titres_artistes` (
  `titre_id` int(11) unsigned NOT NULL,
  `artiste_id` int(11) NOT NULL,
  `fonction` varchar(100) NOT NULL,
  `position` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`titre_id`,`artiste_id`,`fonction`),
  KEY `artiste_id` (`artiste_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `titres_groupes`
--

CREATE TABLE IF NOT EXISTS `titres_groupes` (
  `titre_id` int(10) unsigned NOT NULL,
  `groupe_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`titre_id`,`groupe_id`),
  KEY `groupe_id` (`groupe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `traductions`
--

CREATE TABLE IF NOT EXISTS `traductions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `panier_id` int(10) unsigned NOT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `created_at` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `panier_id` (`panier_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23868 ;

-- --------------------------------------------------------

--
-- Structure de la table `users_adresses`
--

CREATE TABLE IF NOT EXISTS `users_adresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `civilite` varchar(40) NOT NULL COMMENT 'Madame, Monsieur, Mademoiselle',
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `societe` varchar(100) NOT NULL DEFAULT '',
  `pays` varchar(40) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `complement_adresse` varchar(200) NOT NULL DEFAULT '' COMMENT '(N° bât, étage, appt, digicode...)',
  `code_postal` varchar(20) NOT NULL,
  `ville` varchar(40) NOT NULL,
  `adresse_de_facturation` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `videos`
--

CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_id` int(11) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `video` text NOT NULL,
  `video_url` text NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupe_id` (`groupe_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=219 ;

-- --------------------------------------------------------

--
-- Structure de la table `videos_label`
--

CREATE TABLE IF NOT EXISTS `videos_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artist` varchar(255) NOT NULL,
  `song` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `video` text NOT NULL,
  `video_url` text NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=129 ;

-- --------------------------------------------------------

--
-- Structure de la table `zones`
--

CREATE TABLE IF NOT EXISTS `zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `name` varchar(64) NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
