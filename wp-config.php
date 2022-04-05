<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'assignment2' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         'd)>``.,8DPLq%Y0bJhrfY2#<u{BOJz!!CT-:F!c:<Tmi(M31+LkP^Vn`57 +@a6M' );
define( 'SECURE_AUTH_KEY',  'lzqeRmcad)3jfy95FU[0a=8JsMVBvvcyg+:nS@fF4r]&UQy7#t)x6XP1d_re7Kg^' );
define( 'LOGGED_IN_KEY',    't3%<GZL~45`)*SOy:}P7*k|Jw=z(qCiA>SGn4a6Q`F/!o9A9(L#5IgIXVqXZ>g:C' );
define( 'NONCE_KEY',        'fV;F8(5Hd7:w(_I{>)0Sn:8|t}V3Wz|tMLy=?F~[x$&5g` dQ&dLZ6dz*uke 0av' );
define( 'AUTH_SALT',        'XxoljDJ;=F3DSYWII)m|!;kjEDk6Y&_ZHTSxk_})x3T.J`Y{NXy8HP7U%&B8=!G;' );
define( 'SECURE_AUTH_SALT', '}O(-.D)I0z<XS~x3tK0OZxFWo7t8[UHorC>G}i~28|Za!=$ /G>J_d-;ws2Jt`vw' );
define( 'LOGGED_IN_SALT',   'y79KX(=RgEZHs{msCHSP>$t2qY9>,  LSYrK(Z|<V%W[(~ ~=e.)BQNiB|N<K|tS' );
define( 'NONCE_SALT',       '&<eqyVb0lD3{Msa1@lk#fjo mTTi08&VY.9G5ck)69;iH6YG,5[mpF<.SNA&^J0p' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
