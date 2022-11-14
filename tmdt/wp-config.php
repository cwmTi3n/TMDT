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
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'CK_TMDT' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
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
define( 'AUTH_KEY',         'Qpj7H+0igihV/qNDJyeQJ2f;8NHc9nHaGdeZPg-:7YuJ0G?-%w9:jF5to8_er}y7' );
define( 'SECURE_AUTH_KEY',  'nhG`@4n^F9RD,h*.%|Z]ybfWGStL`yjLxK#:Z_c0W1~@.XNtP^(<xb2W+VvLa*AU' );
define( 'LOGGED_IN_KEY',    '&Id{EWn E,y#Fb~ewXp|HD?.^ P4TW,#7ZkxKln@]j[*Okez{4JhsFouK.id8@aY' );
define( 'NONCE_KEY',        '}4/r-e&oT}NUu9x&I*$o5,^nvG[Bm#~H/]wNgWK@uC8>{%><%%B@R+AYHCK?8Q=+' );
define( 'AUTH_SALT',        'pGyguXBJaO%Id +}ndB7~E)OH sWb<?N4H7fz=i)-Fhr@?dlk6hHq)h^mXQ^=JX#' );
define( 'SECURE_AUTH_SALT', 'i k{OfO8|2JtkFzX@U6VZ3_4nz@1xdm1(<}sjx|13,B=1^IJ:p#FMxIkB-m:]L)@' );
define( 'LOGGED_IN_SALT',   ')A1).S-LK,hBD3*6:pJ9<yC~X./krD)y$0o{}m+3bp!W}=].k3v1:Lo9z4e,fM:A' );
define( 'NONCE_SALT',       '>i%=~tgjpG0e.A`L6H{}x<,eWi?-cbxA^v[F2@?n%maGj<Z,a$o@y%8=gL,~(c9_' );

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