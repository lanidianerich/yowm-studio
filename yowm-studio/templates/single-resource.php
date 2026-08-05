<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();


$year     = absint( get_query_var( 'yowm_cohort_year' ) );

include YOWM_STUDIO_DIR . 'templates/cohort-nav.php';
$resource = $GLOBALS['yowm_virtual_post'] ?? null;

if ( ! $resource instanceof WP_Post ) {
	echo '<main id="main"><div class="wide-container yowm-empty" style="padding:40px 0"><p>This item isn’t available in the ' . esc_html( (string) $year ) . ' classroom.</p><p><a href="' . esc_url( home_url( '/' . $year . '/' ) ) . '">← Back to classroom home</a></p></div></main>';
	get_footer();
	return;
}
?>
<main id="main" class="yowm-resource-page">
	<article>
		<header class="yowm-reading-header">
			<div class="yowm-reading-container">
				<p class="eyebrow">Class Info</p>
				<h1><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></h1>
			</div>
		</header>

		<div class="yowm-reading-layout">
			<aside class="page-outline" data-page-outline aria-label="On this page">
				<p class="outline-title">On this page</p>
				<a class="yowm-outline-back" href="<?php echo esc_url( home_url( '/' . $year . '/#library' ) ); ?>">Class Info home</a>
				<nav data-page-outline-nav></nav>
			</aside>
			<div class="entry-content yowm-reading-container" data-outline-content>
				<?php echo wp_kses_post( apply_filters( 'the_content', $resource->post_content ) ); ?>
			</div>
		</div>

		<div class="yowm-reading-container yowm-back-link">
			<a href="<?php echo esc_url( home_url( '/' . $year . '/' ) ); ?>">← Back to the <?php echo esc_html( $year ); ?> classroom</a>
		</div>
	</article>
</main>
<?php get_footer(); ?>
