<?php
/**
 * PRC Post Like Types Activator
 *
 * @package PRC\Platform\Post_Like_Types
 */

class PRC_Post_Like_Types_Activator {

	public static function activate() {
		flush_rewrite_rules();

		wp_mail(
			DEFAULT_TECHNICAL_CONTACT,
			'PRC Post Like Types Activated',
			'The PRC Post Like Types plugin has been activated on ' . get_site_url()
		);
	}
}
