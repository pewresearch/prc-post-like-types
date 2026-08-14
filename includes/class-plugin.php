<?php
/**
 * Plugin class.
 *
 * @package    PRC\Platform\Post_Like_Types
 */

namespace PRC\Platform\Post_Like_Types;

use WP_Error;

/**
 * Plugin class.
 *
 * @package    PRC\Platform\Post_Like_Types
 */
class Plugin {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The registry.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Registry    $registry    The registry.
	 */
	protected $registry;

	/**
	 * Define the core functionality of the platform as initialized by hooks.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = '1.0.0';
		$this->plugin_name = 'prc-post-like-types';

		$this->load_dependencies();
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// Load plugin loading class.
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-loader.php';

		// Initialize the loader.
		$this->loader = new Loader();

		// Load files...
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-registry.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-cli.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-admin-dataview-lists.php';

		$this->init_dependencies();
	}

	/**
	 * Initialize the dependencies.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_dependencies() {
		$this->registry = new Registry( $this->get_loader() );
		new Admin_Dataview_Lists( $this->get_loader() );

		// Decoded.
		$this->registry->register(
			// Basic registration args, covering high level details like the post type slug, the label prefix for singular and plural labels and the description.
			array(
				'slug'        => 'decoded',
				'singular'    => 'Decoded',
				'plural'      => 'Decoded Blog Posts',
				'description' => 'Decoded Blog Posts',
				'pub_listing' => true,
			),
			// Post type args, covering more specific details like the rewrite slug.
			array(
				'rewrite' => array(
					'slug' => 'decoded',
				),
			),
			// Taxonomies to register.
			array( 'decoded-category', 'bylines', 'category' )
		);
		// Press Releases.
		$this->registry->register(
			array(
				'slug'        => 'press-release',
				'singular'    => 'Press Release',
				'plural'      => 'Press Releases',
				'description' => 'Press releases announcing new research findings, publications, and organizational updates from Pew Research Center. These releases provide journalists, policymakers, and the public with accurate, timely information about our latest data-driven research and analysis.',
			),
			array(
				'rewrite' => array(
					'slug' => 'press-release',
				),
			),
			array( 'collections' )
		);
		// Short Reads.
		$this->registry->register(
			array(
				'slug'        => 'short-read',
				'singular'    => 'Short Read',
				'plural'      => 'Short Reads',
				'description' => 'Short-form data and analysis from Pew Research Center writers and social scientists.',
				'pub_listing' => true,
			),
			array(
				'rewrite' => array(
					'slug' => 'short-reads',
				),
			),
			array( 'datasets', 'collections', 'bylines' )
		);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    PRC\Platform\Post_Like_Types\Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
