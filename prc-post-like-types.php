<?php
/**
 * PRC Post Like Types
 *
 * @package           PRC_Post_Like_Types
 * @author            Seth Rubenstein, Nick Zanetti
 * @copyright         2024 Pew Research Center
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       PRC "Post-Like" Types
 * Plugin URI:        https://github.com/pewresearch/prc-post-like-types
 * Description:       Provides "post-like" content types like pewresearch.org/decoded, pewresearch.org/engineering, pewresearch.org/press-releases, or pewresearch.org/short-reads for PRC Platform. These post types all share a common permalink structure: post-type/YYYY/MM/DD/post-name but are independent of each other.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Author:            Seth Rubenstein
 * Author URI:        https://pewresearch.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       prc-post-like-types
 * Requires Plugins:  prc-scripts, prc-post-publish-pipeline
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEFAULT_TECHNICAL_CONTACT' ) ) {
	define( 'DEFAULT_TECHNICAL_CONTACT', 'webdev@pewresearch.org' );
}

define( 'PRC_POST_LIKE_TYPES_FILE', __FILE__ );
define( 'PRC_POST_LIKE_TYPES_DIR', __DIR__ );
define( 'PRC_POST_LIKE_TYPES_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-prc-post-like-types-activator.php
 */
function activate_prc_post_like_types() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-prc-post-like-types-activator.php';
	PRC_Post_Like_Types_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-prc-post-like-types-deactivator.php
 */
function deactivate_prc_post_like_types() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-prc-post-like-types-deactivator.php';
	PRC_Post_Like_Types_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_prc_post_like_types' );
register_deactivation_hook( __FILE__, 'deactivate_prc_post_like_types' );

/**
 * The core plugin class that is used to define the hooks that initialize the various components.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_prc_post_like_types() {
	$plugin = new PRC\Platform\Post_Like_Types\Plugin();
	$plugin->run();
}
run_prc_post_like_types();
