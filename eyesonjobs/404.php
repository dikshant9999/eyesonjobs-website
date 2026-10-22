<?php
/**
 * 404 page.
 *
 * @package EyesOnJobs
 */

get_header();
?>
<div class="eoj-container eoj-section-pad eoj-empty">
	<?php echo eoj_art( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<h2><?php esc_html_e( 'Let’s get you back on track', 'eyesonjobs' ); ?></h2>
	<p><?php esc_html_e( 'Search the site or jump straight to the latest openings.', 'eyesonjobs' ); ?></p>
	<?php get_search_form(); ?>
	<p class="eoj-btnrow">
		<a class="eoj-btn eoj-btn--primary" href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>"><?php esc_html_e( 'Browse jobs', 'eyesonjobs' ); ?></a>
		<a class="eoj-btn eoj-btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home page', 'eyesonjobs' ); ?></a>
	</p>
</div>
<?php
get_footer();
