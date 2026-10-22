<?php
/**
 * Single job with apply form.
 *
 * @package EyesOnJobs
 */

get_header();

while ( have_posts() ) :
	the_post();
	$eoj_id      = get_the_ID();
	$eoj_details = array(
		'pin'       => array( __( 'Location', 'eyesonjobs' ), eoj_job_term( $eoj_id, 'eoj_job_location' ) ),
		'clock'     => array( __( 'Job type', 'eyesonjobs' ), eoj_job_term( $eoj_id, 'eoj_job_type' ) ),
		'briefcase' => array( __( 'Experience', 'eyesonjobs' ), get_post_meta( $eoj_id, '_eoj_experience', true ) ),
		'rupee'     => array( __( 'Salary', 'eyesonjobs' ), get_post_meta( $eoj_id, '_eoj_salary', true ) ),
		'building'  => array( __( 'Industry', 'eyesonjobs' ), eoj_job_term( $eoj_id, 'eoj_job_category' ) ),
	);
	?>
	<section class="eoj-container eoj-section-pad eoj-jobsingle">
		<div class="eoj-jobsingle__main">
			<ul class="eoj-jobsingle__facts" data-reveal>
				<?php foreach ( $eoj_details as $eoj_icon => $eoj_fact ) : ?>
					<?php if ( $eoj_fact[1] ) : ?>
						<li><?php echo eoj_icon( $eoj_icon ); // phpcs:ignore ?><span><small><?php echo esc_html( $eoj_fact[0] ); ?></small><?php echo esc_html( $eoj_fact[1] ); ?></span></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<div class="eoj-prose" data-reveal>
				<?php the_content(); ?>
			</div>
			<p><a class="eoj-link" href="<?php echo esc_url( get_post_type_archive_link( 'eoj_job' ) ); ?>">&larr; <?php esc_html_e( 'All openings', 'eyesonjobs' ); ?></a></p>
		</div>
		<aside class="eoj-jobsingle__aside" id="apply">
			<div class="eoj-card eoj-sticky" data-reveal>
				<h2 class="eoj-h3"><?php esc_html_e( 'Apply for this role', 'eyesonjobs' ); ?></h2>
				<p class="eoj-muted"><?php esc_html_e( 'Takes under a minute. Our recruiter will call you if your profile fits.', 'eyesonjobs' ); ?></p>
				<?php echo do_shortcode( '[eoj_apply_form]' ); ?>
			</div>
		</aside>
	</section>
	<?php
endwhile;

get_footer();
