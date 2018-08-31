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
define('DB_NAME', 'martiny1_wp335');

/** MySQL database username */
define('DB_USER', 'martiny1_wp335');

/** MySQL database password */
define('DB_PASSWORD', 'p7591S9[0)');

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
define('AUTH_KEY',         '5bimdziwydidek7dl71wq4jdguyrpdj4sahtvmtzpti9qt9aspqk3gpvknpxamdi');
define('SECURE_AUTH_KEY',  '9ixgkt2sclvdx1kamzepnliyi3minqotvhvhfqmlhsysidbiwkgrsqwcyxizetmf');
define('LOGGED_IN_KEY',    'lwmeut6hm7pbhce1wlmmigkqjcrxdomlatewrva7xkbwil5i936opiraocdscksc');
define('NONCE_KEY',        'wemihfrftulg3it26qbvl2i0crkkgkdow5d0osvv5ro9nmbrikmsfu4okdxdpph7');
define('AUTH_SALT',        'dl0o2avab16vodsuas4yetrf6yuf5xp5rlygxptigfc8sciwgyvwbhrnb69kvdot');
define('SECURE_AUTH_SALT', 'ojpns3wj6ancpk3tgqvsiewxvvwcsaqzyxdzo4bfte69eonytnx1yg6strdj7xfw');
define('LOGGED_IN_SALT',   'mskrzi4wbcb5chnaxrg3uj0c5p3enlzoelgeayivkkwasfslgl67jlg2y9clnypt');
define('NONCE_SALT',       'ciiaopdlbmt0tslps1zvaunj2wpzupfqckxpqdjle9qhigmggbr3plmghag78yaj');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpoy_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
//Disable File Edits
define('DISALLOW_FILE_EDIT', true);