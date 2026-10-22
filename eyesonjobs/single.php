<?php
/**
 * Single blog post.
 *
 * @package EyesOnJobs
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'eoj-entry eoj-entry--post' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="eoj-entry__thumb"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>
		<?php the_content(); ?>
		<?php wp_link_pages(); ?>
	</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="eoj-container eoj-narrow">';
		comments_template();
		echo '</div>';
	}
endwhile;

get_footer();
