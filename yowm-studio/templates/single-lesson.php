<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();


$year    = absint( get_query_var( 'yowm_cohort_year' ) );

include YOWM_STUDIO_DIR . 'templates/cohort-nav.php';
$cohort  = YOWM_Studio::get_cohort_by_year( $year );
$lesson  = $GLOBALS['yowm_virtual_post'] ?? null;

// Never fatal if the lesson couldn't be resolved for this cohort — show a calm
// message instead of a white screen. (get_header has already printed, so we
// render in place rather than redirect.)
if ( ! $cohort instanceof WP_Post || ! $lesson instanceof WP_Post ) {
	echo '<main id="main"><div class="wide-container yowm-empty" style="padding:40px 0"><p>This lesson isn’t available in the ' . esc_html( (string) $year ) . ' classroom yet.</p><p><a href="' . esc_url( home_url( '/' . $year . '/' ) ) . '">← Back to classroom home</a></p></div></main>';
	get_footer();
	return;
}

$lessons = YOWM_Studio::get_cohort_lessons( $cohort->ID );

$current_index = -1;
foreach ( $lessons as $index => $candidate ) {
	if ( $candidate->ID === $lesson->ID ) {
		$current_index = $index;
		break;
	}
}
$previous = $current_index > 0 ? $lessons[ $current_index - 1 ] : null;
$next     = $current_index >= 0 && isset( $lessons[ $current_index + 1 ] ) ? $lessons[ $current_index + 1 ] : null;

$podcast_media = YOWM_Studio::lesson_podcast_media( $lesson->ID, $cohort->ID );
$post_audio    = $podcast_media['lecture_url'];
$lecture_version = YOWM_Studio::lecture_version_for_cohort( $lesson->ID, $cohort->ID );
$transcript    = (string) $lecture_version['transcript'];
$session_media = YOWM_Studio::lesson_session_media( $lesson->ID, $cohort->ID );
$session_video = $session_media['video'];
$session_audio = $session_media['audio'];
?>
<main id="main" class="yowm-lesson-page">
	<article>
		<header class="yowm-reading-header">
			<div class="yowm-reading-container">
				<p class="eyebrow"><?php echo esc_html( YOWM_Studio::lesson_module_name( $lesson->ID ) ); ?></p>
				<h1><?php echo esc_html( YOWM_Studio::lesson_display_title( $lesson->ID ) ); ?></h1>
			</div>
		</header>

		<div class="yowm-reading-layout">
			<aside class="page-outline" data-page-outline aria-label="On this page">
				<p class="outline-title">On this page</p>
				<nav data-page-outline-nav></nav>
			</aside>

			<div class="entry-content yowm-reading-container" data-outline-content>
				<?php echo apply_filters( 'the_content', $lesson->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php if ( $post_audio ) : ?>
					<h2>Listen</h2>
					<div class="yowm-media-card"><?php echo wp_kses_post( wp_audio_shortcode( array( 'src' => $post_audio ) ) ); ?></div>
				<?php endif; ?>

				<?php if ( $transcript ) : ?>
					<section class="lesson-section yowm-transcript">
						<details>
							<summary>Read or search the lecture transcript</summary>
							<div class="yowm-transcript-content"><?php echo wp_kses_post( wpautop( esc_html( $transcript ) ) ); ?></div>
						</details>
					</section>
				<?php endif; ?>

				<?php if ( $session_video || $session_audio ) : ?>
					<h2>Saturday live session</h2>
					<?php if ( $session_video ) : ?>
						<?php $embed_url = YOWM_Studio::youtube_embed_url( $session_video ); ?>
						<?php if ( $embed_url ) : ?>
							<div class="yowm-video">
								<iframe
									src="<?php echo esc_url( $embed_url ); ?>"
									title="Saturday live session"
									loading="lazy"
									referrerpolicy="strict-origin-when-cross-origin"
									allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
									allowfullscreen
								></iframe>
							</div>
							<p class="yowm-video-fallback">
								<a href="<?php echo esc_url( $session_video ); ?>" target="_blank" rel="noopener noreferrer">Open this video on YouTube →</a>
							</p>
						<?php else : ?>
							<p><a class="button" href="<?php echo esc_url( $session_video ); ?>" target="_blank" rel="noopener noreferrer">Watch the session on YouTube</a></p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( $session_audio ) : ?>
						<h3>Listen to the session</h3>
						<div class="yowm-media-card"><?php echo wp_kses_post( wp_audio_shortcode( array( 'src' => $session_audio ) ) ); ?></div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<nav class="yowm-lesson-navigation wide-container" aria-label="Lesson navigation">
			<div>
				<?php if ( $previous ) : ?>
					<a href="<?php echo esc_url( get_permalink( $previous ) ); ?>">← <?php echo esc_html( YOWM_Studio::lesson_display_title( $previous->ID ) ); ?></a>
				<?php endif; ?>
			</div>
			<a class="yowm-classroom-link" href="<?php echo esc_url( home_url( '/' . $year . '/' ) ); ?>"><?php echo esc_html( $year ); ?> classroom</a>
			<div class="yowm-next-link">
				<?php if ( $next ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next ) ); ?>"><?php echo esc_html( YOWM_Studio::lesson_display_title( $next->ID ) ); ?> →</a>
				<?php endif; ?>
			</div>
		</nav>
	</article>
</main>
<?php get_footer(); ?>
