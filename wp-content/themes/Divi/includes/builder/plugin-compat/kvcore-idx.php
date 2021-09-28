<?php
/**
 * Compatibility for kvCORE.
 *
 * @package Divi
 * @subpackage Builder
 * @since ??
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

/**
 * Compatibility for kvCORE.
 *
 * @since ??
 *
 * @link https://wordpress.org/plugins/kvcore-idx/
 */
class ET_Builder_Plugin_Compat_KvCORE extends ET_Builder_Plugin_Compat_Base {
	/**
	 * Constructor.
	 *
	 * @since ??
	 */
	public function __construct() {
		$this->plugin_id = 'kvcore-idx/kvcore-idx.php';
		$this->init_hooks();
	}

	/**
	 * Hook methods to WordPress.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function init_hooks() {
		// Bail if there's no version found.
		if ( ! $this->get_plugin_version() ) {
			return;
		}

		// Plugin is just incompatible with JQuery Body feature.
		add_filter( 'et_builder_enable_jquery_body', '__return_false', 10, 2 );
	}
}

new ET_Builder_Plugin_Compat_KvCORE();
