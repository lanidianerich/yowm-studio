<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();


$year     = absint( get_query_var( 'yowm_cohort_year' ) );

include YOWM_STUDIO_DIR . 'templates/cohort-nav.php';
$cohort   = YOWM_Studio::get_cohort_by_year( $year );
$timeline = YOWM_Studio::cohort_lesson_timeline( $cohort->ID );
$modules  = array();

foreach ( $timeline['released'] as $item ) {
	$lesson = $item['lesson'];
	$module = YOWM_Studio::lesson_module_name( $lesson->ID );
	$modules[ $module ][] = $lesson;
}
?>
<main id="main">
	<header class="yowm-reading-header">
		<div class="yowm-reading-container">
			<p class="eyebrow"><?php echo esc_html( (string) $year ); ?> Classroom</p>
			<h1>Past lessons</h1>
		</div>
	</header>

	<section class="section">
		<div class="wide-container">
			<?php if ( $modules ) : ?>
				<div class="yowm-module-list">
					<?php foreach ( $modules as $module_name => $module_lessons ) : ?>
						<details class="yowm-module" open>
							<summary>
								<span><?php echo esc_html( $module_name ); ?></span>
								<small><?php echo esc_html( count( $module_lessons ) . ( 1 === count( $module_lessons ) ? ' lesson' : ' lessons' ) ); ?></small>
							</summary>
							<ol>
								<?php foreach ( $module_lessons as $lesson ) : ?>
									<li><a href="<?php echo esc_url( get_permalink( $lesson ) ); ?>"><?php echo esc_html( YOWM_Studio::lesson_display_title( $lesson->ID ) ); ?></a></li>
								<?php endforeach; ?>
							</ol>
						</details>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p>No lessons have been released yet.</p>
			<?php endif; ?>

			<p class="yowm-back-link"><a href="<?php echo esc_url( home_url( '/' . $year . '/' ) ); ?>">← Back to classroom home</a></p>
		</div>
	</section>
</main>
<?php get_footer(); ?>
