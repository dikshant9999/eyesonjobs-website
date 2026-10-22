<?php
/**
 * Search form.
 *
 * @package EyesOnJobs
 */

?>
<form role="search" method="get" class="eoj-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="eoj-s"><?php esc_html_e( 'Search for:', 'eyesonjobs' ); ?></label>
	<input type="search" id="eoj-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search the site', 'eyesonjobs' ); ?>">
	<button type="submit" class="eoj-btn eoj-btn--primary"><?php echo eoj_icon( 'search' ); // phpcs:ignore ?><span class="screen-reader-text"><?php esc_html_e( 'Search', 'eyesonjobs' ); ?></span></button>
</form>
