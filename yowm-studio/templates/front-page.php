<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$cohorts = YOWM_Studio::get_published_cohorts();
$title   = get_option( YOWM_Studio::OPTION_HOME_TITLE, 'The Year of Writing Magically' );
$intro   = get_option( YOWM_Studio::OPTION_HOME_INTRO, 'Choose your cohort to enter the classroom.' );
?>
<main id="main" class="yowm-front">
<section class="section yowm-cohort-chooser">
		<div class="wide-container">
			<h1 class="section-title">Choose your cohort</h1>

			<?php if ( $cohorts ) : ?>
				<div class="yowm-cohort-grid">
					<?php foreach ( $cohorts as $cohort ) : ?>
						<?php $year = YOWM_Studio::cohort_year( $cohort->ID ); ?>
						<?php if ( $year ) : ?>
							<a class="yowm-cohort-card" href="<?php echo esc_url( home_url( '/' . $year . '/' ) ); ?>" aria-label="Enter the <?php echo esc_attr( (string) $year ); ?> cohort">
								<?php if ( has_post_thumbnail( $cohort ) ) : ?>
									<div class="yowm-cohort-image"><?php echo get_the_post_thumbnail( $cohort, 'large', array( 'alt' => 'YOWM ' . $year ) ); ?></div>
								<?php endif; ?>
								<h2><?php echo esc_html( (string) $year ); ?></h2>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="yowm-empty">
					<h3>No cohorts are published yet.</h3>
					<p>Published cohorts will appear here automatically.</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
