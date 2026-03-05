<?php
/**
 * PRC Post Like Types Deactivator
 *
 * @package PRC\Platform\Post_Like_Types
 */

class PRC_Post_Like_Types_Deactivator {

	public static function deactivate() {
		flush_rewrite_rules();

		wp_mail(
			DEFAULT_TECHNICAL_CONTACT,
			'PRC Post Like Types Deactivated',
			'The PRC Post Like Types plugin has been deactivated on ' . get_site_url()
		);
	}
}
