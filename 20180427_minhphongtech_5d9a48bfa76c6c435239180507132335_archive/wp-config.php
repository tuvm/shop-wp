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
define('DB_NAME', 'tgddfl');

/** MySQL database username */
define('DB_USER', 'user_tgddfl');

/** MySQL database password */
define('DB_PASSWORD', 'admin1234');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'R@S/p@77%<UI#tK:jr_N4DS=S&QmTOoc~B2)&60s_M#3rO%ML?JpVbvt4gqxBHxD');
define('SECURE_AUTH_KEY',  '&IFvXU[xiy;qR rC3QD*aiW;G6.rY+LN[:S[ AskFTyEx^YO?mma{w:x_vLfXf`b');
define('LOGGED_IN_KEY',    '4Xxd;aAJfwN*K+3e%_2*6=X~eCA~o5<NfPMU=f[GyMB>AF;3kwhBk(Cf|K3baUjG');
define('NONCE_KEY',        'u<3F-qq7:AGScYD*{,gib#V{os(P4HhI=%b7[2?v9,+7Nok%CT^kCbv|JznDV2;#');
define('AUTH_SALT',        'z#qM1=^JX0(M;pM6*)<]9L2-q0bzdZ>@A3EF5doYKuahwnDO;Z<R:kVFvHu]Nam+');
define('SECURE_AUTH_SALT', '4[?%`_WNLOq<wl.vlVw2Gb+/Uu`g4V-+tOXtKnTJ@H)2#>9#)W7Zx`ljdS4LznJ$');
define('LOGGED_IN_SALT',   'oD:[hvWFYvWtX-R)-5yN%$/J>8~sn]oy8a</;[x9  x.?uz[ NG&X)()%+v1]C!(');
define('NONCE_SALT',       'D;@=];g<d./9.5 %|2OitShV5bO4vxR_]VJEV_vox[=s(I.Kb)v+}&|6oiB7R[k^');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');


