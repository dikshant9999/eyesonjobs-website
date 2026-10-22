<?php
/**
 * Jobs listing with search filters.
 *
 * @package EyesOnJobs
 */

get_header();
global $wp_query;
?>
<section class="eoj-container eoj-section-pad eoj-jobs">
	<div class="eoj-jobs__search" data-reveal>
		<?php echo do_shortcode( '[eoj_job_search]' ); ?>
	</div>

	<div class="eoj-jobs__layout">
		<div class="eoj-jobs__list">
			<p class="eoj-jobs__count">
				<?php
				/* translators: %s number of jobs */
				echo esc_html( sprintf( _n( '%s opening found', '%s openings found', (int) $wp_query->found_posts, 'eyesonjobs' ), number_format_i18n( $wp_query->found_posts ) ) );
				?>
			</p>
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					eoj_job_card();
				endwhile;
				?>
				<div class="eoj-pagination"><?php the_posts_pagination(); ?></div>
			<?php else : ?>
				<div class="eoj-empty eoj-card">
					<?php echo eoj_art( 'search' ); // phpcs:ignore ?>
					<h2><?php esc_html_e( 'No matching openings right now', 'eyesonjobs' ); ?></h2>
					<p><?php esc_html_e( 'New roles are added every week. Share your resume and our recruiters will reach out when a suitable role opens up.', 'eyesonjobs' ); ?></p>
					<a class="eoj-btn eoj-btn--primary" href="<?php echo esc_url( home_url( '/submit-resume/' ) ); ?>"><?php esc_html_e( 'Submit your resume', 'eyesonjobs' ); ?></a>
				</div>
			<?php endif; ?>
		</div>

		<aside class="eoj-jobs__aside">
			<div class="eoj-card eoj-card--navy" data-reveal>
				<?php echo eoj_icon( 'file', 'eoj-icon--lg' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Didn’t find your role?', 'eyesonjobs' ); ?></h3>
				<p><?php esc_html_e( 'Upload your resume once and get matched with openings that are never advertised publicly.', 'eyesonjobs' ); ?></p>
				<a class="eoj-btn eoj-btn--primary" href="<?php echo esc_url( home_url( '/submit-resume/' ) ); ?>"><?php esc_html_e( 'Submit resume', 'eyesonjobs' ); ?></a>
			</div>
			<div class="eoj-card eoj-card--accent" data-reveal>
				<span class="eoj-tag eoj-tag--hot"><?php esc_html_e( '50% off', 'eyesonjobs' ); ?></span>
				<h3><?php esc_html_e( 'Get interview-ready', 'eyesonjobs' ); ?></h3>
				<p><?php esc_html_e( 'ATS-friendly resume, mock interviews and salary negotiation guidance in one package.', 'eyesonjobs' ); ?></p>
				<a class="eoj-link" href="<?php echo esc_url( home_url( '/job-preparation-package/' ) ); ?>"><?php esc_html_e( 'Explore the package', 'eyesonjobs' ); ?> <?php echo eoj_icon( 'arrow' ); // phpcs:ignore ?></a>
			</div>
			<?php
			if ( taxonomy_exists( 'eoj_job_category' ) ) {
				$eoj_cats = get_terms(
					array(
						'taxonomy'   => 'eoj_job_category',
						'hide_empty' => true,
					)
				);
				if ( $eoj_cats && ! is_wp_error( $eoj_cats ) ) {
					echo '<div class="eoj-card" data-reveal><h3>' . esc_html__( 'Browse by industry', 'eyesonjobs' ) . '</h3><ul class="eoj-chips">';
					foreach ( $eoj_cats as $eoj_cat ) {
						printf( '<li><a href="%s">%s <span>%d</span></a></li>', esc_url( get_term_link( $eoj_cat ) ), esc_html( $eoj_cat->name ), (int) $eoj_cat->count );
					}
					echo '</ul></div>';
				}
			}
			?>
		</aside>
	</div>
</section>
<?php
get_footer();
