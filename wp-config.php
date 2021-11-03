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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'WMrb0eMqVXsZq7RP5gk0RD0E7YfVEWYXJq41dC1oy/5KjobpkjUR0PM6JibSMQcBvjtVZMrKIiOGAFOzTY3kLw==');
define('SECURE_AUTH_KEY',  'ndparp2/OCgvPUGIzmjCW13DBuq0y2KicRtPKWQzmcplqrQxzhzO5dBZvYKYq6kFhExSxC3nrCcMe/i5adhdjQ==');
define('LOGGED_IN_KEY',    'KA4rArThoyxpDw2OX4KfqIY6d6M7NASC+NvApBc4+dEnhKTD956WNNAgpQXDHrMoDF8VGUSkdeksBqFnyhskdQ==');
define('NONCE_KEY',        'BAsnRj4FQ/6HRLgftadzawspOYGxFj5JBHA4mvT0Jdi3WqsDdR8lERy1PyHsEONg+Iaa71eL1Xovc17skn4rEA==');
define('AUTH_SALT',        '/ucvv8NcQhBjoC9M8pMJJopm8MQ2xN0lFuZkKCUMqZjcdhFsvTMR0JxXuF+4RMuYFilqlgY0poa/CxNnp2PqLA==');
define('SECURE_AUTH_SALT', '887Oso1Da4dO8zSY0muq5yz9nvQ/3XQKG8P4o1I/JI1ilALXVVLa5glEYYmK95a628pPTNT6dOeHUn9bV7UMnw==');
define('LOGGED_IN_SALT',   'yMRIZz0j9WjuvA23rQGlXGwZOrHJhItGA+MxbRURFyjpuT+1rMe0A9tcyMNTealP+A7orM0E/7/y7BQ5c5UAdA==');
define('NONCE_SALT',       'Hx3hyylD4intQDAWosGAocVgukp+EQddai0Xhu2+fwaRJMR2nJ284k+37KLDWTYNouvQA9oiXJrIt+GO6TnRFA==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
