<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();


$year      = absint( get_query_var( 'yowm_cohort_year' ) );

include YOWM_STUDIO_DIR . 'templates/cohort-nav.php';
$cohort    = YOWM_Studio::get_cohort_by_year( $year );
$timeline  = YOWM_Studio::cohort_lesson_timeline( $cohort->ID );
$released  = $timeline['released'];
$upcoming  = $timeline['upcoming'];
$now_item  = $released[0] ?? null;
$recent    = array_slice( $released, 1, 3 );
$next_item = $upcoming[0] ?? null;
$resources = YOWM_Studio::get_cohort_resources( $cohort->ID );
$announce  = get_post_meta( $cohort->ID, YOWM_Studio::META_ANNOUNCEMENT, true );

$featured_library = array_slice(
	array_values(
		array_filter(
			$resources,
			static fn( WP_Post $resource ): bool =>
				! in_array( YOWM_Studio::resource_type( $resource->ID ), array( 'link', 'podcast' ), true )
		)
	),
	0,
	3
);
?>
<main id="main" class="yowm-cohort-page">
	<header class="yowm-classroom-header">
		<div class="wide-container">
			<p class="eyebrow">The Year of Writing Magically</p>
			<h1><?php echo esc_html( (string) $year ); ?></h1>
			<?php if ( $cohort->post_content ) : ?>
				<div class="yowm-welcome"><?php echo wp_kses_post( apply_filters( 'the_content', $cohort->post_content ) ); ?></div>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( $announce ) : ?>
		<section class="yowm-announcement">
			<div class="wide-container">
				<p class="card-kicker">Announcement</p>
				<p><?php echo nl2br( esc_html( $announce ) ); ?></p>
			</div>
		</section>
	<?php endif; ?>

	<div class="yowm-classroom-layout">
		<?php YOWM_Studio::render_quick_links_rail( $cohort->ID, $year ); ?>

		<div class="yowm-classroom-main">

			<section class="yowm-block yowm-lessons-now">
				<div class="yowm-now-grid">
					<?php if ( $now_item ) : ?>
						<?php $lesson = $now_item['lesson']; ?>
						<article class="yowm-now-card">
							<p class="card-kicker">Previous Lesson</p>
							<h3><?php echo esc_html( YOWM_Studio::lesson_display_title( $lesson->ID ) ); ?></h3>
							<?php if ( has_excerpt( $lesson ) ) : ?>
								<p><?php echo esc_html( get_the_excerpt( $lesson ) ); ?></p>
							<?php endif; ?>
							<a class="button" href="<?php echo esc_url( get_permalink( $lesson ) ); ?>">Open lesson</a>
						</article>
					<?php else : ?>
						<div class="yowm-empty"><p>No lessons have been released yet.</p></div>
					<?php endif; ?>

					<?php if ( $next_item ) : ?>
						<article class="yowm-now-card yowm-coming-up-card">
							<p class="card-kicker">Coming Up</p>
							<h3><?php echo esc_html( YOWM_Studio::lesson_display_title( $next_item['lesson']->ID ) ); ?></h3>
							<p class="yowm-release-date">Coming <?php echo esc_html( wp_date( 'F j, Y', $next_item['release_at'], wp_timezone() ) ); ?></p>
						</article>
					<?php endif; ?>
				</div>
			</section>

			<section class="yowm-block">
				<div class="yowm-section-heading-row">
					<h2 class="section-title">Recent lessons</h2>
					<a class="text-link" href="<?php echo esc_url( home_url( '/' . $year . '/lessons/' ) ); ?>">Past lessons →</a>
				</div>
				<?php if ( $recent ) : ?>
					<div class="yowm-lesson-grid">
						<?php foreach ( $recent as $item ) : ?>
							<?php $lesson = $item['lesson']; ?>
							<article class="yowm-lesson-card">
								<p class="card-kicker"><?php echo esc_html( YOWM_Studio::lesson_module_name( $lesson->ID ) ); ?></p>
								<h3><a href="<?php echo esc_url( get_permalink( $lesson ) ); ?>"><?php echo esc_html( YOWM_Studio::lesson_display_title( $lesson->ID ) ); ?></a></h3>
								<a class="text-link" href="<?php echo esc_url( get_permalink( $lesson ) ); ?>">Open lesson →</a>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p>Past lessons will appear here after more than one lesson has been released.</p>
				<?php endif; ?>
			</section>

			<?php if ( $featured_library ) : ?>
				<section class="yowm-block yowm-resources-section" id="library">
					<div class="yowm-section-heading-row">
						<h2 class="section-title">Class Info</h2>
						<a class="text-link" href="<?php echo esc_url( home_url( '/' . $year . '/library/' ) ); ?>">View Class Info →</a>
					</div>
					<div class="yowm-resource-grid">
						<?php foreach ( $featured_library as $resource ) : ?>
							<?php if ( 'card' === YOWM_Studio::resource_type( $resource->ID ) ) : ?>
								<div class="yowm-resource-card yowm-information-card">
									<strong><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></strong>
									<div class="yowm-information-card-content">
										<?php echo wp_kses_post( YOWM_Studio::resource_card_content( $resource->ID ) ); ?>
									</div>
								</div>
							<?php else : ?>
								<a class="yowm-resource-card yowm-resource-page" href="<?php echo esc_url( YOWM_Studio::resource_url( $resource->ID, $year ) ); ?>">
									<strong><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></strong>
									<?php if ( YOWM_Studio::resource_text( $resource->ID ) ) : ?>
										<small><?php echo esc_html( YOWM_Studio::resource_text( $resource->ID ) ); ?></small>
									<?php endif; ?>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

		</div>
	</div>
</main>
<?php get_footer(); ?>
