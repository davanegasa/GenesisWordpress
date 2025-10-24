-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 24-10-2025 a las 15:49:38
-- Versión del servidor: 10.3.39-MariaDB-cll-lve
-- Versión de PHP: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `emmaus_wpgenesis`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_commentmeta`
--

CREATE TABLE `edgen_commentmeta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_comments`
--

CREATE TABLE `edgen_comments` (
  `comment_ID` bigint(20) UNSIGNED NOT NULL,
  `comment_post_ID` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) NOT NULL DEFAULT '',
  `comment_type` varchar(20) NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_comments`
--

INSERT INTO `edgen_comments` (`comment_ID`, `comment_post_ID`, `comment_author`, `comment_author_email`, `comment_author_url`, `comment_author_IP`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_karma`, `comment_approved`, `comment_agent`, `comment_type`, `comment_parent`, `user_id`) VALUES
(1, 1, 'A WordPress Commenter', 'wapuu@wordpress.example', 'https://wordpress.org/', '', '2024-09-24 20:16:48', '2024-09-24 20:16:48', 'Hi, this is a comment.\nTo get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.\nCommenter avatars come from <a href=\"https://en.gravatar.com/\">Gravatar</a>.', 0, 'post-trashed', '', 'comment', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_links`
--

CREATE TABLE `edgen_links` (
  `link_id` bigint(20) UNSIGNED NOT NULL,
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `link_name` varchar(255) NOT NULL DEFAULT '',
  `link_image` varchar(255) NOT NULL DEFAULT '',
  `link_target` varchar(25) NOT NULL DEFAULT '',
  `link_description` varchar(255) NOT NULL DEFAULT '',
  `link_visible` varchar(20) NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `link_rating` int(11) NOT NULL DEFAULT 0,
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) NOT NULL DEFAULT '',
  `link_notes` mediumtext NOT NULL,
  `link_rss` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_options`
--

CREATE TABLE `edgen_options` (
  `option_id` bigint(20) UNSIGNED NOT NULL,
  `option_name` varchar(191) NOT NULL DEFAULT '',
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL DEFAULT 'yes'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_options`
--

INSERT INTO `edgen_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'cron', 'a:9:{i:1761337835;a:4:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:30:\"wp_scheduled_auto_draft_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:21:\"wp_update_user_counts\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1761340627;a:1:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}}i:1761346433;a:1:{s:24:\"wpb_data_sync_loginpress\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1761380208;a:1:{s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1761380227;a:2:{s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1761423408;a:1:{s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1761683487;a:1:{s:30:\"wp_delete_temp_updater_backups\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}i:1761769008;a:1:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}', 'on'),
(2, 'siteurl', 'https://emmausdigital.com/genesis', 'on'),
(3, 'home', 'https://emmausdigital.com/genesis', 'on'),
(4, 'blogname', 'Genesis', 'on'),
(5, 'blogdescription', 'Genesis', 'on'),
(6, 'users_can_register', '0', 'on'),
(7, 'admin_email', 'admin@emmausdigital.com', 'on'),
(8, 'start_of_week', '1', 'on'),
(9, 'use_balanceTags', '0', 'on'),
(10, 'use_smilies', '1', 'on'),
(11, 'require_name_email', '1', 'on'),
(12, 'comments_notify', '1', 'on'),
(13, 'posts_per_rss', '10', 'on'),
(14, 'rss_use_excerpt', '0', 'on'),
(15, 'mailserver_url', 'mail.example.com', 'on'),
(16, 'mailserver_login', 'login@example.com', 'on'),
(17, 'mailserver_pass', 'password', 'on'),
(18, 'mailserver_port', '110', 'on'),
(19, 'default_category', '1', 'on'),
(20, 'default_comment_status', 'open', 'on'),
(21, 'default_ping_status', 'open', 'on'),
(22, 'default_pingback_flag', '1', 'on'),
(23, 'posts_per_page', '10', 'on'),
(24, 'date_format', 'F j, Y', 'on'),
(25, 'time_format', 'g:i a', 'on'),
(26, 'links_updated_date_format', 'F j, Y g:i a', 'on'),
(27, 'comment_moderation', '0', 'on'),
(28, 'moderation_notify', '1', 'on'),
(29, 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/', 'on'),
(30, 'rewrite_rules', 'a:94:{s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:17:\"^wp-sitemap\\.xml$\";s:23:\"index.php?sitemap=index\";s:17:\"^wp-sitemap\\.xsl$\";s:36:\"index.php?sitemap-stylesheet=sitemap\";s:23:\"^wp-sitemap-index\\.xsl$\";s:34:\"index.php?sitemap-stylesheet=index\";s:48:\"^wp-sitemap-([a-z]+?)-([a-z\\d_-]+?)-(\\d+?)\\.xml$\";s:75:\"index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]\";s:34:\"^wp-sitemap-([a-z]+?)-(\\d+?)\\.xml$\";s:47:\"index.php?sitemap=$matches[1]&paged=$matches[2]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:23:\"category/(.+?)/embed/?$\";s:46:\"index.php?category_name=$matches[1]&embed=true\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:20:\"tag/([^/]+)/embed/?$\";s:36:\"index.php?tag=$matches[1]&embed=true\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:21:\"type/([^/]+)/embed/?$\";s:44:\"index.php?post_format=$matches[1]&embed=true\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:8:\"embed/?$\";s:21:\"index.php?&embed=true\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:17:\"comments/embed/?$\";s:21:\"index.php?&embed=true\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:20:\"search/(.+)/embed/?$\";s:34:\"index.php?s=$matches[1]&embed=true\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:23:\"author/([^/]+)/embed/?$\";s:44:\"index.php?author_name=$matches[1]&embed=true\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:45:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$\";s:74:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:32:\"([0-9]{4})/([0-9]{1,2})/embed/?$\";s:58:\"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:19:\"([0-9]{4})/embed/?$\";s:37:\"index.php?year=$matches[1]&embed=true\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:58:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:68:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:88:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:64:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:53:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/embed/?$\";s:91:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/trackback/?$\";s:85:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&tb=1\";s:77:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:65:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/page/?([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&paged=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/comment-page-([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&cpage=$matches[5]\";s:61:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)(?:/([0-9]+))?/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&page=$matches[5]\";s:47:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:57:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:77:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:53:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&cpage=$matches[4]\";s:51:\"([0-9]{4})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&cpage=$matches[3]\";s:38:\"([0-9]{4})/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&cpage=$matches[2]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\".?.+?/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"(.?.+?)/embed/?$\";s:41:\"index.php?pagename=$matches[1]&embed=true\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";}', 'on'),
(31, 'hack_file', '0', 'on'),
(32, 'blog_charset', 'UTF-8', 'on'),
(33, 'moderation_keys', '', 'off'),
(34, 'active_plugins', 'a:1:{i:0;s:36:\"plg-genesis/registro-estudiantes.php\";}', 'on'),
(35, 'category_base', '', 'on'),
(36, 'ping_sites', 'https://rpc.pingomatic.com/', 'on'),
(37, 'comment_max_links', '2', 'on'),
(38, 'gmt_offset', '0', 'on'),
(39, 'default_email_category', '1', 'on'),
(40, 'recently_edited', '', 'off'),
(41, 'template', 'MiTema', 'on'),
(42, 'stylesheet', 'MiTema', 'on'),
(43, 'comment_registration', '0', 'on'),
(44, 'html_type', 'text/html', 'on'),
(45, 'use_trackback', '0', 'on'),
(46, 'default_role', 'subscriber', 'on'),
(47, 'db_version', '60421', 'on'),
(48, 'uploads_use_yearmonth_folders', '1', 'on'),
(49, 'upload_path', '', 'on'),
(50, 'blog_public', '1', 'on'),
(51, 'default_link_category', '2', 'on'),
(52, 'show_on_front', 'posts', 'on'),
(53, 'tag_base', '', 'on'),
(54, 'show_avatars', '1', 'on'),
(55, 'avatar_rating', 'G', 'on'),
(56, 'upload_url_path', '', 'on'),
(57, 'thumbnail_size_w', '150', 'on'),
(58, 'thumbnail_size_h', '150', 'on'),
(59, 'thumbnail_crop', '1', 'on'),
(60, 'medium_size_w', '300', 'on'),
(61, 'medium_size_h', '300', 'on'),
(62, 'avatar_default', 'mystery', 'on'),
(63, 'large_size_w', '1024', 'on'),
(64, 'large_size_h', '1024', 'on'),
(65, 'image_default_link_type', 'none', 'on'),
(66, 'image_default_size', '', 'on'),
(67, 'image_default_align', '', 'on'),
(68, 'close_comments_for_old_posts', '0', 'on'),
(69, 'close_comments_days_old', '14', 'on'),
(70, 'thread_comments', '1', 'on'),
(71, 'thread_comments_depth', '5', 'on'),
(72, 'page_comments', '0', 'on'),
(73, 'comments_per_page', '50', 'on'),
(74, 'default_comments_page', 'newest', 'on'),
(75, 'comment_order', 'asc', 'on'),
(76, 'sticky_posts', 'a:0:{}', 'on'),
(77, 'widget_categories', 'a:2:{i:1;a:0:{}s:12:\"_multiwidget\";i:1;}', 'auto'),
(78, 'widget_text', 'a:2:{i:1;a:0:{}s:12:\"_multiwidget\";i:1;}', 'auto'),
(79, 'widget_rss', 'a:2:{i:1;a:0:{}s:12:\"_multiwidget\";i:1;}', 'auto'),
(80, 'uninstall_plugins', 'a:1:{s:25:\"loginpress/loginpress.php\";a:2:{i:0;s:16:\"WPBRIGADE_Logger\";i:1;s:18:\"log_uninstallation\";}}', 'off'),
(81, 'timezone_string', '', 'on'),
(82, 'page_for_posts', '0', 'on'),
(83, 'page_on_front', '0', 'on'),
(84, 'default_post_format', '0', 'on'),
(85, 'link_manager_enabled', '0', 'on'),
(86, 'finished_splitting_shared_terms', '1', 'on'),
(87, 'site_icon', '0', 'on'),
(88, 'medium_large_size_w', '768', 'on'),
(89, 'medium_large_size_h', '0', 'on'),
(90, 'wp_page_for_privacy_policy', '3', 'on'),
(91, 'show_comments_cookies_opt_in', '1', 'on'),
(92, 'admin_email_lifespan', '1774979364', 'on'),
(93, 'disallowed_keys', '', 'off'),
(94, 'comment_previously_approved', '1', 'on'),
(95, 'auto_plugin_theme_update_emails', 'a:0:{}', 'off'),
(96, 'auto_update_core_dev', 'enabled', 'on'),
(97, 'auto_update_core_minor', 'enabled', 'on'),
(98, 'auto_update_core_major', 'enabled', 'on'),
(99, 'wp_force_deactivated_plugins', 'a:0:{}', 'off'),
(100, 'wp_attachment_pages_enabled', '0', 'on'),
(101, 'initial_db_version', '57155', 'on'),
(102, 'edgen_user_roles', 'a:9:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:92:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:17:\"plg_view_students\";b:1;s:19:\"plg_create_students\";b:1;s:17:\"plg_edit_students\";b:1;s:19:\"plg_delete_students\";b:1;s:16:\"plg_view_courses\";b:1;s:18:\"plg_assign_courses\";b:1;s:18:\"plg_create_courses\";b:1;s:16:\"plg_edit_courses\";b:1;s:18:\"plg_delete_courses\";b:1;s:17:\"plg_view_programs\";b:1;s:19:\"plg_create_programs\";b:1;s:17:\"plg_edit_programs\";b:1;s:19:\"plg_delete_programs\";b:1;s:17:\"plg_view_contacts\";b:1;s:19:\"plg_create_contacts\";b:1;s:17:\"plg_edit_contacts\";b:1;s:19:\"plg_delete_contacts\";b:1;s:15:\"plg_view_events\";b:1;s:17:\"plg_create_events\";b:1;s:15:\"plg_edit_events\";b:1;s:17:\"plg_delete_events\";b:1;s:14:\"plg_view_stats\";b:1;s:16:\"plg_export_stats\";b:1;s:14:\"plg_view_theme\";b:1;s:16:\"plg_change_theme\";b:1;s:14:\"plg_view_users\";b:1;s:16:\"plg_create_users\";b:1;s:14:\"plg_edit_users\";b:1;s:16:\"plg_delete_users\";b:1;s:17:\"plg_switch_office\";b:1;s:16:\"plg_view_swagger\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:34:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}s:17:\"plg_office_viewer\";a:2:{s:4:\"name\";s:23:\"Visualizador de Oficina\";s:12:\"capabilities\";a:8:{s:4:\"read\";b:1;s:17:\"plg_view_students\";b:1;s:16:\"plg_view_courses\";b:1;s:17:\"plg_view_programs\";b:1;s:17:\"plg_view_contacts\";b:1;s:15:\"plg_view_events\";b:1;s:14:\"plg_view_stats\";b:1;s:14:\"plg_view_theme\";b:1;}}s:16:\"plg_office_staff\";a:2:{s:4:\"name\";s:19:\"Personal de Oficina\";s:12:\"capabilities\";a:16:{s:4:\"read\";b:1;s:17:\"plg_view_students\";b:1;s:19:\"plg_create_students\";b:1;s:17:\"plg_edit_students\";b:1;s:16:\"plg_view_courses\";b:1;s:18:\"plg_assign_courses\";b:1;s:17:\"plg_view_programs\";b:1;s:17:\"plg_view_contacts\";b:1;s:19:\"plg_create_contacts\";b:1;s:17:\"plg_edit_contacts\";b:1;s:15:\"plg_view_events\";b:1;s:17:\"plg_create_events\";b:1;s:15:\"plg_edit_events\";b:1;s:14:\"plg_view_stats\";b:1;s:14:\"plg_view_theme\";b:1;s:16:\"plg_view_swagger\";b:1;}}s:18:\"plg_office_manager\";a:2:{s:4:\"name\";s:24:\"Administrador de Oficina\";s:12:\"capabilities\";a:31:{s:4:\"read\";b:1;s:17:\"plg_view_students\";b:1;s:19:\"plg_create_students\";b:1;s:17:\"plg_edit_students\";b:1;s:19:\"plg_delete_students\";b:1;s:16:\"plg_view_courses\";b:1;s:18:\"plg_assign_courses\";b:1;s:18:\"plg_create_courses\";b:1;s:16:\"plg_edit_courses\";b:1;s:18:\"plg_delete_courses\";b:1;s:17:\"plg_view_programs\";b:1;s:19:\"plg_create_programs\";b:1;s:17:\"plg_edit_programs\";b:1;s:19:\"plg_delete_programs\";b:1;s:17:\"plg_view_contacts\";b:1;s:19:\"plg_create_contacts\";b:1;s:17:\"plg_edit_contacts\";b:1;s:19:\"plg_delete_contacts\";b:1;s:15:\"plg_view_events\";b:1;s:17:\"plg_create_events\";b:1;s:15:\"plg_edit_events\";b:1;s:17:\"plg_delete_events\";b:1;s:14:\"plg_view_stats\";b:1;s:16:\"plg_export_stats\";b:1;s:14:\"plg_view_theme\";b:1;s:16:\"plg_change_theme\";b:1;s:14:\"plg_view_users\";b:1;s:16:\"plg_create_users\";b:1;s:14:\"plg_edit_users\";b:1;s:16:\"plg_delete_users\";b:1;s:16:\"plg_view_swagger\";b:1;}}s:15:\"plg_super_admin\";a:2:{s:4:\"name\";s:19:\"Super Administrador\";s:12:\"capabilities\";a:32:{s:4:\"read\";b:1;s:17:\"plg_view_students\";b:1;s:19:\"plg_create_students\";b:1;s:17:\"plg_edit_students\";b:1;s:19:\"plg_delete_students\";b:1;s:16:\"plg_view_courses\";b:1;s:18:\"plg_assign_courses\";b:1;s:18:\"plg_create_courses\";b:1;s:16:\"plg_edit_courses\";b:1;s:18:\"plg_delete_courses\";b:1;s:17:\"plg_view_programs\";b:1;s:19:\"plg_create_programs\";b:1;s:17:\"plg_edit_programs\";b:1;s:19:\"plg_delete_programs\";b:1;s:17:\"plg_view_contacts\";b:1;s:19:\"plg_create_contacts\";b:1;s:17:\"plg_edit_contacts\";b:1;s:19:\"plg_delete_contacts\";b:1;s:15:\"plg_view_events\";b:1;s:17:\"plg_create_events\";b:1;s:15:\"plg_edit_events\";b:1;s:17:\"plg_delete_events\";b:1;s:14:\"plg_view_stats\";b:1;s:16:\"plg_export_stats\";b:1;s:14:\"plg_view_theme\";b:1;s:16:\"plg_change_theme\";b:1;s:14:\"plg_view_users\";b:1;s:16:\"plg_create_users\";b:1;s:14:\"plg_edit_users\";b:1;s:16:\"plg_delete_users\";b:1;s:17:\"plg_switch_office\";b:1;s:16:\"plg_view_swagger\";b:1;}}}', 'on'),
(103, 'fresh_site', '0', 'off'),
(104, 'user_count', '15', 'off'),
(105, 'widget_block', 'a:6:{i:2;a:1:{s:7:\"content\";s:19:\"<!-- wp:search /-->\";}i:3;a:1:{s:7:\"content\";s:154:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Posts</h2><!-- /wp:heading --><!-- wp:latest-posts /--></div><!-- /wp:group -->\";}i:4;a:1:{s:7:\"content\";s:227:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Comments</h2><!-- /wp:heading --><!-- wp:latest-comments {\"displayAvatar\":false,\"displayDate\":false,\"displayExcerpt\":false} /--></div><!-- /wp:group -->\";}i:5;a:1:{s:7:\"content\";s:146:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Archives</h2><!-- /wp:heading --><!-- wp:archives /--></div><!-- /wp:group -->\";}i:6;a:1:{s:7:\"content\";s:150:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Categories</h2><!-- /wp:heading --><!-- wp:categories /--></div><!-- /wp:group -->\";}s:12:\"_multiwidget\";i:1;}', 'auto'),
(106, 'sidebars_widgets', 'a:2:{s:19:\"wp_inactive_widgets\";a:5:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";i:3;s:7:\"block-5\";i:4;s:7:\"block-6\";}s:13:\"array_version\";i:3;}', 'auto'),
(107, 'widget_pages', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(108, 'widget_calendar', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(109, 'widget_archives', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(110, 'widget_media_audio', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(111, 'widget_media_image', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(112, 'widget_media_gallery', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(113, 'widget_media_video', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(114, 'widget_meta', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(115, 'widget_search', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(116, 'widget_recent-posts', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(117, 'widget_recent-comments', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(118, 'widget_tag_cloud', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(119, 'widget_nav_menu', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(120, 'widget_custom_html', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(121, 'recovery_keys', 'a:0:{}', 'off'),
(122, 'theme_mods_twentytwentyfour', 'a:2:{s:18:\"custom_css_post_id\";i:-1;s:16:\"sidebars_widgets\";a:2:{s:4:\"time\";i:1727842796;s:4:\"data\";a:3:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";}s:9:\"sidebar-2\";a:2:{i:0;s:7:\"block-5\";i:1;s:7:\"block-6\";}}}}', 'off'),
(123, '_transient_wp_core_block_css_files', 'a:2:{s:7:\"version\";s:5:\"6.6.2\";s:5:\"files\";a:496:{i:0;s:23:\"archives/editor-rtl.css\";i:1;s:27:\"archives/editor-rtl.min.css\";i:2;s:19:\"archives/editor.css\";i:3;s:23:\"archives/editor.min.css\";i:4;s:22:\"archives/style-rtl.css\";i:5;s:26:\"archives/style-rtl.min.css\";i:6;s:18:\"archives/style.css\";i:7;s:22:\"archives/style.min.css\";i:8;s:20:\"audio/editor-rtl.css\";i:9;s:24:\"audio/editor-rtl.min.css\";i:10;s:16:\"audio/editor.css\";i:11;s:20:\"audio/editor.min.css\";i:12;s:19:\"audio/style-rtl.css\";i:13;s:23:\"audio/style-rtl.min.css\";i:14;s:15:\"audio/style.css\";i:15;s:19:\"audio/style.min.css\";i:16;s:19:\"audio/theme-rtl.css\";i:17;s:23:\"audio/theme-rtl.min.css\";i:18;s:15:\"audio/theme.css\";i:19;s:19:\"audio/theme.min.css\";i:20;s:21:\"avatar/editor-rtl.css\";i:21;s:25:\"avatar/editor-rtl.min.css\";i:22;s:17:\"avatar/editor.css\";i:23;s:21:\"avatar/editor.min.css\";i:24;s:20:\"avatar/style-rtl.css\";i:25;s:24:\"avatar/style-rtl.min.css\";i:26;s:16:\"avatar/style.css\";i:27;s:20:\"avatar/style.min.css\";i:28;s:21:\"button/editor-rtl.css\";i:29;s:25:\"button/editor-rtl.min.css\";i:30;s:17:\"button/editor.css\";i:31;s:21:\"button/editor.min.css\";i:32;s:20:\"button/style-rtl.css\";i:33;s:24:\"button/style-rtl.min.css\";i:34;s:16:\"button/style.css\";i:35;s:20:\"button/style.min.css\";i:36;s:22:\"buttons/editor-rtl.css\";i:37;s:26:\"buttons/editor-rtl.min.css\";i:38;s:18:\"buttons/editor.css\";i:39;s:22:\"buttons/editor.min.css\";i:40;s:21:\"buttons/style-rtl.css\";i:41;s:25:\"buttons/style-rtl.min.css\";i:42;s:17:\"buttons/style.css\";i:43;s:21:\"buttons/style.min.css\";i:44;s:22:\"calendar/style-rtl.css\";i:45;s:26:\"calendar/style-rtl.min.css\";i:46;s:18:\"calendar/style.css\";i:47;s:22:\"calendar/style.min.css\";i:48;s:25:\"categories/editor-rtl.css\";i:49;s:29:\"categories/editor-rtl.min.css\";i:50;s:21:\"categories/editor.css\";i:51;s:25:\"categories/editor.min.css\";i:52;s:24:\"categories/style-rtl.css\";i:53;s:28:\"categories/style-rtl.min.css\";i:54;s:20:\"categories/style.css\";i:55;s:24:\"categories/style.min.css\";i:56;s:19:\"code/editor-rtl.css\";i:57;s:23:\"code/editor-rtl.min.css\";i:58;s:15:\"code/editor.css\";i:59;s:19:\"code/editor.min.css\";i:60;s:18:\"code/style-rtl.css\";i:61;s:22:\"code/style-rtl.min.css\";i:62;s:14:\"code/style.css\";i:63;s:18:\"code/style.min.css\";i:64;s:18:\"code/theme-rtl.css\";i:65;s:22:\"code/theme-rtl.min.css\";i:66;s:14:\"code/theme.css\";i:67;s:18:\"code/theme.min.css\";i:68;s:22:\"columns/editor-rtl.css\";i:69;s:26:\"columns/editor-rtl.min.css\";i:70;s:18:\"columns/editor.css\";i:71;s:22:\"columns/editor.min.css\";i:72;s:21:\"columns/style-rtl.css\";i:73;s:25:\"columns/style-rtl.min.css\";i:74;s:17:\"columns/style.css\";i:75;s:21:\"columns/style.min.css\";i:76;s:29:\"comment-content/style-rtl.css\";i:77;s:33:\"comment-content/style-rtl.min.css\";i:78;s:25:\"comment-content/style.css\";i:79;s:29:\"comment-content/style.min.css\";i:80;s:30:\"comment-template/style-rtl.css\";i:81;s:34:\"comment-template/style-rtl.min.css\";i:82;s:26:\"comment-template/style.css\";i:83;s:30:\"comment-template/style.min.css\";i:84;s:42:\"comments-pagination-numbers/editor-rtl.css\";i:85;s:46:\"comments-pagination-numbers/editor-rtl.min.css\";i:86;s:38:\"comments-pagination-numbers/editor.css\";i:87;s:42:\"comments-pagination-numbers/editor.min.css\";i:88;s:34:\"comments-pagination/editor-rtl.css\";i:89;s:38:\"comments-pagination/editor-rtl.min.css\";i:90;s:30:\"comments-pagination/editor.css\";i:91;s:34:\"comments-pagination/editor.min.css\";i:92;s:33:\"comments-pagination/style-rtl.css\";i:93;s:37:\"comments-pagination/style-rtl.min.css\";i:94;s:29:\"comments-pagination/style.css\";i:95;s:33:\"comments-pagination/style.min.css\";i:96;s:29:\"comments-title/editor-rtl.css\";i:97;s:33:\"comments-title/editor-rtl.min.css\";i:98;s:25:\"comments-title/editor.css\";i:99;s:29:\"comments-title/editor.min.css\";i:100;s:23:\"comments/editor-rtl.css\";i:101;s:27:\"comments/editor-rtl.min.css\";i:102;s:19:\"comments/editor.css\";i:103;s:23:\"comments/editor.min.css\";i:104;s:22:\"comments/style-rtl.css\";i:105;s:26:\"comments/style-rtl.min.css\";i:106;s:18:\"comments/style.css\";i:107;s:22:\"comments/style.min.css\";i:108;s:20:\"cover/editor-rtl.css\";i:109;s:24:\"cover/editor-rtl.min.css\";i:110;s:16:\"cover/editor.css\";i:111;s:20:\"cover/editor.min.css\";i:112;s:19:\"cover/style-rtl.css\";i:113;s:23:\"cover/style-rtl.min.css\";i:114;s:15:\"cover/style.css\";i:115;s:19:\"cover/style.min.css\";i:116;s:22:\"details/editor-rtl.css\";i:117;s:26:\"details/editor-rtl.min.css\";i:118;s:18:\"details/editor.css\";i:119;s:22:\"details/editor.min.css\";i:120;s:21:\"details/style-rtl.css\";i:121;s:25:\"details/style-rtl.min.css\";i:122;s:17:\"details/style.css\";i:123;s:21:\"details/style.min.css\";i:124;s:20:\"embed/editor-rtl.css\";i:125;s:24:\"embed/editor-rtl.min.css\";i:126;s:16:\"embed/editor.css\";i:127;s:20:\"embed/editor.min.css\";i:128;s:19:\"embed/style-rtl.css\";i:129;s:23:\"embed/style-rtl.min.css\";i:130;s:15:\"embed/style.css\";i:131;s:19:\"embed/style.min.css\";i:132;s:19:\"embed/theme-rtl.css\";i:133;s:23:\"embed/theme-rtl.min.css\";i:134;s:15:\"embed/theme.css\";i:135;s:19:\"embed/theme.min.css\";i:136;s:19:\"file/editor-rtl.css\";i:137;s:23:\"file/editor-rtl.min.css\";i:138;s:15:\"file/editor.css\";i:139;s:19:\"file/editor.min.css\";i:140;s:18:\"file/style-rtl.css\";i:141;s:22:\"file/style-rtl.min.css\";i:142;s:14:\"file/style.css\";i:143;s:18:\"file/style.min.css\";i:144;s:23:\"footnotes/style-rtl.css\";i:145;s:27:\"footnotes/style-rtl.min.css\";i:146;s:19:\"footnotes/style.css\";i:147;s:23:\"footnotes/style.min.css\";i:148;s:23:\"freeform/editor-rtl.css\";i:149;s:27:\"freeform/editor-rtl.min.css\";i:150;s:19:\"freeform/editor.css\";i:151;s:23:\"freeform/editor.min.css\";i:152;s:22:\"gallery/editor-rtl.css\";i:153;s:26:\"gallery/editor-rtl.min.css\";i:154;s:18:\"gallery/editor.css\";i:155;s:22:\"gallery/editor.min.css\";i:156;s:21:\"gallery/style-rtl.css\";i:157;s:25:\"gallery/style-rtl.min.css\";i:158;s:17:\"gallery/style.css\";i:159;s:21:\"gallery/style.min.css\";i:160;s:21:\"gallery/theme-rtl.css\";i:161;s:25:\"gallery/theme-rtl.min.css\";i:162;s:17:\"gallery/theme.css\";i:163;s:21:\"gallery/theme.min.css\";i:164;s:20:\"group/editor-rtl.css\";i:165;s:24:\"group/editor-rtl.min.css\";i:166;s:16:\"group/editor.css\";i:167;s:20:\"group/editor.min.css\";i:168;s:19:\"group/style-rtl.css\";i:169;s:23:\"group/style-rtl.min.css\";i:170;s:15:\"group/style.css\";i:171;s:19:\"group/style.min.css\";i:172;s:19:\"group/theme-rtl.css\";i:173;s:23:\"group/theme-rtl.min.css\";i:174;s:15:\"group/theme.css\";i:175;s:19:\"group/theme.min.css\";i:176;s:21:\"heading/style-rtl.css\";i:177;s:25:\"heading/style-rtl.min.css\";i:178;s:17:\"heading/style.css\";i:179;s:21:\"heading/style.min.css\";i:180;s:19:\"html/editor-rtl.css\";i:181;s:23:\"html/editor-rtl.min.css\";i:182;s:15:\"html/editor.css\";i:183;s:19:\"html/editor.min.css\";i:184;s:20:\"image/editor-rtl.css\";i:185;s:24:\"image/editor-rtl.min.css\";i:186;s:16:\"image/editor.css\";i:187;s:20:\"image/editor.min.css\";i:188;s:19:\"image/style-rtl.css\";i:189;s:23:\"image/style-rtl.min.css\";i:190;s:15:\"image/style.css\";i:191;s:19:\"image/style.min.css\";i:192;s:19:\"image/theme-rtl.css\";i:193;s:23:\"image/theme-rtl.min.css\";i:194;s:15:\"image/theme.css\";i:195;s:19:\"image/theme.min.css\";i:196;s:29:\"latest-comments/style-rtl.css\";i:197;s:33:\"latest-comments/style-rtl.min.css\";i:198;s:25:\"latest-comments/style.css\";i:199;s:29:\"latest-comments/style.min.css\";i:200;s:27:\"latest-posts/editor-rtl.css\";i:201;s:31:\"latest-posts/editor-rtl.min.css\";i:202;s:23:\"latest-posts/editor.css\";i:203;s:27:\"latest-posts/editor.min.css\";i:204;s:26:\"latest-posts/style-rtl.css\";i:205;s:30:\"latest-posts/style-rtl.min.css\";i:206;s:22:\"latest-posts/style.css\";i:207;s:26:\"latest-posts/style.min.css\";i:208;s:18:\"list/style-rtl.css\";i:209;s:22:\"list/style-rtl.min.css\";i:210;s:14:\"list/style.css\";i:211;s:18:\"list/style.min.css\";i:212;s:25:\"media-text/editor-rtl.css\";i:213;s:29:\"media-text/editor-rtl.min.css\";i:214;s:21:\"media-text/editor.css\";i:215;s:25:\"media-text/editor.min.css\";i:216;s:24:\"media-text/style-rtl.css\";i:217;s:28:\"media-text/style-rtl.min.css\";i:218;s:20:\"media-text/style.css\";i:219;s:24:\"media-text/style.min.css\";i:220;s:19:\"more/editor-rtl.css\";i:221;s:23:\"more/editor-rtl.min.css\";i:222;s:15:\"more/editor.css\";i:223;s:19:\"more/editor.min.css\";i:224;s:30:\"navigation-link/editor-rtl.css\";i:225;s:34:\"navigation-link/editor-rtl.min.css\";i:226;s:26:\"navigation-link/editor.css\";i:227;s:30:\"navigation-link/editor.min.css\";i:228;s:29:\"navigation-link/style-rtl.css\";i:229;s:33:\"navigation-link/style-rtl.min.css\";i:230;s:25:\"navigation-link/style.css\";i:231;s:29:\"navigation-link/style.min.css\";i:232;s:33:\"navigation-submenu/editor-rtl.css\";i:233;s:37:\"navigation-submenu/editor-rtl.min.css\";i:234;s:29:\"navigation-submenu/editor.css\";i:235;s:33:\"navigation-submenu/editor.min.css\";i:236;s:25:\"navigation/editor-rtl.css\";i:237;s:29:\"navigation/editor-rtl.min.css\";i:238;s:21:\"navigation/editor.css\";i:239;s:25:\"navigation/editor.min.css\";i:240;s:24:\"navigation/style-rtl.css\";i:241;s:28:\"navigation/style-rtl.min.css\";i:242;s:20:\"navigation/style.css\";i:243;s:24:\"navigation/style.min.css\";i:244;s:23:\"nextpage/editor-rtl.css\";i:245;s:27:\"nextpage/editor-rtl.min.css\";i:246;s:19:\"nextpage/editor.css\";i:247;s:23:\"nextpage/editor.min.css\";i:248;s:24:\"page-list/editor-rtl.css\";i:249;s:28:\"page-list/editor-rtl.min.css\";i:250;s:20:\"page-list/editor.css\";i:251;s:24:\"page-list/editor.min.css\";i:252;s:23:\"page-list/style-rtl.css\";i:253;s:27:\"page-list/style-rtl.min.css\";i:254;s:19:\"page-list/style.css\";i:255;s:23:\"page-list/style.min.css\";i:256;s:24:\"paragraph/editor-rtl.css\";i:257;s:28:\"paragraph/editor-rtl.min.css\";i:258;s:20:\"paragraph/editor.css\";i:259;s:24:\"paragraph/editor.min.css\";i:260;s:23:\"paragraph/style-rtl.css\";i:261;s:27:\"paragraph/style-rtl.min.css\";i:262;s:19:\"paragraph/style.css\";i:263;s:23:\"paragraph/style.min.css\";i:264;s:25:\"post-author/style-rtl.css\";i:265;s:29:\"post-author/style-rtl.min.css\";i:266;s:21:\"post-author/style.css\";i:267;s:25:\"post-author/style.min.css\";i:268;s:33:\"post-comments-form/editor-rtl.css\";i:269;s:37:\"post-comments-form/editor-rtl.min.css\";i:270;s:29:\"post-comments-form/editor.css\";i:271;s:33:\"post-comments-form/editor.min.css\";i:272;s:32:\"post-comments-form/style-rtl.css\";i:273;s:36:\"post-comments-form/style-rtl.min.css\";i:274;s:28:\"post-comments-form/style.css\";i:275;s:32:\"post-comments-form/style.min.css\";i:276;s:27:\"post-content/editor-rtl.css\";i:277;s:31:\"post-content/editor-rtl.min.css\";i:278;s:23:\"post-content/editor.css\";i:279;s:27:\"post-content/editor.min.css\";i:280;s:23:\"post-date/style-rtl.css\";i:281;s:27:\"post-date/style-rtl.min.css\";i:282;s:19:\"post-date/style.css\";i:283;s:23:\"post-date/style.min.css\";i:284;s:27:\"post-excerpt/editor-rtl.css\";i:285;s:31:\"post-excerpt/editor-rtl.min.css\";i:286;s:23:\"post-excerpt/editor.css\";i:287;s:27:\"post-excerpt/editor.min.css\";i:288;s:26:\"post-excerpt/style-rtl.css\";i:289;s:30:\"post-excerpt/style-rtl.min.css\";i:290;s:22:\"post-excerpt/style.css\";i:291;s:26:\"post-excerpt/style.min.css\";i:292;s:34:\"post-featured-image/editor-rtl.css\";i:293;s:38:\"post-featured-image/editor-rtl.min.css\";i:294;s:30:\"post-featured-image/editor.css\";i:295;s:34:\"post-featured-image/editor.min.css\";i:296;s:33:\"post-featured-image/style-rtl.css\";i:297;s:37:\"post-featured-image/style-rtl.min.css\";i:298;s:29:\"post-featured-image/style.css\";i:299;s:33:\"post-featured-image/style.min.css\";i:300;s:34:\"post-navigation-link/style-rtl.css\";i:301;s:38:\"post-navigation-link/style-rtl.min.css\";i:302;s:30:\"post-navigation-link/style.css\";i:303;s:34:\"post-navigation-link/style.min.css\";i:304;s:28:\"post-template/editor-rtl.css\";i:305;s:32:\"post-template/editor-rtl.min.css\";i:306;s:24:\"post-template/editor.css\";i:307;s:28:\"post-template/editor.min.css\";i:308;s:27:\"post-template/style-rtl.css\";i:309;s:31:\"post-template/style-rtl.min.css\";i:310;s:23:\"post-template/style.css\";i:311;s:27:\"post-template/style.min.css\";i:312;s:24:\"post-terms/style-rtl.css\";i:313;s:28:\"post-terms/style-rtl.min.css\";i:314;s:20:\"post-terms/style.css\";i:315;s:24:\"post-terms/style.min.css\";i:316;s:24:\"post-title/style-rtl.css\";i:317;s:28:\"post-title/style-rtl.min.css\";i:318;s:20:\"post-title/style.css\";i:319;s:24:\"post-title/style.min.css\";i:320;s:26:\"preformatted/style-rtl.css\";i:321;s:30:\"preformatted/style-rtl.min.css\";i:322;s:22:\"preformatted/style.css\";i:323;s:26:\"preformatted/style.min.css\";i:324;s:24:\"pullquote/editor-rtl.css\";i:325;s:28:\"pullquote/editor-rtl.min.css\";i:326;s:20:\"pullquote/editor.css\";i:327;s:24:\"pullquote/editor.min.css\";i:328;s:23:\"pullquote/style-rtl.css\";i:329;s:27:\"pullquote/style-rtl.min.css\";i:330;s:19:\"pullquote/style.css\";i:331;s:23:\"pullquote/style.min.css\";i:332;s:23:\"pullquote/theme-rtl.css\";i:333;s:27:\"pullquote/theme-rtl.min.css\";i:334;s:19:\"pullquote/theme.css\";i:335;s:23:\"pullquote/theme.min.css\";i:336;s:39:\"query-pagination-numbers/editor-rtl.css\";i:337;s:43:\"query-pagination-numbers/editor-rtl.min.css\";i:338;s:35:\"query-pagination-numbers/editor.css\";i:339;s:39:\"query-pagination-numbers/editor.min.css\";i:340;s:31:\"query-pagination/editor-rtl.css\";i:341;s:35:\"query-pagination/editor-rtl.min.css\";i:342;s:27:\"query-pagination/editor.css\";i:343;s:31:\"query-pagination/editor.min.css\";i:344;s:30:\"query-pagination/style-rtl.css\";i:345;s:34:\"query-pagination/style-rtl.min.css\";i:346;s:26:\"query-pagination/style.css\";i:347;s:30:\"query-pagination/style.min.css\";i:348;s:25:\"query-title/style-rtl.css\";i:349;s:29:\"query-title/style-rtl.min.css\";i:350;s:21:\"query-title/style.css\";i:351;s:25:\"query-title/style.min.css\";i:352;s:20:\"query/editor-rtl.css\";i:353;s:24:\"query/editor-rtl.min.css\";i:354;s:16:\"query/editor.css\";i:355;s:20:\"query/editor.min.css\";i:356;s:19:\"quote/style-rtl.css\";i:357;s:23:\"quote/style-rtl.min.css\";i:358;s:15:\"quote/style.css\";i:359;s:19:\"quote/style.min.css\";i:360;s:19:\"quote/theme-rtl.css\";i:361;s:23:\"quote/theme-rtl.min.css\";i:362;s:15:\"quote/theme.css\";i:363;s:19:\"quote/theme.min.css\";i:364;s:23:\"read-more/style-rtl.css\";i:365;s:27:\"read-more/style-rtl.min.css\";i:366;s:19:\"read-more/style.css\";i:367;s:23:\"read-more/style.min.css\";i:368;s:18:\"rss/editor-rtl.css\";i:369;s:22:\"rss/editor-rtl.min.css\";i:370;s:14:\"rss/editor.css\";i:371;s:18:\"rss/editor.min.css\";i:372;s:17:\"rss/style-rtl.css\";i:373;s:21:\"rss/style-rtl.min.css\";i:374;s:13:\"rss/style.css\";i:375;s:17:\"rss/style.min.css\";i:376;s:21:\"search/editor-rtl.css\";i:377;s:25:\"search/editor-rtl.min.css\";i:378;s:17:\"search/editor.css\";i:379;s:21:\"search/editor.min.css\";i:380;s:20:\"search/style-rtl.css\";i:381;s:24:\"search/style-rtl.min.css\";i:382;s:16:\"search/style.css\";i:383;s:20:\"search/style.min.css\";i:384;s:20:\"search/theme-rtl.css\";i:385;s:24:\"search/theme-rtl.min.css\";i:386;s:16:\"search/theme.css\";i:387;s:20:\"search/theme.min.css\";i:388;s:24:\"separator/editor-rtl.css\";i:389;s:28:\"separator/editor-rtl.min.css\";i:390;s:20:\"separator/editor.css\";i:391;s:24:\"separator/editor.min.css\";i:392;s:23:\"separator/style-rtl.css\";i:393;s:27:\"separator/style-rtl.min.css\";i:394;s:19:\"separator/style.css\";i:395;s:23:\"separator/style.min.css\";i:396;s:23:\"separator/theme-rtl.css\";i:397;s:27:\"separator/theme-rtl.min.css\";i:398;s:19:\"separator/theme.css\";i:399;s:23:\"separator/theme.min.css\";i:400;s:24:\"shortcode/editor-rtl.css\";i:401;s:28:\"shortcode/editor-rtl.min.css\";i:402;s:20:\"shortcode/editor.css\";i:403;s:24:\"shortcode/editor.min.css\";i:404;s:24:\"site-logo/editor-rtl.css\";i:405;s:28:\"site-logo/editor-rtl.min.css\";i:406;s:20:\"site-logo/editor.css\";i:407;s:24:\"site-logo/editor.min.css\";i:408;s:23:\"site-logo/style-rtl.css\";i:409;s:27:\"site-logo/style-rtl.min.css\";i:410;s:19:\"site-logo/style.css\";i:411;s:23:\"site-logo/style.min.css\";i:412;s:27:\"site-tagline/editor-rtl.css\";i:413;s:31:\"site-tagline/editor-rtl.min.css\";i:414;s:23:\"site-tagline/editor.css\";i:415;s:27:\"site-tagline/editor.min.css\";i:416;s:25:\"site-title/editor-rtl.css\";i:417;s:29:\"site-title/editor-rtl.min.css\";i:418;s:21:\"site-title/editor.css\";i:419;s:25:\"site-title/editor.min.css\";i:420;s:24:\"site-title/style-rtl.css\";i:421;s:28:\"site-title/style-rtl.min.css\";i:422;s:20:\"site-title/style.css\";i:423;s:24:\"site-title/style.min.css\";i:424;s:26:\"social-link/editor-rtl.css\";i:425;s:30:\"social-link/editor-rtl.min.css\";i:426;s:22:\"social-link/editor.css\";i:427;s:26:\"social-link/editor.min.css\";i:428;s:27:\"social-links/editor-rtl.css\";i:429;s:31:\"social-links/editor-rtl.min.css\";i:430;s:23:\"social-links/editor.css\";i:431;s:27:\"social-links/editor.min.css\";i:432;s:26:\"social-links/style-rtl.css\";i:433;s:30:\"social-links/style-rtl.min.css\";i:434;s:22:\"social-links/style.css\";i:435;s:26:\"social-links/style.min.css\";i:436;s:21:\"spacer/editor-rtl.css\";i:437;s:25:\"spacer/editor-rtl.min.css\";i:438;s:17:\"spacer/editor.css\";i:439;s:21:\"spacer/editor.min.css\";i:440;s:20:\"spacer/style-rtl.css\";i:441;s:24:\"spacer/style-rtl.min.css\";i:442;s:16:\"spacer/style.css\";i:443;s:20:\"spacer/style.min.css\";i:444;s:20:\"table/editor-rtl.css\";i:445;s:24:\"table/editor-rtl.min.css\";i:446;s:16:\"table/editor.css\";i:447;s:20:\"table/editor.min.css\";i:448;s:19:\"table/style-rtl.css\";i:449;s:23:\"table/style-rtl.min.css\";i:450;s:15:\"table/style.css\";i:451;s:19:\"table/style.min.css\";i:452;s:19:\"table/theme-rtl.css\";i:453;s:23:\"table/theme-rtl.min.css\";i:454;s:15:\"table/theme.css\";i:455;s:19:\"table/theme.min.css\";i:456;s:23:\"tag-cloud/style-rtl.css\";i:457;s:27:\"tag-cloud/style-rtl.min.css\";i:458;s:19:\"tag-cloud/style.css\";i:459;s:23:\"tag-cloud/style.min.css\";i:460;s:28:\"template-part/editor-rtl.css\";i:461;s:32:\"template-part/editor-rtl.min.css\";i:462;s:24:\"template-part/editor.css\";i:463;s:28:\"template-part/editor.min.css\";i:464;s:27:\"template-part/theme-rtl.css\";i:465;s:31:\"template-part/theme-rtl.min.css\";i:466;s:23:\"template-part/theme.css\";i:467;s:27:\"template-part/theme.min.css\";i:468;s:30:\"term-description/style-rtl.css\";i:469;s:34:\"term-description/style-rtl.min.css\";i:470;s:26:\"term-description/style.css\";i:471;s:30:\"term-description/style.min.css\";i:472;s:27:\"text-columns/editor-rtl.css\";i:473;s:31:\"text-columns/editor-rtl.min.css\";i:474;s:23:\"text-columns/editor.css\";i:475;s:27:\"text-columns/editor.min.css\";i:476;s:26:\"text-columns/style-rtl.css\";i:477;s:30:\"text-columns/style-rtl.min.css\";i:478;s:22:\"text-columns/style.css\";i:479;s:26:\"text-columns/style.min.css\";i:480;s:19:\"verse/style-rtl.css\";i:481;s:23:\"verse/style-rtl.min.css\";i:482;s:15:\"verse/style.css\";i:483;s:19:\"verse/style.min.css\";i:484;s:20:\"video/editor-rtl.css\";i:485;s:24:\"video/editor-rtl.min.css\";i:486;s:16:\"video/editor.css\";i:487;s:20:\"video/editor.min.css\";i:488;s:19:\"video/style-rtl.css\";i:489;s:23:\"video/style-rtl.min.css\";i:490;s:15:\"video/style.css\";i:491;s:19:\"video/style.min.css\";i:492;s:19:\"video/theme-rtl.css\";i:493;s:23:\"video/theme-rtl.min.css\";i:494;s:15:\"video/theme.css\";i:495;s:19:\"video/theme.min.css\";}}', 'on'),
(688, '_transient_wp_styles_for_blocks', 'a:2:{s:4:\"hash\";s:32:\"8c7d46a72d7d4591fc1dd9485bedb304\";s:6:\"blocks\";a:5:{s:11:\"core/button\";s:0:\"\";s:14:\"core/site-logo\";s:0:\"\";s:18:\"core/post-template\";s:120:\":where(.wp-block-post-template.is-layout-flex){gap: 1.25em;}:where(.wp-block-post-template.is-layout-grid){gap: 1.25em;}\";s:12:\"core/columns\";s:102:\":where(.wp-block-columns.is-layout-flex){gap: 2em;}:where(.wp-block-columns.is-layout-grid){gap: 2em;}\";s:14:\"core/pullquote\";s:69:\":root :where(.wp-block-pullquote){font-size: 1.5em;line-height: 1.6;}\";}}', 'on'),
(501, 'current_theme', 'Tema Personalizado', 'auto'),
(502, 'theme_mods_MiTema', 'a:3:{i:0;b:0;s:18:\"nav_menu_locations\";a:0:{}s:18:\"custom_css_post_id\";i:-1;}', 'on'),
(503, 'theme_switched', '', 'auto'),
(667, 'auto_core_update_notified', 'a:4:{s:4:\"type\";s:7:\"success\";s:5:\"email\";s:23:\"admin@emmausdigital.com\";s:7:\"version\";s:5:\"6.8.3\";s:9:\"timestamp\";i:1761252497;}', 'off'),
(274, 'wpb_sdk_module_id', '6', 'auto'),
(275, 'wpb_sdk_module_slug', 'loginpress', 'auto'),
(276, 'loginpress_customization', 'a:0:{}', 'auto'),
(277, 'loginpress_setting', 'a:0:{}', 'auto'),
(278, 'customize_presets_settings', 'minimalist', 'on'),
(279, 'loginpress_active_time', '1727304098', 'off'),
(280, 'loginpress_autologin', '', 'auto'),
(281, 'loginpress_hidelogin', '', 'auto'),
(282, 'loginpress_limit_login_attempts', '', 'auto'),
(283, 'loginpress_login_redirects', '', 'auto'),
(284, 'loginpress_social_logins', '', 'auto'),
(285, 'loginpress_premium', '', 'auto'),
(286, 'rdn_fetch_12516765', 'fetch', 'auto'),
(287, '_loginpress_optin', 'yes', 'auto'),
(288, 'wpb_sdk_loginpress', '{\"communication\":\"1\",\"diagnostic_info\":\"1\",\"extensions\":\"1\",\"user_skip\":\"0\"}', 'auto'),
(239, '_transient_health-check-site-status-result', '{\"good\":17,\"recommended\":5,\"critical\":1}', 'on'),
(7068, '_transient_timeout_feed_d117b5738fbd35bd8c0391cda1f2b5d9', '1761379861', 'off'),
(152, '_site_transient_wp_plugin_dependencies_plugin_data', 'a:0:{}', 'off'),
(153, 'recently_activated', 'a:0:{}', 'off'),
(158, 'finished_updating_comment_type', '1', 'auto'),
(465, 'category_children', 'a:0:{}', 'auto'),
(662, 'db_upgraded', '', 'on'),
(450, 'recovery_mode_email_last_sent', '1732026601', 'auto'),
(6642, 'WPLANG', '', 'auto'),
(6643, 'new_admin_email', 'admin@emmausdigital.com', 'auto'),
(6666, 'plg_genesis_theme_BOG', '{\"bg\":\"#F5F7FB\",\"text\":\"#0F172A\",\"accent\":\"#0C497A\",\"sidebarBg\":\"#0C1018\",\"sidebarText\":\"#E5E7EB\",\"cardBg\":\"#FFFFFF\",\"border\":\"#D7DFEB\",\"mutedText\":\"#6B7280\",\"success\":\"#3FAB49\",\"warning\":\"#FFF100\",\"danger\":\"#E11D48\",\"info\":\"#154289\"}', 'off'),
(7056, '_site_transient_timeout_theme_roots', '1761338443', 'off'),
(7057, '_site_transient_theme_roots', 'a:4:{s:6:\"MiTema\";s:7:\"/themes\";s:16:\"twentytwentyfive\";s:7:\"/themes\";s:16:\"twentytwentyfour\";s:7:\"/themes\";s:17:\"twentytwentythree\";s:7:\"/themes\";}', 'off'),
(6981, '_site_transient_timeout_php_check_38979a08dcd71638878b7b4419751271', '1761770685', 'off'),
(6982, '_site_transient_php_check_38979a08dcd71638878b7b4419751271', 'a:5:{s:19:\"recommended_version\";s:3:\"8.3\";s:15:\"minimum_version\";s:6:\"7.2.24\";s:12:\"is_supported\";b:0;s:9:\"is_secure\";b:0;s:13:\"is_acceptable\";b:0;}', 'off'),
(6933, 'plg_genesis_theme_FDL', '{\"bg\":\"#FFFFFF\",\"text\":\"#0F172A\",\"accent\":\"#0C497A\",\"sidebarBg\":\"#0A1224\",\"sidebarText\":\"#F1F5F9\",\"cardBg\":\"#FFFFFF\",\"border\":\"#E5E7EB\",\"mutedText\":\"#6B7280\",\"success\":\"#3FAB49\",\"warning\":\"#EAB308\",\"danger\":\"#EF4444\",\"info\":\"#3B82F6\"}', 'off');
INSERT INTO `edgen_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(7039, '_site_transient_update_core', 'O:8:\"stdClass\":4:{s:7:\"updates\";a:1:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:6:\"latest\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.8.3.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-6.8.3.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-6.8.3-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-6.8.3-new-bundled.zip\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"6.8.3\";s:7:\"version\";s:5:\"6.8.3\";s:11:\"php_version\";s:6:\"7.2.24\";s:13:\"mysql_version\";s:5:\"5.5.5\";s:11:\"new_bundled\";s:3:\"6.7\";s:15:\"partial_version\";s:0:\"\";}}s:12:\"last_checked\";i:1761337115;s:15:\"version_checked\";s:5:\"6.8.3\";s:12:\"translations\";a:0:{}}', 'off'),
(7075, '_site_transient_update_themes', 'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1761337115;s:7:\"checked\";a:4:{s:6:\"MiTema\";s:3:\"1.0\";s:16:\"twentytwentyfive\";s:3:\"1.2\";s:16:\"twentytwentyfour\";s:3:\"1.3\";s:17:\"twentytwentythree\";s:3:\"1.6\";}s:8:\"response\";a:1:{s:16:\"twentytwentyfive\";a:6:{s:5:\"theme\";s:16:\"twentytwentyfive\";s:11:\"new_version\";s:3:\"1.3\";s:3:\"url\";s:46:\"https://wordpress.org/themes/twentytwentyfive/\";s:7:\"package\";s:62:\"https://downloads.wordpress.org/theme/twentytwentyfive.1.3.zip\";s:8:\"requires\";s:3:\"6.7\";s:12:\"requires_php\";s:3:\"7.2\";}}s:9:\"no_update\";a:2:{s:16:\"twentytwentyfour\";a:6:{s:5:\"theme\";s:16:\"twentytwentyfour\";s:11:\"new_version\";s:3:\"1.3\";s:3:\"url\";s:46:\"https://wordpress.org/themes/twentytwentyfour/\";s:7:\"package\";s:62:\"https://downloads.wordpress.org/theme/twentytwentyfour.1.3.zip\";s:8:\"requires\";s:3:\"6.4\";s:12:\"requires_php\";s:3:\"7.0\";}s:17:\"twentytwentythree\";a:6:{s:5:\"theme\";s:17:\"twentytwentythree\";s:11:\"new_version\";s:3:\"1.6\";s:3:\"url\";s:47:\"https://wordpress.org/themes/twentytwentythree/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/theme/twentytwentythree.1.6.zip\";s:8:\"requires\";s:3:\"6.1\";s:12:\"requires_php\";s:3:\"5.6\";}}s:12:\"translations\";a:0:{}}', 'off'),
(7076, '_site_transient_update_plugins', 'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1761337115;s:8:\"response\";a:1:{s:19:\"akismet/akismet.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:21:\"w.org/plugins/akismet\";s:4:\"slug\";s:7:\"akismet\";s:6:\"plugin\";s:19:\"akismet/akismet.php\";s:11:\"new_version\";s:3:\"5.5\";s:3:\"url\";s:38:\"https://wordpress.org/plugins/akismet/\";s:7:\"package\";s:54:\"https://downloads.wordpress.org/plugin/akismet.5.5.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:60:\"https://ps.w.org/akismet/assets/icon-256x256.png?rev=2818463\";s:2:\"1x\";s:60:\"https://ps.w.org/akismet/assets/icon-128x128.png?rev=2818463\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:63:\"https://ps.w.org/akismet/assets/banner-1544x500.png?rev=2900731\";s:2:\"1x\";s:62:\"https://ps.w.org/akismet/assets/banner-772x250.png?rev=2900731\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.8\";s:6:\"tested\";s:5:\"6.8.3\";s:12:\"requires_php\";s:3:\"7.2\";s:16:\"requires_plugins\";a:0:{}}}s:12:\"translations\";a:0:{}s:9:\"no_update\";a:1:{s:9:\"hello.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:25:\"w.org/plugins/hello-dolly\";s:4:\"slug\";s:11:\"hello-dolly\";s:6:\"plugin\";s:9:\"hello.php\";s:11:\"new_version\";s:5:\"1.7.2\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/hello-dolly/\";s:7:\"package\";s:60:\"https://downloads.wordpress.org/plugin/hello-dolly.1.7.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-256x256.jpg?rev=2052855\";s:2:\"1x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-128x128.jpg?rev=2052855\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/hello-dolly/assets/banner-1544x500.jpg?rev=2645582\";s:2:\"1x\";s:66:\"https://ps.w.org/hello-dolly/assets/banner-772x250.jpg?rev=2052855\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.6\";}}s:7:\"checked\";a:3:{s:19:\"akismet/akismet.php\";s:5:\"5.3.7\";s:9:\"hello.php\";s:5:\"1.7.2\";s:36:\"plg-genesis/registro-estudiantes.php\";s:3:\"1.0\";}}', 'off'),
(5156, '_site_transient_timeout_community-events-270d391a97c88df429e71f9e9e3c8dee', '1753791860', 'off'),
(7065, '_transient_timeout_feed_9bbd59226dc36b9b26cd43f15694c5c3', '1761379861', 'off'),
(7066, '_transient_timeout_feed_mod_9bbd59226dc36b9b26cd43f15694c5c3', '1761379861', 'off'),
(7067, '_transient_feed_mod_9bbd59226dc36b9b26cd43f15694c5c3', '1761336661', 'off'),
(7069, '_transient_timeout_feed_mod_d117b5738fbd35bd8c0391cda1f2b5d9', '1761379861', 'off'),
(7070, '_transient_feed_mod_d117b5738fbd35bd8c0391cda1f2b5d9', '1761336661', 'off'),
(6761, 'plg_genesis_roles_version', '1.0.0', 'auto'),
(7063, '_site_transient_timeout_browser_3268fe57febc0d1640e7d7359e3c065d', '1761941458', 'off'),
(7064, '_site_transient_browser_3268fe57febc0d1640e7d7359e3c065d', 'a:10:{s:4:\"name\";s:6:\"Chrome\";s:7:\"version\";s:9:\"141.0.0.0\";s:8:\"platform\";s:9:\"Macintosh\";s:10:\"update_url\";s:29:\"https://www.google.com/chrome\";s:7:\"img_src\";s:43:\"http://s.w.org/images/browsers/chrome.png?1\";s:11:\"img_src_ssl\";s:44:\"https://s.w.org/images/browsers/chrome.png?1\";s:15:\"current_version\";s:2:\"18\";s:7:\"upgrade\";b:0;s:8:\"insecure\";b:0;s:6:\"mobile\";b:0;}', 'off'),
(7071, '_transient_timeout_dash_v2_88ae138922fe95674369b1cb3d215a2b', '1761379861', 'off'),
(7072, '_transient_dash_v2_88ae138922fe95674369b1cb3d215a2b', '<div class=\"rss-widget\"><ul><li><a class=\'rsswidget\' href=\'https://wordpress.org/news/2025/09/wordpress-6-8-3-release/\'>WordPress 6.8.3 Release</a></li><li><a class=\'rsswidget\' href=\'https://wordpress.org/news/2025/08/portland-welcomes-wcus-2025/\'>Portland Welcomes WordCamp US 2025: A Community Gathering</a></li></ul></div><div class=\"rss-widget\"><ul><li><a class=\'rsswidget\' href=\'https://central.wordcamp.org/news/2025/10/bhopal-hosts-wp-build-tour-2025-empowering-1700-students-across-central-india/\'>WordCamp Central: Bhopal Hosts WP Build Tour 2025: Empowering 1700+ Students Across Central India</a></li><li><a class=\'rsswidget\' href=\'https://ma.tt/2025/10/under-the-weather/\'>Matt: Under the Weather</a></li><li><a class=\'rsswidget\' href=\'https://ma.tt/2025/10/new-woo/\'>Matt: New Woo</a></li></ul></div>', 'off'),
(7077, '_site_transient_timeout_wp_theme_files_patterns-41b33adeff96d6ae17cdc464af3998ca', '1761338915', 'off'),
(7078, '_site_transient_wp_theme_files_patterns-41b33adeff96d6ae17cdc464af3998ca', 'a:2:{s:7:\"version\";s:3:\"1.0\";s:8:\"patterns\";a:0:{}}', 'off'),
(6614, 'plg_genesis_use_api', '0', 'auto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_postmeta`
--

CREATE TABLE `edgen_postmeta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_postmeta`
--

INSERT INTO `edgen_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(3, 1, '_edit_lock', '1727209849:1'),
(58, 66, 'origin', 'theme'),
(15, 23, 'origin', 'theme'),
(32, 45, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1024;s:6:\"height\";i:1024;s:4:\"file\";s:19:\"2024/09/logo-1.webp\";s:8:\"filesize\";i:104938;s:5:\"sizes\";a:3:{s:6:\"medium\";a:5:{s:4:\"file\";s:19:\"logo-1-300x300.webp\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:7410;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:19:\"logo-1-150x150.webp\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:2880;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:19:\"logo-1-768x768.webp\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:23104;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),
(31, 45, '_wp_attached_file', '2024/09/logo-1.webp'),
(33, 46, '_wp_attached_file', '2024/09/logo-1-1.webp'),
(34, 46, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1024;s:6:\"height\";i:1024;s:4:\"file\";s:21:\"2024/09/logo-1-1.webp\";s:8:\"filesize\";i:104938;s:5:\"sizes\";a:3:{s:6:\"medium\";a:5:{s:4:\"file\";s:21:\"logo-1-1-300x300.webp\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:7410;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:21:\"logo-1-1-150x150.webp\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:2880;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:21:\"logo-1-1-768x768.webp\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:23104;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),
(96, 133, '_edit_lock', '1731934600:2'),
(38, 55, 'origin', 'theme'),
(97, 133, '_edit_last', '2'),
(46, 59, '_edit_lock', '1727833903:1'),
(56, 59, '_wp_desired_post_slug', 'dashboard'),
(49, 1, '_wp_desired_post_slug', 'hello-world'),
(50, 1, '_wp_trash_meta_comments_status', 'a:1:{i:1;s:1:\"1\";}'),
(61, 85, 'origin', 'theme'),
(72, 99, 'origin', 'theme'),
(94, 133, '_wp_attached_file', '2024/11/logo-2.webp'),
(95, 133, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1024;s:6:\"height\";i:1024;s:4:\"file\";s:19:\"2024/11/logo-2.webp\";s:8:\"filesize\";i:104938;s:5:\"sizes\";a:3:{s:6:\"medium\";a:5:{s:4:\"file\";s:19:\"logo-2-300x300.webp\";s:5:\"width\";i:300;s:6:\"height\";i:300;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:7410;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:19:\"logo-2-150x150.webp\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:2880;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:19:\"logo-2-768x768.webp\";s:5:\"width\";i:768;s:6:\"height\";i:768;s:9:\"mime-type\";s:10:\"image/webp\";s:8:\"filesize\";i:23104;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}}'),
(91, 125, '_edit_lock', '1760034058:2'),
(86, 119, 'footnotes', ''),
(85, 112, '_edit_lock', '1741285087:13'),
(106, 150, '_edit_lock', '1760031495:2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_posts`
--

CREATE TABLE `edgen_posts` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `post_author` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext NOT NULL,
  `post_title` text NOT NULL,
  `post_excerpt` text NOT NULL,
  `post_status` varchar(20) NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) NOT NULL DEFAULT 'open',
  `post_password` varchar(255) NOT NULL DEFAULT '',
  `post_name` varchar(200) NOT NULL DEFAULT '',
  `to_ping` text NOT NULL,
  `pinged` text NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext NOT NULL,
  `post_parent` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `guid` varchar(255) NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_posts`
--

INSERT INTO `edgen_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(1, 1, '2024-09-24 20:16:48', '2024-09-24 20:16:48', '<!-- wp:paragraph -->\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n<!-- /wp:paragraph -->', 'Hello world!', '', 'trash', 'open', 'open', '', 'hello-world__trashed', '', '', '2024-10-02 01:55:18', '2024-10-02 01:55:18', '', 0, 'https://emmausdigital.com/genesis/?p=1', 0, 'post', '', 1),
(62, 2, '2024-10-02 01:55:18', '2024-10-02 01:55:18', '<!-- wp:paragraph -->\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n<!-- /wp:paragraph -->', 'Hello world!', '', 'inherit', 'closed', 'closed', '', '1-revision-v1', '', '', '2024-10-02 01:55:18', '2024-10-02 01:55:18', '', 1, 'https://emmausdigital.com/genesis/?p=62', 0, 'revision', '', 0),
(85, 2, '2024-10-02 03:32:59', '2024-10-02 03:32:59', '<!-- wp:template-part {\"slug\":\"header\",\"theme\":\"twentytwentyfour\",\"tagName\":\"header\",\"area\":\"header\"} /-->', 'Page No Title', '', 'publish', 'closed', 'closed', '', 'page-no-title', '', '', '2024-10-02 03:33:00', '2024-10-02 03:33:00', '', 0, 'https://emmausdigital.com/genesis/2024/10/02/page-no-title/', 0, 'wp_template', '', 0),
(129, 2, '2024-11-15 01:27:22', '2024-11-15 01:27:22', '<!-- wp:html -->\n<!DOCTYPE html>\n<html lang=\"es\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Login</title>\n    <link rel=\"stylesheet\" href=\"<?php echo get_stylesheet_directory_uri(); ?>/frontend/styles.css\">\n</head>\n<body>\n<?php if (isset($error_message)) : ?>\n    <div id=\"error-message\" style=\"position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: #f44336; color: white; padding: 15px 20px; border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\">\n        <?php echo $error_message; ?>\n    </div>\n<?php endif; ?>\n\n<script>\n    setTimeout(function() {\n        var errorMessage = document.getElementById(\'error-message\');\n        if (errorMessage) {\n            errorMessage.style.display = \'none\';\n        }\n    }, 4000);\n</script>\n\n<div class=\"card\">\n    <div class=\"card-header\">\n        <div class=\"responsive-banner\">\n            <img src=\"<?php echo get_stylesheet_directory_uri(); ?>/images/emmaus/header.png\" alt=\"Logo Emmaus\">\n        </div>\n    </div>\n    <div class=\"card-body\">\n        <form action=\"<?php echo wp_login_url(); ?>\" method=\"post\">\n            <div class=\"responsive-banner\">\n                <img src=\"<?php echo get_stylesheet_directory_uri(); ?>/images/genesis/logo.png\" alt=\"Logo Genesis\">\n            </div>\n            <div class=\"form-group\">\n                <label for=\"username\" class=\"form-label\">Usuario</label>\n                <input type=\"text\" id=\"username\" name=\"log\" class=\"form-control\" placeholder=\"Usuario\" required>\n            </div>\n            <div class=\"form-group\">\n                <label for=\"password\" class=\"form-label\">Contraseña</label>\n                <input type=\"password\" id=\"password\" name=\"pwd\" class=\"form-control\" placeholder=\"Contraseña\" required>\n            </div>\n            <button type=\"submit\" class=\"btn-primary\">Iniciar sesión</button>\n            <input type=\"hidden\" name=\"redirect_to\" value=\"<?php echo home_url(); ?>\">\n        </form>\n        <div class=\"text-center mt-3\">\n            <a href=\"<?php echo wp_lostpassword_url(); ?>\" class=\"text-muted\">¿Olvidaste tu contraseña?</a>\n        </div>\n    </div>\n</div>\n</body>\n</html>\n<!-- /wp:html -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'Login', '', 'inherit', 'closed', 'closed', '', '125-revision-v1', '', '', '2024-11-15 01:27:22', '2024-11-15 01:27:22', '', 125, 'https://emmausdigital.com/genesis/?p=129', 0, 'revision', '', 0),
(99, 2, '2024-10-02 03:55:59', '2024-10-02 03:55:59', '<!-- wp:group {\"tagName\":\"main\",\"style\":{\"spacing\":{\"blockGap\":\"0\",\"margin\":{\"top\":\"0\"}}},\"layout\":{\"type\":\"default\"}} -->\n<main class=\"wp-block-group\" style=\"margin-top:0\"><!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"}}},\"layout\":{\"type\":\"constrained\",\"contentSize\":\"\",\"wideSize\":\"\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"style\":{\"spacing\":{\"blockGap\":\"0px\"}},\"layout\":{\"type\":\"constrained\",\"contentSize\":\"565px\"}} -->\n<div class=\"wp-block-group\"><!-- wp:heading {\"textAlign\":\"center\",\"level\":1,\"fontSize\":\"x-large\"} -->\n<h1 class=\"wp-block-heading has-text-align-center has-x-large-font-size\">A commitment to innovation and sustainability</h1>\n<!-- /wp:heading -->\n\n<!-- wp:spacer {\"height\":\"1.25rem\"} -->\n<div style=\"height:1.25rem\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">Études is a pioneering firm that seamlessly merges creativity and functionality to redefine architectural excellence.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:spacer {\"height\":\"1.25rem\"} -->\n<div style=\"height:1.25rem\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:buttons {\"layout\":{\"type\":\"flex\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\">About us</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons --></div>\n<!-- /wp:group -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|30\",\"style\":{\"layout\":[]}} -->\n<div style=\"height:var(--wp--preset--spacing--30)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:image {\"sizeSlug\":\"full\",\"linkDestination\":\"none\",\"align\":\"wide\",\"className\":\"is-style-rounded\"} -->\n<figure class=\"wp-block-image alignwide size-full is-style-rounded\"><img src=\"https://emmausdigital.com/genesis/wp-content/themes/twentytwentyfour/assets/images/building-exterior.webp\" alt=\"Building exterior in Toronto, Canada\"/></figure>\n<!-- /wp:image --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"0\",\"bottom\":\"0\"},\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"}}},\"backgroundColor\":\"base-2\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull has-base-2-background-color has-background\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"style\":{\"spacing\":{\"blockGap\":\"0px\"}},\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-group\"><!-- wp:heading {\"textAlign\":\"center\",\"className\":\"is-style-asterisk\"} -->\n<h2 class=\"wp-block-heading has-text-align-center is-style-asterisk\">A passion for creating spaces</h2>\n<!-- /wp:heading -->\n\n<!-- wp:spacer {\"height\":\"0px\",\"style\":{\"layout\":{\"flexSize\":\"1.25rem\",\"selfStretch\":\"fixed\"}}} -->\n<div style=\"height:0px\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">Our comprehensive suite of professional services caters to a diverse clientele, ranging from homeowners to commercial developers.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|40\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}}} -->\n<div style=\"margin-top:0;margin-bottom:0;height:var(--wp--preset--spacing--40)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|30\",\"left\":\"var:preset|spacing|40\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Renovation and restoration</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Continuous Support</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">App Access</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|20\"} -->\n<div style=\"height:var(--wp--preset--spacing--20)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|30\",\"left\":\"var:preset|spacing|40\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Consulting</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Project Management</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Architectural Solutions</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":\"0\"}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignwide\"><!-- wp:group {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}},\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-group\"><!-- wp:heading {\"textAlign\":\"center\",\"className\":\"is-style-asterisk\"} -->\n<h2 class=\"wp-block-heading has-text-align-center is-style-asterisk\">An array of resources</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"style\":{\"layout\":{\"selfStretch\":\"fit\",\"flexSize\":null}}} -->\n<p class=\"has-text-align-center\">Our comprehensive suite of professional services caters to a diverse clientele, ranging from homeowners to commercial developers.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|40\"} -->\n<div style=\"height:var(--wp--preset--spacing--40)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|60\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"40%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:40%\"><!-- wp:heading {\"level\":3,\"className\":\"is-style-asterisk\"} -->\n<h3 class=\"wp-block-heading is-style-asterisk\">Études Architect App</h3>\n<!-- /wp:heading -->\n\n<!-- wp:list {\"className\":\"is-style-checkmark-list\",\"style\":{\"typography\":{\"lineHeight\":\"1.75\"}}} -->\n<ul style=\"line-height:1.75\" class=\"wp-block-list is-style-checkmark-list\"><!-- wp:list-item -->\n<li>Collaborate with fellow architects.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Showcase your projects.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Experience the world of architecture.</li>\n<!-- /wp:list-item --></ul>\n<!-- /wp:list --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"width\":\"50%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:50%\"><!-- wp:image {\"sizeSlug\":\"large\",\"linkDestination\":\"none\",\"className\":\"is-style-rounded\"} -->\n<figure class=\"wp-block-image size-large is-style-rounded\"><img src=\"https://emmausdigital.com/genesis/wp-content/themes/twentytwentyfour/assets/images/tourist-and-building.webp\" alt=\"Tourist taking photo of a building\"/></figure>\n<!-- /wp:image --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|40\"} -->\n<div style=\"height:var(--wp--preset--spacing--40)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|60\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"width\":\"50%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:50%\"><!-- wp:image {\"sizeSlug\":\"large\",\"linkDestination\":\"none\",\"className\":\"is-style-rounded\"} -->\n<figure class=\"wp-block-image size-large is-style-rounded\"><img src=\"https://emmausdigital.com/genesis/wp-content/themes/twentytwentyfour/assets/images/windows.webp\" alt=\"Windows of a building in Nuremberg, Germany\"/></figure>\n<!-- /wp:image --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"40%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:40%\"><!-- wp:heading {\"level\":3,\"className\":\"is-style-asterisk\"} -->\n<h3 class=\"wp-block-heading is-style-asterisk\">Études Newsletter</h3>\n<!-- /wp:heading -->\n\n<!-- wp:list {\"className\":\"is-style-checkmark-list\",\"style\":{\"typography\":{\"lineHeight\":\"1.75\"}}} -->\n<ul style=\"line-height:1.75\" class=\"wp-block-list is-style-checkmark-list\"><!-- wp:list-item -->\n<li>A world of thought-provoking articles.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Case studies that celebrate architecture.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Exclusive access to design insights.</li>\n<!-- /wp:list-item --></ul>\n<!-- /wp:list --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"metadata\":{\"name\":\"Testimonial\"},\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|60\",\"bottom\":\"var:preset|spacing|60\",\"left\":\"var:preset|spacing|60\",\"right\":\"var:preset|spacing|60\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"backgroundColor\":\"contrast\",\"textColor\":\"base\",\"layout\":{\"type\":\"constrained\",\"contentSize\":\"\"}} -->\n<div class=\"wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)\"><!-- wp:group {\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group\"><!-- wp:paragraph {\"align\":\"center\",\"style\":{\"typography\":{\"lineHeight\":\"1.2\"}},\"textColor\":\"base\",\"fontSize\":\"x-large\",\"fontFamily\":\"heading\"} -->\n<p class=\"has-text-align-center has-base-color has-text-color has-heading-font-family has-x-large-font-size\" style=\"line-height:1.2\">\n			<em>“Études has saved us thousands of hours of work and has unlocked insights we never thought possible.”</em>\n		</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|10\"} -->\n<div style=\"height:var(--wp--preset--spacing--10)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:group {\"metadata\":{\"name\":\"Testimonial source\"},\"style\":{\"spacing\":{\"blockGap\":\"0\"}},\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\",\"justifyContent\":\"center\",\"flexWrap\":\"nowrap\"}} -->\n<div class=\"wp-block-group\"><!-- wp:image {\"width\":\"60px\",\"aspectRatio\":\"1\",\"scale\":\"cover\",\"sizeSlug\":\"thumbnail\",\"linkDestination\":\"none\",\"align\":\"center\",\"style\":{\"border\":{\"radius\":\"100px\"}}} -->\n<figure class=\"wp-block-image aligncenter size-thumbnail is-resized has-custom-border\"><img alt=\"\" style=\"border-radius:100px;aspect-ratio:1;object-fit:cover;width:60px\"/></figure>\n<!-- /wp:image -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"var:preset|spacing|10\",\"bottom\":\"0\"}}}} -->\n<p class=\"has-text-align-center\" style=\"margin-top:var(--wp--preset--spacing--10);margin-bottom:0\">Annie Steiner</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"300\"}},\"textColor\":\"contrast-3\",\"fontSize\":\"small\"} -->\n<p class=\"has-text-align-center has-contrast-3-color has-text-color has-small-font-size\" style=\"font-style:normal;font-weight:300\">CEO, Greenprint</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:heading {\"align\":\"wide\",\"style\":{\"typography\":{\"lineHeight\":\"1\"},\"spacing\":{\"margin\":{\"top\":\"0\",\"bottom\":\"var:preset|spacing|40\"}}},\"fontSize\":\"x-large\"} -->\n<h2 class=\"wp-block-heading alignwide has-x-large-font-size\" style=\"margin-top:0;margin-bottom:var(--wp--preset--spacing--40);line-height:1\">Watch, Read, Listen</h2>\n<!-- /wp:heading -->\n\n<!-- wp:group {\"align\":\"wide\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignwide\"><!-- wp:query {\"queryId\":4,\"query\":{\"perPage\":10,\"pages\":0,\"offset\":0,\"postType\":\"post\",\"order\":\"desc\",\"orderBy\":\"date\",\"author\":\"\",\"search\":\"\",\"exclude\":[],\"sticky\":\"\",\"inherit\":true},\"align\":\"wide\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-query alignwide\"><!-- wp:post-template -->\n<!-- wp:separator {\"className\":\"alignwide is-style-wide\",\"backgroundColor\":\"contrast-3\"} -->\n<hr class=\"wp-block-separator has-text-color has-contrast-3-color has-alpha-channel-opacity has-contrast-3-background-color has-background alignwide is-style-wide\"/>\n<!-- /wp:separator -->\n\n<!-- wp:columns {\"verticalAlignment\":\"center\",\"align\":\"wide\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"var:preset|spacing|20\",\"bottom\":\"var:preset|spacing|20\"}}}} -->\n<div class=\"wp-block-columns alignwide are-vertically-aligned-center\" style=\"margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20)\"><!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"72%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:72%\"><!-- wp:post-title {\"isLink\":true,\"style\":{\"typography\":{\"lineHeight\":\"1.1\",\"fontSize\":\"1.5rem\"}}} /--></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"28%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:28%\"><!-- wp:template-part {\"slug\":\"post-meta\",\"theme\":\"twentytwentyfour\"} /--></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n<!-- /wp:post-template -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|30\"} -->\n<div style=\"height:var(--wp--preset--spacing--30)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:query-pagination {\"paginationArrow\":\"arrow\",\"layout\":{\"type\":\"flex\",\"justifyContent\":\"space-between\"}} -->\n<!-- wp:query-pagination-previous /-->\n\n<!-- wp:query-pagination-numbers /-->\n\n<!-- wp:query-pagination-next /-->\n<!-- /wp:query-pagination -->\n\n<!-- wp:query-no-results -->\n<!-- wp:paragraph -->\n<p>No posts were found.</p>\n<!-- /wp:paragraph -->\n<!-- /wp:query-no-results --></div>\n<!-- /wp:query --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"align\":\"wide\",\"style\":{\"border\":{\"radius\":\"16px\"},\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|40\",\"bottom\":\"var:preset|spacing|40\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"}}},\"backgroundColor\":\"base-2\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignwide has-base-2-background-color has-background\" style=\"border-radius:16px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:spacer {\"height\":\"var:preset|spacing|10\"} -->\n<div style=\"height:var(--wp--preset--spacing--10)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:heading {\"textAlign\":\"center\",\"fontSize\":\"x-large\"} -->\n<h2 class=\"wp-block-heading has-text-align-center has-x-large-font-size\">Join 900+ subscribers</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">Stay in the loop with everything you need to know.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:buttons {\"layout\":{\"type\":\"flex\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\">Sign up</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|10\"} -->\n<div style=\"height:var(--wp--preset--spacing--10)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group --></main>\n<!-- /wp:group -->\n\n<!-- wp:template-part {\"slug\":\"footer\",\"theme\":\"twentytwentyfour\",\"tagName\":\"footer\",\"area\":\"footer\"} /-->', 'Blog Home', 'Displays the latest posts as either the site homepage or as the \"Posts page\" as defined under reading settings. If it exists, the Front Page template overrides this template when posts are shown on the homepage.', 'publish', 'closed', 'closed', '', 'home', '', '', '2024-10-02 03:56:08', '2024-10-02 03:56:08', '', 0, 'https://emmausdigital.com/genesis/2024/10/02/home/', 0, 'wp_template', '', 0),
(6, 1, '2024-09-24 20:32:24', '2024-09-24 20:32:24', '{\"styles\":{\"blocks\":{\"core\\/button\":{\"variations\":{\"outline\":{\"spacing\":{\"padding\":{\"bottom\":\"calc(0.9rem - 2px)\",\"left\":\"calc(2rem - 2px)\",\"right\":\"calc(2rem - 2px)\",\"top\":\"calc(0.9rem - 2px)\"}},\"border\":{\"width\":\"2px\"}}}},\"core\\/pullquote\":{\"typography\":{\"fontSize\":\"var(--wp--preset--font-size--large)\",\"fontStyle\":\"normal\",\"fontWeight\":\"normal\",\"lineHeight\":\"1.2\"}},\"core\\/quote\":{\"typography\":{\"fontFamily\":\"var(--wp--preset--font-family--heading)\",\"fontSize\":\"var(--wp--preset--font-size--large)\",\"fontStyle\":\"normal\"},\"variations\":{\"plain\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"400\"}}}},\"core\\/site-title\":{\"typography\":{\"fontWeight\":\"400\"}},\"core\\/calendar\":{\"css\":\" .wp-block-calendar table:where(:not(.has-text-color)) th{background-color:var(--wp--preset--color--contrast);color:var(--wp--preset--color--base);border-color:var(--wp--preset--color--contrast)} & table:where(:not(.has-text-color)) td{border-color:var(--wp--preset--color--contrast)}\"},\"core\\/post-terms\":{\"css\":\" & .wp-block-post-terms__prefix{color: var(--wp--preset--color--contrast);}\"}},\"elements\":{\"button\":{\"border\":{\"radius\":\"100px\",\"color\":\"var(--wp--preset--color--contrast-2)\"},\"color\":{\"background\":\"var(--wp--preset--color--contrast-2)\",\"text\":\"var(--wp--preset--color--white)\"},\"spacing\":{\"padding\":{\"bottom\":\"0.9rem\",\"left\":\"2rem\",\"right\":\"2rem\",\"top\":\"0.9rem\"}},\"typography\":{\"fontFamily\":\"var(--wp--preset--font-family--heading)\",\"fontSize\":\"var(--wp--preset--font-size--small)\",\"fontStyle\":\"normal\"},\":hover\":{\"color\":{\"background\":\"var(--wp--preset--color--contrast)\"}}},\"heading\":{\"typography\":{\"fontWeight\":\"normal\",\"letterSpacing\":\"0\"}}}},\"settings\":{\"color\":{\"gradients\":{\"theme\":[{\"slug\":\"gradient-1\",\"gradient\":\"linear-gradient(to bottom, #E1DFDB 0%, #D6D2CE 100%)\",\"name\":\"Vertical linen to beige\"},{\"slug\":\"gradient-2\",\"gradient\":\"linear-gradient(to bottom, #958D86 0%, #D6D2CE 100%)\",\"name\":\"Vertical taupe to beige\"},{\"slug\":\"gradient-3\",\"gradient\":\"linear-gradient(to bottom, #65574E 0%, #D6D2CE 100%)\",\"name\":\"Vertical sable to beige\"},{\"slug\":\"gradient-4\",\"gradient\":\"linear-gradient(to bottom, #1A1514 0%, #D6D2CE 100%)\",\"name\":\"Vertical ebony to beige\"},{\"slug\":\"gradient-5\",\"gradient\":\"linear-gradient(to bottom, #65574E 0%, #958D86 100%)\",\"name\":\"Vertical sable to beige\"},{\"slug\":\"gradient-6\",\"gradient\":\"linear-gradient(to bottom, #1A1514 0%, #65574E 100%)\",\"name\":\"Vertical ebony to sable\"},{\"slug\":\"gradient-7\",\"gradient\":\"linear-gradient(to bottom, #D6D2CE 50%, #E1DFDB 50%)\",\"name\":\"Vertical hard beige to linen\"},{\"slug\":\"gradient-8\",\"gradient\":\"linear-gradient(to bottom, #958D86 50%, #D6D2CE 50%)\",\"name\":\"Vertical hard taupe to beige\"},{\"slug\":\"gradient-9\",\"gradient\":\"linear-gradient(to bottom, #65574E 50%, #D6D2CE 50%)\",\"name\":\"Vertical hard sable to beige\"},{\"slug\":\"gradient-10\",\"gradient\":\"linear-gradient(to bottom, #1A1514 50%, #D6D2CE 50%)\",\"name\":\"Vertical hard ebony to beige\"},{\"slug\":\"gradient-11\",\"gradient\":\"linear-gradient(to bottom, #65574E 50%, #958D86 50%)\",\"name\":\"Vertical hard sable to taupe\"},{\"slug\":\"gradient-12\",\"gradient\":\"linear-gradient(to bottom, #1A1514 50%, #65574E 50%)\",\"name\":\"Vertical hard ebony to sable\"}]},\"palette\":{\"theme\":[{\"color\":\"#D6D2CE\",\"name\":\"Base\",\"slug\":\"base\"},{\"color\":\"#E1DFDB\",\"name\":\"Base \\/ Two\",\"slug\":\"base-2\"},{\"color\":\"#1A1514\",\"name\":\"Contrast\",\"slug\":\"contrast\"},{\"color\":\"#65574E\",\"name\":\"Contrast \\/ Two\",\"slug\":\"contrast-2\"},{\"color\":\"#958D86\",\"name\":\"Contrast \\/ Three\",\"slug\":\"contrast-3\"}]}},\"typography\":{\"fontFamilies\":{\"theme\":[{\"fontFace\":[{\"fontFamily\":\"Inter\",\"fontStretch\":\"normal\",\"fontStyle\":\"normal\",\"fontWeight\":\"300 900\",\"src\":[\"file:.\\/assets\\/fonts\\/inter\\/Inter-VariableFont_slnt,wght.woff2\"]}],\"fontFamily\":\"\\\"Inter\\\", sans-serif\",\"name\":\"Inter\",\"slug\":\"heading\"},{\"fontFace\":[{\"fontFamily\":\"Cardo\",\"fontStyle\":\"normal\",\"fontWeight\":\"400\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_normal_400.woff2\"]},{\"fontFamily\":\"Cardo\",\"fontStyle\":\"italic\",\"fontWeight\":\"400\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_italic_400.woff2\"]},{\"fontFamily\":\"Cardo\",\"fontStyle\":\"normal\",\"fontWeight\":\"700\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_normal_700.woff2\"]}],\"fontFamily\":\"Cardo\",\"name\":\"Cardo\",\"slug\":\"body\"},{\"fontFamily\":\"-apple-system, BlinkMacSystemFont, avenir next, avenir, segoe ui, helvetica neue, helvetica, Cantarell, Ubuntu, roboto, noto, arial, sans-serif\",\"name\":\"System Sans-serif\",\"slug\":\"system-sans-serif\"},{\"fontFamily\":\"Iowan Old Style, Apple Garamond, Baskerville, Times New Roman, Droid Serif, Times, Source Serif Pro, serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol\",\"name\":\"System Serif\",\"slug\":\"system-serif\"}]},\"fontSizes\":{\"theme\":[{\"fluid\":false,\"name\":\"Small\",\"size\":\"1rem\",\"slug\":\"small\"},{\"fluid\":false,\"name\":\"Medium\",\"size\":\"1.2rem\",\"slug\":\"medium\"},{\"fluid\":{\"min\":\"1.5rem\",\"max\":\"2rem\"},\"name\":\"Large\",\"size\":\"2rem\",\"slug\":\"large\"},{\"fluid\":{\"min\":\"2rem\",\"max\":\"2.65rem\"},\"name\":\"Extra Large\",\"size\":\"2.65rem\",\"slug\":\"x-large\"},{\"fluid\":{\"min\":\"2.65rem\",\"max\":\"3.5rem\"},\"name\":\"Extra Extra Large\",\"size\":\"3.5rem\",\"slug\":\"xx-large\"}]},\"defaultFontSizes\":false}},\"isGlobalStylesUserThemeJSON\":true,\"version\":3}', 'Custom Styles', '', 'publish', 'closed', 'closed', '', 'wp-global-styles-twentytwentyfour', '', '', '2024-10-02 03:56:24', '2024-10-02 03:56:24', '', 0, 'https://emmausdigital.com/genesis/2024/09/24/wp-global-styles-twentytwentyfour/', 0, 'wp_global_styles', '', 0),
(128, 2, '2024-11-15 01:26:50', '2024-11-15 01:26:50', '<!-- wp:paragraph -->\n<p>&lt;!DOCTYPE html><br>&lt;html lang=\"es\"><br>&lt;head><br>    &lt;meta charset=\"UTF-8\"><br>    &lt;meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"><br>    &lt;title>Login&lt;/title><br>    &lt;link rel=\"stylesheet\" href=\"&lt;?php echo get_stylesheet_directory_uri(); ?>/frontend/styles.css\"><br>&lt;/head><br>&lt;body><br>&lt;?php if (isset($error_message)) : ?><br>    &lt;div id=\"error-message\" style=\"position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: #f44336; color: white; padding: 15px 20px; border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\"><br>        &lt;?php echo $error_message; ?><br>    &lt;/div><br>&lt;?php endif; ?><br><br>&lt;script><br>    setTimeout(function() {<br>        var errorMessage = document.getElementById(\'error-message\');<br>        if (errorMessage) {<br>            errorMessage.style.display = \'none\';<br>        }<br>    }, 4000);<br>&lt;/script><br><br>&lt;div class=\"card\"><br>    &lt;div class=\"card-header\"><br>        &lt;div class=\"responsive-banner\"><br>            &lt;img src=\"&lt;?php echo get_stylesheet_directory_uri(); ?>/images/emmaus/header.png\" alt=\"Logo Emmaus\"><br>        &lt;/div><br>    &lt;/div><br>    &lt;div class=\"card-body\"><br>        &lt;form action=\"&lt;?php echo wp_login_url(); ?>\" method=\"post\"><br>            &lt;div class=\"responsive-banner\"><br>                &lt;img src=\"&lt;?php echo get_stylesheet_directory_uri(); ?>/images/genesis/logo.png\" alt=\"Logo Genesis\"><br>            &lt;/div><br>            &lt;div class=\"form-group\"><br>                &lt;label for=\"username\" class=\"form-label\">Usuario&lt;/label><br>                &lt;input type=\"text\" id=\"username\" name=\"log\" class=\"form-control\" placeholder=\"Usuario\" required><br>            &lt;/div><br>            &lt;div class=\"form-group\"><br>                &lt;label for=\"password\" class=\"form-label\">Contraseña&lt;/label><br>                &lt;input type=\"password\" id=\"password\" name=\"pwd\" class=\"form-control\" placeholder=\"Contraseña\" required><br>            &lt;/div><br>            &lt;button type=\"submit\" class=\"btn-primary\">Iniciar sesión&lt;/button><br>            &lt;input type=\"hidden\" name=\"redirect_to\" value=\"&lt;?php echo home_url(); ?>\"><br>        &lt;/form><br>        &lt;div class=\"text-center mt-3\"><br>            &lt;a href=\"&lt;?php echo wp_lostpassword_url(); ?>\" class=\"text-muted\">¿Olvidaste tu contraseña?&lt;/a><br>        &lt;/div><br>    &lt;/div><br>&lt;/div><br>&lt;/body><br>&lt;/html></p>\n<!-- /wp:paragraph -->', 'Login', '', 'inherit', 'closed', 'closed', '', '125-revision-v1', '', '', '2024-11-15 01:26:50', '2024-11-15 01:26:50', '', 125, 'https://emmausdigital.com/genesis/?p=128', 0, 'revision', '', 0),
(82, 2, '2024-10-02 03:28:18', '2024-10-02 03:28:18', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->', 'Pages', 'Displays a static page unless a custom template has been applied to that page or a dedicated template exists.', 'inherit', 'closed', 'closed', '', '55-revision-v1', '', '', '2024-10-02 03:28:18', '2024-10-02 03:28:18', '', 55, 'https://emmausdigital.com/genesis/?p=82', 0, 'revision', '', 0),
(86, 2, '2024-10-02 03:32:59', '2024-10-02 03:32:59', '', 'Footer', '', 'inherit', 'closed', 'closed', '', '66-revision-v1', '', '', '2024-10-02 03:32:59', '2024-10-02 03:32:59', '', 66, 'https://emmausdigital.com/genesis/?p=86', 0, 'revision', '', 0),
(87, 2, '2024-10-02 03:32:59', '2024-10-02 03:32:59', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->', 'Header', '', 'inherit', 'closed', 'closed', '', '23-revision-v1', '', '', '2024-10-02 03:32:59', '2024-10-02 03:32:59', '', 23, 'https://emmausdigital.com/genesis/?p=87', 0, 'revision', '', 0),
(80, 2, '2024-10-02 03:25:20', '2024-10-02 03:25:20', '<!-- wp:group {\"metadata\":{\"categories\":[\"footer\",\"wireframe\"],\"patternName\":\"core/centered-footer-with-social-links\",\"name\":\"Centered footer with social links\"},\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"right\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|60\",\"left\":\"var:preset|spacing|50\",\"top\":\"var:preset|spacing|60\"},\"blockGap\":\"var:preset|spacing|40\",\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}},\"dimensions\":{\"minHeight\":\"40vh\"}},\"textColor\":\"contrast\",\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\",\"justifyContent\":\"center\",\"verticalAlignment\":\"center\"}} -->\n<div class=\"wp-block-group alignfull has-contrast-color has-text-color\" style=\"min-height:40vh;margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:site-logo {\"align\":\"center\",\"style\":{\"spacing\":{\"margin\":{\"bottom\":\"6px\"}}}} /-->\n\n<!-- wp:paragraph {\"align\":\"center\",\"fontSize\":\"medium\"} -->\n<p class=\"has-text-align-center has-medium-font-size\">Proudly powered by <a href=\"https://wordpress.org\">WordPr</a></p>\n<!-- /wp:paragraph -->\n\n<!-- wp:social-links {\"size\":\"has-normal-icon-size\",\"className\":\"is-style-logos-only\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"12px\",\"left\":\"12px\"}}},\"layout\":{\"type\":\"flex\",\"flexWrap\":\"nowrap\"}} -->\n<ul class=\"wp-block-social-links has-normal-icon-size is-style-logos-only\"><!-- wp:social-link {\"url\":\"#\",\"service\":\"facebook\"} /-->\n\n<!-- wp:social-link {\"url\":\"#\",\"service\":\"twitter\"} /-->\n\n<!-- wp:social-link {\"url\":\"#\",\"service\":\"wordpress\"} /--></ul>\n<!-- /wp:social-links --></div>\n<!-- /wp:group -->', 'Footer', '', 'inherit', 'closed', 'closed', '', '66-revision-v1', '', '', '2024-10-02 03:25:20', '2024-10-02 03:25:20', '', 66, 'https://emmausdigital.com/genesis/?p=80', 0, 'revision', '', 0),
(72, 2, '2024-10-02 02:18:20', '2024-10-02 02:18:20', '<!-- wp:shortcode -->\n[shortcode_simple_test]\n<!-- /wp:shortcode -->', 'Footer', '', 'inherit', 'closed', 'closed', '', '66-revision-v1', '', '', '2024-10-02 02:18:20', '2024-10-02 02:18:20', '', 66, 'https://emmausdigital.com/genesis/?p=72', 0, 'revision', '', 0),
(23, 2, '2024-09-25 21:52:31', '2024-09-25 21:52:31', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->', 'Header', '', 'publish', 'closed', 'closed', '', 'header', '', '', '2024-10-02 03:32:59', '2024-10-02 03:32:59', '', 0, 'https://emmausdigital.com/genesis/2024/09/25/header/', 0, 'wp_template_part', '', 0),
(38, 2, '2024-09-26 22:06:17', '2024-09-26 22:06:17', '', 'Header', '', 'inherit', 'closed', 'closed', '', '23-revision-v1', '', '', '2024-09-26 22:06:17', '2024-09-26 22:06:17', '', 23, 'https://emmausdigital.com/genesis/?p=38', 0, 'revision', '', 0),
(88, 2, '2024-10-02 03:33:00', '2024-10-02 03:33:00', '<!-- wp:template-part {\"slug\":\"header\",\"theme\":\"twentytwentyfour\",\"tagName\":\"header\",\"area\":\"header\"} /-->', 'Page No Title', '', 'inherit', 'closed', 'closed', '', '85-revision-v1', '', '', '2024-10-02 03:33:00', '2024-10-02 03:33:00', '', 85, 'https://emmausdigital.com/genesis/?p=88', 0, 'revision', '', 0),
(90, 2, '2024-10-02 03:36:27', '2024-10-02 03:36:27', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->', 'Footer', '', 'inherit', 'closed', 'closed', '', '66-revision-v1', '', '', '2024-10-02 03:36:27', '2024-10-02 03:36:27', '', 66, 'https://emmausdigital.com/genesis/?p=90', 0, 'revision', '', 0),
(73, 2, '2024-10-02 02:19:58', '2024-10-02 02:19:58', '<!-- wp:shortcode -->\n[test_shortcode]\n<!-- /wp:shortcode -->', 'Footer', '', 'inherit', 'closed', 'closed', '', '66-revision-v1', '', '', '2024-10-02 02:19:58', '2024-10-02 02:19:58', '', 66, 'https://emmausdigital.com/genesis/?p=73', 0, 'revision', '', 0),
(74, 2, '2024-10-02 02:21:02', '2024-10-02 02:21:02', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->', 'Footer', '', 'inherit', 'closed', 'closed', '', '66-revision-v1', '', '', '2024-10-02 02:21:02', '2024-10-02 02:21:02', '', 66, 'https://emmausdigital.com/genesis/?p=74', 0, 'revision', '', 0),
(45, 2, '2024-09-28 02:04:20', '2024-09-28 02:04:20', '', 'logo (1)', '', 'inherit', 'open', 'closed', '', 'logo-1', '', '', '2024-09-28 02:04:20', '2024-09-28 02:04:20', '', 0, 'https://emmausdigital.com/genesis/wp-content/uploads/2024/09/logo-1.webp', 0, 'attachment', 'image/webp', 0),
(46, 2, '2024-09-28 02:04:44', '2024-09-28 02:04:44', '', 'logo (1)', '', 'inherit', 'open', 'closed', '', 'logo-1-2', '', '', '2024-09-28 02:04:44', '2024-09-28 02:04:44', '', 0, 'https://emmausdigital.com/genesis/wp-content/uploads/2024/09/logo-1-1.webp', 0, 'attachment', 'image/webp', 0),
(55, 2, '2024-09-28 02:28:26', '2024-09-28 02:28:26', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->', 'Pages', 'Displays a static page unless a custom template has been applied to that page or a dedicated template exists.', 'publish', 'closed', 'closed', '', 'page', '', '', '2024-10-02 03:28:18', '2024-10-02 03:28:18', '', 0, 'https://emmausdigital.com/genesis/2024/09/28/page/', 0, 'wp_template', '', 0),
(79, 2, '2024-10-02 03:25:20', '2024-10-02 03:25:20', '', 'Pages', 'Displays a static page unless a custom template has been applied to that page or a dedicated template exists.', 'inherit', 'closed', 'closed', '', '55-revision-v1', '', '', '2024-10-02 03:25:20', '2024-10-02 03:25:20', '', 55, 'https://emmausdigital.com/genesis/?p=79', 0, 'revision', '', 0),
(78, 2, '2024-10-02 03:24:42', '2024-10-02 03:24:42', '<!-- wp:template-part {\"slug\":\"footer\",\"theme\":\"twentytwentyfour\",\"tagName\":\"footer\",\"area\":\"footer\"} /-->', 'Pages', 'Displays a static page unless a custom template has been applied to that page or a dedicated template exists.', 'inherit', 'closed', 'closed', '', '55-revision-v1', '', '', '2024-10-02 03:24:42', '2024-10-02 03:24:42', '', 55, 'https://emmausdigital.com/genesis/?p=78', 0, 'revision', '', 0),
(67, 2, '2024-10-02 02:05:23', '2024-10-02 02:05:23', '<!-- wp:group {\"tagName\":\"main\"} -->\n<main class=\"wp-block-group\"></main>\n<!-- /wp:group -->\n\n<!-- wp:template-part {\"slug\":\"footer\",\"theme\":\"twentytwentyfour\",\"tagName\":\"footer\",\"area\":\"footer\"} /-->', 'Pages', 'Displays a static page unless a custom template has been applied to that page or a dedicated template exists.', 'inherit', 'closed', 'closed', '', '55-revision-v1', '', '', '2024-10-02 02:05:23', '2024-10-02 02:05:23', '', 55, 'https://emmausdigital.com/genesis/?p=67', 0, 'revision', '', 0),
(59, 1, '2024-10-02 01:51:22', '2024-10-02 01:51:22', '<!-- wp:shortcode -->\n[shortcode_mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'trash', 'closed', 'closed', '', 'dashboard__trashed-2', '', '', '2024-10-02 01:55:19', '2024-10-02 01:55:19', '', 0, 'https://emmausdigital.com/genesis/?page_id=59', 0, 'page', '', 0),
(66, 2, '2024-10-02 02:05:23', '2024-10-02 02:05:23', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->', 'Footer', '', 'publish', 'closed', 'closed', '', 'footer', '', '', '2024-10-02 03:36:43', '2024-10-02 03:36:43', '', 0, 'https://emmausdigital.com/genesis/2024/10/02/footer/', 0, 'wp_template_part', '', 0),
(40, 2, '2024-09-26 22:08:20', '2024-09-26 22:08:20', '{\"styles\":{\"blocks\":{\"core\\/button\":{\"variations\":{\"outline\":{\"spacing\":{\"padding\":{\"bottom\":\"calc(0.8rem - 2px)\",\"left\":\"calc(1.6rem - 2px)\",\"right\":\"calc(1.6rem - 2px)\",\"top\":\"calc(0.8rem - 2px)\"}},\"border\":{\"width\":\"2px\"}}}},\"core\\/site-title\":{\"typography\":{\"fontFamily\":\"var(--wp--preset--font-family--heading)\",\"fontWeight\":\"normal\"}},\"core\\/navigation\":{\"typography\":{\"fontSize\":\"var(--wp--preset--font-size--small)\",\"fontWeight\":\"normal\"}}},\"elements\":{\"button\":{\"border\":{\"radius\":\"6px\"},\"color\":{\"background\":\"var(--wp--preset--color--contrast)\",\"text\":\"var(--wp--preset--color--base-2)\"},\"spacing\":{\"padding\":{\"bottom\":\"0.98rem\",\"left\":\"1.6rem\",\"right\":\"1.6rem\",\"top\":\"0.8rem\"}},\"typography\":{\"fontFamily\":\"var(--wp--preset--font-family--heading)\",\"fontSize\":\"var(--wp--preset--font-size--small)\",\"fontStyle\":\"normal\"},\":hover\":{\"color\":{\"background\":\"var(--wp--preset--color--contrast)\"}}},\"heading\":{\"typography\":{\"fontFamily\":\"var(--wp--preset--font-family--heading)\",\"letterSpacing\":\"0\"}}}},\"settings\":{\"color\":{\"palette\":{\"theme\":[{\"color\":\"#38629F\",\"name\":\"Base\",\"slug\":\"base\"},{\"color\":\"#244E8A\",\"name\":\"Base \\/ Two\",\"slug\":\"base-2\"},{\"color\":\"#FFFFFFA1\",\"name\":\"Contrast \\/ 2\",\"slug\":\"contrast-2\"},{\"color\":\"#FFFFFF\",\"name\":\"Contrast\",\"slug\":\"contrast\"},{\"color\":\"#D5E0F0\",\"name\":\"Contrast \\/ 3\",\"slug\":\"contrast-3\"}]}},\"typography\":{\"fontFamilies\":{\"theme\":[{\"fontFace\":[{\"fontFamily\":\"Cardo\",\"fontStyle\":\"normal\",\"fontWeight\":\"400\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_normal_400.woff2\"]},{\"fontFamily\":\"Cardo\",\"fontStyle\":\"italic\",\"fontWeight\":\"400\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_italic_400.woff2\"]},{\"fontFamily\":\"Cardo\",\"fontStyle\":\"normal\",\"fontWeight\":\"700\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_normal_700.woff2\"]}],\"fontFamily\":\"Cardo\",\"name\":\"Cardo\",\"slug\":\"body\"},{\"fontFace\":[{\"fontFamily\":\"Jost\",\"fontStyle\":\"normal\",\"fontWeight\":\"100 900\",\"src\":[\"file:.\\/assets\\/fonts\\/jost\\/Jost-VariableFont_wght.woff2\"]},{\"fontFamily\":\"Jost\",\"fontStyle\":\"italic\",\"fontWeight\":\"100 900\",\"src\":[\"file:.\\/assets\\/fonts\\/jost\\/Jost-Italic-VariableFont_wght.woff2\"]}],\"fontFamily\":\"\\\"Jost\\\", sans-serif\",\"name\":\"Jost\",\"slug\":\"heading\"},{\"fontFamily\":\"-apple-system, BlinkMacSystemFont, avenir next, avenir, segoe ui, helvetica neue, helvetica, Cantarell, Ubuntu, roboto, noto, arial, sans-serif\",\"name\":\"System Sans-serif\",\"slug\":\"system-sans-serif\"},{\"fontFamily\":\"Iowan Old Style, Apple Garamond, Baskerville, Times New Roman, Droid Serif, Times, Source Serif Pro, serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol\",\"name\":\"System Serif\",\"slug\":\"system-serif\"}]},\"fontSizes\":{\"theme\":[{\"fluid\":false,\"name\":\"Small\",\"size\":\"1rem\",\"slug\":\"small\"},{\"fluid\":false,\"name\":\"Medium\",\"size\":\"1.2rem\",\"slug\":\"medium\"},{\"fluid\":{\"min\":\"1.5rem\",\"max\":\"2rem\"},\"name\":\"Large\",\"size\":\"2rem\",\"slug\":\"large\"},{\"fluid\":{\"min\":\"2rem\",\"max\":\"2.65rem\"},\"name\":\"Extra Large\",\"size\":\"2.65rem\",\"slug\":\"x-large\"},{\"fluid\":{\"min\":\"2.65rem\",\"max\":\"3.5rem\"},\"name\":\"Extra Extra Large\",\"size\":\"3.5rem\",\"slug\":\"xx-large\"}]},\"defaultFontSizes\":false}},\"isGlobalStylesUserThemeJSON\":true,\"version\":3}', 'Custom Styles', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2024-09-26 22:08:20', '2024-09-26 22:08:20', '', 6, 'https://emmausdigital.com/genesis/?p=40', 0, 'revision', '', 0);
INSERT INTO `edgen_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(100, 2, '2024-10-02 03:56:08', '2024-10-02 03:56:08', '<!-- wp:group {\"tagName\":\"main\",\"style\":{\"spacing\":{\"blockGap\":\"0\",\"margin\":{\"top\":\"0\"}}},\"layout\":{\"type\":\"default\"}} -->\n<main class=\"wp-block-group\" style=\"margin-top:0\"><!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"}}},\"layout\":{\"type\":\"constrained\",\"contentSize\":\"\",\"wideSize\":\"\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"style\":{\"spacing\":{\"blockGap\":\"0px\"}},\"layout\":{\"type\":\"constrained\",\"contentSize\":\"565px\"}} -->\n<div class=\"wp-block-group\"><!-- wp:heading {\"textAlign\":\"center\",\"level\":1,\"fontSize\":\"x-large\"} -->\n<h1 class=\"wp-block-heading has-text-align-center has-x-large-font-size\">A commitment to innovation and sustainability</h1>\n<!-- /wp:heading -->\n\n<!-- wp:spacer {\"height\":\"1.25rem\"} -->\n<div style=\"height:1.25rem\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">Études is a pioneering firm that seamlessly merges creativity and functionality to redefine architectural excellence.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:spacer {\"height\":\"1.25rem\"} -->\n<div style=\"height:1.25rem\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:buttons {\"layout\":{\"type\":\"flex\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\">About us</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons --></div>\n<!-- /wp:group -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|30\",\"style\":{\"layout\":[]}} -->\n<div style=\"height:var(--wp--preset--spacing--30)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:image {\"sizeSlug\":\"full\",\"linkDestination\":\"none\",\"align\":\"wide\",\"className\":\"is-style-rounded\"} -->\n<figure class=\"wp-block-image alignwide size-full is-style-rounded\"><img src=\"https://emmausdigital.com/genesis/wp-content/themes/twentytwentyfour/assets/images/building-exterior.webp\" alt=\"Building exterior in Toronto, Canada\"/></figure>\n<!-- /wp:image --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"0\",\"bottom\":\"0\"},\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"}}},\"backgroundColor\":\"base-2\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull has-base-2-background-color has-background\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"style\":{\"spacing\":{\"blockGap\":\"0px\"}},\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-group\"><!-- wp:heading {\"textAlign\":\"center\",\"className\":\"is-style-asterisk\"} -->\n<h2 class=\"wp-block-heading has-text-align-center is-style-asterisk\">A passion for creating spaces</h2>\n<!-- /wp:heading -->\n\n<!-- wp:spacer {\"height\":\"0px\",\"style\":{\"layout\":{\"flexSize\":\"1.25rem\",\"selfStretch\":\"fixed\"}}} -->\n<div style=\"height:0px\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">Our comprehensive suite of professional services caters to a diverse clientele, ranging from homeowners to commercial developers.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|40\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}}} -->\n<div style=\"margin-top:0;margin-bottom:0;height:var(--wp--preset--spacing--40)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|30\",\"left\":\"var:preset|spacing|40\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Renovation and restoration</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Continuous Support</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">App Access</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|20\"} -->\n<div style=\"height:var(--wp--preset--spacing--20)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|30\",\"left\":\"var:preset|spacing|40\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Consulting</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Project Management</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}}} -->\n<div class=\"wp-block-column\"><!-- wp:heading {\"textAlign\":\"left\",\"level\":3,\"className\":\"is-style-asterisk\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"600\"}},\"fontSize\":\"medium\",\"fontFamily\":\"body\"} -->\n<h3 class=\"wp-block-heading has-text-align-left is-style-asterisk has-body-font-family has-medium-font-size\" style=\"font-style:normal;font-weight:600\">Architectural Solutions</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"left\"} -->\n<p class=\"has-text-align-left\">Experience the fusion of imagination and expertise with Études Architectural Solutions.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":\"0\"}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignwide\"><!-- wp:group {\"style\":{\"spacing\":{\"blockGap\":\"var:preset|spacing|10\"}},\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-group\"><!-- wp:heading {\"textAlign\":\"center\",\"className\":\"is-style-asterisk\"} -->\n<h2 class=\"wp-block-heading has-text-align-center is-style-asterisk\">An array of resources</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"style\":{\"layout\":{\"selfStretch\":\"fit\",\"flexSize\":null}}} -->\n<p class=\"has-text-align-center\">Our comprehensive suite of professional services caters to a diverse clientele, ranging from homeowners to commercial developers.</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|40\"} -->\n<div style=\"height:var(--wp--preset--spacing--40)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|60\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"40%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:40%\"><!-- wp:heading {\"level\":3,\"className\":\"is-style-asterisk\"} -->\n<h3 class=\"wp-block-heading is-style-asterisk\">Études Architect App</h3>\n<!-- /wp:heading -->\n\n<!-- wp:list {\"className\":\"is-style-checkmark-list\",\"style\":{\"typography\":{\"lineHeight\":\"1.75\"}}} -->\n<ul style=\"line-height:1.75\" class=\"wp-block-list is-style-checkmark-list\"><!-- wp:list-item -->\n<li>Collaborate with fellow architects.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Showcase your projects.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Experience the world of architecture.</li>\n<!-- /wp:list-item --></ul>\n<!-- /wp:list --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"width\":\"50%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:50%\"><!-- wp:image {\"sizeSlug\":\"large\",\"linkDestination\":\"none\",\"className\":\"is-style-rounded\"} -->\n<figure class=\"wp-block-image size-large is-style-rounded\"><img src=\"https://emmausdigital.com/genesis/wp-content/themes/twentytwentyfour/assets/images/tourist-and-building.webp\" alt=\"Tourist taking photo of a building\"/></figure>\n<!-- /wp:image --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|40\"} -->\n<div style=\"height:var(--wp--preset--spacing--40)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:columns {\"align\":\"wide\",\"style\":{\"spacing\":{\"blockGap\":{\"top\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|60\"}}}} -->\n<div class=\"wp-block-columns alignwide\"><!-- wp:column {\"width\":\"50%\"} -->\n<div class=\"wp-block-column\" style=\"flex-basis:50%\"><!-- wp:image {\"sizeSlug\":\"large\",\"linkDestination\":\"none\",\"className\":\"is-style-rounded\"} -->\n<figure class=\"wp-block-image size-large is-style-rounded\"><img src=\"https://emmausdigital.com/genesis/wp-content/themes/twentytwentyfour/assets/images/windows.webp\" alt=\"Windows of a building in Nuremberg, Germany\"/></figure>\n<!-- /wp:image --></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"40%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:40%\"><!-- wp:heading {\"level\":3,\"className\":\"is-style-asterisk\"} -->\n<h3 class=\"wp-block-heading is-style-asterisk\">Études Newsletter</h3>\n<!-- /wp:heading -->\n\n<!-- wp:list {\"className\":\"is-style-checkmark-list\",\"style\":{\"typography\":{\"lineHeight\":\"1.75\"}}} -->\n<ul style=\"line-height:1.75\" class=\"wp-block-list is-style-checkmark-list\"><!-- wp:list-item -->\n<li>A world of thought-provoking articles.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Case studies that celebrate architecture.</li>\n<!-- /wp:list-item -->\n\n<!-- wp:list-item -->\n<li>Exclusive access to design insights.</li>\n<!-- /wp:list-item --></ul>\n<!-- /wp:list --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"metadata\":{\"name\":\"Testimonial\"},\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|60\",\"bottom\":\"var:preset|spacing|60\",\"left\":\"var:preset|spacing|60\",\"right\":\"var:preset|spacing|60\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"backgroundColor\":\"contrast\",\"textColor\":\"base\",\"layout\":{\"type\":\"constrained\",\"contentSize\":\"\"}} -->\n<div class=\"wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)\"><!-- wp:group {\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group\"><!-- wp:paragraph {\"align\":\"center\",\"style\":{\"typography\":{\"lineHeight\":\"1.2\"}},\"textColor\":\"base\",\"fontSize\":\"x-large\",\"fontFamily\":\"heading\"} -->\n<p class=\"has-text-align-center has-base-color has-text-color has-heading-font-family has-x-large-font-size\" style=\"line-height:1.2\">\n			<em>“Études has saved us thousands of hours of work and has unlocked insights we never thought possible.”</em>\n		</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|10\"} -->\n<div style=\"height:var(--wp--preset--spacing--10)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:group {\"metadata\":{\"name\":\"Testimonial source\"},\"style\":{\"spacing\":{\"blockGap\":\"0\"}},\"layout\":{\"type\":\"flex\",\"orientation\":\"vertical\",\"justifyContent\":\"center\",\"flexWrap\":\"nowrap\"}} -->\n<div class=\"wp-block-group\"><!-- wp:image {\"width\":\"60px\",\"aspectRatio\":\"1\",\"scale\":\"cover\",\"sizeSlug\":\"thumbnail\",\"linkDestination\":\"none\",\"align\":\"center\",\"style\":{\"border\":{\"radius\":\"100px\"}}} -->\n<figure class=\"wp-block-image aligncenter size-thumbnail is-resized has-custom-border\"><img alt=\"\" style=\"border-radius:100px;aspect-ratio:1;object-fit:cover;width:60px\"/></figure>\n<!-- /wp:image -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"var:preset|spacing|10\",\"bottom\":\"0\"}}}} -->\n<p class=\"has-text-align-center\" style=\"margin-top:var(--wp--preset--spacing--10);margin-bottom:0\">Annie Steiner</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"style\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"300\"}},\"textColor\":\"contrast-3\",\"fontSize\":\"small\"} -->\n<p class=\"has-text-align-center has-contrast-3-color has-text-color has-small-font-size\" style=\"font-style:normal;font-weight:300\">CEO, Greenprint</p>\n<!-- /wp:paragraph --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:heading {\"align\":\"wide\",\"style\":{\"typography\":{\"lineHeight\":\"1\"},\"spacing\":{\"margin\":{\"top\":\"0\",\"bottom\":\"var:preset|spacing|40\"}}},\"fontSize\":\"x-large\"} -->\n<h2 class=\"wp-block-heading alignwide has-x-large-font-size\" style=\"margin-top:0;margin-bottom:var(--wp--preset--spacing--40);line-height:1\">Watch, Read, Listen</h2>\n<!-- /wp:heading -->\n\n<!-- wp:group {\"align\":\"wide\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignwide\"><!-- wp:query {\"queryId\":4,\"query\":{\"perPage\":10,\"pages\":0,\"offset\":0,\"postType\":\"post\",\"order\":\"desc\",\"orderBy\":\"date\",\"author\":\"\",\"search\":\"\",\"exclude\":[],\"sticky\":\"\",\"inherit\":true},\"align\":\"wide\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-query alignwide\"><!-- wp:post-template -->\n<!-- wp:separator {\"className\":\"alignwide is-style-wide\",\"backgroundColor\":\"contrast-3\"} -->\n<hr class=\"wp-block-separator has-text-color has-contrast-3-color has-alpha-channel-opacity has-contrast-3-background-color has-background alignwide is-style-wide\"/>\n<!-- /wp:separator -->\n\n<!-- wp:columns {\"verticalAlignment\":\"center\",\"align\":\"wide\",\"style\":{\"spacing\":{\"margin\":{\"top\":\"var:preset|spacing|20\",\"bottom\":\"var:preset|spacing|20\"}}}} -->\n<div class=\"wp-block-columns alignwide are-vertically-aligned-center\" style=\"margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20)\"><!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"72%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:72%\"><!-- wp:post-title {\"isLink\":true,\"style\":{\"typography\":{\"lineHeight\":\"1.1\",\"fontSize\":\"1.5rem\"}}} /--></div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"28%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:28%\"><!-- wp:template-part {\"slug\":\"post-meta\",\"theme\":\"twentytwentyfour\"} /--></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->\n<!-- /wp:post-template -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|30\"} -->\n<div style=\"height:var(--wp--preset--spacing--30)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:query-pagination {\"paginationArrow\":\"arrow\",\"layout\":{\"type\":\"flex\",\"justifyContent\":\"space-between\"}} -->\n<!-- wp:query-pagination-previous /-->\n\n<!-- wp:query-pagination-numbers /-->\n\n<!-- wp:query-pagination-next /-->\n<!-- /wp:query-pagination -->\n\n<!-- wp:query-no-results -->\n<!-- wp:paragraph -->\n<p>No posts were found.</p>\n<!-- /wp:paragraph -->\n<!-- /wp:query-no-results --></div>\n<!-- /wp:query --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group -->\n\n<!-- wp:group {\"align\":\"full\",\"style\":{\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|50\",\"bottom\":\"var:preset|spacing|50\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"},\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignfull\" style=\"margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:group {\"align\":\"wide\",\"style\":{\"border\":{\"radius\":\"16px\"},\"spacing\":{\"padding\":{\"top\":\"var:preset|spacing|40\",\"bottom\":\"var:preset|spacing|40\",\"left\":\"var:preset|spacing|50\",\"right\":\"var:preset|spacing|50\"}}},\"backgroundColor\":\"base-2\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group alignwide has-base-2-background-color has-background\" style=\"border-radius:16px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--50)\"><!-- wp:spacer {\"height\":\"var:preset|spacing|10\"} -->\n<div style=\"height:var(--wp--preset--spacing--10)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer -->\n\n<!-- wp:heading {\"textAlign\":\"center\",\"fontSize\":\"x-large\"} -->\n<h2 class=\"wp-block-heading has-text-align-center has-x-large-font-size\">Join 900+ subscribers</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph {\"align\":\"center\"} -->\n<p class=\"has-text-align-center\">Stay in the loop with everything you need to know.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:buttons {\"layout\":{\"type\":\"flex\",\"justifyContent\":\"center\"}} -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\">Sign up</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->\n\n<!-- wp:spacer {\"height\":\"var:preset|spacing|10\"} -->\n<div style=\"height:var(--wp--preset--spacing--10)\" aria-hidden=\"true\" class=\"wp-block-spacer\"></div>\n<!-- /wp:spacer --></div>\n<!-- /wp:group --></div>\n<!-- /wp:group --></main>\n<!-- /wp:group -->\n\n<!-- wp:template-part {\"slug\":\"footer\",\"theme\":\"twentytwentyfour\",\"tagName\":\"footer\",\"area\":\"footer\"} /-->', 'Blog Home', 'Displays the latest posts as either the site homepage or as the \"Posts page\" as defined under reading settings. If it exists, the Front Page template overrides this template when posts are shown on the homepage.', 'inherit', 'closed', 'closed', '', '99-revision-v1', '', '', '2024-10-02 03:56:08', '2024-10-02 03:56:08', '', 99, 'https://emmausdigital.com/genesis/?p=100', 0, 'revision', '', 0),
(101, 2, '2024-10-02 03:56:24', '2024-10-02 03:56:24', '{\"styles\":{\"blocks\":{\"core\\/button\":{\"variations\":{\"outline\":{\"spacing\":{\"padding\":{\"bottom\":\"calc(0.9rem - 2px)\",\"left\":\"calc(2rem - 2px)\",\"right\":\"calc(2rem - 2px)\",\"top\":\"calc(0.9rem - 2px)\"}},\"border\":{\"width\":\"2px\"}}}},\"core\\/pullquote\":{\"typography\":{\"fontSize\":\"var(--wp--preset--font-size--large)\",\"fontStyle\":\"normal\",\"fontWeight\":\"normal\",\"lineHeight\":\"1.2\"}},\"core\\/quote\":{\"typography\":{\"fontFamily\":\"var(--wp--preset--font-family--heading)\",\"fontSize\":\"var(--wp--preset--font-size--large)\",\"fontStyle\":\"normal\"},\"variations\":{\"plain\":{\"typography\":{\"fontStyle\":\"normal\",\"fontWeight\":\"400\"}}}},\"core\\/site-title\":{\"typography\":{\"fontWeight\":\"400\"}},\"core\\/calendar\":{\"css\":\" .wp-block-calendar table:where(:not(.has-text-color)) th{background-color:var(--wp--preset--color--contrast);color:var(--wp--preset--color--base);border-color:var(--wp--preset--color--contrast)} & table:where(:not(.has-text-color)) td{border-color:var(--wp--preset--color--contrast)}\"},\"core\\/post-terms\":{\"css\":\" & .wp-block-post-terms__prefix{color: var(--wp--preset--color--contrast);}\"}},\"elements\":{\"button\":{\"border\":{\"radius\":\"100px\",\"color\":\"var(--wp--preset--color--contrast-2)\"},\"color\":{\"background\":\"var(--wp--preset--color--contrast-2)\",\"text\":\"var(--wp--preset--color--white)\"},\"spacing\":{\"padding\":{\"bottom\":\"0.9rem\",\"left\":\"2rem\",\"right\":\"2rem\",\"top\":\"0.9rem\"}},\"typography\":{\"fontFamily\":\"var(--wp--preset--font-family--heading)\",\"fontSize\":\"var(--wp--preset--font-size--small)\",\"fontStyle\":\"normal\"},\":hover\":{\"color\":{\"background\":\"var(--wp--preset--color--contrast)\"}}},\"heading\":{\"typography\":{\"fontWeight\":\"normal\",\"letterSpacing\":\"0\"}}}},\"settings\":{\"color\":{\"gradients\":{\"theme\":[{\"slug\":\"gradient-1\",\"gradient\":\"linear-gradient(to bottom, #E1DFDB 0%, #D6D2CE 100%)\",\"name\":\"Vertical linen to beige\"},{\"slug\":\"gradient-2\",\"gradient\":\"linear-gradient(to bottom, #958D86 0%, #D6D2CE 100%)\",\"name\":\"Vertical taupe to beige\"},{\"slug\":\"gradient-3\",\"gradient\":\"linear-gradient(to bottom, #65574E 0%, #D6D2CE 100%)\",\"name\":\"Vertical sable to beige\"},{\"slug\":\"gradient-4\",\"gradient\":\"linear-gradient(to bottom, #1A1514 0%, #D6D2CE 100%)\",\"name\":\"Vertical ebony to beige\"},{\"slug\":\"gradient-5\",\"gradient\":\"linear-gradient(to bottom, #65574E 0%, #958D86 100%)\",\"name\":\"Vertical sable to beige\"},{\"slug\":\"gradient-6\",\"gradient\":\"linear-gradient(to bottom, #1A1514 0%, #65574E 100%)\",\"name\":\"Vertical ebony to sable\"},{\"slug\":\"gradient-7\",\"gradient\":\"linear-gradient(to bottom, #D6D2CE 50%, #E1DFDB 50%)\",\"name\":\"Vertical hard beige to linen\"},{\"slug\":\"gradient-8\",\"gradient\":\"linear-gradient(to bottom, #958D86 50%, #D6D2CE 50%)\",\"name\":\"Vertical hard taupe to beige\"},{\"slug\":\"gradient-9\",\"gradient\":\"linear-gradient(to bottom, #65574E 50%, #D6D2CE 50%)\",\"name\":\"Vertical hard sable to beige\"},{\"slug\":\"gradient-10\",\"gradient\":\"linear-gradient(to bottom, #1A1514 50%, #D6D2CE 50%)\",\"name\":\"Vertical hard ebony to beige\"},{\"slug\":\"gradient-11\",\"gradient\":\"linear-gradient(to bottom, #65574E 50%, #958D86 50%)\",\"name\":\"Vertical hard sable to taupe\"},{\"slug\":\"gradient-12\",\"gradient\":\"linear-gradient(to bottom, #1A1514 50%, #65574E 50%)\",\"name\":\"Vertical hard ebony to sable\"}]},\"palette\":{\"theme\":[{\"color\":\"#D6D2CE\",\"name\":\"Base\",\"slug\":\"base\"},{\"color\":\"#E1DFDB\",\"name\":\"Base \\/ Two\",\"slug\":\"base-2\"},{\"color\":\"#1A1514\",\"name\":\"Contrast\",\"slug\":\"contrast\"},{\"color\":\"#65574E\",\"name\":\"Contrast \\/ Two\",\"slug\":\"contrast-2\"},{\"color\":\"#958D86\",\"name\":\"Contrast \\/ Three\",\"slug\":\"contrast-3\"}]}},\"typography\":{\"fontFamilies\":{\"theme\":[{\"fontFace\":[{\"fontFamily\":\"Inter\",\"fontStretch\":\"normal\",\"fontStyle\":\"normal\",\"fontWeight\":\"300 900\",\"src\":[\"file:.\\/assets\\/fonts\\/inter\\/Inter-VariableFont_slnt,wght.woff2\"]}],\"fontFamily\":\"\\\"Inter\\\", sans-serif\",\"name\":\"Inter\",\"slug\":\"heading\"},{\"fontFace\":[{\"fontFamily\":\"Cardo\",\"fontStyle\":\"normal\",\"fontWeight\":\"400\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_normal_400.woff2\"]},{\"fontFamily\":\"Cardo\",\"fontStyle\":\"italic\",\"fontWeight\":\"400\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_italic_400.woff2\"]},{\"fontFamily\":\"Cardo\",\"fontStyle\":\"normal\",\"fontWeight\":\"700\",\"src\":[\"file:.\\/assets\\/fonts\\/cardo\\/cardo_normal_700.woff2\"]}],\"fontFamily\":\"Cardo\",\"name\":\"Cardo\",\"slug\":\"body\"},{\"fontFamily\":\"-apple-system, BlinkMacSystemFont, avenir next, avenir, segoe ui, helvetica neue, helvetica, Cantarell, Ubuntu, roboto, noto, arial, sans-serif\",\"name\":\"System Sans-serif\",\"slug\":\"system-sans-serif\"},{\"fontFamily\":\"Iowan Old Style, Apple Garamond, Baskerville, Times New Roman, Droid Serif, Times, Source Serif Pro, serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol\",\"name\":\"System Serif\",\"slug\":\"system-serif\"}]},\"fontSizes\":{\"theme\":[{\"fluid\":false,\"name\":\"Small\",\"size\":\"1rem\",\"slug\":\"small\"},{\"fluid\":false,\"name\":\"Medium\",\"size\":\"1.2rem\",\"slug\":\"medium\"},{\"fluid\":{\"min\":\"1.5rem\",\"max\":\"2rem\"},\"name\":\"Large\",\"size\":\"2rem\",\"slug\":\"large\"},{\"fluid\":{\"min\":\"2rem\",\"max\":\"2.65rem\"},\"name\":\"Extra Large\",\"size\":\"2.65rem\",\"slug\":\"x-large\"},{\"fluid\":{\"min\":\"2.65rem\",\"max\":\"3.5rem\"},\"name\":\"Extra Extra Large\",\"size\":\"3.5rem\",\"slug\":\"xx-large\"}]},\"defaultFontSizes\":false}},\"isGlobalStylesUserThemeJSON\":true,\"version\":3}', 'Custom Styles', '', 'inherit', 'closed', 'closed', '', '6-revision-v1', '', '', '2024-10-02 03:56:24', '2024-10-02 03:56:24', '', 6, 'https://emmausdigital.com/genesis/?p=101', 0, 'revision', '', 0),
(119, 2, '2024-11-13 14:34:44', '2024-11-13 14:34:44', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'inherit', 'closed', 'closed', '', '112-autosave-v1', '', '', '2024-11-13 14:34:44', '2024-11-13 14:34:44', '', 112, 'https://emmausdigital.com/genesis/?p=119', 0, 'revision', '', 0),
(125, 2, '2024-11-15 01:24:27', '2024-11-15 01:24:27', '<!-- wp:html /-->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'Custom Login', '', 'publish', 'closed', 'closed', '', 'login', '', '', '2024-11-18 14:07:27', '2024-11-18 14:07:27', '', 0, 'https://emmausdigital.com/genesis/?page_id=125', 0, 'page', '', 0),
(126, 2, '2024-11-15 01:24:27', '2024-11-15 01:24:27', '', 'Login', '', 'inherit', 'closed', 'closed', '', '125-revision-v1', '', '', '2024-11-15 01:24:27', '2024-11-15 01:24:27', '', 125, 'https://emmausdigital.com/genesis/?p=126', 0, 'revision', '', 0),
(130, 2, '2024-11-15 01:28:59', '2024-11-15 01:28:59', '<!-- wp:html -->\n<!DOCTYPE html>\n<html lang=\"es\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Login</title>\n    <link rel=\"stylesheet\" href=\"<?php echo get_stylesheet_directory_uri(); ?>/frontend/styles.css\">\n</head>\n<body>\n<?php if (isset($error_message)) : ?>\n    <div id=\"error-message\" style=\"position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: #f44336; color: white; padding: 15px 20px; border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\">\n        <?php echo $error_message; ?>\n    </div>\n<?php endif; ?>\n\n<script>\n    setTimeout(function() {\n        var errorMessage = document.getElementById(\'error-message\');\n        if (errorMessage) {\n            errorMessage.style.display = \'none\';\n        }\n    }, 4000);\n</script>\n\n<div class=\"card\">\n    <div class=\"card-header\">\n        <div class=\"responsive-banner\">\n            <img src=\"<?php echo get_stylesheet_directory_uri(); ?>/images/emmaus/header.png\" alt=\"Logo Emmaus\">\n        </div>\n    </div>\n    <div class=\"card-body\">\n        <form action=\"<?php echo wp_login_url(); ?>\" method=\"post\">\n            <div class=\"responsive-banner\">\n                <img src=\"<?php echo get_stylesheet_directory_uri(); ?>/images/genesis/logo.png\" alt=\"Logo Genesis\">\n            </div>\n            <div class=\"form-group\">\n                <label for=\"username\" class=\"form-label\">Usuario</label>\n                <input type=\"text\" id=\"username\" name=\"log\" class=\"form-control\" placeholder=\"Usuario\" required>\n            </div>\n            <div class=\"form-group\">\n                <label for=\"password\" class=\"form-label\">Contraseña</label>\n                <input type=\"password\" id=\"password\" name=\"pwd\" class=\"form-control\" placeholder=\"Contraseña\" required>\n            </div>\n            <button type=\"submit\" class=\"btn-primary\">Iniciar sesión</button>\n            <input type=\"hidden\" name=\"redirect_to\" value=\"<?php echo home_url(); ?>\">\n        </form>\n        <div class=\"text-center mt-3\">\n            <a href=\"<?php echo wp_lostpassword_url(); ?>\" class=\"text-muted\">¿Olvidaste tu contraseña?</a>\n        </div>\n    </div>\n</div>\n</body>\n</html>\n<!-- /wp:html -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'login', '', 'inherit', 'closed', 'closed', '', '125-revision-v1', '', '', '2024-11-15 01:28:59', '2024-11-15 01:28:59', '', 125, 'https://emmausdigital.com/genesis/?p=130', 0, 'revision', '', 0),
(112, 2, '2024-10-02 04:57:22', '2024-10-02 04:57:22', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'publish', 'closed', 'closed', '', 'dashboard', '', '', '2024-10-02 05:03:18', '2024-10-02 05:03:18', '', 0, 'https://emmausdigital.com/genesis/?page_id=112', 0, 'page', '', 0),
(113, 2, '2024-10-02 04:57:22', '2024-10-02 04:57:22', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'inherit', 'closed', 'closed', '', '112-revision-v1', '', '', '2024-10-02 04:57:22', '2024-10-02 04:57:22', '', 112, 'https://emmausdigital.com/genesis/?p=113', 0, 'revision', '', 0),
(115, 2, '2024-10-02 05:00:26', '2024-10-02 05:00:26', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'inherit', 'closed', 'closed', '', '112-revision-v1', '', '', '2024-10-02 05:00:26', '2024-10-02 05:00:26', '', 112, 'https://emmausdigital.com/genesis/?p=115', 0, 'revision', '', 0),
(114, 2, '2024-10-02 04:57:34', '2024-10-02 04:57:34', '<!-- wp:shortcode -->\n[shortcode_mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'inherit', 'closed', 'closed', '', '112-revision-v1', '', '', '2024-10-02 04:57:34', '2024-10-02 04:57:34', '', 112, 'https://emmausdigital.com/genesis/?p=114', 0, 'revision', '', 0),
(117, 2, '2024-10-02 05:03:18', '2024-10-02 05:03:18', '<!-- wp:shortcode -->\n[mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'inherit', 'closed', 'closed', '', '112-revision-v1', '', '', '2024-10-02 05:03:18', '2024-10-02 05:03:18', '', 112, 'https://emmausdigital.com/genesis/?p=117', 0, 'revision', '', 0),
(116, 2, '2024-10-02 05:01:13', '2024-10-02 05:01:13', '<!-- wp:shortcode -->\n[shortcode_mostrar_dashboard]\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'dashboard', '', 'inherit', 'closed', 'closed', '', '112-revision-v1', '', '', '2024-10-02 05:01:13', '2024-10-02 05:01:13', '', 112, 'https://emmausdigital.com/genesis/?p=116', 0, 'revision', '', 0),
(134, 2, '2024-11-18 14:07:27', '2024-11-18 14:07:27', '<!-- wp:html /-->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'Custom Login', '', 'inherit', 'closed', 'closed', '', '125-revision-v1', '', '', '2024-11-18 14:07:27', '2024-11-18 14:07:27', '', 125, 'https://emmausdigital.com/genesis/?p=134', 0, 'revision', '', 0),
(132, 2, '2024-11-15 01:50:49', '2024-11-15 01:50:49', '<!-- wp:html -->\n<!DOCTYPE html>\n<html lang=\"es\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Login</title>\n  <style>\n      body {\n          font-family: Arial, sans-serif;\n          background-color: #f8f9fa;\n          margin: 0;\n          padding: 0;\n          display: flex;\n          justify-content: center;\n          align-items: center;\n          height: 100vh;\n      }\n\n      .card {\n          background-color: #fff;\n          border-radius: 8px;\n          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\n          width: 400px;\n      }\n\n      .card-header {\n          background-color: #0D457E;\n          border-top-left-radius: 8px;\n          border-top-right-radius: 8px;\n          color: #fff;\n          font-size: 1.5rem;\n          padding: 20px;\n          text-align: center;\n      }\n\n      .card-body {\n          padding: 40px;\n      }\n\n      .form-group {\n          margin-bottom: 20px;\n      }\n\n      .form-label {\n          display: block;\n          font-size: 1rem;\n          margin-bottom: 8px;\n      }\n\n      .form-control {\n          border: 1px solid #0D457E;\n          border-radius: 5px;\n          font-size: 1rem;\n          height: 40px;\n          padding: 8px;\n          width: calc(100% - 16px);\n      }\n\n      .btn-primary {\n          background-color: #0D457E;\n          border: none;\n          border-radius: 5px;\n          color: #fff;\n          cursor: pointer;\n          font-size: 1rem;\n          height: 40px;\n          text-transform: uppercase;\n          width: 100%;\n      }\n\n      .btn-primary:hover {\n          background-color: #07365A;\n      }\n\n      .text-center {\n          text-align: center;\n      }\n\n      .text-muted {\n          color: #6c757d;\n          font-size: 0.9rem;\n      }\n\n      .responsive-banner img {\n          max-width: 100%;\n          height: auto;\n      }\n\n      .responsive-banner {\n          background-color: #102B4F;\n          width: 100%;\n          display: flex;\n          justify-content: center;\n          align-items: center;\n          overflow: hidden;\n          border-top-left-radius: 8px;\n          border-top-right-radius: 8px;\n          margin-bottom: 20px;\n      }\n\n      .error-message {\n          position: fixed;\n          bottom: 20px;\n          left: 50%;\n          transform: translateX(-50%);\n          background-color: #f44336;\n          color: white;\n          padding: 15px 20px;\n          border-radius: 5px;\n          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\n          opacity: 0;\n          transition: opacity 0.5s ease;\n      }\n\n      .error-message.show {\n          opacity: 1;\n      }\n  </style>\n</head>\n<body>\n<?php\nif(isset($_GET[\'error\'])) {\n    echo \'<div id=\"error-message\" class=\"error-message show\">\' . htmlspecialchars($_GET[\'error\']) . \'</div>\';\n}\n?>\n<script>\n    setTimeout(function() {\n        var errorMessage = document.getElementById(\'error-message\');\n        if (errorMessage) {\n            errorMessage.classList.remove(\'show\');\n        }\n    }, 4000);\n</script>\n  <div class=\"card\">\n    <div class=\"card-header\">\n      <div class=\"responsive-banner\">\n        <img src=\"<?php echo get_template_directory_uri(); ?>/images/emmaus/header.png\" alt=\"Logo Emmaus\">\n      </div>      \n    </div>\n    <div class=\"card-body\">\n        <form action=\"<?php echo esc_url(site_url(\'wp-login.php\')); ?>\" method=\"post\">\n          <div class=\"responsive-banner\">\n            <img src=\"<?php echo get_template_directory_uri(); ?>/images/genesis/logo.png\" alt=\"Logo Genesis\">\n          </div>\n          <div class=\"form-group\">\n            <label for=\"username\" class=\"form-label\">Usuario</label>\n            <input type=\"text\" id=\"username\" name=\"log\" class=\"form-control\" placeholder=\"Usuario\" required>\n          </div>\n          <div class=\"form-group\">\n            <label for=\"password\" class=\"form-label\">Contraseña</label>\n            <input type=\"password\" id=\"password\" name=\"pwd\" class=\"form-control\" placeholder=\"Contraseña\" required>\n          </div>\n          <button type=\"submit\" class=\"btn-primary\">Iniciar sesión</button>\n        </form>\n        <div class=\"text-center mt-3\">\n            <a href=\"<?php echo esc_url(wp_lostpassword_url()); ?>\" class=\"text-muted\">¿Olvidaste tu contraseña?</a>\n        </div>\n    </div>\n  </div>\n</body>\n</html>\n<!-- /wp:html -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'login', '', 'inherit', 'closed', 'closed', '', '125-revision-v1', '', '', '2024-11-15 01:50:49', '2024-11-15 01:50:49', '', 125, 'https://emmausdigital.com/genesis/?p=132', 0, 'revision', '', 0),
(133, 2, '2024-11-18 12:58:30', '2024-11-18 12:58:30', '', 'logoGenesis', '', 'inherit', 'open', 'closed', '', 'logo-2', '', '', '2024-11-18 12:58:52', '2024-11-18 12:58:52', '', 0, 'https://emmausdigital.com/genesis/wp-content/uploads/2024/11/logo-2.webp', 0, 'attachment', 'image/webp', 0),
(150, 2, '2025-10-09 16:46:11', '2025-10-09 16:46:11', '<!-- wp:shortcode -->\nmostrar_dashboard_v2\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'DashBoard v2', '', 'publish', 'closed', 'closed', '', 'dashboard-v2', '', '', '2025-10-09 16:46:11', '2025-10-09 16:46:11', '', 0, 'https://emmausdigital.com/genesis/?page_id=150', 0, 'page', '', 0),
(151, 2, '2025-10-09 16:46:11', '2025-10-09 16:46:11', '<!-- wp:shortcode -->\nmostrar_dashboard_v2\n<!-- /wp:shortcode -->\n\n<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->', 'DashBoard v2', '', 'inherit', 'closed', 'closed', '', '150-revision-v1', '', '', '2025-10-09 16:46:11', '2025-10-09 16:46:11', '', 150, 'https://emmausdigital.com/genesis/?p=151', 0, 'revision', '', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_termmeta`
--

CREATE TABLE `edgen_termmeta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_terms`
--

CREATE TABLE `edgen_terms` (
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(200) NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_terms`
--

INSERT INTO `edgen_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES
(1, 'Uncategorized', 'uncategorized', 0),
(2, 'twentytwentyfour', 'twentytwentyfour', 0),
(3, 'footer', 'footer', 0),
(4, 'header', 'header', 0),
(5, 'uncategorized', 'uncategorized', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_term_relationships`
--

CREATE TABLE `edgen_term_relationships` (
  `object_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_term_relationships`
--

INSERT INTO `edgen_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(1, 1, 0),
(6, 2, 0),
(66, 3, 0),
(85, 2, 0),
(66, 2, 0),
(23, 2, 0),
(23, 4, 0),
(55, 2, 0),
(99, 2, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_term_taxonomy`
--

CREATE TABLE `edgen_term_taxonomy` (
  `term_taxonomy_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `parent` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_term_taxonomy`
--

INSERT INTO `edgen_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1, 1, 'category', '', 0, 0),
(2, 2, 'wp_theme', '', 0, 6),
(3, 3, 'wp_template_part_area', '', 0, 1),
(4, 4, 'wp_template_part_area', '', 0, 1),
(5, 5, 'wp_template_part_area', '', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_usermeta`
--

CREATE TABLE `edgen_usermeta` (
  `umeta_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_usermeta`
--

INSERT INTO `edgen_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES
(72, 4, 'comment_shortcuts', 'false'),
(73, 4, 'admin_color', 'fresh'),
(74, 4, 'use_ssl', '0'),
(75, 4, 'show_admin_bar_front', 'true'),
(76, 4, 'locale', ''),
(66, 4, 'nickname', 'Yamile Diaz'),
(67, 4, 'first_name', 'Yamile'),
(68, 4, 'last_name', 'Diaz'),
(69, 4, 'description', ''),
(70, 4, 'rich_editing', 'true'),
(71, 4, 'syntax_highlighting', 'true'),
(349, 15, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(313, 13, 'edgen_dashboard_quick_press_last_post_id', '139'),
(401, 2, 'session_tokens', 'a:3:{s:64:\"a3de3cb24a3abdfd0743e412148c95a3c80e3d71a652c91d2b7615f051174b46\";a:4:{s:10:\"expiration\";i:1761241147;s:2:\"ip\";s:15:\"190.121.155.164\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36\";s:5:\"login\";i:1760031547;}s:64:\"e9356a51b364fcaab8dbe416251ed85fbad01ff38510fb1a75f65e80a5f6ceb4\";a:4:{s:10:\"expiration\";i:1761367847;s:2:\"ip\";s:12:\"179.32.76.52\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\";s:5:\"login\";i:1761195047;}s:64:\"ec7d8d179ae49f17ae973f977a4202111382a8ee7e0d7cd385076b19d9f24535\";a:4:{s:10:\"expiration\";i:1761398878;s:2:\"ip\";s:12:\"179.32.76.52\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\";s:5:\"login\";i:1761226078;}}'),
(312, 13, 'session_tokens', 'a:1:{s:64:\"1833be1a58f48fa903f4548001e177db1bbb4e6a740327e0c62b824b9fd731af\";a:4:{s:10:\"expiration\";i:1751052303;s:2:\"ip\";s:14:\"190.146.40.106\";s:2:\"ua\";s:111:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36\";s:5:\"login\";i:1750879503;}}'),
(21, 2, 'nickname', 'daniel.vanegas'),
(22, 2, 'first_name', 'Daniel'),
(23, 2, 'last_name', 'Vanegas'),
(24, 2, 'description', ''),
(25, 2, 'rich_editing', 'true'),
(26, 2, 'syntax_highlighting', 'true'),
(27, 2, 'comment_shortcuts', 'false'),
(28, 2, 'admin_color', 'ocean'),
(29, 2, 'use_ssl', '0'),
(30, 2, 'show_admin_bar_front', 'true'),
(31, 2, 'locale', ''),
(32, 2, 'edgen_capabilities', 'a:1:{s:15:\"plg_super_admin\";b:1;}'),
(33, 2, 'edgen_user_level', '0'),
(34, 2, 'dismissed_wp_pointers', ''),
(36, 2, 'edgen_dashboard_quick_press_last_post_id', '141'),
(37, 2, 'community-events-location', 'a:1:{s:2:\"ip\";s:11:\"179.32.76.0\";}'),
(41, 2, 'oficina', 'BOG'),
(43, 2, 'edgen_persisted_preferences', 'a:4:{s:4:\"core\";a:2:{s:26:\"isComplementaryAreaVisible\";b:0;s:10:\"editorMode\";s:6:\"visual\";}s:14:\"core/edit-site\";a:2:{s:12:\"welcomeGuide\";b:0;s:16:\"welcomeGuidePage\";b:0;}s:9:\"_modified\";s:24:\"2024-10-02T03:44:32.261Z\";s:14:\"core/edit-post\";a:3:{s:12:\"welcomeGuide\";b:0;s:20:\"welcomeGuideTemplate\";b:0;s:14:\"fullscreenMode\";b:0;}}'),
(139, 7, 'rich_editing', 'true'),
(140, 7, 'syntax_highlighting', 'true'),
(141, 7, 'comment_shortcuts', 'false'),
(142, 7, 'admin_color', 'fresh'),
(143, 7, 'use_ssl', '0'),
(144, 7, 'show_admin_bar_front', 'true'),
(145, 7, 'locale', ''),
(47, 2, 'edgen_user-settings', 'libraryContent=browse'),
(48, 2, 'edgen_user-settings-time', '1731934715'),
(77, 4, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(78, 4, 'edgen_user_level', '0'),
(79, 4, 'dismissed_wp_pointers', ''),
(80, 4, 'oficina', 'BOG'),
(49, 3, 'nickname', 'laura.vanegas'),
(50, 3, 'first_name', 'Laura Melissa'),
(51, 3, 'last_name', 'Vanegas Alfaro'),
(52, 3, 'description', ''),
(53, 3, 'rich_editing', 'true'),
(54, 3, 'syntax_highlighting', 'true'),
(55, 3, 'comment_shortcuts', 'false'),
(56, 3, 'admin_color', 'fresh'),
(57, 3, 'use_ssl', '0'),
(58, 3, 'show_admin_bar_front', 'true'),
(59, 3, 'locale', ''),
(60, 3, 'edgen_capabilities', 'a:1:{s:18:\"plg_office_manager\";b:1;}'),
(61, 3, 'edgen_user_level', '0'),
(62, 3, 'dismissed_wp_pointers', ''),
(63, 3, 'oficina', 'BOG'),
(166, 3, 'edgen_dashboard_quick_press_last_post_id', '120'),
(167, 3, 'community-events-location', 'a:1:{s:2:\"ip\";s:12:\"190.68.147.0\";}'),
(207, 10, 'first_name', 'Liliana'),
(208, 10, 'last_name', 'Bastidas'),
(209, 10, 'description', ''),
(210, 10, 'rich_editing', 'true'),
(388, 15, 'session_tokens', 'a:2:{s:64:\"aaad2a09a8f35e05dc1bd7ff55b6d0d29d01c406d0035bc20acc099f81ca51f9\";a:4:{s:10:\"expiration\";i:1755989426;s:2:\"ip\";s:15:\"152.203.255.239\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36\";s:5:\"login\";i:1755816626;}s:64:\"cba656e0e848c92b2b7a4a1285b81fa80353da01034652b53bea6fccfea7182d\";a:4:{s:10:\"expiration\";i:1757028549;s:2:\"ip\";s:15:\"152.203.255.239\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36\";s:5:\"login\";i:1755818949;}}'),
(407, 14, 'session_tokens', 'a:3:{s:64:\"be7e8da6a729275f0854320d4cdad87cfba1b4aa92277a2b64ac74ae47ba70dc\";a:4:{s:10:\"expiration\";i:1761240984;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1761068184;}s:64:\"234d0968c813f479f3c88a84f91a7425d76de7d49dceaa85587b463bcf2a4282\";a:4:{s:10:\"expiration\";i:1761241879;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36\";s:5:\"login\";i:1761069079;}s:64:\"6806717166f7a90291540aab93394ab286f34eb921866f129329568462fd34ad\";a:4:{s:10:\"expiration\";i:1761241880;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36\";s:5:\"login\";i:1761069080;}}'),
(364, 16, 'locale', ''),
(363, 16, 'show_admin_bar_front', 'true'),
(362, 16, 'use_ssl', '0'),
(361, 16, 'admin_color', 'fresh'),
(82, 4, 'edgen_dashboard_quick_press_last_post_id', '58'),
(83, 4, 'community-events-location', 'a:1:{s:2:\"ip\";s:11:\"186.171.3.0\";}'),
(307, 6, 'session_tokens', 'a:1:{s:64:\"9327b62b33b87619b0bfef3e2d156d0bbd9147728d78a7f412c2dafcc7d4404b\";a:4:{s:10:\"expiration\";i:1756777083;s:2:\"ip\";s:15:\"181.137.147.156\";s:2:\"ua\";s:111:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36\";s:5:\"login\";i:1756604283;}}'),
(322, 14, 'nickname', 'FL.Laura.vanegas'),
(333, 14, 'edgen_capabilities', 'a:1:{s:18:\"plg_office_manager\";b:1;}'),
(334, 14, 'edgen_user_level', '0'),
(335, 14, 'dismissed_wp_pointers', ''),
(265, 13, 'nickname', 'admin.bolivia'),
(266, 13, 'first_name', ''),
(267, 13, 'last_name', ''),
(268, 13, 'description', ''),
(269, 13, 'rich_editing', 'true'),
(270, 13, 'syntax_highlighting', 'true'),
(402, 11, 'oficina', 'BOG'),
(284, 8, 'session_tokens', 'a:1:{s:64:\"c61d54639dfe55a9679d6215d0255cfe41807ecb40e54c2d0e8674ffe5488ba2\";a:4:{s:10:\"expiration\";i:1760112803;s:2:\"ip\";s:13:\"152.203.44.93\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0\";s:5:\"login\";i:1759940003;}}'),
(360, 16, 'comment_shortcuts', 'false'),
(206, 10, 'nickname', 'lilibastigar'),
(308, 3, 'session_tokens', 'a:6:{s:64:\"49a939517a006d8d46c7009baaba5a3a6bd56b433e58015a616fb6037d111cae\";a:4:{s:10:\"expiration\";i:1761344953;s:2:\"ip\";s:14:\"181.61.208.183\";s:2:\"ua\";s:144:\"Mozilla/5.0 (iPhone; CPU iPhone OS 17_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/141.0.7390.41 Mobile/15E148 Safari/604.1\";s:5:\"login\";i:1760135353;}s:64:\"7b650326bcf0c7bfc1e538bfe2e28ae7d5bf8c6a37fc36a5a5e708ad4ff8b3ea\";a:4:{s:10:\"expiration\";i:1761319401;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1761146601;}s:64:\"2f00ceb4b65aee269be7e977a638f324d33ed09255d671d2c4ee1166e7d953fa\";a:4:{s:10:\"expiration\";i:1761407750;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1761234950;}s:64:\"50638de8901bfc8f317806aec4aa4456b7e52e6675fa3b8d7c6e6ee75656ed09\";a:4:{s:10:\"expiration\";i:1761409014;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1761236214;}s:64:\"325fe2908774eee9ae13a619f6ce9325c26a9bc23fef49b62ca099d85013e474\";a:4:{s:10:\"expiration\";i:1761409014;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1761236214;}s:64:\"1a36aaf830e6119dbecb3e8cc57beea84e84002f576b9edb07c406fb005a16b8\";a:4:{s:10:\"expiration\";i:1761425437;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1761252637;}}'),
(366, 16, 'edgen_user_level', '0'),
(367, 16, 'dismissed_wp_pointers', ''),
(368, 16, 'oficina', 'FDL'),
(369, 17, 'nickname', 'Fl.vale.rodiguez'),
(370, 17, 'first_name', 'Vale'),
(328, 14, 'comment_shortcuts', 'false'),
(329, 14, 'admin_color', 'fresh'),
(330, 14, 'use_ssl', '0'),
(331, 14, 'show_admin_bar_front', 'true'),
(332, 14, 'locale', ''),
(151, 8, 'nickname', 'sandra.vanegas'),
(152, 8, 'first_name', 'Sandra'),
(153, 8, 'last_name', 'Alfaro'),
(154, 8, 'description', ''),
(155, 8, 'rich_editing', 'true'),
(156, 8, 'syntax_highlighting', 'true'),
(157, 8, 'comment_shortcuts', 'false'),
(158, 8, 'admin_color', 'fresh'),
(159, 8, 'use_ssl', '0'),
(160, 8, 'show_admin_bar_front', 'true'),
(161, 8, 'locale', ''),
(162, 8, 'edgen_capabilities', 'a:1:{s:18:\"plg_office_manager\";b:1;}'),
(163, 8, 'edgen_user_level', '0'),
(164, 8, 'oficina', 'BOG'),
(379, 17, 'locale', ''),
(380, 17, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(381, 17, 'edgen_user_level', '0'),
(382, 17, 'dismissed_wp_pointers', ''),
(383, 17, 'oficina', 'FDL'),
(394, 10, 'session_tokens', 'a:1:{s:64:\"7f6646cfae4b746075c629cbc0e1b923508f9ee322f2c3175b85bb91c2d35259\";a:4:{s:10:\"expiration\";i:1761416862;s:2:\"ip\";s:15:\"152.203.249.114\";s:2:\"ua\";s:111:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\";s:5:\"login\";i:1761244062;}}'),
(226, 11, 'syntax_highlighting', 'true'),
(271, 13, 'comment_shortcuts', 'false'),
(272, 13, 'admin_color', 'light'),
(273, 13, 'use_ssl', '0'),
(274, 13, 'show_admin_bar_front', 'true'),
(275, 13, 'locale', ''),
(276, 13, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(135, 7, 'nickname', 'pereira'),
(136, 7, 'first_name', 'Oficina Emmaus'),
(137, 7, 'last_name', 'Pereira'),
(138, 7, 'description', ''),
(97, 5, 'nickname', 'bucaramanga'),
(98, 5, 'first_name', ''),
(99, 5, 'last_name', ''),
(100, 5, 'description', ''),
(101, 5, 'rich_editing', 'true'),
(102, 5, 'syntax_highlighting', 'true'),
(103, 5, 'comment_shortcuts', 'false'),
(104, 5, 'admin_color', 'fresh'),
(105, 5, 'use_ssl', '0'),
(106, 5, 'show_admin_bar_front', 'true'),
(107, 5, 'locale', ''),
(108, 5, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(109, 5, 'edgen_user_level', '0'),
(110, 5, 'dismissed_wp_pointers', ''),
(111, 5, 'oficina', 'BUC'),
(115, 6, 'nickname', 'barranquilla'),
(116, 6, 'first_name', ''),
(117, 6, 'last_name', ''),
(118, 6, 'description', ''),
(119, 6, 'rich_editing', 'true'),
(120, 6, 'syntax_highlighting', 'true'),
(121, 6, 'comment_shortcuts', 'false'),
(122, 6, 'admin_color', 'fresh'),
(123, 6, 'use_ssl', '0'),
(124, 6, 'show_admin_bar_front', 'true'),
(125, 6, 'locale', ''),
(126, 6, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(127, 6, 'edgen_user_level', '0'),
(128, 6, 'dismissed_wp_pointers', ''),
(129, 6, 'oficina', 'BAR'),
(146, 7, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(147, 7, 'edgen_user_level', '0'),
(148, 7, 'dismissed_wp_pointers', ''),
(149, 7, 'oficina', 'PER'),
(359, 16, 'syntax_highlighting', 'true'),
(358, 16, 'rich_editing', 'true'),
(357, 16, 'description', ''),
(356, 16, 'last_name', 'rodriguez'),
(355, 16, 'first_name', 'Jenny'),
(354, 16, 'nickname', 'Fl.jenny.rodriguez'),
(211, 10, 'syntax_highlighting', 'true'),
(212, 10, 'comment_shortcuts', 'false'),
(213, 10, 'admin_color', 'fresh'),
(214, 10, 'use_ssl', '0'),
(215, 10, 'show_admin_bar_front', 'true'),
(216, 10, 'locale', ''),
(217, 10, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(218, 10, 'edgen_user_level', '0'),
(219, 10, 'dismissed_wp_pointers', ''),
(220, 10, 'oficina', 'BOG'),
(221, 11, 'nickname', 'jeissonmzapata'),
(222, 11, 'first_name', 'Jeisson'),
(223, 11, 'last_name', 'Mendoza'),
(224, 11, 'description', ''),
(225, 11, 'rich_editing', 'true'),
(227, 11, 'comment_shortcuts', 'false'),
(228, 11, 'admin_color', 'fresh'),
(229, 11, 'use_ssl', '0'),
(230, 11, 'show_admin_bar_front', 'true'),
(231, 11, 'locale', ''),
(232, 11, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(233, 11, 'edgen_user_level', '0'),
(234, 11, 'dismissed_wp_pointers', ''),
(238, 12, 'nickname', 'benjaminarray'),
(239, 12, 'first_name', 'benjamin'),
(240, 12, 'last_name', 'array'),
(241, 12, 'description', ''),
(242, 12, 'rich_editing', 'true'),
(243, 12, 'syntax_highlighting', 'true'),
(244, 12, 'comment_shortcuts', 'false'),
(245, 12, 'admin_color', 'fresh'),
(246, 12, 'use_ssl', '0'),
(247, 12, 'show_admin_bar_front', 'true'),
(248, 12, 'locale', ''),
(249, 12, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(250, 12, 'edgen_user_level', '0'),
(251, 12, 'dismissed_wp_pointers', ''),
(252, 12, 'oficina', 'PR'),
(277, 13, 'edgen_user_level', '0'),
(278, 13, 'dismissed_wp_pointers', ''),
(279, 13, 'oficina', 'BO'),
(373, 17, 'rich_editing', 'true'),
(374, 17, 'syntax_highlighting', 'true'),
(375, 17, 'comment_shortcuts', 'false'),
(376, 17, 'admin_color', 'fresh'),
(377, 17, 'use_ssl', '0'),
(378, 17, 'show_admin_bar_front', 'true'),
(348, 15, 'locale', ''),
(314, 13, 'community-events-location', 'a:1:{s:2:\"ip\";s:12:\"190.146.40.0\";}'),
(343, 15, 'syntax_highlighting', 'true'),
(344, 15, 'comment_shortcuts', 'false'),
(345, 15, 'admin_color', 'fresh'),
(346, 15, 'use_ssl', '0'),
(347, 15, 'show_admin_bar_front', 'true'),
(340, 15, 'last_name', 'Mendoza'),
(341, 15, 'description', ''),
(342, 15, 'rich_editing', 'true'),
(319, 7, 'session_tokens', 'a:1:{s:64:\"86a61dc4fc766ff0f542d53d73fd93f8bb70fa526a87f6375c9a491dac60dba2\";a:4:{s:10:\"expiration\";i:1753577024;s:2:\"ip\";s:14:\"191.106.231.78\";s:2:\"ua\";s:101:\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36\";s:5:\"login\";i:1752367424;}}'),
(336, 14, 'oficina', 'FDL'),
(365, 16, 'edgen_capabilities', 'a:1:{s:16:\"plg_office_staff\";b:1;}'),
(338, 15, 'nickname', 'Fl.jeisson.mendoza'),
(339, 15, 'first_name', 'Jeisson'),
(296, 5, 'session_tokens', 'a:1:{s:64:\"6f09e49c60b0cf2143f02dae4841ff1ea3521db76b653d3d2968459e6a37a981\";a:4:{s:10:\"expiration\";i:1761314722;s:2:\"ip\";s:15:\"191.110.110.131\";s:2:\"ua\";s:111:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\";s:5:\"login\";i:1761141922;}}'),
(350, 15, 'edgen_user_level', '0'),
(351, 15, 'dismissed_wp_pointers', ''),
(352, 15, 'oficina', 'FDL'),
(371, 17, 'last_name', 'rodriguez'),
(372, 17, 'description', ''),
(323, 14, 'first_name', 'laura melisa'),
(324, 14, 'last_name', 'vanegas alfaro'),
(325, 14, 'description', ''),
(326, 14, 'rich_editing', 'true'),
(327, 14, 'syntax_highlighting', 'true'),
(391, 16, 'session_tokens', 'a:1:{s:64:\"3e1fc3366e05f9ed7fb76fa47cc41139c96ecf5d4dee8a119f15f172272a4c0e\";a:4:{s:10:\"expiration\";i:1757624231;s:2:\"ip\";s:14:\"152.203.152.19\";s:2:\"ua\";s:111:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36\";s:5:\"login\";i:1757451431;}}'),
(286, 13, 'edgen_persisted_preferences', 'a:3:{s:4:\"core\";a:1:{s:26:\"isComplementaryAreaVisible\";b:1;}s:14:\"core/edit-post\";a:1:{s:12:\"welcomeGuide\";b:0;}s:9:\"_modified\";s:24:\"2025-03-06T18:14:43.318Z\";}'),
(396, 12, 'session_tokens', 'a:2:{s:64:\"fc078d726da4028a0b5b0e6a8f5ef7d56f12f69b2cbe95c0e019d8eae09e6eae\";a:4:{s:10:\"expiration\";i:1760803046;s:2:\"ip\";s:13:\"196.42.37.209\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1760630246;}s:64:\"96290921efa79198360fb549c98ee5c3275927201bf6aa5f963aa54a4c6265c2\";a:4:{s:10:\"expiration\";i:1760810461;s:2:\"ip\";s:13:\"196.42.37.209\";s:2:\"ua\";s:125:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0\";s:5:\"login\";i:1760637661;}}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edgen_users`
--

CREATE TABLE `edgen_users` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `user_login` varchar(60) NOT NULL DEFAULT '',
  `user_pass` varchar(255) NOT NULL DEFAULT '',
  `user_nicename` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `user_url` varchar(100) NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `edgen_users`
--

INSERT INTO `edgen_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
(5, 'bucaramanga', '$wp$2y$10$dRlA/50AedlB6zlfXhlzHucFX8hAGgegD2bo/WxvvPFTxSRKTdxLu', 'bucaramanga', 'bucaramanga@emmausdigital.com', '', '2024-10-02 14:20:41', '', 0, 'bucaramanga'),
(2, 'daniel.vanegas', '$wp$2y$10$iiVDvaXHsfIGZlsOTWyBUefYAnqvE0rr98DleOEscrSQ1llhsas2.', 'daniel-vanegas', 'davanegasa@gmail.com', '', '2024-09-25 17:19:00', '', 0, 'Daniel Vanegas'),
(3, 'laura.vanegas', '$wp$2y$10$8KYlT.KamgFX6LuSIVNcWetRTQ3gm49cuoa6PqBOaknSTUMTUVmPW', 'laura-vanegas', 'lauvanbiolog@gmail.com', '', '2024-09-28 01:43:25', '', 0, 'Laura Melissa Vanegas Alfaro'),
(4, 'Yamile Diaz', '$wp$2y$10$di/YM7jo9ecZlxDIWxmFy.LAQtiiP8dJqd6ulJkY7pYfxQ0R3GDIe', 'yamile-diaz', 'mayadime3@gmail.com', '', '2024-10-02 01:43:18', '', 0, 'Yamile Diaz'),
(6, 'barranquilla', '$wp$2y$10$dlBaD70BOC/EtFg6x1x0We3J35fV1h0L1g3T9.Tml6hks7CTSPrK.', 'barranquilla', 'barranquilla@emmausdigital.com', '', '2024-10-02 14:52:36', '', 0, 'barranquilla'),
(7, 'pereira', '$wp$2y$10$TiAXZwv0t/4SLaJ/BHVX5O/w/NL4HgGUr4tGzGC1nkSuowXJGTE16', 'pereira', 'pereira@emmaus.com', '', '2024-10-22 21:25:32', '', 0, 'Oficina Emmaus Pereira'),
(8, 'sandra.vanegas', '$wp$2y$10$4QSP2up5G2KSOOAGzTd9suQcxxZHK3TUh4X274nfg6n.YQw0UrQJq', 'sandra-vanegas', 'sandra.vanegas5@gmail.com', '', '2024-11-13 14:33:44', '', 0, 'Sandra Alfaro'),
(10, 'lilibastigar', '$wp$2y$10$W5pSO//bYPbI5ZA2CLXs1udR0zqJyXlcWANpez5AwvVf5skM.xtiW', 'lilibastigar', 'lilibastigar@gmail.com', '', '2024-12-05 22:44:52', '', 0, 'Liliana Bastidas'),
(12, 'benjaminarray', '$wp$2y$10$XBdip2huYBmXFZF24FubMebV6Qju3m8chWmomp2Jl3.43WmjoJg2e', 'benjaminarray', 'benjaminarray@proton.me', '', '2025-02-05 13:48:05', '', 0, 'benjamin array'),
(11, 'jeissonmzapata', '$P$BnAYkrwVSQ/6UNSgf51MQH4mZLS65X0', 'jeissonmzapata', 'jeissonmzapata@gmail.com', '', '2024-12-10 18:22:23', '1733854943:$P$B2q9KkbHOq1LyJWlZgmEuUh7A2v9Cm0', 0, 'Jeisson Mendoza'),
(13, 'admin.bolivia', '$wp$2y$10$2jhwlOhj98TN8hTZCK.6keokzk4ZXyLWFHUXZPvoSer1wEgYRHEey', 'admin-bolivia', 'admin.bolivia@emmausdigital.com', '', '2025-02-25 20:17:00', '', 0, 'admin.bolivia'),
(16, 'Fl.jenny.rodriguez', '$wp$2y$10$rxZqMMg9q5gKABoimKyLDOBZvYmwQYLHR1lFErjyvFskT8OQFzjdG', 'fl-jenny-rodriguez', 'Fl.jenny.rodriguez@gmail.com', '', '2025-07-29 00:48:49', '', 0, 'Jenny rodriguez'),
(14, 'FL.Laura.vanegas', '$wp$2y$10$bE/oo4/GTMonc2TzcngnWu.YcO/C98YzAoahZfRSWAY2kGBogkR4C', 'fl-laura-vanegas', 'FL.Laura.vanegas@gmail.com', '', '2025-07-29 00:23:25', '', 0, 'laura melisa vanegas alfaro'),
(15, 'Fl.jeisson.mendoza', '$wp$2y$10$cFs9uI0HHKrixgYi6Zq6O./hHf6Fq4cfS4UMMqX1zmVryGPzEw0mO', 'fl-jeisson-mendoza', 'Fl.jeisson.mendoza@gmail.com', '', '2025-07-29 00:42:14', '', 0, 'Jeisson Mendoza'),
(17, 'Fl.vale.rodiguez', '$wp$2y$10$WbLPSlNd3SqqHhnz3nDZFe5MnyHNjQchFWd9Z4CYwtEv8HFZ7YpsC', 'fl-vale-rodiguez', 'Fl.vale.rodiguez@gmail.com', '', '2025-07-29 00:50:35', '1753750235:$generic$HLte8ihDoFyBYop3i-PUbCn1HXjEzg3mViyAvDSK', 0, 'Vale rodriguez');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `edgen_commentmeta`
--
ALTER TABLE `edgen_commentmeta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indices de la tabla `edgen_comments`
--
ALTER TABLE `edgen_comments`
  ADD PRIMARY KEY (`comment_ID`),
  ADD KEY `comment_post_ID` (`comment_post_ID`),
  ADD KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  ADD KEY `comment_date_gmt` (`comment_date_gmt`),
  ADD KEY `comment_parent` (`comment_parent`),
  ADD KEY `comment_author_email` (`comment_author_email`(10));

--
-- Indices de la tabla `edgen_links`
--
ALTER TABLE `edgen_links`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `link_visible` (`link_visible`);

--
-- Indices de la tabla `edgen_options`
--
ALTER TABLE `edgen_options`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `option_name` (`option_name`),
  ADD KEY `autoload` (`autoload`);

--
-- Indices de la tabla `edgen_postmeta`
--
ALTER TABLE `edgen_postmeta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indices de la tabla `edgen_posts`
--
ALTER TABLE `edgen_posts`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `post_name` (`post_name`(191)),
  ADD KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  ADD KEY `post_parent` (`post_parent`),
  ADD KEY `post_author` (`post_author`);

--
-- Indices de la tabla `edgen_termmeta`
--
ALTER TABLE `edgen_termmeta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `term_id` (`term_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indices de la tabla `edgen_terms`
--
ALTER TABLE `edgen_terms`
  ADD PRIMARY KEY (`term_id`),
  ADD KEY `slug` (`slug`(191)),
  ADD KEY `name` (`name`(191));

--
-- Indices de la tabla `edgen_term_relationships`
--
ALTER TABLE `edgen_term_relationships`
  ADD PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  ADD KEY `term_taxonomy_id` (`term_taxonomy_id`);

--
-- Indices de la tabla `edgen_term_taxonomy`
--
ALTER TABLE `edgen_term_taxonomy`
  ADD PRIMARY KEY (`term_taxonomy_id`),
  ADD UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  ADD KEY `taxonomy` (`taxonomy`);

--
-- Indices de la tabla `edgen_usermeta`
--
ALTER TABLE `edgen_usermeta`
  ADD PRIMARY KEY (`umeta_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indices de la tabla `edgen_users`
--
ALTER TABLE `edgen_users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_login_key` (`user_login`),
  ADD KEY `user_nicename` (`user_nicename`),
  ADD KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `edgen_commentmeta`
--
ALTER TABLE `edgen_commentmeta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `edgen_comments`
--
ALTER TABLE `edgen_comments`
  MODIFY `comment_ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `edgen_links`
--
ALTER TABLE `edgen_links`
  MODIFY `link_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `edgen_options`
--
ALTER TABLE `edgen_options`
  MODIFY `option_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7079;

--
-- AUTO_INCREMENT de la tabla `edgen_postmeta`
--
ALTER TABLE `edgen_postmeta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `edgen_posts`
--
ALTER TABLE `edgen_posts`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT de la tabla `edgen_termmeta`
--
ALTER TABLE `edgen_termmeta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `edgen_terms`
--
ALTER TABLE `edgen_terms`
  MODIFY `term_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `edgen_term_taxonomy`
--
ALTER TABLE `edgen_term_taxonomy`
  MODIFY `term_taxonomy_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `edgen_usermeta`
--
ALTER TABLE `edgen_usermeta`
  MODIFY `umeta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=408;

--
-- AUTO_INCREMENT de la tabla `edgen_users`
--
ALTER TABLE `edgen_users`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;
