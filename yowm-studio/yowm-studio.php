<?php
/**
 * Plugin Name: YOWM Studio
 * Plugin URI:  https://lanidianerich.com/
 * Description: Cohorts, modules, lessons, resources, and private classroom pages for the Year of Writing Magically.
 * Version:     0.23.0
 * Author:      Lani Diane Rich
 * Author URI:  https://lanidianerich.com/
 * Text Domain: yowm-studio
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * License:     GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YOWM_STUDIO_VERSION', '0.23.0' );
define( 'YOWM_STUDIO_FILE', __FILE__ );
define( 'YOWM_STUDIO_DIR', plugin_dir_path( __FILE__ ) );
define( 'YOWM_STUDIO_URL', plugin_dir_url( __FILE__ ) );

final class YOWM_Studio {
	const COHORT   = 'yowm_cohort';
	const LESSON   = 'yowm_lesson';
	const RESOURCE = 'yowm_resource';
	const MODULE   = 'yowm_module';

	const META_COHORT          = '_yowm_cohort_id';
	const META_LESSON_COHORTS  = '_yowm_lesson_cohorts';
	const META_NUMBER          = '_yowm_lesson_number';
	const META_POST_AUDIO      = '_yowm_post_audio_url';
	const META_SESSION_VIDEO   = '_yowm_session_video_url';
	const META_SESSION_AUDIO   = '_yowm_session_audio_url';
	const META_SESSION_MEDIA   = '_yowm_session_media_by_cohort';
	const META_RELEASES        = '_yowm_release_by_cohort';
	const META_PODCAST_MEDIA   = '_yowm_podcast_media_by_cohort';
	const META_LECTURE_AUDIO   = '_yowm_lecture_audio_url';
	const META_LECTURE_TRANSCRIPT = '_yowm_lecture_transcript';
	const META_LECTURE_COHORTS = '_yowm_lecture_podcast_cohorts';
	const META_LECTURE_RELEASES = '_yowm_lecture_release_by_cohort';
	const META_LECTURE_VERSIONS = '_yowm_lecture_versions';
	const META_LECTURE_ASSIGNMENTS = '_yowm_lecture_version_assignments';
	const META_COHORT_YEAR     = '_yowm_cohort_year';
	const META_START_DATE      = '_yowm_start_date';
	const META_END_DATE        = '_yowm_end_date';
	const META_DISCORD_URL     = '_yowm_discord_url';
	const META_QUICK_SHEET_URL = '_yowm_quick_sheet_url';
	const META_ANNOUNCEMENT    = '_yowm_announcement';
	const META_PASSWORD_HASH    = '_yowm_password_hash';
	const META_PODCAST_ENABLED = '_yowm_podcast_enabled';
	const META_PODCAST_TOKEN   = '_yowm_podcast_token';
	const META_PODCAST_TITLE   = '_yowm_podcast_title';
	const META_PODCAST_DESC    = '_yowm_podcast_description';
	const META_PODCAST_AUTHOR  = '_yowm_podcast_author';
	const META_PODCAST_ARTWORK = '_yowm_podcast_artwork_id';
	const META_RESOURCE_URL    = '_yowm_resource_url';
	const META_RESOURCE_COHORT = '_yowm_resource_cohorts';
	const META_RESOURCE_TYPE   = '_yowm_resource_type';
	const META_RESOURCE_TEXT   = '_yowm_resource_text';
	const META_RESOURCE_NEW_TAB = '_yowm_resource_new_tab';
	const META_RESOURCE_DOWNLOAD = '_yowm_resource_download';

	const OPTION_HOME_TITLE    = 'yowm_studio_home_title';
	const OPTION_HOME_INTRO    = 'yowm_studio_home_intro';
	const OPTION_SIGNUP_URL    = 'yowm_studio_signup_url';
	const OPTION_VERSION       = 'yowm_studio_version';
	const OPTION_LAST_FATAL    = 'yowm_studio_last_fatal';


	public static function init(): void {
		register_shutdown_function( array( __CLASS__, 'capture_fatal_error' ) );
		add_action( 'init', array( __CLASS__, 'register_content' ) );
		add_action( 'init', array( __CLASS__, 'register_rewrites' ) );
		add_action( 'parse_request', array( __CLASS__, 'parse_virtual_request' ), 1 );
		add_action( 'send_headers', array( __CLASS__, 'send_private_classroom_headers' ), 0 );
		add_filter( 'redirect_canonical', array( __CLASS__, 'disable_canonical_for_virtual_routes' ), 10, 2 );
		add_action( 'init', array( __CLASS__, 'maybe_upgrade' ), 30 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'init', array( __CLASS__, 'maybe_render_podcast_feed' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'handle_virtual_routes' ) );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_filter( 'post_type_link', array( __CLASS__, 'post_type_link' ), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_assets' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
		add_action( 'admin_post_nopriv_yowm_unlock', array( __CLASS__, 'handle_unlock' ) );
		add_action( 'admin_post_yowm_unlock', array( __CLASS__, 'handle_unlock' ) );
		add_action( 'admin_post_yowm_lock', array( __CLASS__, 'handle_lock' ) );

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_filter( 'parent_file', array( __CLASS__, 'keep_studio_menu_open' ) );
		add_filter( 'submenu_file', array( __CLASS__, 'highlight_studio_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
		add_action( 'admin_post_yowm_save_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_yowm_duplicate_resource', array( __CLASS__, 'duplicate_resource' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'resource_row_actions' ), 10, 2 );

		add_filter( 'the_title', array( __CLASS__, 'display_title' ), 10, 2 );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'force_block_editor' ), 100, 2 );
		add_filter( 'use_block_editor_for_post', array( __CLASS__, 'force_block_editor_for_post' ), 100, 2 );
		add_filter( 'manage_yowm_lesson_posts_columns', array( __CLASS__, 'lesson_columns' ) );
		add_action( 'manage_yowm_lesson_posts_custom_column', array( __CLASS__, 'lesson_column_content' ), 10, 2 );
	}

	public static function activate(): void {
		self::register_content();
		self::register_rewrites();

		add_option( self::OPTION_HOME_TITLE, 'The Year of Writing Magically' );
		add_option( self::OPTION_HOME_INTRO, 'Choose your cohort to enter the classroom.' );
		add_option( self::OPTION_SIGNUP_URL, 'https://lanidianerich.com/year-of-writing-magically.html' );
		update_option( self::OPTION_VERSION, YOWM_STUDIO_VERSION );

		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	public static function maybe_upgrade(): void {
		$installed = (string) get_option( self::OPTION_VERSION, '' );

		if ( YOWM_STUDIO_VERSION === $installed ) {
			return;
		}

		// Rewrite rules do not flush automatically when WordPress replaces a plugin ZIP.
		self::register_content();
		self::register_rewrites();
		flush_rewrite_rules( false );
		update_option( self::OPTION_VERSION, YOWM_STUDIO_VERSION );

		// Repair older cohorts whose year existed only in the title.
		$cohorts = get_posts(
			array(
				'post_type'      => self::COHORT,
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);

		foreach ( $cohorts as $cohort ) {
			if ( ! get_post_meta( $cohort->ID, self::META_PODCAST_TOKEN, true ) ) {
				update_post_meta( $cohort->ID, self::META_PODCAST_TOKEN, self::generate_podcast_token() );
			}

			if ( ! get_post_meta( $cohort->ID, self::META_COHORT_YEAR, true ) ) {
				if ( preg_match( '/\b(20\d{2})\b/', $cohort->post_title . ' ' . $cohort->post_name, $matches ) ) {
					update_post_meta( $cohort->ID, self::META_COHORT_YEAR, absint( $matches[1] ) );
				}
			}

			// Retire WordPress's native one-password cookie system. If an older
			// cohort only has a native password, migrate it first; then clear it.
			if ( $cohort->post_password ) {
				if ( ! get_post_meta( $cohort->ID, self::META_PASSWORD_HASH, true ) ) {
					update_post_meta( $cohort->ID, self::META_PASSWORD_HASH, wp_hash_password( $cohort->post_password ) );
				}

				wp_update_post(
					array(
						'ID'            => $cohort->ID,
						'post_password' => '',
					)
				);
			}
		}

		$resources = get_posts(
			array(
				'post_type'      => self::RESOURCE,
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);

		foreach ( $resources as $resource ) {
			if ( ! get_post_meta( $resource->ID, self::META_RESOURCE_TYPE, true ) ) {
				update_post_meta( $resource->ID, self::META_RESOURCE_TYPE, 'page' );
			}
		}

		$lessons = get_posts(
			array(
				'post_type'      => self::LESSON,
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);

		foreach ( $lessons as $lesson ) {
			$legacy_cohort = absint( get_post_meta( $lesson->ID, self::META_COHORT, true ) );
			$cohort_ids    = get_post_meta( $lesson->ID, self::META_LESSON_COHORTS, true );

			if ( ! is_array( $cohort_ids ) && $legacy_cohort ) {
				update_post_meta( $lesson->ID, self::META_LESSON_COHORTS, array( $legacy_cohort ) );
			}

			$media = get_post_meta( $lesson->ID, self::META_SESSION_MEDIA, true );
			if ( ! is_array( $media ) && $legacy_cohort ) {
				$video = (string) get_post_meta( $lesson->ID, self::META_SESSION_VIDEO, true );
				$audio = (string) get_post_meta( $lesson->ID, self::META_SESSION_AUDIO, true );

				if ( $video || $audio ) {
					update_post_meta(
						$lesson->ID,
						self::META_SESSION_MEDIA,
						array(
							$legacy_cohort => array(
								'video' => $video,
								'audio' => $audio,
							),
						)
					);
				}
			}

			$versions = get_post_meta( $lesson->ID, self::META_LECTURE_VERSIONS, true );
			if ( ! is_array( $versions ) || ! $versions ) {
				$legacy_audio = (string) get_post_meta( $lesson->ID, self::META_LECTURE_AUDIO, true );
				$legacy_transcript = (string) get_post_meta( $lesson->ID, self::META_LECTURE_TRANSCRIPT, true );
				$legacy_cohorts = get_post_meta( $lesson->ID, self::META_LECTURE_COHORTS, true );
				$legacy_cohorts = is_array( $legacy_cohorts ) ? array_values( array_filter( array_map( 'absint', $legacy_cohorts ) ) ) : array();

				if ( $legacy_audio || $legacy_transcript ) {
					update_post_meta(
						$lesson->ID,
						self::META_LECTURE_VERSIONS,
						array(
							'v1' => array(
								'label'      => 'Original lecture',
								'audio'      => $legacy_audio,
								'transcript' => $legacy_transcript,
								'archived'   => 0,
							),
						)
					);

					$assignments = array();
					foreach ( $legacy_cohorts as $cohort_id ) {
						$assignments[ $cohort_id ] = 'v1';
					}
					update_post_meta( $lesson->ID, self::META_LECTURE_ASSIGNMENTS, $assignments );
				}
			}
		}
	}

	private static function labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'add_new_item'  => 'Add New ' . $singular,
			'edit_item'     => 'Edit ' . $singular,
			'new_item'      => 'New ' . $singular,
			'view_item'     => 'View ' . $singular,
			'search_items'  => 'Search ' . $plural,
			'all_items'     => 'All ' . $plural,
			'not_found'     => 'No ' . strtolower( $plural ) . ' found.',
		);
	}

	public static function register_content(): void {
		register_post_type(
			self::COHORT,
			array(
				'labels'       => self::labels( 'Cohort', 'Cohorts' ),
				'public'       => true,
				'show_in_rest' => true,
				'show_in_menu' => false,
				'has_archive'  => false,
				// No 'editor': the cohort welcome text is a field in the Cohort box,
				// so the big empty content editor is hidden entirely.
				'supports'     => array( 'title', 'thumbnail', 'revisions', 'custom-fields' ),
				'rewrite'      => false,
				'menu_icon'    => 'dashicons-groups',
			)
		);

		register_taxonomy(
			self::MODULE,
			array( self::LESSON ),
			array(
				'labels' => array(
					'name'          => 'Modules',
					'singular_name' => 'Module',
					'add_new_item'  => 'Add New Module',
					'edit_item'     => 'Edit Module',
					'menu_name'     => 'Modules',
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'meta_box_cb'       => false,
				'rewrite'           => false,
			)
		);

		register_post_type(
			self::LESSON,
			array(
				'labels'       => self::labels( 'Lesson', 'Lessons' ),
				'public'       => true,
				'show_in_rest' => true,
				'show_in_menu' => false,
				'has_archive'  => false,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' ), // excerpt handled by the YOWM "Podcast episode notes" field
				'taxonomies'   => array( self::MODULE ),
				'rewrite'      => false,
				'menu_icon'    => 'dashicons-welcome-learn-more',
			)
		);

		register_taxonomy_for_object_type( self::MODULE, self::LESSON );

		register_post_type(
			self::RESOURCE,
			array(
				'labels'       => self::labels( 'Class Info Item', 'Class Info' ),
				'public'       => true,
				'show_in_rest' => true,
				'show_in_menu' => false,
				'has_archive'  => false,
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
				'rewrite'      => false,
				'menu_icon'    => 'dashicons-portfolio',
			)
		);
	}

	public static function request_route(): array {
		$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/', PHP_URL_PATH );
		$home_path = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );

		$path = '/' . ltrim( $path, '/' );
		$home_path = '/' . trim( $home_path, '/' );

		if ( '/' !== $home_path && str_starts_with( $path, $home_path ) ) {
			$path = substr( $path, strlen( $home_path ) );
		}

		$path = trim( $path, '/' );
		$route = array(
			'year'            => 0,
			'lesson_slug'     => '',
			'resource_slug'   => '',
			'lesson_archive'  => false,
			'library_archive' => false,
			'podcast_token'   => '',
		);

		if ( preg_match( '#^podcast/(20\d{2})/([A-Za-z0-9_-]+)/?$#', $path, $matches ) ) {
			$route['year'] = absint( $matches[1] );
			$route['podcast_token'] = sanitize_text_field( $matches[2] );
			return $route;
		}

		if ( preg_match( '#^(20\d{2})/?$#', $path, $matches ) ) {
			$route['year'] = absint( $matches[1] );
			return $route;
		}

		if ( preg_match( '#^(20\d{2})/lessons/?$#', $path, $matches ) ) {
			$route['year'] = absint( $matches[1] );
			$route['lesson_archive'] = true;
			return $route;
		}

		if ( preg_match( '#^(20\d{2})/library/?$#', $path, $matches ) ) {
			$route['year'] = absint( $matches[1] );
			$route['library_archive'] = true;
			return $route;
		}

		if ( preg_match( '#^(20\d{2})/resources/([^/]+)/?$#', $path, $matches ) ) {
			$route['year'] = absint( $matches[1] );
			$route['resource_slug'] = sanitize_title( $matches[2] );
			return $route;
		}

		if ( preg_match( '#^(20\d{2})/([^/]+)/?$#', $path, $matches ) ) {
			$route['year'] = absint( $matches[1] );
			$route['lesson_slug'] = sanitize_title( $matches[2] );
		}

		return $route;
	}

	public static function parse_virtual_request( WP $wp ): void {
		$route = self::request_route();
		if ( ! $route['year'] || $route['podcast_token'] ) {
			return;
		}

		$wp->query_vars['yowm_cohort_year'] = $route['year'];
		if ( $route['lesson_archive'] ) {
			$wp->query_vars['yowm_lesson_archive'] = 1;
		}
		if ( $route['library_archive'] ) {
			$wp->query_vars['yowm_library_archive'] = 1;
		}
		if ( $route['lesson_slug'] ) {
			$wp->query_vars['yowm_lesson_slug'] = $route['lesson_slug'];
		}
		if ( $route['resource_slug'] ) {
			$wp->query_vars['yowm_resource_slug'] = $route['resource_slug'];
		}
	}

	public static function disable_canonical_for_virtual_routes( $redirect_url, string $requested_url ) {
		$route = self::request_route();
		return $route['year'] ? false : $redirect_url;
	}

	public static function register_rewrites(): void {
		add_rewrite_rule(
			'^([0-9]{4})/?$',
			'index.php?yowm_cohort_year=$matches[1]',
			'top'
		);

		add_rewrite_rule(
			'^([0-9]{4})/lessons/?$',
			'index.php?yowm_cohort_year=$matches[1]&yowm_lesson_archive=1',
			'top'
		);

		add_rewrite_rule(
			'^([0-9]{4})/library/?$',
			'index.php?yowm_cohort_year=$matches[1]&yowm_library_archive=1',
			'top'
		);

		add_rewrite_rule(
			'^([0-9]{4})/resources/([^/]+)/?$',
			'index.php?yowm_cohort_year=$matches[1]&yowm_resource_slug=$matches[2]',
			'top'
		);

		add_rewrite_rule(
			'^([0-9]{4})/([^/]+)/?$',
			'index.php?yowm_cohort_year=$matches[1]&yowm_lesson_slug=$matches[2]',
			'top'
		);
	}

	public static function query_vars( array $vars ): array {
		$vars[] = 'yowm_cohort_year';
		$vars[] = 'yowm_lesson_slug';
		$vars[] = 'yowm_resource_slug';
		$vars[] = 'yowm_lesson_archive';
		$vars[] = 'yowm_library_archive';
		return $vars;
	}

	public static function handle_virtual_routes(): void {
		$route         = self::request_route();
		$year          = absint( get_query_var( 'yowm_cohort_year' ) ?: $route['year'] );
		$lesson_slug    = sanitize_title( (string) ( get_query_var( 'yowm_lesson_slug' ) ?: $route['lesson_slug'] ) );
		$resource_slug  = sanitize_title( (string) ( get_query_var( 'yowm_resource_slug' ) ?: $route['resource_slug'] ) );
		$lesson_archive  = absint( get_query_var( 'yowm_lesson_archive' ) ?: $route['lesson_archive'] );
		$library_archive = absint( get_query_var( 'yowm_library_archive' ) ?: $route['library_archive'] );

		if ( ! $year ) {
			return;
		}

		$cohort = self::get_cohort_by_year( $year );
		if ( ! $cohort ) {
			self::force_404();
			return;
		}

		if ( $lesson_archive || $library_archive ) {
			self::mark_virtual_route_valid();
			$GLOBALS['yowm_virtual_post'] = $cohort;
			return;
		}

		if ( $resource_slug ) {
			$resource = self::get_resource_for_cohort_by_slug( $cohort->ID, $resource_slug );
			if ( ! $resource ) {
				self::force_404();
				return;
			}
			self::mark_virtual_route_valid();
			$GLOBALS['yowm_virtual_post'] = $resource;
			return;
		}

		if ( $lesson_slug ) {
			$lesson = self::get_lesson_for_cohort_by_slug( $cohort->ID, $lesson_slug );
			if ( ! $lesson ) {
				self::force_404();
				return;
			}
			self::mark_virtual_route_valid();
			$GLOBALS['yowm_virtual_post'] = $lesson;
			return;
		}

		self::mark_virtual_route_valid();
		$GLOBALS['yowm_virtual_post'] = $cohort;
	}

	private static function mark_virtual_route_valid(): void {
		global $wp_query;

		if ( $wp_query ) {
			$wp_query->is_404 = false;
			$wp_query->is_page = true;
		}

		status_header( 200 );
	}

	private static function force_404(): void {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	public static function template_include( string $template ): string {
		$route         = self::request_route();
		$year          = absint( get_query_var( 'yowm_cohort_year' ) ?: $route['year'] );
		$lesson_slug   = sanitize_title( (string) ( get_query_var( 'yowm_lesson_slug' ) ?: $route['lesson_slug'] ) );
		$resource_slug   = sanitize_title( (string) ( get_query_var( 'yowm_resource_slug' ) ?: $route['resource_slug'] ) );
		$lesson_archive  = absint( get_query_var( 'yowm_lesson_archive' ) ?: $route['lesson_archive'] );
		$library_archive = absint( get_query_var( 'yowm_library_archive' ) ?: $route['library_archive'] );

		// Virtual cohort routes must take priority. WordPress may still label
		// /2027/ as the front page because it was not backed by a normal Page.
		if ( $year && $lesson_archive ) {
			return self::has_access()
				? YOWM_STUDIO_DIR . 'templates/lesson-archive.php'
				: YOWM_STUDIO_DIR . 'templates/gate.php';
		}

		if ( $year && $library_archive ) {
			return self::has_access()
				? YOWM_STUDIO_DIR . 'templates/library-archive.php'
				: YOWM_STUDIO_DIR . 'templates/gate.php';
		}

		if ( $year && $resource_slug ) {
			if ( ! self::has_access() ) {
				return YOWM_STUDIO_DIR . 'templates/gate.php';
			}
			// The router only sets the virtual post when the resource resolved for
			// this cohort. Without it, serve a 404 rather than a broken template.
			return isset( $GLOBALS['yowm_virtual_post'] )
				? YOWM_STUDIO_DIR . 'templates/single-resource.php'
				: YOWM_STUDIO_DIR . 'templates/404.php';
		}

		if ( $year && $lesson_slug ) {
			if ( ! self::has_access() ) {
				return YOWM_STUDIO_DIR . 'templates/gate.php';
			}
			// Same guard: a lesson that isn't resolvable for this cohort (e.g. not
			// assigned to it) must 404, not fall through to single-lesson.php.
			return isset( $GLOBALS['yowm_virtual_post'] )
				? YOWM_STUDIO_DIR . 'templates/single-lesson.php'
				: YOWM_STUDIO_DIR . 'templates/404.php';
		}

		if ( $year ) {
			return self::has_access()
				? YOWM_STUDIO_DIR . 'templates/single-cohort.php'
				: YOWM_STUDIO_DIR . 'templates/gate.php';
		}

		if ( is_front_page() ) {
			return YOWM_STUDIO_DIR . 'templates/front-page.php';
		}

		if ( is_404() ) {
			return YOWM_STUDIO_DIR . 'templates/404.php';
		}

		if ( is_singular( array( self::COHORT, self::LESSON, self::RESOURCE ) ) ) {
			return self::has_access()
				? $template
				: YOWM_STUDIO_DIR . 'templates/gate.php';
		}

		return $template;
	}

	public static function post_type_link( string $permalink, WP_Post $post ): string {
		if ( self::COHORT === $post->post_type ) {
			$year = self::cohort_year( $post->ID );
			return $year ? home_url( '/' . $year . '/' ) : $permalink;
		}

		if ( self::LESSON === $post->post_type ) {
			// Keep the link inside the cohort the reader is currently viewing, so a
			// lesson opened from /2026/ stays on /2026/ instead of jumping to the
			// lesson's first-assigned year.
			$current_year = absint( get_query_var( 'yowm_cohort_year' ) );
			if ( $current_year ) {
				$current_cohort = self::get_cohort_by_year( $current_year );
				if ( $current_cohort && self::lesson_applies_to_cohort( $post->ID, (int) $current_cohort->ID ) ) {
					return home_url( '/' . $current_year . '/' . $post->post_name . '/' );
				}
			}

			$cohort_ids = self::lesson_cohort_ids( $post->ID );

			if ( empty( $cohort_ids ) ) {
				$cohorts   = self::get_published_cohorts();
				$cohort_id = $cohorts ? $cohorts[0]->ID : 0;
			} else {
				$cohort_id = (int) $cohort_ids[0];
			}

			$year = self::cohort_year( $cohort_id );
			return $year ? home_url( '/' . $year . '/' . $post->post_name . '/' ) : $permalink;
		}

		if ( self::RESOURCE === $post->post_type ) {
			$ids = self::resource_cohort_ids( $post->ID );

			// Same rule: stay in the cohort currently being viewed when it applies.
			$current_year = absint( get_query_var( 'yowm_cohort_year' ) );
			if ( $current_year ) {
				$current_cohort = self::get_cohort_by_year( $current_year );
				if ( $current_cohort && ( empty( $ids ) || in_array( (int) $current_cohort->ID, $ids, true ) ) ) {
					return home_url( '/' . $current_year . '/resources/' . $post->post_name . '/' );
				}
			}

			$cohort_id = $ids ? (int) $ids[0] : 0;
			$year      = self::cohort_year( $cohort_id );
			return $year ? home_url( '/' . $year . '/resources/' . $post->post_name . '/' ) : $permalink;
		}

		return $permalink;
	}

	public static function frontend_assets(): void {
		wp_enqueue_style(
			'yowm-studio-front',
			YOWM_STUDIO_URL . 'assets/front.css',
			array(),
			YOWM_STUDIO_VERSION
		);
		wp_enqueue_script(
			'yowm-studio-front',
			YOWM_STUDIO_URL . 'assets/front.js',
			array(),
			YOWM_STUDIO_VERSION,
			true
		);
	}

	public static function body_classes( array $classes ): array {
		$route = self::request_route();
		$year = absint( get_query_var( 'yowm_cohort_year' ) ?: $route['year'] );
		if ( $year ) {
			$classes[] = 'yowm-cohort-view';
		}
		return $classes;
	}

	public static function cohort_year( int $cohort_id ): int {
		$year = absint( get_post_meta( $cohort_id, self::META_COHORT_YEAR, true ) );
		if ( $year ) {
			return $year;
		}

		$post = get_post( $cohort_id );
		if ( $post && preg_match( '/\b(20\d{2})\b/', $post->post_title . ' ' . $post->post_name, $matches ) ) {
			return absint( $matches[1] );
		}

		return 0;
	}

	public static function get_cohort_by_year( int $year ): ?WP_Post {
		foreach ( self::get_published_cohorts() as $cohort ) {
			if ( $year === self::cohort_year( $cohort->ID ) ) {
				return $cohort;
			}
		}
		return null;
	}

	public static function get_published_cohorts(): array {
		$query = new WP_Query(
			array(
				'post_type'           => self::COHORT,
				'post_status'         => 'publish',
				'posts_per_page'      => -1,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		$cohorts = $query->posts;

		usort(
			$cohorts,
			static fn( WP_Post $a, WP_Post $b ): int =>
				self::cohort_year( $b->ID ) <=> self::cohort_year( $a->ID )
		);

		return $cohorts;
	}

	public static function lesson_cohort_ids( int $lesson_id ): array {
		$value = get_post_meta( $lesson_id, self::META_LESSON_COHORTS, true );

		if ( is_array( $value ) ) {
			return array_values( array_unique( array_filter( array_map( 'absint', $value ) ) ) );
		}

		$legacy = absint( get_post_meta( $lesson_id, self::META_COHORT, true ) );
		return $legacy ? array( $legacy ) : array();
	}

	public static function lesson_applies_to_cohort( int $lesson_id, int $cohort_id ): bool {
		$ids = self::lesson_cohort_ids( $lesson_id );
		$scope_allows = empty( $ids ) || in_array( $cohort_id, $ids, true );

		if ( ! $scope_allows ) {
			return false;
		}

		$versions = self::lecture_versions( $lesson_id );

		// Older or non-lecture Lessons continue to use the normal cohort scope.
		if ( empty( $versions ) ) {
			return true;
		}

		// A reusable lecture Lesson is invisible until this cohort explicitly
		// receives one of its versions. Blank means not assigned, not inherited.
		$assigned = self::lecture_version_for_cohort( $lesson_id, $cohort_id );
		return ! empty( $assigned['id'] );
	}

	public static function generate_podcast_token(): string {
		return wp_generate_password( 40, false, false );
	}

	public static function podcast_token( int $cohort_id ): string {
		$token = (string) get_post_meta( $cohort_id, self::META_PODCAST_TOKEN, true );

		if ( ! $token && current_user_can( 'edit_post', $cohort_id ) ) {
			$token = self::generate_podcast_token();
			update_post_meta( $cohort_id, self::META_PODCAST_TOKEN, $token );
		}

		return $token;
	}

	public static function podcast_feed_url( int $cohort_id ): string {
		$year  = self::cohort_year( $cohort_id );
		$token = self::podcast_token( $cohort_id );

		if ( ! $year || ! $token ) {
			return '';
		}

		return add_query_arg(
			array(
				'yowm_podcast'       => 1,
				'yowm_podcast_year'  => $year,
				'yowm_podcast_token' => $token,
			),
			home_url( '/' )
		);
	}

	public static function release_timezone(): DateTimeZone {
		return new DateTimeZone( 'America/Denver' );
	}

	public static function release_timezone_label(): string {
		return '6:00 AM America/Denver';
	}

	private static function new_episode_guid(): string {
		return wp_generate_uuid4();
	}

	public static function lecture_versions( int $lesson_id ): array {
		$value = get_post_meta( $lesson_id, self::META_LECTURE_VERSIONS, true );
		$value = is_array( $value ) ? $value : array();
		$changed = false;

		foreach ( $value as $version_id => $version ) {
			if ( ! is_array( $version ) ) {
				continue;
			}
			if ( empty( $version['guid'] ) ) {
				$value[ $version_id ]['guid'] = self::new_episode_guid();
				$changed = true;
			}
		}

		if ( $changed ) {
			update_post_meta( $lesson_id, self::META_LECTURE_VERSIONS, $value );
		}

		return $value;
	}

	public static function lecture_version_assignments( int $lesson_id ): array {
		$value = get_post_meta( $lesson_id, self::META_LECTURE_ASSIGNMENTS, true );
		return is_array( $value ) ? $value : array();
	}

	public static function lecture_version_for_cohort( int $lesson_id, int $cohort_id ): array {
		$versions = self::lecture_versions( $lesson_id );
		$assignments = self::lecture_version_assignments( $lesson_id );
		$version_id = sanitize_key( (string) ( $assignments[ $cohort_id ] ?? '' ) );

		if ( ! $version_id || empty( $versions[ $version_id ] ) || ! is_array( $versions[ $version_id ] ) ) {
			return array( 'id' => '', 'guid' => '', 'label' => '', 'audio' => '', 'transcript' => '', 'archived' => 0 );
		}

		$version = $versions[ $version_id ];
		return array(
			'id'         => $version_id,
			'guid'       => (string) ( $version['guid'] ?? '' ),
			'label'      => (string) ( $version['label'] ?? '' ),
			'audio'      => (string) ( $version['audio'] ?? '' ),
			'transcript' => (string) ( $version['transcript'] ?? '' ),
			'archived'   => ! empty( $version['archived'] ) ? 1 : 0,
		);
	}

	public static function lecture_podcast_cohort_ids( int $lesson_id ): array {
		return array_values( array_filter( array_map( 'absint', array_keys( self::lecture_version_assignments( $lesson_id ) ) ) ) );
	}

	public static function lecture_is_in_cohort_feed( int $lesson_id, int $cohort_id ): bool {
		$version = self::lecture_version_for_cohort( $lesson_id, $cohort_id );
		return ! empty( $version['audio'] );
	}

	public static function lecture_release_value( int $lesson_id, int $cohort_id ): string {
		$releases = get_post_meta( $lesson_id, self::META_RELEASES, true );
		if ( is_array( $releases ) && array_key_exists( $cohort_id, $releases ) ) {
			return (string) $releases[ $cohort_id ];
		}

		// Backward-compatible fallback for dates saved before 0.10.0.
		$legacy = get_post_meta( $lesson_id, self::META_LECTURE_RELEASES, true );
		return is_array( $legacy ) ? (string) ( $legacy[ $cohort_id ] ?? '' ) : '';
	}

	public static function lesson_podcast_media( int $lesson_id, int $cohort_id ): array {
		$all = get_post_meta( $lesson_id, self::META_PODCAST_MEDIA, true );
		$item = is_array( $all ) && isset( $all[ $cohort_id ] ) && is_array( $all[ $cohort_id ] )
			? $all[ $cohort_id ]
			: array();

		if ( ! empty( $item['session_url'] ) && empty( $item['session_guid'] ) ) {
			$item['session_guid'] = self::new_episode_guid();
			$all[ $cohort_id ] = $item;
			update_post_meta( $lesson_id, self::META_PODCAST_MEDIA, $all );
		}
		$session = self::lesson_session_media( $lesson_id, $cohort_id );

		$lecture_version = self::lecture_version_for_cohort( $lesson_id, $cohort_id );

		return array(
			'lecture_url'     => (string) $lecture_version['audio'],
			'lecture_guid'    => (string) $lecture_version['guid'],
			'lecture_release' => self::lecture_release_value( $lesson_id, $cohort_id ),
			'lecture_added'   => 0,
			'session_url'     => (string) ( $item['session_url'] ?? $session['audio'] ),
			'session_guid'    => (string) ( $item['session_guid'] ?? '' ),
			'session_release' => (string) ( $item['session_release'] ?? '' ),
			'session_added'   => absint( $item['session_added'] ?? 0 ),
		);
	}

	private static function local_datetime_timestamp( string $value ): int {
		if ( ! $value ) {
			return 0;
		}
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			$value .= 'T06:00';
		}
		$date = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $value, self::release_timezone() );
		return $date ? $date->getTimestamp() : 0;
	}

	private static function podcast_audio_details( string $url ): array {
		$attachment_id = attachment_url_to_postid( $url );
		$mime = $attachment_id ? (string) get_post_mime_type( $attachment_id ) : '';
		$length = 0;

		if ( $attachment_id ) {
			$file = get_attached_file( $attachment_id );
			if ( $file && is_readable( $file ) ) {
				$size = filesize( $file );
				$length = false === $size ? 0 : (int) $size;
			}
		}

		if ( ! $mime ) {
			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			$extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
			$mime = match ( $extension ) {
				'm4a', 'mp4' => 'audio/mp4',
				'wav'        => 'audio/wav',
				'ogg', 'oga' => 'audio/ogg',
				default      => 'audio/mpeg',
			};
		}

		return array(
			'url'    => $url,
			'mime'   => $mime,
			'length' => $length,
		);
	}

	private static function podcast_episode_title( WP_Post $lesson, string $kind ): string {
		$title = trim( self::clean_title( $lesson->ID ) );

		if ( ! $title ) {
			$title = self::lesson_display_title( $lesson->ID );
		}

		return 'session' === $kind
			? $title . ' — Live Session'
			: $title;
	}

	private static function podcast_episode_description( WP_Post $lesson, string $kind ): string {
		$excerpt = trim( (string) get_the_excerpt( $lesson ) );

		if ( $excerpt ) {
			return $excerpt;
		}

		$label = 'lecture' === $kind ? 'Lecture audio' : 'Live-session audio';
		return $label . ' for ' . self::lesson_display_title( $lesson->ID ) . '.';
	}

	public static function podcast_episodes( int $cohort_id ): array {
		$episodes = array();
		$now = current_time( 'timestamp' );

		foreach ( self::get_cohort_lessons( $cohort_id ) as $lesson ) {
			$media = self::lesson_podcast_media( $lesson->ID, $cohort_id );
			$lesson_release = self::lesson_release_at( $lesson->ID, $cohort_id );

			if ( $media['lecture_url'] ) {
				$release = self::local_datetime_timestamp( $media['lecture_release'] );
				if ( ! $release ) {
					$release = get_post_timestamp( $lesson ) ?: current_time( 'timestamp' );
				}

				if ( $release <= $now ) {
					$episodes[] = array(
						'lesson'      => $lesson,
						'kind'        => 'lecture',
						'guid'        => 'yowm:' . $cohort_id . ':lecture:' . $media['lecture_guid'],
						'title'       => self::podcast_episode_title( $lesson, 'lecture' ),
						'published'   => $release,
						'audio'       => self::podcast_audio_details( $media['lecture_url'] ),
						'description' => self::podcast_episode_description( $lesson, 'lecture' ),
					);
				}
			}

			if ( $media['session_url'] ) {
				$release = self::local_datetime_timestamp( $media['session_release'] );
				if ( ! $release ) {
					$release = $media['session_added'] ?: $lesson_release ?: get_post_timestamp( $lesson );
				}

				if ( $release <= $now ) {
					$episodes[] = array(
						'lesson'      => $lesson,
						'kind'        => 'session',
						'guid'        => 'yowm:' . $cohort_id . ':session:' . $media['session_guid'],
						'title'       => self::podcast_episode_title( $lesson, 'session' ),
						'published'   => $release,
						'audio'       => self::podcast_audio_details( $media['session_url'] ),
						'description' => self::podcast_episode_description( $lesson, 'session' ),
					);
				}
			}
		}

		usort(
			$episodes,
			static fn( array $a, array $b ): int =>
				$b['published'] <=> $a['published']
		);

		return $episodes;
	}

	public static function maybe_render_podcast_feed(): void {
		$is_query_feed = ! empty( $_GET['yowm_podcast'] );

		if ( $is_query_feed ) {
			$year = isset( $_GET['yowm_podcast_year'] )
				? absint( $_GET['yowm_podcast_year'] )
				: 0;
			$requested_token = isset( $_GET['yowm_podcast_token'] )
				? sanitize_text_field( wp_unslash( $_GET['yowm_podcast_token'] ) )
				: '';
		} else {
			$route = self::request_route();
			$year = absint( $route['year'] );
			$requested_token = (string) $route['podcast_token'];
		}

		if ( ! $year || ! $requested_token ) {
			return;
		}

		$cohort = self::get_cohort_by_year( $year );
		$token  = $cohort ? (string) get_post_meta( $cohort->ID, self::META_PODCAST_TOKEN, true ) : '';

		if (
			! $cohort
			|| ! get_post_meta( $cohort->ID, self::META_PODCAST_ENABLED, true )
			|| ! $token
			|| ! hash_equals( $token, $requested_token )
		) {
			status_header( 404 );
			nocache_headers();
			echo 'Podcast feed not found.';
			exit;
		}

		self::render_podcast_feed( $cohort );
		exit;
	}

	public static function render_podcast_feed( WP_Post $cohort, string $feed_url = '' ): void {
		$year        = self::cohort_year( $cohort->ID );
		$feed_url    = $feed_url ?: self::podcast_feed_url( $cohort->ID );
		$title       = trim( (string) get_post_meta( $cohort->ID, self::META_PODCAST_TITLE, true ) );
		$description = trim( (string) get_post_meta( $cohort->ID, self::META_PODCAST_DESC, true ) );
		$author      = trim( (string) get_post_meta( $cohort->ID, self::META_PODCAST_AUTHOR, true ) );
		$artwork_id  = absint( get_post_meta( $cohort->ID, self::META_PODCAST_ARTWORK, true ) );
		$image       = $artwork_id
			? wp_get_attachment_image_url( $artwork_id, 'full' )
			: get_the_post_thumbnail_url( $cohort->ID, 'full' );
		$episodes    = self::podcast_episodes( $cohort->ID );

		$title = $title ?: self::clean_title( $cohort->ID ) . ' Audio';
		$author = $author ?: 'Lani Diane Rich';
		$description = $description ?: wp_trim_words(
			wp_strip_all_tags( (string) $cohort->post_content ),
			45,
			''
		);
		$description = $description ?: 'Private audio feed for the Year of Writing Magically ' . $year . ' cohort.';

		$newest = $episodes ? max( array_column( $episodes, 'published' ) ) : get_post_timestamp( $cohort );
		$etag = '"' . md5( $cohort->ID . '|' . $newest . '|' . count( $episodes ) . '|' . $title . '|' . $description . '|' . $image ) . '"';

		nocache_headers();
		header( 'Content-Type: application/rss+xml; charset=' . get_option( 'blog_charset' ), true );
		header( 'Cache-Control: no-cache, must-revalidate, max-age=0', true );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $newest ) . ' GMT', true );
		header( 'ETag: ' . $etag, true );
		header( 'X-Robots-Tag: noindex, nofollow', true );

		echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?>' . "\n";
		?>
<rss version="2.0"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
	xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
	<title><?php echo esc_xml( $title ); ?></title>
	<link><?php echo esc_url( home_url( '/' . $year . '/' ) ); ?></link>
	<atom:link href="<?php echo esc_url( $feed_url ); ?>" rel="self" type="application/rss+xml" />
	<description><?php echo esc_xml( $description ); ?></description>
	<language><?php echo esc_xml( get_bloginfo( 'language' ) ?: 'en-US' ); ?></language>
	<lastBuildDate><?php echo esc_xml( gmdate( DATE_RSS, $newest ) ); ?></lastBuildDate>
	<ttl>15</ttl>
	<itunes:block>Yes</itunes:block>
	<itunes:author><?php echo esc_xml( $author ); ?></itunes:author>
	<itunes:summary><?php echo esc_xml( $description ); ?></itunes:summary>
	<itunes:owner><itunes:name><?php echo esc_xml( $author ); ?></itunes:name></itunes:owner>
	<itunes:explicit>false</itunes:explicit>
	<?php if ( $image ) : ?>
	<image>
		<url><?php echo esc_url( $image ); ?></url>
		<title><?php echo esc_xml( $title ); ?></title>
		<link><?php echo esc_url( home_url( '/' . $year . '/' ) ); ?></link>
	</image>
	<itunes:image href="<?php echo esc_url( $image ); ?>" />
	<?php endif; ?>

	<?php foreach ( $episodes as $episode ) : ?>
	<item>
		<title><?php echo esc_xml( $episode['title'] ); ?></title>
		<link><?php echo esc_url( get_permalink( $episode['lesson'] ) ); ?></link>
		<guid isPermaLink="false"><?php echo esc_xml( $episode['guid'] ); ?></guid>
		<pubDate><?php echo esc_xml( gmdate( DATE_RSS, $episode['published'] ) ); ?></pubDate>
		<description><?php echo esc_xml( $episode['description'] ); ?></description>
		<content:encoded><![CDATA[<?php echo wp_kses_post( wpautop( $episode['description'] ) ); ?><p><a href="<?php echo esc_url( home_url( '/' . $year . '/' ) ); ?>"><?php echo esc_html( (string) $year ); ?> classroom &rarr;</a></p>]]></content:encoded>
		<enclosure url="<?php echo esc_url( $episode['audio']['url'] ); ?>" length="<?php echo esc_attr( (string) $episode['audio']['length'] ); ?>" type="<?php echo esc_attr( $episode['audio']['mime'] ); ?>" />
		<itunes:summary><?php echo esc_xml( $episode['description'] ); ?></itunes:summary>
		<itunes:episodeType>full</itunes:episodeType>
		<itunes:explicit>false</itunes:explicit>
		<?php if ( $image ) : ?><itunes:image href="<?php echo esc_url( $image ); ?>" /><?php endif; ?>
	</item>
	<?php endforeach; ?>
</channel>
</rss>
		<?php
	}

	public static function lesson_release_at( int $lesson_id, int $cohort_id ): int {
		$releases = get_post_meta( $lesson_id, self::META_RELEASES, true );

		if ( ! is_array( $releases ) || empty( $releases[ $cohort_id ] ) ) {
			return 0;
		}

		$value = (string) $releases[ $cohort_id ];
		$date = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $value, self::release_timezone() );

		return $date ? $date->getTimestamp() : 0;
	}

	public static function lesson_is_released( int $lesson_id, int $cohort_id ): bool {
		if ( current_user_can( 'edit_posts' ) ) {
			return true;
		}

		$release_at = self::lesson_release_at( $lesson_id, $cohort_id );
		return 0 === $release_at || $release_at <= current_time( 'timestamp' );
	}

	public static function cohort_lesson_timeline( int $cohort_id ): array {
		$lessons = self::get_cohort_lessons( $cohort_id );
		$released = array();
		$upcoming = array();
		$now = current_time( 'timestamp' );

		foreach ( $lessons as $lesson ) {
			$release_at = self::lesson_release_at( $lesson->ID, $cohort_id );

			if ( 0 === $release_at || $release_at <= $now ) {
				$released[] = array(
					'lesson'     => $lesson,
					'release_at' => $release_at,
				);
			} else {
				$upcoming[] = array(
					'lesson'     => $lesson,
					'release_at' => $release_at,
				);
			}
		}

		usort(
			$released,
			static fn( array $a, array $b ): int =>
				( $b['release_at'] ?: get_post_timestamp( $b['lesson'] ) )
				<=>
				( $a['release_at'] ?: get_post_timestamp( $a['lesson'] ) )
		);

		usort(
			$upcoming,
			static fn( array $a, array $b ): int =>
				$a['release_at'] <=> $b['release_at']
		);

		return array(
			'released' => $released,
			'upcoming' => $upcoming,
		);
	}

	public static function youtube_embed_url( string $url ): string {
		$url = trim( $url );

		if ( ! $url ) {
			return '';
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) ) {
			return '';
		}

		$host = strtolower( (string) ( $parts['host'] ?? '' ) );
		$path = trim( (string) ( $parts['path'] ?? '' ), '/' );
		$id   = '';

		if ( str_contains( $host, 'youtu.be' ) ) {
			$id = explode( '/', $path )[0] ?? '';
		} elseif ( str_contains( $host, 'youtube.com' ) || str_contains( $host, 'youtube-nocookie.com' ) ) {
			if ( str_starts_with( $path, 'embed/' ) ) {
				$id = explode( '/', substr( $path, 6 ) )[0] ?? '';
			} elseif ( str_starts_with( $path, 'live/' ) ) {
				$id = explode( '/', substr( $path, 5 ) )[0] ?? '';
			} elseif ( 'watch' === $path ) {
				parse_str( (string) ( $parts['query'] ?? '' ), $query );
				$id = (string) ( $query['v'] ?? '' );
			} elseif ( str_starts_with( $path, 'shorts/' ) ) {
				$id = explode( '/', substr( $path, 7 ) )[0] ?? '';
			}
		}

		$id = preg_replace( '/[^A-Za-z0-9_-]/', '', $id );
		return $id ? 'https://www.youtube-nocookie.com/embed/' . $id : '';
	}

	public static function lesson_session_media( int $lesson_id, int $cohort_id ): array {
		$media = get_post_meta( $lesson_id, self::META_SESSION_MEDIA, true );

		if ( is_array( $media ) && isset( $media[ $cohort_id ] ) && is_array( $media[ $cohort_id ] ) ) {
			return array(
				'video' => (string) ( $media[ $cohort_id ]['video'] ?? '' ),
				'audio' => (string) ( $media[ $cohort_id ]['audio'] ?? '' ),
			);
		}

		$legacy_cohort = absint( get_post_meta( $lesson_id, self::META_COHORT, true ) );
		$legacy_video  = (string) get_post_meta( $lesson_id, self::META_SESSION_VIDEO, true );
		$legacy_audio  = (string) get_post_meta( $lesson_id, self::META_SESSION_AUDIO, true );

		if ( $legacy_cohort === $cohort_id ) {
			return array( 'video' => $legacy_video, 'audio' => $legacy_audio );
		}

		// Some earlier editor versions saved a generic YouTube meta value without
		// retaining the legacy cohort ID. Use it only when this Lesson belongs to
		// one explicit cohort, avoiding accidental cross-cohort video leakage.
		$scoped_cohorts = self::lesson_cohort_ids( $lesson_id );
		if ( 1 === count( $scoped_cohorts ) && $cohort_id === (int) $scoped_cohorts[0] ) {
			return array( 'video' => $legacy_video, 'audio' => $legacy_audio );
		}

		return array( 'video' => '', 'audio' => '' );
	}

	public static function get_cohort_lessons( int $cohort_id, string $order = 'ASC' ): array {
		$lessons = get_posts(
			array(
				'post_type'      => self::LESSON,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'date'       => $order,
				),
				'order'          => $order,
			)
		);

		$lessons = array_values(
			array_filter(
				$lessons,
				static fn( WP_Post $lesson ): bool =>
					self::lesson_applies_to_cohort( $lesson->ID, $cohort_id )
			)
		);

		usort(
			$lessons,
			static function ( WP_Post $a, WP_Post $b ): int {
				$module_a = self::lesson_module_name( $a->ID );
				$module_b = self::lesson_module_name( $b->ID );
				$cmp      = strcasecmp( $module_a, $module_b );

				if ( 0 !== $cmp ) {
					return $cmp;
				}

				$num_a = (string) get_post_meta( $a->ID, self::META_NUMBER, true );
				$num_b = (string) get_post_meta( $b->ID, self::META_NUMBER, true );
				return strnatcasecmp( $num_a, $num_b );
			}
		);

		return $lessons;
	}

	public static function get_recent_lessons( int $cohort_id, int $limit = 6 ): array {
		$lessons = get_posts(
			array(
				'post_type'      => self::LESSON,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$lessons = array_values(
			array_filter(
				$lessons,
				static fn( WP_Post $lesson ): bool =>
					self::lesson_applies_to_cohort( $lesson->ID, $cohort_id )
			)
		);

		return array_slice( $lessons, 0, $limit );
	}

	public static function get_cohort_resources( int $cohort_id ): array {
		$all = get_posts(
			array(
				'post_type'      => self::RESOURCE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
			)
		);

		return array_values(
			array_filter(
				$all,
				static function ( WP_Post $resource ) use ( $cohort_id ): bool {
					$ids = self::resource_cohort_ids( $resource->ID );
					return empty( $ids ) || in_array( $cohort_id, $ids, true );
				}
			)
		);
	}

	public static function get_lesson_for_cohort_by_slug( int $cohort_id, string $slug ): ?WP_Post {
		$posts = get_posts(
			array(
				'post_type'      => self::LESSON,
				'post_status'    => 'publish',
				'name'           => $slug,
				'posts_per_page' => 1,
			)
		);

		if ( ! $posts ) {
			return null;
		}

		if ( ! self::lesson_applies_to_cohort( $posts[0]->ID, $cohort_id ) ) {
			return null;
		}

		return self::lesson_is_released( $posts[0]->ID, $cohort_id ) ? $posts[0] : null;
	}

	public static function get_resource_for_cohort_by_slug( int $cohort_id, string $slug ): ?WP_Post {
		$posts = get_posts(
			array(
				'post_type'      => self::RESOURCE,
				'post_status'    => 'publish',
				'name'           => $slug,
				'posts_per_page' => 1,
			)
		);
		if ( ! $posts ) {
			return null;
		}
		$ids = self::resource_cohort_ids( $posts[0]->ID );
		$allowed = empty( $ids ) || in_array( $cohort_id, $ids, true );

		return $allowed && 'page' === self::resource_type( $posts[0]->ID ) ? $posts[0] : null;
	}

	public static function lesson_module_name( int $lesson_id ): string {
		$terms = get_the_terms( $lesson_id, self::MODULE );
		return is_array( $terms ) && $terms ? $terms[0]->name : 'Lessons';
	}

	public static function lesson_display_title( int $lesson_id ): string {
		$title  = get_post_field( 'post_title', $lesson_id );
		$number = trim( (string) get_post_meta( $lesson_id, self::META_NUMBER, true ) );
		$module = self::lesson_module_name( $lesson_id );
		$prefix = trim( $module . ' ' . $number );
		return $prefix ? $prefix . ': ' . $title : $title;
	}

	public static function clean_title( int $post_id ): string {
		return (string) get_post_field( 'post_title', $post_id );
	}

	public static function resource_type( int $resource_id ): string {
		$type = (string) get_post_meta( $resource_id, self::META_RESOURCE_TYPE, true );
		return in_array( $type, array( 'link', 'card', 'page', 'podcast' ), true ) ? $type : 'page';
	}

	public static function resource_url( int $resource_id, int $year ): string {
		$url = trim( (string) get_post_meta( $resource_id, self::META_RESOURCE_URL, true ) );

		if ( 'page' === self::resource_type( $resource_id ) ) {
			return home_url( '/' . $year . '/resources/' . get_post_field( 'post_name', $resource_id ) . '/' );
		}
		if ( 'podcast' === self::resource_type( $resource_id ) ) {
			$cohort = self::get_cohort_by_year( $year );
			if ( ! $cohort ) {
				return '';
			}
			if ( class_exists( 'YOWM_Student_Access' ) ) {
				$personal = YOWM_Student_Access::current_user_feed_url( $cohort->ID );
				if ( $personal ) {
					return $personal;
				}
			}
			return current_user_can( 'edit_posts' ) ? self::podcast_feed_url( $cohort->ID ) : '';
		}

		if ( $url && str_starts_with( $url, '/' ) ) {
			return home_url( $url );
		}

		return $url;
	}

	public static function resource_text( int $resource_id ): string {
		$excerpt = trim( (string) get_post_field( 'post_excerpt', $resource_id ) );
		if ( $excerpt ) {
			return $excerpt;
		}

		$text = trim( (string) get_post_meta( $resource_id, self::META_RESOURCE_TEXT, true ) );
		if ( $text ) {
			return $text;
		}

		return wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $resource_id ) ), 28 );
	}

	public static function resource_card_content( int $resource_id ): string {
		$content = trim( (string) get_post_field( 'post_content', $resource_id ) );

		if ( $content ) {
			return (string) apply_filters( 'the_content', $content );
		}

		$legacy = trim( (string) get_post_meta( $resource_id, self::META_RESOURCE_TEXT, true ) );
		return $legacy ? wpautop( esc_html( $legacy ) ) : '';
	}

	public static function resource_cohort_ids( int $resource_id ): array {
		$value = get_post_meta( $resource_id, self::META_RESOURCE_COHORT, true );
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_values( array_unique( array_filter( array_map( 'absint', $value ) ) ) );
	}

	public static function display_title( string $title, int $post_id ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $title;
		}
		return self::LESSON === get_post_type( $post_id )
			? self::lesson_display_title( $post_id )
			: $title;
	}

	private static function request_is_classroom_route(): bool {
		$route = self::request_route();

		if ( ! empty( $route['year'] ) ) {
			return true;
		}

		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );

		return (bool) preg_match( '#^(20\d{2})(?:/|$)#', $path )
			|| str_starts_with( $path, 'wp-admin/admin-post.php' )
				&& isset( $_REQUEST['action'] )
				&& in_array( sanitize_key( (string) wp_unslash( $_REQUEST['action'] ) ), array( 'yowm_unlock', 'yowm_lock' ), true );
	}

	public static function send_private_classroom_headers(): void {
		if ( ! self::request_is_classroom_route() ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}
		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true );
		}

		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private', true );
		header( 'Pragma: no-cache', true );
		header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT', true );
		header( 'Vary: Cookie', false );
		header( 'X-YOWM-Cache: bypass', true );
	}

	private static function access_signature( int $cohort_id, string $hash ): string {
		return hash_hmac( 'sha256', 'cohort|' . $cohort_id . '|' . $hash, wp_salt( 'auth' ) );
	}

	private static function set_access_cookie( int $cohort_id, string $value, int $expires ): void {
		$options = array(
			'expires'  => $expires,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		);

		setcookie( self::cookie_name( $cohort_id ), $value, $options );

		if ( $expires > time() ) {
			$_COOKIE[ self::cookie_name( $cohort_id ) ] = $value;
		} else {
			unset( $_COOKIE[ self::cookie_name( $cohort_id ) ] );
		}
	}

	private static function login_rate_key( int $cohort_id ): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		return 'yowm_login_' . md5( $cohort_id . '|' . $ip );
	}

	private static function login_is_rate_limited( int $cohort_id ): bool {
		$attempts = absint( get_transient( self::login_rate_key( $cohort_id ) ) );
		return $attempts >= 12;
	}

	private static function record_failed_login( int $cohort_id ): void {
		$key      = self::login_rate_key( $cohort_id );
		$attempts = absint( get_transient( $key ) );
		set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
	}

	private static function clear_failed_logins( int $cohort_id ): void {
		delete_transient( self::login_rate_key( $cohort_id ) );
	}

	public static function cohort_has_password( int $cohort_id ): bool {
		return (bool) get_post_meta( $cohort_id, self::META_PASSWORD_HASH, true );
	}

	private static function cookie_name( int $cohort_id ): string {
		return 'yowm_studio_access_' . $cohort_id;
	}

	public static function has_access( ?int $cohort_id = null ): bool {
		if ( ! $cohort_id ) {
			$year = absint( get_query_var( 'yowm_cohort_year' ) );

			if ( ! $year ) {
				$route = self::request_route();
				$year  = absint( $route['year'] ?? 0 );
			}

			if ( $year ) {
				$cohort = self::get_cohort_by_year( $year );
				$cohort_id = $cohort ? (int) $cohort->ID : 0;
			}
		}

		if ( ! $cohort_id || ! is_user_logged_in() ) {
			return false;
		}

		$user_id = get_current_user_id();

		if ( user_can( $user_id, 'edit_posts' ) ) {
			return true;
		}

		$memberships = get_user_meta( $user_id, '_yowm_cohort_memberships', true );

		return is_array( $memberships )
			&& isset( $memberships[ $cohort_id ] )
			&& 'active' === sanitize_key( (string) $memberships[ $cohort_id ] );
	}

	public static function handle_unlock(): void {
		$redirect = isset( $_REQUEST['redirect_to'] )
			? wp_validate_redirect( esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ), home_url( '/' ) )
			: home_url( '/' );
		wp_safe_redirect( wp_login_url( $redirect ) );
		exit;
	}

	public static function handle_lock(): void {
		wp_logout();
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	public static function lock_url( int $cohort_id ): string {
		return wp_logout_url( home_url( '/' ) );
	}

	public static function admin_menu(): void {
		add_menu_page( 'YOWM Studio', 'YOWM Studio', 'edit_posts', 'yowm-studio', array( __CLASS__, 'dashboard' ), 'dashicons-welcome-learn-more', 3 );
		add_submenu_page( 'yowm-studio', 'Dashboard', 'Dashboard', 'edit_posts', 'yowm-studio', array( __CLASS__, 'dashboard' ) );
		add_submenu_page( 'yowm-studio', 'Cohorts', 'Cohorts', 'edit_posts', 'edit.php?post_type=' . self::COHORT );
		add_submenu_page( 'yowm-studio', 'Modules', 'Modules', 'manage_categories', 'edit-tags.php?taxonomy=' . self::MODULE . '&post_type=' . self::LESSON );
		add_submenu_page( 'yowm-studio', 'Lessons', 'Lessons', 'edit_posts', 'edit.php?post_type=' . self::LESSON );
		add_submenu_page( 'yowm-studio', 'Class Info', 'Class Info', 'edit_posts', 'yowm-studio-resources', array( __CLASS__, 'resources_page' ) );
		add_submenu_page( 'yowm-studio', 'Settings', 'Settings', 'manage_options', 'yowm-studio-settings', array( __CLASS__, 'settings_page' ) );

		add_submenu_page(
			'yowm-studio',
			'Students',
			'Students',
			'manage_options',
			'yowm-student-access',
			array( 'YOWM_Student_Access', 'admin_page' )
		);

		add_submenu_page(
			'yowm-studio',
			'System Diagnostics',
			'System Diagnostics',
			'manage_options',
			'yowm-system-diagnostics',
			array( __CLASS__, 'system_diagnostics' )
		);
	}

	public static function keep_studio_menu_open( string $parent_file ): string {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return $parent_file;
		}

		if (
			in_array( $screen->post_type, array( self::COHORT, self::LESSON, self::RESOURCE ), true )
			|| self::MODULE === $screen->taxonomy
		) {
			return 'yowm-studio';
		}

		return $parent_file;
	}

	public static function highlight_studio_submenu( ?string $submenu_file ): ?string {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return $submenu_file;
		}

		if ( self::COHORT === $screen->post_type ) {
			return 'edit.php?post_type=' . self::COHORT;
		}

		if ( self::LESSON === $screen->post_type && self::MODULE !== $screen->taxonomy ) {
			return 'edit.php?post_type=' . self::LESSON;
		}

		if ( self::RESOURCE === $screen->post_type || 'yowm-studio_page_yowm-studio-resources' === $screen->id ) {
			return 'yowm-studio-resources';
		}

		if ( self::MODULE === $screen->taxonomy ) {
			return 'edit-tags.php?taxonomy=' . self::MODULE . '&post_type=' . self::LESSON;
		}

		return $submenu_file;
	}

	public static function admin_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		if ( str_contains( (string) $screen->id, 'yowm' ) || in_array( $screen->post_type, array( self::COHORT, self::LESSON, self::RESOURCE ), true ) ) {
			wp_enqueue_style( 'yowm-studio-admin', YOWM_STUDIO_URL . 'assets/admin.css', array(), YOWM_STUDIO_VERSION );
			wp_enqueue_media();
			wp_enqueue_script( 'yowm-studio-admin', YOWM_STUDIO_URL . 'assets/admin.js', array(), YOWM_STUDIO_VERSION, true );
		}
	}

	public static function authentication_diagnostics(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$cohorts = get_posts(
			array(
				'post_type'      => self::COHORT,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'DESC',
			)
		);

		echo '<div class="wrap yowm-admin"><h1>Classroom Authentication</h1>';
		echo '<p>YOWM Studio uses a separate signed browser cookie for every cohort. WordPress post passwords are not used.</p>';
		echo '<div class="yowm-auth-summary"><strong>Protected classroom cache policy:</strong> no-store, private, Vary: Cookie.</div>';
		echo '<table class="widefat striped yowm-auth-table"><thead><tr><th>Cohort</th><th>YOWM password</th><th>WordPress password</th><th>This browser</th><th>Classroom URL</th></tr></thead><tbody>';

		foreach ( $cohorts as $cohort ) {
			$year       = self::cohort_year( $cohort->ID );
			$has_yowm   = self::cohort_has_password( $cohort->ID );
			$has_native = '' !== (string) $cohort->post_password;
			$has_cookie = self::has_access( $cohort->ID );
			$url        = $year ? home_url( '/' . $year . '/' ) : get_permalink( $cohort );

			echo '<tr>';
			echo '<td><strong>' . esc_html( self::clean_title( $cohort->ID ) ) . '</strong></td>';
			echo '<td>' . ( $has_yowm ? '<span class="yowm-ok">Configured</span>' : '<span class="yowm-warn">Public / missing</span>' ) . '</td>';
			echo '<td>' . ( $has_native ? '<span class="yowm-warn">Found — update the Cohort to remove it</span>' : '<span class="yowm-ok">None</span>' ) . '</td>';
			echo '<td>' . ( $has_cookie ? '<span class="yowm-ok">Unlocked</span>' : '<span>Locked</span>' ) . '</td>';
			echo '<td><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">Open classroom</a></td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '<h2>Hostinger cache exclusions</h2>';
		echo '<p>If Hostinger, LiteSpeed Cache, or a CDN has URL exclusions, exclude these patterns:</p>';
		echo '<pre><code>/20*/\n/wp-admin/admin-post.php?action=yowm_unlock\n/wp-admin/admin-post.php?action=yowm_lock</code></pre>';
		echo '</div>';
	}

	public static function capture_fatal_error(): void {
		$error = error_get_last();
		if ( ! $error || ! in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ), true ) ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			: '';

		update_option(
			self::OPTION_LAST_FATAL,
			array(
				'time'        => current_time( 'mysql', true ),
				'request_uri' => $request_uri,
				'type'        => (int) $error['type'],
				'message'     => (string) $error['message'],
				'file'        => (string) $error['file'],
				'line'        => (int) $error['line'],
				'php_version' => PHP_VERSION,
				'wp_version'  => get_bloginfo( 'version' ),
				'plugin'      => YOWM_STUDIO_VERSION,
			),
			false
		);
	}

	public static function system_diagnostics(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['yowm_clear_fatal'] ) ) {
			check_admin_referer( 'yowm_clear_fatal' );
			delete_option( self::OPTION_LAST_FATAL );
			echo '<div class="notice notice-success"><p>Stored fatal error cleared.</p></div>';
		}

		$fatal = get_option( self::OPTION_LAST_FATAL, array() );

		echo '<div class="wrap yowm-admin"><h1>YOWM System Diagnostics</h1>';
		echo '<p>This screen records the last fatal PHP error triggered while YOWM Studio is active.</p>';
		echo '<p><strong>Test procedure:</strong> open <code>/2026/</code> in another tab, reproduce the critical error, then reload this page.</p>';

		echo '<table class="widefat striped" style="max-width:1100px"><tbody>';
		echo '<tr><th style="width:220px">YOWM Studio</th><td>' . esc_html( YOWM_STUDIO_VERSION ) . '</td></tr>';
		echo '<tr><th>PHP</th><td>' . esc_html( PHP_VERSION ) . '</td></tr>';
		echo '<tr><th>WordPress</th><td>' . esc_html( get_bloginfo( 'version' ) ) . '</td></tr>';
		echo '<tr><th>Student Access class</th><td>' . ( class_exists( 'YOWM_Student_Access' ) ? 'Loaded' : 'Not loaded' ) . '</td></tr>';
		echo '<tr><th>Theme</th><td>' . esc_html( wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' ) ) . '</td></tr>';
		echo '</tbody></table>';

		echo '<h2>Last recorded fatal error</h2>';
		if ( ! is_array( $fatal ) || empty( $fatal['message'] ) ) {
			echo '<div class="notice notice-info inline"><p>No fatal error has been recorded since this diagnostic build was installed.</p></div>';
		} else {
			$fields = array(
				'Time (UTC)'     => $fatal['time'] ?? '',
				'Request'        => $fatal['request_uri'] ?? '',
				'Message'        => $fatal['message'] ?? '',
				'File'           => $fatal['file'] ?? '',
				'Line'           => $fatal['line'] ?? '',
				'Error type'     => $fatal['type'] ?? '',
				'PHP version'    => $fatal['php_version'] ?? '',
				'WP version'     => $fatal['wp_version'] ?? '',
				'Plugin version' => $fatal['plugin'] ?? '',
			);

			echo '<table class="widefat striped" style="max-width:1100px"><tbody>';
			foreach ( $fields as $label => $value ) {
				echo '<tr><th style="width:220px">' . esc_html( $label ) . '</th><td><code style="white-space:pre-wrap">' . esc_html( (string) $value ) . '</code></td></tr>';
			}
			echo '</tbody></table>';

			// One-click copy of the whole error as plain text (handler lives in admin.js).
			$copy_lines = array();
			foreach ( $fields as $label => $value ) {
				$copy_lines[] = $label . ': ' . (string) $value;
			}
			$copy_text = 'YOWM Studio ' . YOWM_STUDIO_VERSION . " fatal error\n" . implode( "\n", $copy_lines );

			echo '<p style="margin-top:16px"><button type="button" class="button" data-yowm-copy-url="' . esc_attr( $copy_text ) . '">Copy error details</button></p>';

			echo '<form method="post">';
			wp_nonce_field( 'yowm_clear_fatal' );
			echo '<button class="button" name="yowm_clear_fatal" value="1">Clear stored error</button>';
			echo '</form>';
		}

		echo '</div>';
	}

	public static function dashboard(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$items = array(
			array( 'Cohorts', self::COHORT ),
			array( 'Lessons', self::LESSON ),
			array( 'Class Info', self::RESOURCE ),
		);
		?>
		<div class="wrap yowm-wrap">
			<h1>YOWM Studio</h1>
			<p class="yowm-lede">Your cohorts, modules, lessons, resources, and protected classroom.</p>
			<div class="yowm-grid">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$count = wp_count_posts( $item[1] );
					$total = (int) ( $count->publish ?? 0 ) + (int) ( $count->draft ?? 0 );
					?>
					<section class="yowm-card">
						<p class="yowm-kicker"><?php echo esc_html( $item[0] ); ?></p>
						<p class="yowm-count"><?php echo esc_html( (string) $total ); ?></p>
						<p><a href="<?php echo esc_url( self::RESOURCE === $item[1] ? admin_url( 'admin.php?page=yowm-studio-resources' ) : admin_url( 'edit.php?post_type=' . $item[1] ) ); ?>">Manage</a> · <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $item[1] ) ); ?>">Add new</a></p>
					</section>
				<?php endforeach; ?>
				<?php $module_count = wp_count_terms( array( 'taxonomy' => self::MODULE, 'hide_empty' => false ) ); ?>
				<section class="yowm-card">
					<p class="yowm-kicker">Modules</p>
					<p class="yowm-count"><?php echo esc_html( is_wp_error( $module_count ) ? '0' : (string) $module_count ); ?></p>
					<p><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . self::MODULE . '&post_type=' . self::LESSON ) ); ?>">Manage modules</a></p>
				</section>
			</div>

			<?php
			$dashboard_cohorts = self::get_published_cohorts();
			if ( $dashboard_cohorts ) :
				$dashboard_cohort = $dashboard_cohorts[0];
				$dashboard_timeline = self::cohort_lesson_timeline( $dashboard_cohort->ID );
				$dashboard_next = $dashboard_timeline['upcoming'][0] ?? null;
			?>
				<section class="yowm-note">
					<h2>Next lesson</h2>
					<?php if ( $dashboard_next ) : ?>
						<p><strong><?php echo esc_html( self::lesson_display_title( $dashboard_next['lesson']->ID ) ); ?></strong></p>
						<p><?php echo esc_html( wp_date( 'F j, Y 	 g:i a', $dashboard_next['release_at'], wp_timezone() ) ); ?></p>
						<p><a href="<?php echo esc_url( get_edit_post_link( $dashboard_next['lesson']->ID ) ); ?>">Edit release details</a></p>
					<?php else : ?>
						<p>No future lesson is scheduled for <?php echo esc_html( self::clean_title( $dashboard_cohort->ID ) ); ?>.</p>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<section class="yowm-note">
				<h2>Front-facing classroom</h2>
				<p><a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">View classroom home</a></p>
				<p>Published cohorts appear there automatically. Cohort pages, lessons, and resources use one shared classroom password.</p>
			</section>
		</div>
		<?php
	}

	public static function duplicate_resource_url( int $resource_id ): string {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action'      => 'yowm_duplicate_resource',
					'resource_id' => $resource_id,
				),
				admin_url( 'admin-post.php' )
			),
			'yowm_duplicate_resource_' . $resource_id
		);
	}

	public static function resource_row_actions( array $actions, WP_Post $post ): array {
		if ( self::RESOURCE !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$actions['yowm_duplicate'] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( self::duplicate_resource_url( $post->ID ) ),
			esc_attr( 'Duplicate ' . self::clean_title( $post->ID ) ),
			esc_html( 'Duplicate' )
		);

		return $actions;
	}

	public static function duplicate_resource(): void {
		$resource_id = isset( $_GET['resource_id'] ) ? absint( $_GET['resource_id'] ) : 0;

		if (
			! $resource_id
			|| ! isset( $_GET['_wpnonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ),
				'yowm_duplicate_resource_' . $resource_id
			)
		) {
			wp_die( 'Invalid duplicate-resource request.' );
		}

		$source = get_post( $resource_id );

		if (
			! $source
			|| self::RESOURCE !== $source->post_type
			|| ! current_user_can( 'edit_post', $resource_id )
		) {
			wp_die( 'You do not have permission to duplicate this resource.' );
		}

		$new_id = wp_insert_post(
			array(
				'post_type'      => self::RESOURCE,
				'post_status'    => 'draft',
				'post_title'     => self::clean_title( $resource_id ) . ' — Copy',
				'post_content'   => $source->post_content,
				'post_excerpt'   => $source->post_excerpt,
				'post_author'    => get_current_user_id(),
				'menu_order'     => $source->menu_order,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			wp_die( esc_html( $new_id->get_error_message() ) );
		}

		$meta = get_post_meta( $resource_id );
		foreach ( $meta as $key => $values ) {
			if ( str_starts_with( $key, '_edit_' ) || '_wp_old_slug' === $key ) {
				continue;
			}

			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}

		$thumbnail_id = get_post_thumbnail_id( $resource_id );
		if ( $thumbnail_id ) {
			set_post_thumbnail( $new_id, $thumbnail_id );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'post'             => $new_id,
					'action'           => 'edit',
					'yowm_duplicated'  => 1,
				),
				admin_url( 'post.php' )
			)
		);
		exit;
	}

	private static function resource_admin_groups( array $resources, array $cohorts ): array {
		$groups = array(
			'all' => array(
				'label'     => 'All Cohorts',
				'resources' => array(),
			),
		);

		foreach ( $cohorts as $cohort ) {
			$groups[ (string) $cohort->ID ] = array(
				'label'     => self::clean_title( $cohort->ID ),
				'resources' => array(),
			);
		}

		foreach ( $resources as $resource ) {
			$cohort_ids = self::resource_cohort_ids( $resource->ID );

			if ( empty( $cohort_ids ) ) {
				$groups['all']['resources'][] = $resource;
				continue;
			}

			foreach ( $cohort_ids as $cohort_id ) {
				$key = (string) $cohort_id;

				if ( ! isset( $groups[ $key ] ) ) {
					$groups[ $key ] = array(
						'label'     => 'Archived cohort',
						'resources' => array(),
					);
				}

				$groups[ $key ]['resources'][] = $resource;
			}
		}

		return $groups;
	}

	public static function resources_page(): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$resources = get_posts(
			array(
				'post_type'      => self::RESOURCE,
				'post_status'    => array( 'publish', 'draft', 'private', 'future' ),
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
			)
		);

		$cohorts = get_posts(
			array(
				'post_type'      => self::COHORT,
				'post_status'    => array( 'publish', 'draft', 'private', 'future' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'DESC',
			)
		);

		$groups = self::resource_admin_groups( $resources, $cohorts );
		?>
		<div class="wrap yowm-wrap yowm-resources-admin">
			<div class="yowm-admin-heading">
				<div>
					<h1>Class Info</h1>
					<p class="yowm-lede">Class Info items are grouped by cohort. Global items appear under All Cohorts.</p>
				</div>
				<div class="yowm-admin-heading-actions">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::RESOURCE ) ); ?>">Add Class Info Item</a>
					<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . self::RESOURCE ) ); ?>">Standard WordPress list</a>
				</div>
			</div>

			<?php if ( empty( $resources ) ) : ?>
				<div class="yowm-note">
					<h2>No Class Info items yet</h2>
					<p>Create the first link, information card, or page for Class Info.</p>
				</div>
			<?php else : ?>
				<div class="yowm-resource-folders">
					<?php foreach ( $groups as $group_key => $group ) : ?>
						<?php if ( empty( $group['resources'] ) ) continue; ?>
						<details class="yowm-resource-folder" <?php echo 'all' === $group_key ? 'open' : ''; ?>>
							<summary>
								<span><?php echo esc_html( $group['label'] ); ?></span>
								<small><?php echo esc_html( count( $group['resources'] ) . ( 1 === count( $group['resources'] ) ? ' resource' : ' resources' ) ); ?></small>
							</summary>

							<div class="yowm-resource-folder-content">
								<table class="widefat striped yowm-resource-table">
									<thead>
										<tr>
											<th>Order</th>
											<th>Resource</th>
											<th>Type</th>
											<th>Status</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $group['resources'] as $resource ) : ?>
											<tr>
												<td class="yowm-order-cell"><?php echo esc_html( (string) $resource->menu_order ); ?></td>
												<td>
													<strong>
														<a href="<?php echo esc_url( get_edit_post_link( $resource->ID ) ); ?>">
															<?php echo esc_html( self::clean_title( $resource->ID ) ); ?>
														</a>
													</strong>
													<?php if ( self::resource_text( $resource->ID ) ) : ?>
														<p class="description"><?php echo esc_html( self::resource_text( $resource->ID ) ); ?></p>
													<?php endif; ?>
												</td>
												<td><?php echo esc_html( ucfirst( self::resource_type( $resource->ID ) ) ); ?></td>
												<td><?php echo esc_html( ucfirst( $resource->post_status ) ); ?></td>
												<td class="yowm-resource-actions">
													<a href="<?php echo esc_url( get_edit_post_link( $resource->ID ) ); ?>">Edit</a>
													<span aria-hidden="true">·</span>
													<a href="<?php echo esc_url( self::duplicate_resource_url( $resource->ID ) ); ?>">Duplicate</a>
													<?php if ( 'publish' === $resource->post_status && 'page' === self::resource_type( $resource->ID ) ) : ?>
														<span aria-hidden="true">·</span>
														<a href="<?php echo esc_url( get_permalink( $resource->ID ) ); ?>">View</a>
													<?php endif; ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</details>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$saved = isset( $_GET['settings-updated'] );
		?>
		<div class="wrap yowm-wrap">
			<h1>YOWM Studio Settings</h1>
			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="yowm-settings-form">
				<input type="hidden" name="action" value="yowm_save_settings">
				<?php wp_nonce_field( 'yowm_save_settings', 'yowm_settings_nonce' ); ?>


				<section class="yowm-note">
					<h2>Classroom home</h2>
					<?php self::field( 'text', 'yowm_home_title', 'Homepage title', get_option( self::OPTION_HOME_TITLE, 'The Year of Writing Magically' ) ); ?>
					<?php self::textarea( 'yowm_home_intro', 'Homepage introduction', get_option( self::OPTION_HOME_INTRO, 'Choose your cohort to enter the classroom.' ) ); ?>
					<?php self::field( 'url', 'yowm_signup_url', 'Public signup page', get_option( self::OPTION_SIGNUP_URL, 'https://lanidianerich.com/year-of-writing-magically.html' ) ); ?>
				</section>

				<?php submit_button( 'Save YOWM Studio settings' ); ?>
			</form>
		</div>
		<?php
	}

	public static function save_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do that.' );
		}

		if (
			! isset( $_POST['yowm_settings_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yowm_settings_nonce'] ) ), 'yowm_save_settings' )
		) {
			wp_die( 'Invalid request.' );
		}


		update_option(
			self::OPTION_HOME_TITLE,
			isset( $_POST['yowm_home_title'] ) ? sanitize_text_field( wp_unslash( $_POST['yowm_home_title'] ) ) : ''
		);
		update_option(
			self::OPTION_HOME_INTRO,
			isset( $_POST['yowm_home_intro'] ) ? sanitize_textarea_field( wp_unslash( $_POST['yowm_home_intro'] ) ) : ''
		);
		update_option(
			self::OPTION_SIGNUP_URL,
			isset( $_POST['yowm_signup_url'] ) ? esc_url_raw( wp_unslash( $_POST['yowm_signup_url'] ) ) : ''
		);

		wp_safe_redirect( admin_url( 'admin.php?page=yowm-studio-settings&settings-updated=1' ) );
		exit;
	}

	public static function meta_boxes(): void {
		add_meta_box( 'yowm-cohort-details', 'Cohort details', array( __CLASS__, 'cohort_box' ), self::COHORT, 'normal', 'high' );
		add_meta_box( 'yowm-lesson-details', 'Lesson details', array( __CLASS__, 'lesson_box' ), self::LESSON, 'normal', 'high' );
		add_meta_box( 'yowm-lesson-media', 'Lesson recordings', array( __CLASS__, 'lesson_media_box' ), self::LESSON, 'normal', 'default' );
		add_meta_box( 'yowm-resource-details', 'Resource details', array( __CLASS__, 'resource_box' ), self::RESOURCE, 'normal', 'high' );
	}

	public static function cohort_box( WP_Post $post ): void {
		wp_nonce_field( 'yowm_save', 'yowm_nonce' );
		self::field( 'number', 'yowm_cohort_year', 'Year', get_post_meta( $post->ID, self::META_COHORT_YEAR, true ), '2027' );
		self::field( 'date', 'yowm_start_date', 'Start date', get_post_meta( $post->ID, self::META_START_DATE, true ) );
		self::field( 'date', 'yowm_end_date', 'End date', get_post_meta( $post->ID, self::META_END_DATE, true ) );
		self::textarea( 'yowm_announcement', 'Current announcement', get_post_meta( $post->ID, self::META_ANNOUNCEMENT, true ) );
		echo '<div class="yowm-note"><h3>Student access</h3>';
		echo '<p>Classroom access is now managed with individual, invite-only student accounts.</p>';
		echo '<p><a class="button" href="' . esc_url( add_query_arg( array( 'page' => 'yowm-student-access', 'cohort_id' => $post->ID ), admin_url( 'admin.php' ) ) ) . '">Manage this cohort roster</a></p></div>';
		echo '<p class="yowm-field"><label for="yowm_welcome_text"><strong>Welcome text</strong></label>';
		echo '<textarea id="yowm_welcome_text" name="yowm_welcome_text" rows="4" class="large-text">' . esc_textarea( (string) $post->post_content ) . '</textarea>';
		echo '<span class="description">Shown at the top of the cohort classroom, under the year.</span></p>';

		$podcast_enabled = (bool) get_post_meta( $post->ID, self::META_PODCAST_ENABLED, true );
		$podcast_title   = get_post_meta( $post->ID, self::META_PODCAST_TITLE, true );
		$podcast_desc    = get_post_meta( $post->ID, self::META_PODCAST_DESC, true );
		$podcast_author  = get_post_meta( $post->ID, self::META_PODCAST_AUTHOR, true );
		$artwork_id      = absint( get_post_meta( $post->ID, self::META_PODCAST_ARTWORK, true ) );
		$artwork_url     = $artwork_id ? wp_get_attachment_image_url( $artwork_id, 'medium' ) : '';
		$feed_url        = self::podcast_feed_url( $post->ID );

		echo '<div class="yowm-note yowm-podcast-box"><h3>Private podcast feed</h3>';
		echo '<p class="description">One secret RSS URL for this cohort. Students paste it into a podcast app that supports adding a feed by URL. Regenerating the token revokes the old feed URL for everyone.</p>';
		echo '<p><label><input type="checkbox" name="yowm_podcast_enabled" value="1" ' . checked( $podcast_enabled, true, false ) . '> Enable this cohort podcast</label></p>';
		self::field( 'text', 'yowm_podcast_title', 'Podcast title', $podcast_title, self::clean_title( $post->ID ) . ' Audio' );
		self::field( 'text', 'yowm_podcast_author', 'Podcast author', $podcast_author, 'Lani Diane Rich' );
		self::textarea( 'yowm_podcast_description', 'Podcast description', $podcast_desc );

		echo '<div class="yowm-podcast-artwork">';
		echo '<p><strong>Podcast artwork</strong></p>';
		echo '<input type="hidden" id="yowm_podcast_artwork_id" name="yowm_podcast_artwork_id" value="' . esc_attr( (string) $artwork_id ) . '">';
		echo '<div class="yowm-podcast-artwork-preview" data-yowm-artwork-preview>';
		if ( $artwork_url ) {
			echo '<img src="' . esc_url( $artwork_url ) . '" alt="">';
		} else {
			echo '<span>No artwork selected</span>';
		}
		echo '</div>';
		echo '<p><button type="button" class="button" data-yowm-image-target="#yowm_podcast_artwork_id">Choose podcast artwork</button> ';
		echo '<button type="button" class="button" data-yowm-clear-image="#yowm_podcast_artwork_id">Remove artwork</button></p>';
		echo '<p class="description">Use a square image. Podcast apps generally look best with artwork at least 1400 × 1400 pixels.</p>';
		echo '</div>';

		echo $podcast_enabled
			? '<p class="yowm-password-status">✓ This cohort podcast is enabled.</p>'
			: '<p class="yowm-password-status yowm-password-missing">This cohort podcast is currently disabled. Enable it and update the Cohort before testing the URL.</p>';

		if ( $feed_url ) {
			echo '<p class="yowm-field"><label for="yowm_podcast_feed_url"><strong>Private RSS feed URL</strong></label>';
			echo '<span class="yowm-copy-field"><input id="yowm_podcast_feed_url" type="url" readonly value="' . esc_attr( $feed_url ) . '"><button type="button" class="button" data-yowm-copy="#yowm_podcast_feed_url">Copy</button></span></p>';
			echo '<p><a class="button" href="' . esc_url( $feed_url ) . '" target="_blank" rel="noopener noreferrer">Test feed in a new tab</a></p>';
		}

		echo '<p><label><input type="checkbox" name="yowm_regenerate_podcast_token" value="1"> Generate a new secret feed URL and revoke the old one</label></p>';
		echo '</div>';
	}

	public static function lesson_box( WP_Post $post ): void {
		wp_nonce_field( 'yowm_save', 'yowm_nonce' );

		$number  = get_post_meta( $post->ID, self::META_NUMBER, true );
		$saved   = self::lesson_cohort_ids( $post->ID );
		$cohorts = get_posts(
			array(
				'post_type'      => self::COHORT,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		$modules = get_terms(
			array(
				'taxonomy'   => self::MODULE,
				'hide_empty' => false,
				'orderby'    => 'name',
			)
		);
		$assigned_modules = wp_get_object_terms( $post->ID, self::MODULE, array( 'fields' => 'ids' ) );
		$assigned_module  = ! is_wp_error( $assigned_modules ) && $assigned_modules ? absint( $assigned_modules[0] ) : 0;
		?>
		<div class="yowm-fields-two">
			<div><?php self::field( 'text', 'yowm_lesson_number', 'Number', $number, '01' ); ?></div>
			<div>
				<p><strong>Who should see this lesson?</strong></p>
				<label>
					<input type="radio" name="yowm_lesson_scope" value="all" <?php checked( empty( $saved ) ); ?>>
					<strong>All cohorts</strong>
				</label>
				<label class="yowm-inline-choice">
					<input type="radio" name="yowm_lesson_scope" value="specific" <?php checked( ! empty( $saved ) ); ?>>
					<strong>Specific cohorts</strong>
				</label>
			</div>
		</div>

		<div class="yowm-checkboxes" data-yowm-lesson-cohort-choices <?php echo empty( $saved ) ? 'hidden' : ''; ?>>
			<?php foreach ( $cohorts as $cohort ) : ?>
				<label>
					<input type="checkbox" name="yowm_lesson_cohorts[]" value="<?php echo esc_attr( (string) $cohort->ID ); ?>" <?php checked( in_array( $cohort->ID, $saved, true ) ); ?>>
					<?php echo esc_html( self::clean_title( $cohort->ID ) ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<div class="yowm-module-picker">
			<p><strong>Module</strong></p>
			<?php if ( ! is_wp_error( $modules ) && $modules ) : ?>
				<div class="yowm-module-options">
					<?php foreach ( $modules as $module ) : ?>
						<label>
							<input type="radio" name="yowm_module_id" value="<?php echo esc_attr( (string) $module->term_id ); ?>" <?php checked( $assigned_module, $module->term_id ); ?>>
							<?php echo esc_html( $module->name ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="description">No modules exist yet. Create them under YOWM Studio → Modules.</p>
			<?php endif; ?>
		</div>

		<div class="yowm-episode-notes">
			<p><strong>Podcast episode notes</strong></p>
			<textarea name="yowm_episode_notes" rows="4" class="large-text" placeholder="Shown as the episode description in podcast apps, and as this lesson's excerpt."><?php echo esc_textarea( (string) $post->post_excerpt ); ?></textarea>
			<p class="description">Used as the podcast episode notes and the lesson excerpt. A link back to the cohort classroom is added automatically in the feed.</p>
		</div>

		<div class="yowm-preview">
			<strong>Reusable lesson:</strong> the Gutenberg post and post audio remain the same from year to year. Live-session video and audio are stored separately for each cohort.
		</div>
		<?php
	}

	public static function lesson_media_box( WP_Post $post ): void {
		$cohorts = get_posts(
			array(
				'post_type'      => self::COHORT,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'DESC',
			)
		);
		$versions = self::lecture_versions( $post->ID );
		$assignments = self::lecture_version_assignments( $post->ID );

		echo '<div class="yowm-lecture-versions"><h3>Lecture versions</h3>';
		echo '<p class="description">Earlier cohorts can keep an older recording while later cohorts use a revision. Archived versions remain active wherever they are assigned.</p>';
		echo '<p class="yowm-format-note"><strong>Podcast audio must be uploaded as MP3.</strong> YOWM Studio will warn you if another format is selected, but it will not convert files on the server.</p>';
		echo '<div data-yowm-version-list>';
		foreach ( $versions as $version_id => $version ) {
			self::lecture_version_editor( (string) $version_id, (array) $version );
		}
		echo '</div>';
		echo '<p><button type="button" class="button button-secondary" data-yowm-add-version>Add lecture version</button></p>';
		echo '<template data-yowm-version-template>';
		self::lecture_version_editor( '__VERSION_ID__', array( 'guid' => '', 'label' => '', 'audio' => '', 'transcript' => '', 'archived' => 0 ) );
		echo '</template></div>';

		echo '<div class="yowm-lecture-assignments"><h3>Cohort lecture assignments</h3>';
		echo '<p class="description">New cohorts begin with no lecture assigned. Podcast releases occur at ' . esc_html( self::release_timezone_label() ) . '.</p>';
		foreach ( $cohorts as $cohort ) {
			$assigned = sanitize_key( (string) ( $assignments[ $cohort->ID ] ?? '' ) );
			$release = self::lecture_release_value( $post->ID, $cohort->ID );
			echo '<div class="yowm-version-assignment-row">';
			echo '<div><strong>' . esc_html( self::clean_title( $cohort->ID ) ) . '</strong></div>';
			echo '<div><label for="yowm_lecture_assignment_' . esc_attr( (string) $cohort->ID ) . '"><strong>Lecture version</strong></label>';
			echo '<select data-yowm-version-assignment-select id="yowm_lecture_assignment_' . esc_attr( (string) $cohort->ID ) . '" name="yowm_lecture_assignment[' . esc_attr( (string) $cohort->ID ) . ']">';
			echo '<option value="">No lecture assigned</option>';
			foreach ( $versions as $version_id => $version ) {
				$label = trim( (string) ( $version['label'] ?? '' ) ) ?: 'Untitled version';
				if ( ! empty( $version['archived'] ) ) $label .= ' — Archived';
				echo '<option value="' . esc_attr( (string) $version_id ) . '" ' . selected( $assigned, (string) $version_id, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select></div><div>';
			self::field( 'date', 'yowm_release_' . $cohort->ID, 'Lecture release date', $release ? substr( $release, 0, 10 ) : '' );
			echo '<p class="description">Releases the written Lesson and podcast episode together. Blank = immediate. Time: ' . esc_html( self::release_timezone_label() ) . '.</p></div></div>';
		}
		echo '</div>';

		echo '<div class="yowm-session-recordings"><h3>Live-session recordings by cohort</h3>';
		echo '<p class="description">These stay unique to each cohort. Audio drops immediately unless you enter a future date and time.</p>';
		echo '<p class="yowm-format-note"><strong>Upload the MP3 version for podcast delivery.</strong></p>';
		foreach ( $cohorts as $cohort ) {
			$media = self::lesson_session_media( $post->ID, $cohort->ID );
			$podcast = self::lesson_podcast_media( $post->ID, $cohort->ID );
			echo '<details class="yowm-session-cohort"><summary>' . esc_html( self::clean_title( $cohort->ID ) ) . '</summary><div class="yowm-session-cohort-fields">';
			self::field( 'url', 'yowm_session_video_' . $cohort->ID, 'YouTube video URL', $media['video'] );
			echo '<div class="yowm-audio-field">';
			self::field( 'url', 'yowm_session_audio_' . $cohort->ID, 'Live-session audio URL', $podcast['session_url'] );
			echo '<button type="button" class="button" data-yowm-media-target="#yowm_session_audio_' . esc_attr( (string) $cohort->ID ) . '">Upload or choose audio</button></div>';
			self::field( 'datetime-local', 'yowm_session_audio_release_' . $cohort->ID, 'Optional future live-session release', $podcast['session_release'] );
			echo '<p class="description">Blank = immediate. Times use America/Denver.</p></div></details>';
		}
		echo '</div>';
	}

	private static function lecture_version_editor( string $version_id, array $version ): void {
		$guid = (string) ( $version['guid'] ?? '' );
		$label = (string) ( $version['label'] ?? '' );
		$audio = (string) ( $version['audio'] ?? '' );
		$transcript = (string) ( $version['transcript'] ?? '' );
		$archived = ! empty( $version['archived'] );
		echo '<details class="yowm-lecture-version" data-yowm-version-id="' . esc_attr( $version_id ) . '" ' . ( $archived ? '' : 'open' ) . '><summary><span data-yowm-version-summary>' . esc_html( $label ?: 'New lecture version' ) . '</span><small data-yowm-version-status>' . ( $archived ? 'Archived' : 'Current' ) . '</small></summary><div class="yowm-lecture-version-fields">';
		echo '<input type="hidden" name="yowm_lecture_versions[' . esc_attr( $version_id ) . '][id]" value="' . esc_attr( $version_id ) . '">';
		echo '<input type="hidden" name="yowm_lecture_versions[' . esc_attr( $version_id ) . '][guid]" value="' . esc_attr( $guid ) . '">';
		echo '<p class="yowm-field"><label for="yowm_version_label_' . esc_attr( $version_id ) . '"><strong>Version name</strong></label><input data-yowm-version-label type="text" id="yowm_version_label_' . esc_attr( $version_id ) . '" name="yowm_lecture_versions[' . esc_attr( $version_id ) . '][label]" value="' . esc_attr( $label ) . '" placeholder="2026 Original or 2027 Revised"></p>';
		echo '<div class="yowm-audio-field">';
		self::field( 'url', 'yowm_version_audio_' . $version_id, 'Lecture audio URL', $audio, '', 'yowm_lecture_versions[' . $version_id . '][audio]' );
		echo '<button type="button" class="button" data-yowm-media-target="#yowm_version_audio_' . esc_attr( $version_id ) . '">Upload or choose audio</button></div>';
		echo '<p class="yowm-field"><label for="yowm_version_transcript_' . esc_attr( $version_id ) . '"><strong>Lecture transcript</strong></label><textarea id="yowm_version_transcript_' . esc_attr( $version_id ) . '" name="yowm_lecture_versions[' . esc_attr( $version_id ) . '][transcript]" rows="12">' . esc_textarea( $transcript ) . '</textarea></p>';
		echo '<label><input data-yowm-version-archived type="checkbox" name="yowm_lecture_versions[' . esc_attr( $version_id ) . '][archived]" value="1" ' . checked( $archived, true, false ) . '> Archive this version in the backend</label>';
		echo '<p class="description">Archived versions remain available to cohorts already assigned to them.</p></div></details>';
	}


	public static function resource_box( WP_Post $post ): void {
		wp_nonce_field( 'yowm_save', 'yowm_nonce' );

		$type     = self::resource_type( $post->ID );
		$url      = get_post_meta( $post->ID, self::META_RESOURCE_URL, true );
		$text     = get_post_meta( $post->ID, self::META_RESOURCE_TEXT, true );
		$new_tab  = (bool) get_post_meta( $post->ID, self::META_RESOURCE_NEW_TAB, true );
		$saved    = self::resource_cohort_ids( $post->ID );
		$cohorts  = get_posts(
			array(
				'post_type'      => self::COHORT,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<div class="yowm-resource-type-picker">
			<p><strong>What kind of resource is this?</strong></p>
			<label>
				<input type="radio" name="yowm_resource_type" value="link" <?php checked( $type, 'link' ); ?>>
				<span><strong>Link</strong><small>Discord, Zoom, Google Calendar, YouTube, downloads</small></span>
			</label>
			<label>
				<input type="radio" name="yowm_resource_type" value="podcast" <?php checked( $type, 'podcast' ); ?>>
				<span><strong>Copy podcast feed</strong><small>Copies the current cohort's private RSS URL</small></span>
			</label>
			<label>
				<input type="radio" name="yowm_resource_type" value="card" <?php checked( $type, 'card' ); ?>>
				<span><strong>Information card</strong><small>Email addresses, meeting time, short reminders</small></span>
			</label>
			<label>
				<input type="radio" name="yowm_resource_type" value="page" <?php checked( $type, 'page' ); ?>>
				<span><strong>Page</strong><small>Schedule, class ethos, confidentiality, detailed instructions</small></span>
			</label>
		</div>

		<div data-yowm-resource-panel="link">
			<?php self::field( 'url', 'yowm_resource_url', 'Destination URL', $url, 'https:// or /path/to/file.zip' ); ?>
			<p>
				<label><input type="checkbox" name="yowm_resource_new_tab" value="1" <?php checked( $new_tab ); ?>> Open this link in a new tab</label>
			</p>
			<p>
				<label><input type="checkbox" name="yowm_resource_download" value="1" <?php checked( (bool) get_post_meta( $post->ID, self::META_RESOURCE_DOWNLOAD, true ) ); ?>> Treat this as a file download</label>
			</p>
		</div>

		<div data-yowm-resource-panel="podcast">
			<p class="description">No URL is needed. This button copies the current cohort's private podcast feed URL.</p>
		</div>
		<div data-yowm-resource-panel="card">
			<?php self::field( 'url', 'yowm_resource_card_url', 'Optional link or mailto URL', $url, 'https:// or mailto:' ); ?>
			<p class="description">Write and format the card content in the main Gutenberg editor. The formatted content appears directly on the cohort page. A URL is optional.</p>
		</div>

		<div data-yowm-resource-panel="page">
			<p class="description">Write the full resource in the main WordPress editor. The cohort card opens its own readable page.</p>
		</div>

		<hr>
		<div class="yowm-resource-availability">
			<p><strong>Who should see this resource?</strong></p>
			<label>
				<input type="radio" name="yowm_resource_scope" value="all" <?php checked( empty( $saved ) ); ?>>
				<strong>All cohorts</strong>
			</label>
			<label>
				<input type="radio" name="yowm_resource_scope" value="specific" <?php checked( ! empty( $saved ) ); ?>>
				<strong>Specific cohorts</strong>
			</label>

			<div class="yowm-checkboxes" data-yowm-cohort-choices>
				<?php foreach ( $cohorts as $cohort ) : ?>
					<label>
						<input type="checkbox" name="yowm_resource_cohorts[]" value="<?php echo esc_attr( (string) $cohort->ID ); ?>" <?php checked( in_array( $cohort->ID, $saved, true ) ); ?>>
						<?php echo esc_html( self::clean_title( $cohort->ID ) ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<hr>
		<?php self::field( 'number', 'yowm_resource_order', 'Display order', (string) $post->menu_order, '0' ); ?>
		<p class="description">Lower numbers appear first. Resources with the same number are sorted by title.</p>
		<?php
	}

	private static function field( string $type, string $name, string $label, $value, string $placeholder = '', string $input_name = '' ): void {
		$input_name = $input_name ?: $name;
		echo '<p class="yowm-field"><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $label ) . '</strong></label><input type="' . esc_attr( $type ) . '" id="' . esc_attr( $name ) . '" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( (string) $value ) . '" placeholder="' . esc_attr( $placeholder ) . '"></p>';
	}

	private static function textarea( string $name, string $label, $value ): void {
		echo '<p class="yowm-field"><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $label ) . '</strong></label><textarea id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" rows="4">' . esc_textarea( (string) $value ) . '</textarea></p>';
	}

	public static function save_meta( int $post_id, WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if (
			! isset( $_POST['yowm_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yowm_nonce'] ) ), 'yowm_save' )
		) {
			return;
		}

		$fields = array(
			'yowm_cohort_year'     => array( self::META_COHORT_YEAR, 'absint' ),
			'yowm_start_date'      => array( self::META_START_DATE, 'sanitize_text_field' ),
			'yowm_end_date'        => array( self::META_END_DATE, 'sanitize_text_field' ),
			'yowm_announcement'    => array( self::META_ANNOUNCEMENT, 'sanitize_textarea_field' ),
			'yowm_lesson_number'   => array( self::META_NUMBER, 'sanitize_text_field' ),
		);

		foreach ( $fields as $input => $settings ) {
			if ( isset( $_POST[ $input ] ) ) {
				$value = call_user_func( $settings[1], wp_unslash( $_POST[ $input ] ) );
				if ( '' === $value || 0 === $value ) {
					delete_post_meta( $post_id, $settings[0] );
				} else {
					update_post_meta( $post_id, $settings[0], $value );
				}
			}
		}

		// Lesson podcast episode notes live in the post excerpt. Detach this hook
		// while saving so wp_update_post doesn't trigger save_meta recursively.
		if ( self::LESSON === $post->post_type && isset( $_POST['yowm_episode_notes'] ) ) {
			$notes = sanitize_textarea_field( wp_unslash( $_POST['yowm_episode_notes'] ) );
			if ( $notes !== (string) $post->post_excerpt ) {
				remove_action( 'save_post', array( __CLASS__, 'save_meta' ), 10 );
				wp_update_post( array( 'ID' => $post_id, 'post_excerpt' => $notes ) );
				add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
			}
		}

		// Cohort welcome text lives in the post content (the block editor is hidden
		// for cohorts). Same detach-to-avoid-recursion pattern.
		if ( self::COHORT === $post->post_type && isset( $_POST['yowm_welcome_text'] ) ) {
			$welcome = wp_kses_post( wp_unslash( $_POST['yowm_welcome_text'] ) );
			if ( $welcome !== (string) $post->post_content ) {
				remove_action( 'save_post', array( __CLASS__, 'save_meta' ), 10 );
				wp_update_post( array( 'ID' => $post_id, 'post_content' => $welcome ) );
				add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
			}
		}

		if ( self::COHORT === $post->post_type ) {
			// WordPress's native post-password cookie remembers only one password.
			// Migrate any value entered through Quick Edit, then clear it so the
			// independent YOWM cohort-cookie system is the only gatekeeper.
			if ( ! empty( $post->post_password ) ) {
				if ( ! get_post_meta( $post_id, self::META_PASSWORD_HASH, true ) ) {
					update_post_meta(
						$post_id,
						self::META_PASSWORD_HASH,
						wp_hash_password( (string) $post->post_password )
					);
				}
				remove_action( 'save_post', array( __CLASS__, 'save_meta' ), 10 );
				wp_update_post( array( 'ID' => $post_id, 'post_password' => '' ) );
				add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
			}

			if ( ! empty( $_POST['yowm_remove_cohort_password'] ) ) {
				delete_post_meta( $post_id, self::META_PASSWORD_HASH );
			} elseif ( isset( $_POST['yowm_cohort_password'] ) && '' !== (string) $_POST['yowm_cohort_password'] ) {
				update_post_meta(
					$post_id,
					self::META_PASSWORD_HASH,
					wp_hash_password( (string) wp_unslash( $_POST['yowm_cohort_password'] ) )
				);
			}

			if ( ! get_post_meta( $post_id, self::META_COHORT_YEAR, true ) ) {
				if ( preg_match( '/\\b(20\\d{2})\\b/', $post->post_title, $matches ) ) {
					update_post_meta( $post_id, self::META_COHORT_YEAR, absint( $matches[1] ) );
				}
			}

			update_post_meta(
				$post_id,
				self::META_PODCAST_ENABLED,
				! empty( $_POST['yowm_podcast_enabled'] ) ? 1 : 0
			);
			update_post_meta(
				$post_id,
				self::META_PODCAST_TITLE,
				isset( $_POST['yowm_podcast_title'] )
					? sanitize_text_field( wp_unslash( $_POST['yowm_podcast_title'] ) )
					: ''
			);
			update_post_meta(
				$post_id,
				self::META_PODCAST_DESC,
				isset( $_POST['yowm_podcast_description'] )
					? sanitize_textarea_field( wp_unslash( $_POST['yowm_podcast_description'] ) )
					: ''
			);
			update_post_meta(
				$post_id,
				self::META_PODCAST_AUTHOR,
				isset( $_POST['yowm_podcast_author'] )
					? sanitize_text_field( wp_unslash( $_POST['yowm_podcast_author'] ) )
					: ''
			);
			update_post_meta(
				$post_id,
				self::META_PODCAST_ARTWORK,
				isset( $_POST['yowm_podcast_artwork_id'] )
					? absint( $_POST['yowm_podcast_artwork_id'] )
					: 0
			);

			if (
				! get_post_meta( $post_id, self::META_PODCAST_TOKEN, true )
				|| ! empty( $_POST['yowm_regenerate_podcast_token'] )
			) {
				update_post_meta( $post_id, self::META_PODCAST_TOKEN, self::generate_podcast_token() );
			}
		}

		if ( self::LESSON === $post->post_type ) {
			$raw_versions = isset( $_POST['yowm_lecture_versions'] ) ? (array) wp_unslash( $_POST['yowm_lecture_versions'] ) : array();
			$versions = array();
			foreach ( $raw_versions as $version_id => $version ) {
				if ( ! is_array( $version ) ) continue;
				$clean_id = sanitize_key( (string) $version_id );
				if ( ! $clean_id || '__version_id__' === strtolower( $clean_id ) ) continue;
				$versions[ $clean_id ] = array(
					'guid'       => sanitize_text_field( (string) ( $version['guid'] ?? '' ) ) ?: self::new_episode_guid(),
					'label'      => sanitize_text_field( (string) ( $version['label'] ?? '' ) ),
					'audio'      => esc_url_raw( (string) ( $version['audio'] ?? '' ) ),
					'transcript' => sanitize_textarea_field( (string) ( $version['transcript'] ?? '' ) ),
					'archived'   => ! empty( $version['archived'] ) ? 1 : 0,
				);
			}
			$raw_assignments = isset( $_POST['yowm_lecture_assignment'] ) ? (array) wp_unslash( $_POST['yowm_lecture_assignment'] ) : array();
			$assignments = array();
			foreach ( $raw_assignments as $cohort_id => $version_id ) {
				$cohort_id = absint( $cohort_id );
				$version_id = sanitize_key( (string) $version_id );
				if ( $cohort_id && $version_id && isset( $versions[ $version_id ] ) ) $assignments[ $cohort_id ] = $version_id;
			}
			update_post_meta( $post_id, self::META_LECTURE_VERSIONS, $versions );
			update_post_meta( $post_id, self::META_LECTURE_ASSIGNMENTS, $assignments );

			$module_id = isset( $_POST['yowm_module_id'] ) ? absint( $_POST['yowm_module_id'] ) : 0;
			if ( $module_id ) {
				wp_set_object_terms( $post_id, array( $module_id ), self::MODULE, false );
			}

			$scope = isset( $_POST['yowm_lesson_scope'] )
				? sanitize_key( wp_unslash( $_POST['yowm_lesson_scope'] ) )
				: 'all';

			$cohort_ids = 'specific' === $scope && isset( $_POST['yowm_lesson_cohorts'] )
				? array_values( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['yowm_lesson_cohorts'] ) ) ) ) )
				: array();

			update_post_meta( $post_id, self::META_LESSON_COHORTS, $cohort_ids );

			if ( $cohort_ids ) {
				update_post_meta( $post_id, self::META_COHORT, (int) $cohort_ids[0] );
			} else {
				delete_post_meta( $post_id, self::META_COHORT );
			}

			$cohorts = get_posts(
				array(
					'post_type'      => self::COHORT,
					'post_status'    => array( 'publish', 'draft', 'private' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			$media = array();
			$releases = array();
			$lecture_releases = array();
			$old_podcast_media = get_post_meta( $post_id, self::META_PODCAST_MEDIA, true );
			$old_podcast_media = is_array( $old_podcast_media ) ? $old_podcast_media : array();
			$podcast_media = array();

			foreach ( $cohorts as $cohort_id ) {
				$video_key           = 'yowm_session_video_' . $cohort_id;
				$audio_key           = 'yowm_session_audio_' . $cohort_id;
				$release_key         = 'yowm_release_' . $cohort_id;
				$session_release_key = 'yowm_session_audio_release_' . $cohort_id;

				$video  = isset( $_POST[ $video_key ] ) ? esc_url_raw( wp_unslash( $_POST[ $video_key ] ) ) : '';
				$audio  = isset( $_POST[ $audio_key ] ) ? esc_url_raw( wp_unslash( $_POST[ $audio_key ] ) ) : '';
				$release = isset( $_POST[ $release_key ] )
					? sanitize_text_field( wp_unslash( $_POST[ $release_key ] ) )
					: '';
				$session_release = isset( $_POST[ $session_release_key ] )
					? sanitize_text_field( wp_unslash( $_POST[ $session_release_key ] ) )
					: '';

				if ( $video || $audio ) {
					$media[ $cohort_id ] = array(
						'video' => $video,
						'audio' => $audio,
					);
				}

				$old_item = isset( $old_podcast_media[ $cohort_id ] ) && is_array( $old_podcast_media[ $cohort_id ] )
					? $old_podcast_media[ $cohort_id ]
					: array();
				$session_added = absint( $old_item['session_added'] ?? 0 );

				if ( $audio && $audio !== (string) ( $old_item['session_url'] ?? '' ) ) {
					$session_added = current_time( 'timestamp' );
				}

				if ( isset( $assignments[ $cohort_id ] ) ) {
					$unified_release = $release ? substr( $release, 0, 10 ) . 'T06:00' : '';
					$releases[ $cohort_id ] = $unified_release;
					$lecture_releases[ $cohort_id ] = $unified_release;
				}

				if ( $audio || $session_release ) {
					$session_guid = sanitize_text_field( (string) ( $old_item['session_guid'] ?? '' ) );
					if ( ! $session_guid ) {
						$session_guid = self::new_episode_guid();
					}

					$podcast_media[ $cohort_id ] = array(
						'session_url'     => $audio,
						'session_guid'    => $session_guid,
						'session_release' => $session_release,
						'session_added'   => $session_added,
					);
				}

			}

			update_post_meta( $post_id, self::META_SESSION_MEDIA, $media );
			update_post_meta( $post_id, self::META_RELEASES, $releases );
			update_post_meta( $post_id, self::META_LECTURE_RELEASES, $lecture_releases );
			update_post_meta( $post_id, self::META_PODCAST_MEDIA, $podcast_media );
			clean_object_term_cache( $post_id, self::LESSON );
		}

		if ( self::RESOURCE === $post->post_type ) {
			$type = isset( $_POST['yowm_resource_type'] )
				? sanitize_key( wp_unslash( $_POST['yowm_resource_type'] ) )
				: 'page';
			$type = in_array( $type, array( 'link', 'card', 'page', 'podcast' ), true ) ? $type : 'page';
			update_post_meta( $post_id, self::META_RESOURCE_TYPE, $type );

			$url_input = 'card' === $type ? 'yowm_resource_card_url' : 'yowm_resource_url';
			if ( 'podcast' === $type ) { $url_input = ''; }
			$url = $url_input && isset( $_POST[ $url_input ] ) ? esc_url_raw( wp_unslash( $_POST[ $url_input ] ) ) : '';
			if ( $url ) {
				update_post_meta( $post_id, self::META_RESOURCE_URL, $url );
			} else {
				delete_post_meta( $post_id, self::META_RESOURCE_URL );
			}

			$text = isset( $_POST['yowm_resource_text'] )
				? sanitize_textarea_field( wp_unslash( $_POST['yowm_resource_text'] ) )
				: '';
			if ( $text ) {
				update_post_meta( $post_id, self::META_RESOURCE_TEXT, $text );
			} else {
				delete_post_meta( $post_id, self::META_RESOURCE_TEXT );
			}

			update_post_meta(
				$post_id,
				self::META_RESOURCE_NEW_TAB,
				! empty( $_POST['yowm_resource_new_tab'] ) ? 1 : 0
			);

			update_post_meta(
				$post_id,
				self::META_RESOURCE_DOWNLOAD,
				! empty( $_POST['yowm_resource_download'] ) ? 1 : 0
			);

			$order = isset( $_POST['yowm_resource_order'] ) ? intval( $_POST['yowm_resource_order'] ) : 0;
			global $wpdb;
			$wpdb->update(
				$wpdb->posts,
				array( 'menu_order' => $order ),
				array( 'ID' => $post_id ),
				array( '%d' ),
				array( '%d' )
			);
			clean_post_cache( $post_id );

			$scope = isset( $_POST['yowm_resource_scope'] )
				? sanitize_key( wp_unslash( $_POST['yowm_resource_scope'] ) )
				: 'all';

			$ids = 'specific' === $scope && isset( $_POST['yowm_resource_cohorts'] )
				? array_values( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['yowm_resource_cohorts'] ) ) ) ) )
				: array();

			update_post_meta( $post_id, self::META_RESOURCE_COHORT, $ids );
		}
	}

	public static function force_block_editor( bool $use_block_editor, string $post_type ): bool {
		if ( in_array( $post_type, array( self::LESSON, self::RESOURCE ), true ) ) {
			return true;
		}

		return $use_block_editor;
	}

	public static function force_block_editor_for_post( bool $use_block_editor, WP_Post $post ): bool {
		if ( in_array( $post->post_type, array( self::LESSON, self::RESOURCE ), true ) ) {
			return true;
		}

		return $use_block_editor;
	}

	public static function title_placeholder( string $placeholder, WP_Post $post ): string {
		if ( self::COHORT === $post->post_type ) {
			return 'Example: Year of Writing Magically 2027';
		}
		if ( self::LESSON === $post->post_type ) {
			return 'Enter only the lesson title — example: Why Mindset Matters';
		}
		if ( self::RESOURCE === $post->post_type ) {
			return 'Example: Discord, Meeting Time, or 2027 Schedule';
		}
		return $placeholder;
	}

	public static function lesson_columns( array $columns ): array {
		return array(
			'cb'          => $columns['cb'],
			'title'       => 'Lesson title',
			'yowm_cohort' => 'Cohort',
			'yowm_module' => 'Module',
			'yowm_number' => 'Number',
			'date'        => $columns['date'],
		);
	}

	public static function lesson_column_content( string $column, int $post_id ): void {
		if ( 'yowm_cohort' === $column ) {
			$cohort_id = (int) get_post_meta( $post_id, self::META_COHORT, true );
			echo $cohort_id ? esc_html( get_the_title( $cohort_id ) ) : '—';
		}
		if ( 'yowm_module' === $column ) {
			echo esc_html( self::lesson_module_name( $post_id ) );
		}
		if ( 'yowm_number' === $column ) {
			$number = get_post_meta( $post_id, self::META_NUMBER, true );
			echo $number ? esc_html( (string) $number ) : '—';
		}
	}
}

register_activation_hook( __FILE__, array( 'YOWM_Studio', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'YOWM_Studio', 'deactivate' ) );
require_once YOWM_STUDIO_DIR . 'student-access.php';
require_once YOWM_STUDIO_DIR . 'admin-simplify.php';
YOWM_Student_Access::init();
YOWM_Admin_Simplify::init();
YOWM_Studio::init();

/*
 * One-click updates from GitHub.
 *
 * Checks https://github.com/lanidianerich/yowm-studio for new releases so that
 * YOWM Studio appears in the normal WordPress Plugins → Update list. Each release
 * carries a built yowm-studio-<version>.zip asset, which is what gets installed.
 */
require_once YOWM_STUDIO_DIR . 'lib/plugin-update-checker/plugin-update-checker.php';

$yowm_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/lanidianerich/yowm-studio/',
	YOWM_STUDIO_FILE,
	'yowm-studio'
);

$yowm_update_checker->getVcsApi()->enableReleaseAssets();
