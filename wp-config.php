<?php
//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

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
define('DB_NAME', 'i4241924_wp1');

/** MySQL database username */
define('DB_USER', 'i4241924_wp1');

/** MySQL database password */
define('DB_PASSWORD', 'N~qPnV#TO8pX7oCUHv&49*(0');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'I09O9nxkkF8BAqrU2hWFqJ0iZMqchRL49O4IMZkEhmwlp0CsEOgrSbtd9fLEbudK');
define('SECURE_AUTH_KEY',  'GuKzqEm2PPZrU3tehEaJcNWRAf2LfBrrPb5geFWrenrIaZ7HDDdH1XpBHVXLKACP');
define('LOGGED_IN_KEY',    's0KzU7LQjZKSlxRONaQAWmhcEeLYskwgnlUcXHS7tyWbahfl6T9lZy3ux9lGaEo0');
define('NONCE_KEY',        '4AG1pcp92HhGpvptBPlY0NHC8fM3Sudf08We5gbr5WOdJVe1BkIUleH2M7OaMeju');
define('AUTH_SALT',        'CNorzUMXo3teVRHCzETR5Gxr55t1VGzA1Ybv7lqCuUNATwbQLZ5pe6xXWbE2lLwe');
define('SECURE_AUTH_SALT', 'syh0kIo4iG76cHCNbqm2C8GD6DTdX51n2q9YCtaDC4htY1nxeP2wg2LN5eT6vtHX');
define('LOGGED_IN_SALT',   'GD8qWYGtRyCOb1xMrbkWRx9MXS4fr3qX6b1VmwQcCSLRAmqsZCXlYDqwduTGd1Ee');
define('NONCE_SALT',       'qFABuLSk7QqqSPBpTBUGMph6YltWPCMlefAZvzwLdqp0JegiqUC1eNXWqpEwnxMs');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
