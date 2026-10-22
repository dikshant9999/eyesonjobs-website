<?php
/**
 * Blog index and generic archive fallback.
 *
 * @package EyesOnJobs
 */

get_header();
?>
<div class="eoj-container eoj-section-pad">
	<?php if ( have_posts() ) : ?>
		<div class="eoj-postgrid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'eoj-card eoj-postcard' ); ?> data-reveal>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="eoj-postcard__img" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
					<?php endif; ?>
					<p class="eoj-postcard__meta"><?php echo esc_html( get_the_date() ); ?></p>
					<h2 class="eoj-postcard__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<a class="eoj-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'eyesonjobs' ); ?> <?php echo eoj_icon( 'arrow' ); // phpcs:ignore ?></a>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="eoj-pagination"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<div class="eoj-empty">
			<h2><?php esc_html_e( 'Nothing here yet', 'eyesonjobs' ); ?></h2>
			<p><?php esc_html_e( 'Try a search, or head back to the home page.', 'eyesonjobs' ); ?></p>
			<?php get_search_form(); ?>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
