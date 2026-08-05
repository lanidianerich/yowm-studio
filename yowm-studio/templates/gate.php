<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$year = absint( get_query_var( 'yowm_cohort_year' ) );
$cohort = YOWM_Studio::get_cohort_by_year( $year );
$redirect = home_url( '/' . $year . '/' );

$lesson_slug   = sanitize_title( (string) get_query_var( 'yowm_lesson_slug' ) );
$resource_slug = sanitize_title( (string) get_query_var( 'yowm_resource_slug' ) );

if ( $resource_slug ) {
	$redirect = home_url( '/' . $year . '/resources/' . $resource_slug . '/' );
} elseif ( $lesson_slug ) {
	$redirect = home_url( '/' . $year . '/' . $lesson_slug . '/' );
} elseif ( get_query_var( 'yowm_lesson_archive' ) ) {
	$redirect = home_url( '/' . $year . '/lessons/' );
} elseif ( get_query_var( 'yowm_library_archive' ) ) {
	$redirect = home_url( '/' . $year . '/library/' );
}
?>
<main id="main">
	<section class="hero yowm-gate">
		<div class="container hero-inner">
			<p class="eyebrow">The Year of Writing Magically</p>
			<h1><?php echo esc_html( $cohort ? (string) $year : 'Classroom' ); ?></h1>
			<?php if ( is_user_logged_in() ) : ?>
				<p class="hero-deck">You're signed in, but this account doesn't have access to the <?php echo esc_html( (string) $year ); ?> cohort.</p>
				<p>If your access should be here, let Lani know and she'll sort it out.</p>
				<p><a class="button" href="mailto:lani@lanidianerich.com">Email Lani</a></p>
			<?php else : ?>
				<p class="hero-deck">Welcome back. Sign in with the email address you used for the class.</p>
				<div class="yowm-account-login">
					<?php wp_login_form( array(
						'redirect' => $redirect,
						'label_username' => 'Email address',
						'label_password' => 'Password',
						'label_remember' => 'Remember me',
						'label_log_in' => 'Enter the classroom',
						'remember' => true,
					) ); ?>
				</div>
				<p class="yowm-small-link"><a href="<?php echo esc_url( wp_lostpassword_url( $redirect ) ); ?>">Forgot your password?</a></p>
			<?php endif; ?>
			<p class="yowm-small-link"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to classroom home</a></p>
		</div>
	</section>
</main>
<?php get_footer(); ?>
