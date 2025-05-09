<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */


// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ethicals_po1' );

/** MySQL database username */
define( 'DB_USER', 'ethicals_po1' );

/** MySQL database password */
define( 'DB_PASSWORD', '71@k2!SUpN' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'g6h3jvynhjvyoxwu927c2ya3rrm0kf4zla51eltzze24booz4vtztrb8uaez3xbh' );
define( 'SECURE_AUTH_KEY',  'ch37k9l4dgaihx3bcdlea7jq6vazivxbadixd4mjdvlbvef3b86n0iqhnk4ixurg' );
define( 'LOGGED_IN_KEY',    'ttg5w6zfwfbycq4afw85a9qdvjsvsm89pafjkwji40s6hnznmehaquh0blyjhjci' );
define( 'NONCE_KEY',        'zhcyw12jf2sprejnkzjvazgbvszti9becvinf5qpdl0vwcks2xlxabxiiy1edgse' );
define( 'AUTH_SALT',        'jjlptlvuazmpmfqvdctyiybbp7ahgwstb8jrcxwewotrs5jch83gcx6xfbwzcyjg' );
define( 'SECURE_AUTH_SALT', '1pm0dxg10ub3qreaxbbblcvjbmzcrmltkxuywez7yf8zkn3nechzkjnaxltbfrr9' );
define( 'LOGGED_IN_SALT',   '3e9gf5fbjzq58zyjxh5nkcjbiraq3scepstqtahtsjrgz8w2gkl6hoyb188le5tv' );
define( 'NONCE_SALT',       'ksr8hlwyzh7iswarzfnjnf5tmmfdo0il5tvyypbvo3cknfggoejpdq7mxs4x4ip0' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'po1_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* Multisite */
define( 'WP_ALLOW_MULTISITE', true );
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
define('DOMAIN_CURRENT_SITE', 'thelastcage.org');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

define('ALLOW_UNFILTERED_UPLOADS', true);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

define( 'PDB_MULTILINGUAL', true );


