<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$signup = get_option( YOWM_Studio::OPTION_SIGNUP_URL, 'https://lanidianerich.com/year-of-writing-magically.html' );
?>
<main id="main">
	<section class="hero yowm-404">
		<div class="container hero-inner">
			<p class="eyebrow">404</p>
			<h1>That page wandered off.</h1>
			<p class="hero-deck">You can return to the classroom home, or learn more about joining the Year of Writing Magically.</p>
			<div class="button-row">
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Classroom home</a>
				<a class="button button-secondary" href="<?php echo esc_url( $signup ); ?>">Learn about the class</a>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
