<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$yowm_nav_year = isset( $year ) ? absint( $year ) : absint( get_query_var( 'yowm_cohort_year' ) );
if ( ! $yowm_nav_year ) return;

$yowm_nav_path = trim(
	(string) wp_parse_url(
		isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/',
		PHP_URL_PATH
	),
	'/'
);
$yowm_nav_user = wp_get_current_user();
$yowm_nav_name = '';
if ( $yowm_nav_user && $yowm_nav_user->exists() ) {
	$yowm_nav_name = trim( (string) $yowm_nav_user->first_name );
	if ( ! $yowm_nav_name ) {
		$yowm_nav_name = trim( (string) $yowm_nav_user->display_name );
	}
}
?>
<nav class="yowm-cohort-nav" aria-label="<?php echo esc_attr( $yowm_nav_year . ' classroom navigation' ); ?>">
	<div class="wide-container yowm-cohort-nav-inner">
		<?php if ( $yowm_nav_name ) : ?>
			<span class="yowm-welcome">Welcome, <?php echo esc_html( $yowm_nav_name ); ?></span>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/' . $yowm_nav_year . '/' ) ); ?>"
			<?php echo $yowm_nav_path === (string) $yowm_nav_year ? 'aria-current="page"' : ''; ?>>
			<?php echo esc_html( (string) $yowm_nav_year ); ?> home
		</a>
		<a href="<?php echo esc_url( home_url( '/' . $yowm_nav_year . '/lessons/' ) ); ?>"
			<?php echo str_starts_with( $yowm_nav_path, $yowm_nav_year . '/lessons' ) ? 'aria-current="page"' : ''; ?>>
			Lessons
		</a>
		<a href="<?php echo esc_url( home_url( '/' . $yowm_nav_year . '/library/' ) ); ?>"
			<?php echo str_starts_with( $yowm_nav_path, $yowm_nav_year . '/library' ) || str_starts_with( $yowm_nav_path, $yowm_nav_year . '/resources/' ) ? 'aria-current="page"' : ''; ?>>
			Class Info
		</a>
		<a class="yowm-change-cohort" href="<?php echo esc_url( home_url( '/' ) ); ?>">Change cohort</a>
		<a class="yowm-logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
	</div>
</nav>
