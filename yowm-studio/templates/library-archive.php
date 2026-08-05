<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();


$year      = absint( get_query_var( 'yowm_cohort_year' ) );

include YOWM_STUDIO_DIR . 'templates/cohort-nav.php';
$cohort    = YOWM_Studio::get_cohort_by_year( $year );
$resources = YOWM_Studio::get_cohort_resources( $cohort->ID );

$groups = array( 'link' => array(), 'podcast' => array(), 'card' => array(), 'page' => array() );
foreach ( $resources as $resource ) {
	$groups[ YOWM_Studio::resource_type( $resource->ID ) ][] = $resource;
}
?>
<main id="main">
	<header class="yowm-reading-header">
		<div class="yowm-reading-container">
			<p class="eyebrow"><?php echo esc_html( (string) $year ); ?> Classroom</p>
			<h1>Class Info</h1>
		</div>
	</header>

	<section class="section">
		<div class="wide-container">
			<?php if ( $groups['link'] || $groups['podcast'] ) : ?>
				<div class="yowm-resource-group">
					<h2>Class links</h2>
					<div class="yowm-resource-buttons">
						<?php foreach ( array_merge( $groups['link'], $groups['podcast'] ) as $resource ) : ?>
							<?php $url = YOWM_Studio::resource_url( $resource->ID, $year ); ?>
							<?php if ( $url ) : ?>
								<?php if ( 'podcast' === YOWM_Studio::resource_type( $resource->ID ) ) : ?>
									<button class="button yowm-resource-button" type="button" data-yowm-copy-url="<?php echo esc_url( $url ); ?>"><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></button>
								<?php else : ?>
									<a class="button yowm-resource-button" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></a>
								<?php endif; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $groups['card'] ) : ?>
				<div class="yowm-resource-group">
					<h2>At a glance</h2>
					<div class="yowm-resource-grid">
						<?php foreach ( $groups['card'] as $resource ) : ?>
							<div class="yowm-resource-card yowm-information-card">
								<strong><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></strong>
								<div class="yowm-information-card-content">
									<?php echo wp_kses_post( YOWM_Studio::resource_card_content( $resource->ID ) ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $groups['page'] ) : ?>
				<div class="yowm-resource-group">
					<h2>Information</h2>
					<div class="yowm-resource-grid">
						<?php foreach ( $groups['page'] as $resource ) : ?>
							<a class="yowm-resource-card yowm-resource-page" href="<?php echo esc_url( YOWM_Studio::resource_url( $resource->ID, $year ) ); ?>">
								<strong><?php echo esc_html( YOWM_Studio::clean_title( $resource->ID ) ); ?></strong>
								<?php if ( YOWM_Studio::resource_text( $resource->ID ) ) : ?>
									<small><?php echo esc_html( YOWM_Studio::resource_text( $resource->ID ) ); ?></small>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<p class="yowm-back-link"><a href="<?php echo esc_url( home_url( '/' . $year . '/' ) ); ?>">← Back to classroom home</a></p>
		</div>
	</section>
</main>
<?php get_footer(); ?>
