<?php
/**
 * Pages. Content is edited in the block editor; the theme styles the
 * "eoj-" classes used by the starter content.
 *
 * @package EyesOnJobs
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'eoj-entry' ); ?>>
		<?php the_content(); ?>
	</article>
	<?php
endwhile;

get_footer();
