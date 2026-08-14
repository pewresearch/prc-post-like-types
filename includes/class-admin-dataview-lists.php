<?php
/**
 * Registers DataViews admin lists for post-like types.
 *
 * @package PRC\Platform\Post_Like_Types
 */

namespace PRC\Platform\Post_Like_Types;

/**
 * Soft-depends on prc-wp-admin-dataview via the register_lists action.
 */
class Admin_Dataview_Lists {
	/**
	 * Constructor.
	 *
	 * @param Loader $loader Plugin loader.
	 */
	public function __construct( $loader ) {
		$loader->add_action( 'prc_wp_admin_dataview_register_lists', $this, 'register_lists', 10, 1 );
	}

	/**
	 * Register list configs for decoded, press-release, and short-read.
	 *
	 * @param object $lists List registry from prc-wp-admin-dataview.
	 */
	public function register_lists( $lists ): void {
		if ( ! is_object( $lists ) || ! method_exists( $lists, 'register' ) ) {
			return;
		}

		foreach ( self::list_configs() as $config ) {
			$lists->register( $config );
		}
	}

	/**
	 * List configs owned by this plugin.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function list_configs(): array {
		return array(
			array(
				'postType'  => 'decoded',
				'pageSlug'  => 'prc-wp-admin-dataview-decoded',
				'menuTitle' => __( 'All Decoded', 'prc-post-like-types' ),
				'pageTitle' => __( 'All Decoded', 'prc-post-like-types' ),
			),
			array(
				'postType'  => 'press-release',
				'pageSlug'  => 'prc-wp-admin-dataview-press-release',
				'menuTitle' => __( 'All Press Releases', 'prc-post-like-types' ),
				'pageTitle' => __( 'All Press Releases', 'prc-post-like-types' ),
			),
			array(
				'postType'  => 'short-read',
				'pageSlug'  => 'prc-wp-admin-dataview-short-read',
				'menuTitle' => __( 'All Short Reads', 'prc-post-like-types' ),
				'pageTitle' => __( 'All Short Reads', 'prc-post-like-types' ),
			),
		);
	}
}
