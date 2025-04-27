<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'emmaus_wpgenesis' );

/** Database username */
define( 'DB_USER', 'emmaus_wpgenesis' );

/** Database password */
define( 'DB_PASSWORD', '4)3p9bd!Sn' );

/** Database hostname (poner en localhost en producción y en docker poner mysql)*/
define( 'DB_HOST', 'mysql' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );


/** VARIABLES PARA CONECTARSE A LA BASE DE DATOS**/
// Credenciales de la base de datos de cada oficina
define('BOG_DB_HOST', 'localhost');
define('BOG_DB_NAME', 'emmaus_estudiantes');
define('BOG_DB_USER', 'emmaus_admin');
define('BOG_DB_PASSWORD', 'emmaus1234+');

define('PER_DB_HOST', 'localhost');
define('PER_DB_NAME', 'emmaus_per_estudiantes');
define('PER_DB_USER', 'emmaus_admin');
define('PER_DB_PASSWORD', 'emmaus1234+');

define('BUC_DB_HOST', 'localhost');
define('BUC_DB_NAME', 'emmaus_buc_estudiantes');
define('BUC_DB_USER', 'emmaus_admin');
define('BUC_DB_PASSWORD', 'emmaus1234+');

define('BAR_DB_HOST', 'localhost');
define('BAR_DB_NAME', 'emmaus_bar_estudiantes');
define('BAR_DB_USER', 'emmaus_admin');
define('BAR_DB_PASSWORD', 'emmaus1234+');

define('FDL_DB_HOST', 'localhost');
define('FDL_DB_NAME', 'emmaus_source_of_light');
define('FDL_DB_USER', 'emmaus_admin_sol');
define('FDL_DB_PASSWORD', 'Fuente1234+');

define('PR_DB_HOST', 'localhost');
define('PR_DB_NAME', 'emmaus_pr_estudiantes');
define('PR_DB_USER', 'emmaus_pr_admin');
define('PR_DB_PASSWORD', '1l75evuzngd0');

define('BO_DB_HOST', 'localhost');
define('BO_DB_NAME', 'emmaus_bo_estudiantes');
define('BO_DB_USER', 'emmaus_bo_admin');
define('BO_DB_PASSWORD', 'uAfSwtGaCtbK');


/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'n2oy5fqakwkqee2slon6y50jnmcjaevaicnj9d0pmglmt2ksxopxnyycqx543hss' );
define( 'SECURE_AUTH_KEY',  'k1buafrhzcsidzaf58egkab9okce80dlxlfwyxhfulssvqghvpphxlry25537iy1' );
define( 'LOGGED_IN_KEY',    'dkwaqrcqqrwdhso4dfptvqrzeshxxsehtuq1miyfprrsakvzb69xfitb3gf9lidc' );
define( 'NONCE_KEY',        'zfovvtbnyyuebatrfsnaqdsjtepiny1hkm8p0hut3dwmdwm5jatl6hnxbcxvvw4t' );
define( 'AUTH_SALT',        'mx2znaen8foalbuvkhlhsjqsekinax1tosibqrtxfmrgtpeyjyihaunsxog0ozi6' );
define( 'SECURE_AUTH_SALT', 'b2lbwti07wspmrmtccdqjh6jqecffia1aaalidcmvtworlpxljl66uceh8zmryko' );
define( 'LOGGED_IN_SALT',   'c4pgjtgqf4bbjxpzpkmafegyokj7jpnqhm3qqq5lv4b9kyaijkql78k6ljbqkh9t' );
define( 'NONCE_SALT',       'mcnj6izxzyflworlp1chwcdh94qylentckqljebscvrbmwhciugv2qns9a1evt1a' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'edgen_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

