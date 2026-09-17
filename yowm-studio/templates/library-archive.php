<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();


$year      = absint( get_query_var( 'yowm_cohort_year' ) );

include YOWM_STUDIO_DIR . 'templates/cohort-nav.php';
$cohort    = YOWM_Studio::get_cohort_by_year( $year );
$resources = YOWM_Studio::get_cohort_resources( $cohort->ID );

// Class Info content = the card + page resources, already sorted by Display
// order. One flow now (no At-a-glance / Information split): first item is the
// full-width stage-setter (Mission), last is the full-width closer
// (Confidentiality), everything between is the box grid. (Link + podcast
// resources go to the quick-links rail instead.)
$content = array_values(
	array_filter(
		$resources,
		static fn( WP_Post $r ): bool =>
			in_array( YOWM_Studio::resource_type( $r->ID ), array( 'card', 'page' ), true )
	)
);
$count = count( $content );
?>
<main id="main" class="yowm-classinfo-page">
	<header class="yowm-reading-header">
		<div class="yowm-reading-container">
			<p class="eyebrow"><?php echo esc_html( (string) $year ); ?> Classroom</p>
			<h1>Class Info</h1>
		</div>
	</header>

	<div class="yowm-classroom-layout">
		<?php YOWM_Studio::render_quick_links_rail( $cohort->ID, $year ); ?>

		<div class="yowm-classroom-main">
			<?php if ( $content ) : ?>
				<div class="yowm-classinfo-flow">
					<?php foreach ( $content as $i => $resource ) : ?>
						<?php
						$is_full    = ( 0 === $i ) || ( $count > 1 && $i === $count - 1 );
						$item_class = $is_full ? 'yowm-classinfo-full' : 'yowm-classinfo-box';
						?>
						<?php if ( 'card' === YOWM_Studio::resource_type( $resource->ID ) ) : ?>
							<div class="yowm-resource-card yowm-information-card <?php echo esc_attr( $item_class ); ?>">
								<strong><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></strong>
								<div class="yowm-information-card-content">
									<?php echo wp_kses_post( YOWM_Studio::resource_card_content( $resource->ID ) ); ?>
								</div>
							</div>
						<?php else : ?>
							<a class="yowm-resource-card yowm-resource-page <?php echo esc_attr( $item_class ); ?>" href="<?php echo esc_url( YOWM_Studio::resource_url( $resource->ID, $year ) ); ?>">
								<strong><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></strong>
								<?php if ( YOWM_Studio::resource_text( $resource->ID ) ) : ?>
									<small><?php echo esc_html( YOWM_Studio::resource_text( $resource->ID ) ); ?></small>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p>Class information will appear here.</p>
			<?php endif; ?>

			<p class="yowm-back-link"><a href="<?php echo esc_url( home_url( '/' . $year . '/' ) ); ?>">← Back to classroom home</a></p>
		</div>
	</div>
</main>
<?php get_footer(); ?>
