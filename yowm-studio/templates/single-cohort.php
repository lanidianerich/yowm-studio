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

$link_resources = array_values(
	array_filter(
		$resources,
		static fn( WP_Post $resource ): bool =>
			in_array( YOWM_Studio::resource_type( $resource->ID ), array( 'link', 'podcast' ), true )
	)
);
$featured_library = array_slice(
	array_values(
		array_filter(
			$resources,
			static fn( WP_Post $resource ): bool =>
				'link' !== YOWM_Studio::resource_type( $resource->ID )
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

	<section class="section yowm-now-section">
		<div class="wide-container">
			<h2 class="section-title">On deck</h2>

			<?php if ( $now_item ) : ?>
				<?php $lesson = $now_item['lesson']; ?>
				<article class="yowm-now-card">
					<p class="card-kicker"><?php echo esc_html( YOWM_Studio::lesson_module_name( $lesson->ID ) ); ?></p>
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
				<article class="yowm-next-card">
					<p class="card-kicker">Coming next</p>
					<h3><?php echo esc_html( YOWM_Studio::lesson_display_title( $next_item['lesson']->ID ) ); ?></h3>
					<?php if ( has_excerpt( $next_item['lesson'] ) ) : ?>
						<p><?php echo esc_html( get_the_excerpt( $next_item['lesson'] ) ); ?></p>
					<?php endif; ?>
					<p class="yowm-release-date">
						Coming <?php echo esc_html( wp_date( 'F j, Y 	 g:i a', $next_item['release_at'], wp_timezone() ) ); ?>
					</p>
				</article>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $link_resources ) : ?>
		<section class="section">
			<div class="wide-container">
				<h2 class="section-title">Quick links</h2>
				<div class="yowm-resource-buttons">
					<?php foreach ( $link_resources as $resource ) : ?>
						<?php
						$url      = YOWM_Studio::resource_url( $resource->ID, $year );
						$new_tab  = (bool) get_post_meta( $resource->ID, YOWM_Studio::META_RESOURCE_NEW_TAB, true );
						$download = (bool) get_post_meta( $resource->ID, YOWM_Studio::META_RESOURCE_DOWNLOAD, true );
						?>
						<?php if ( $url ) : ?>
							<?php if ( 'podcast' === YOWM_Studio::resource_type( $resource->ID ) ) : ?>
								<button class="button yowm-resource-button" type="button" data-yowm-copy-url="<?php echo esc_url( $url ); ?>"><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></button>
							<?php else : ?>
								<a class="button yowm-resource-button" href="<?php echo esc_url( $url ); ?>"
									<?php echo $new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
									<?php echo $download ? 'download' : ''; ?>>
									<?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?>
								</a>
							<?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="section">
		<div class="wide-container">
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
		</div>
	</section>

	<?php if ( $featured_library ) : ?>
		<section class="section yowm-resources-section" id="library">
			<div class="wide-container">
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
			</div>
		</section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
