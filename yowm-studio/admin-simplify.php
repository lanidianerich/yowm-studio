<?php
/**
 * Simplifies the WordPress admin for YOWM Studio administrators.
 *
 * In "simple" mode (the default for administrators) the left-hand admin menu is
 * trimmed to just the handful of things Lani actually uses — YOWM Studio, Media,
 * Plugins, and Settings — plus a single escape hatch that reveals the full
 * WordPress menu when it is needed. Nothing is ever removed permanently; hidden
 * menus are always one click away.
 *
 * The mode is stored per user, so it only ever changes Lani's own view. Students,
 * editors, and any future collaborator see a completely standard WordPress admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class YOWM_Admin_Simplify {

	const META_MODE = '_yowm_admin_mode'; // '' or 'simple' = trimmed; 'full' = everything visible.

	const PAGE_FULL   = 'yowm-admin-full';   // Escape-hatch slug that switches to full mode.
	const PAGE_SIMPLE = 'yowm-admin-simple'; // Slug that switches back to the trimmed menu.

	/**
	 * Top-level menu slugs kept visible in simple mode. Everything else is hidden.
	 * Plugins stays because that is where updates are installed; Media holds the
	 * lesson and podcast audio; Settings is the occasional home for permalinks etc.
	 */
	const KEEP = array(
		'yowm-studio',         // YOWM Studio
		'upload.php',          // Media
		'plugins.php',         // Plugins
		'options-general.php', // Settings
	);

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'handle_requests' ) );
		add_action( 'admin_menu', array( __CLASS__, 'trim_menu' ), 999 );
	}

	/** Only administrators (Lani) ever get the simplified view. */
	private static function applies(): bool {
		return is_admin() && current_user_can( 'manage_options' );
	}

	public static function is_simple(): bool {
		if ( ! self::applies() ) {
			return false;
		}
		return 'full' !== (string) get_user_meta( get_current_user_id(), self::META_MODE, true );
	}

	/**
	 * Runs before any admin page renders. Handles the two menu-toggle links and,
	 * in simple mode, sends the bare dashboard straight to YOWM Studio.
	 */
	public static function handle_requests(): void {
		if ( ! self::applies() ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( self::PAGE_FULL === $page ) {
			update_user_meta( get_current_user_id(), self::META_MODE, 'full' );
			wp_safe_redirect( admin_url( 'index.php' ) );
			exit;
		}

		if ( self::PAGE_SIMPLE === $page ) {
			update_user_meta( get_current_user_id(), self::META_MODE, 'simple' );
			wp_safe_redirect( admin_url( 'admin.php?page=yowm-studio' ) );
			exit;
		}

		// Land on YOWM Studio instead of the generic dashboard when the menu is trimmed.
		if ( self::is_simple() && 'index.php' === ( $GLOBALS['pagenow'] ?? '' ) && empty( $_GET ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=yowm-studio' ) );
			exit;
		}
	}

	public static function trim_menu(): void {
		if ( ! self::applies() ) {
			return;
		}

		if ( ! self::is_simple() ) {
			// Full menu is showing: offer a way back to the trimmed view.
			add_menu_page(
				'Simplify this menu',
				'⬅ Simplify menu',
				'manage_options',
				self::PAGE_SIMPLE,
				'__return_null',
				'dashicons-arrow-left-alt',
				2
			);
			return;
		}

		global $menu;

		$remove = array();
		foreach ( (array) $menu as $item ) {
			$slug = isset( $item[2] ) ? (string) $item[2] : '';
			if ( '' !== $slug && ! in_array( $slug, self::KEEP, true ) ) {
				$remove[] = $slug;
			}
		}

		foreach ( $remove as $slug ) {
			remove_menu_page( $slug );
		}

		// The escape hatch: one link that brings the whole WordPress menu back.
		add_menu_page(
			'Full WordPress menu',
			'Full WordPress menu →',
			'manage_options',
			self::PAGE_FULL,
			'__return_null',
			'dashicons-admin-generic',
			99
		);
	}
}
