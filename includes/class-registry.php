<?php
/**
 * Post Like Content Types
 *
 * @package PRC\Platform\Post_Like_Types
 */

namespace PRC\Platform\Post_Like_Types;

/**
 * The registry class.
 *
 * @package PRC\Platform\Post_Like_Types
 */
class Registry {
	/**
	 * The array of post-like types.
	 *
	 * @var array
	 */
	public $post_like_types = array();

	/**
	 * The loader.
	 *
	 * @var Loader
	 */
	public $loader;

	/**
	 * The constructor.
	 *
	 * @param Loader $loader The loader.
	 */
	public function __construct( $loader ) {
		$this->loader = $loader;
		$this->init();
	}

	/**
	 * Initialize the hooks.
	 *
	 * @hook init
	 */
	public function init() {
		$this->loader->add_action( 'init', $this, 'add_post_like_type_rewrite_rules' );
		$this->loader->add_filter( 'post_type_link', $this, 'get_post_like_type_permalink', 30, 3 );
		$this->loader->add_action( 'init', $this, 'register_post_like_types', 10, 1 );
		$this->loader->add_filter( 'prc_platform_post_publish_pipeline_post_types', $this, 'opt_post_like_types_into_pipeline', 10, 1 );
		$this->loader->add_action( 'prc_platform_on_incremental_save', $this, 'enforce_post_like_type_format', 10, 1 );
	}

	/**
	 * Add notes support to the given post type.
	 *
	 * @param string $post_type The post type.
	 */
	public function add_notes_support( $post_type = 'post' ) {
		$supports        = get_all_post_type_supports( $post_type );
		$editor_supports = array( 'notes' => true );
		// `add_post_type_support()` doesn't merge support sub-properties, so we explicitly merge it here.
		if ( is_array( $supports['editor'] ) && isset( $supports['editor'][0] ) && is_array( $supports['editor'][0] ) ) {
			$editor_supports = array_merge( $editor_supports, $supports['editor'][0] );
		}
		add_post_type_support( $post_type, 'editor', $editor_supports );
	}

	/**
	 * Opt the post-like types into the PRC Platform post-publish-pipeline.
	 *
	 * @param array $post_types The post types.
	 * @return array The post types.
	 */
	public function opt_post_like_types_into_pipeline( $post_types ) {
		foreach ( $this->post_like_types as $slug => $args ) {
			array_push( $post_types, $slug );
		}
		return $post_types;
	}

	/**
	 * Register a post-like type.
	 *
	 * @param array $registration_args The registration arguments for the post-like type.
	 * @param array $post_type_args The post type arguments for the post-like type.
	 * @param array $taxonomies The taxonomies for the post-like type.
	 */
	public function register( $registration_args = array(), $post_type_args = array(), $taxonomies = array() ) {
		$registration_args = wp_parse_args(
			$registration_args,
			array(
				'slug'        => 'post-like-type',
				'singular'    => 'Post Like Type',
				'plural'      => 'Post Like Types',
				'description' => 'Description of the post like type post type',
				'pub_listing' => false,
			)
		);
		// If a default rewrite slug is not set then set it to the slug.
		if ( ! isset( $post_type_args['rewrite']['slug'] ) ) {
			$post_type_args['rewrite']['slug'] = $registration_args['slug'];
		}
		if ( $registration_args['pub_listing'] ) {
			// Add the _post_visibility taxonomy to the taxonomies array for any post opting into the pub listing.
			$taxonomies = array_merge( $taxonomies, array( '_post_visibility' ) );
		}
		$this->post_like_types[ $registration_args['slug'] ] = $this->construct_args( $registration_args, $post_type_args, $taxonomies, $registration_args['pub_listing'] );
	}

	/**
	 * Get the post-like types.
	 *
	 * @return array The post-like types.
	 */
	public function get_post_like_types() {
		return $this->post_like_types;
	}

	/**
	 * Get the post-like post-type slugs.
	 *
	 * @return array The post-like post-type slugs.
	 */
	public function get_post_like_types_post_types() {
		$post_types = array_keys( $this->post_like_types );
		return $post_types;
	}

	/**
	 * Construct labels for a post-like type.
	 *
	 * @param string $singular The singular name of the post-like type.
	 * @param string $plural The plural name of the post-like type.
	 * @return array The labels for the post-like type.
	 */
	public function construct_labels( $singular = 'Post Like Content Type', $plural = 'Post Like Content Types' ) {
		return array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'archives'              => $plural . ' Archives',
			'parent_item_colon'     => 'Parent ' . $singular . ':',
			'all_items'             => 'All ' . $plural,
			'add_new_item'          => 'Add New ' . $singular,
			'add_new'               => 'Add New',
			'new_item'              => 'New ' . $singular,
			'edit_item'             => 'Edit ' . $singular,
			'update_item'           => 'Update ' . $singular,
			'view_item'             => 'View ' . $singular,
			'search_items'          => 'Search ' . $plural,
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into ' . $singular,
			'uploaded_to_this_item' => 'Uploaded to this ' . $singular,
			'items_list'            => $plural . ' List',
			'items_list_navigation' => $plural . ' List Navigation',
			'filter_items_list'     => 'Filter ' . $plural . ' List',
		);
	}

	/**
	 * Construct arguments for a post-like type.
	 *
	 * @param array $registration_args The registration arguments for the post-like type.
	 * @param array $post_type_args The post type arguments for the post-like type.
	 * @param array $taxonomies The taxonomies for the post-like type.
	 * @param bool  $pub_listing Whether this post type should be included in publication listings.
	 * @return array The arguments for the post-like type.
	 */
	public function construct_args( $registration_args = array(), $post_type_args = array(), $taxonomies = array(), $pub_listing = false ) {
		$default_rewrite = array(
			'slug'       => $post_type_args['rewrite']['slug'],
			'with_front' => true,
			'pages'      => true,
			'feeds'      => true,
		);

		$default_supports = array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'revisions',
			'prc-revisions',
			'custom-fields',
			'comments',
			'prc-schema-seo',
			'prc-social-builder',
			'prc-bylines',
			'prc-art-direction',
			'prc-related-posts',
			'prc-datasets',
			'prc-collections',
			'prc-sitemap',
			'prc-markdown-for-agents',
			'prc-spoken-article',
		);

		// Add publication listing support if enabled.
		if ( $pub_listing ) {
			$default_supports[] = 'prc-publication-listing';
		}

		$default_taxonomies = wp_parse_args(
			$taxonomies,
			array(
				'category',
				'collection',
				'formats',
				'languages',
				'research-teams',
			)
		);

		$labels = $this->construct_labels(
			$registration_args['singular'],
			$registration_args['plural']
		);

		return wp_parse_args(
			$post_type_args,
			array(
				'label'               => $labels['name'],
				'description'         => $registration_args['description'],
				'labels'              => $labels,
				'supports'            => $default_supports,
				'taxonomies'          => $default_taxonomies,
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 5,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'show_in_rest'        => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'rewrite'             => $default_rewrite,
				'capability_type'     => 'post',
			)
		);
	}

	/**
	 * Register the post-like content types.
	 *
	 * @hook init
	 */
	public function register_post_like_types() {
		foreach ( $this->post_like_types as $slug => $args ) {
			register_post_type( $slug, $args );
			$this->add_notes_support( $slug );
		}
	}

	/**
	 * Construct the rewrite rules for the post-like content types.
	 * 1. An attachment rewrite rule.
	 * 2. A date based permalink rewrite rule.
	 *
	 * @param string $rewrite_slug The rewrite slug.
	 * @param string $post_type The post type.
	 * @return array The rewrite rules.
	 */
	public function construct_additional_rewrite_rules( $rewrite_slug, $post_type ) {
		return array(
			'^' . $rewrite_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/(.+)/(.+)/?$' => 'index.php?attachment=$matches[5]',
			'^' . $rewrite_slug . '/([0-9]{4})/([0-9]{2})/([0-9]{2})/(.+)/?$' => 'index.php?post_type=' . $post_type . '&year=$matches[1]&monthnum=$matches[2]&name=$matches[4]',
		);
	}

	/**
	 * Add the rewrite rules for the post-like content types.
	 *
	 * @hook init
	 */
	public function add_post_like_type_rewrite_rules() {
		foreach ( $this->post_like_types as $slug => $args ) {
			$rewrite_slug = $args['rewrite']['slug'];
			$rules        = $this->construct_additional_rewrite_rules( $rewrite_slug, $slug );
			foreach ( $rules as $rule => $query ) {
				add_rewrite_rule( $rule, $query, 'top' );
			}
		}
	}

	/**
	 * Get the permalink for a post-like content type.
	 *
	 * @hook post_type_link
	 *
	 * @param string $url The URL of the post.
	 * @param object $post The post object.
	 * @return string The permalink for the post.
	 */
	public function get_post_like_type_permalink( $url, $post ) {
		foreach ( $this->post_like_types as $slug => $args ) {
			if ( $post->post_type === $slug ) {
				if ( 'publish' !== $post->post_status ) {
					return $url;
				}
				$rewrite_slug = $args['rewrite']['slug'];
				$date_path    = get_the_date( 'Y/m/d', $post );
				$url          = str_replace( $rewrite_slug, $rewrite_slug . '/' . $date_path, $url );
			}
		}
		return $url;
	}

	/**
	 * Whenever a post-like content type is updated it should have the matching format enforced.
	 *
	 * @hook prc_platform_on_incremental_save
	 *
	 * @param object $post The post object.
	 * @return void
	 */
	public function enforce_post_like_type_format( $post ) {
		if ( in_array( $post->post_type, $this->get_post_like_types_post_types() ) ) {
			// Check if the post already has the decoded format, if not, append it.
			$format              = wp_get_object_terms( $post->ID, 'formats' );
			$has_matching_format = array_filter(
				$format,
				function ( $term ) {
					return in_array( $term->slug, $this->get_post_like_types_post_types() );
				}
			);
			// If no matching format, set the first one.
			if ( ! empty( $has_matching_format ) ) {
				$format_id = $has_matching_format[0]->term_id;
			} else {
				// Check if the format exists...
				$format = get_term_by( 'slug', $post->post_type, 'formats' );
				if ( ! $format ) {
					$new_format = wp_insert_term( $post->post_type, 'formats' );
					$format_id  = $new_format['term_id'];
				} else {
					$format_id = $format->term_id;
				}
			}
			wp_set_object_terms( $post->ID, $format_id, 'formats', true );
		}
	}
}
