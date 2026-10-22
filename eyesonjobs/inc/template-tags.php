<?php
/**
 * Small template helpers: logo, icons, hero, menus.
 *
 * @package EyesOnJobs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icon (stroke icons, 24px grid).
 *
 * @param string $name  Icon name.
 * @param string $class Extra class.
 * @return string
 */
function eoj_icon( $name, $class = '' ) {
	$icons = array(
		'search'     => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'pin'        => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'briefcase'  => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
		'phone'      => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'users'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 13.5a6 6 0 0 1 4 6.5"/>',
		'shield'     => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
		'check'      => '<path d="m5 12 5 5 9-10"/>',
		'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'star'       => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9z"/>',
		'award'      => '<circle cx="12" cy="9" r="6"/><path d="m8.5 14-1.5 7 5-3 5 3-1.5-7"/>',
		'target'     => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
		'chat'       => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/>',
		'file'       => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
		'mic'        => '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>',
		'rupee'      => '<path d="M6 4h12M6 9h12M9 4c6 0 6 10 0 10H6l8 7"/>',
		'menu'       => '<path d="M3 6h18M3 12h18M3 18h18"/>',
		'close'      => '<path d="M6 6l12 12M18 6 6 18"/>',
		'building'   => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1M10 21v-3h4v3"/>',
		'chart'      => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
		'heart'      => '<path d="M12 20s-8-4.7-8-10.5A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 8 2.5C20 15.3 12 20 12 20z"/>',
		'cap'        => '<path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5M22 9v6"/>',
		'globe'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'lock'       => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'handshake'  => '<path d="m3 12 4-4 4 2 3-2 7 5-4 4M3 12l6 6 2-1M14 17l-3-3M17 15l-3-3"/>',
		'trend'      => '<path d="m3 17 6-6 4 4 8-8M15 7h6v6"/>',
		'sparkle'    => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
		'logout'     => '<path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 17l5-5-5-5M15 12H3"/>',
		'facebook'   => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8.5a.5.5 0 0 1 .5-.5z"/>',
		'twitter'    => '<path d="M4 4l16 16M20 4 4 20"/>',
		'linkedin'   => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
		'instagram'  => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5v.01"/>',
		'pinterest'  => '<circle cx="12" cy="12" r="9"/><path d="M10 21l2-8M10.5 14.5c3 1.5 6-1 5.5-4.5-.5-3-4-4-6.5-2.5-1.5 1-2 3-1 4.5"/>',
		'whatsapp'   => '<path d="M4 20l1.3-4.2A8 8 0 1 1 8.5 19z"/><path d="M9 9c0 3 3 6 6 6l1-1.5-2-1-1 .8c-1-.4-2-1.4-2.4-2.4l.8-1-1-2z"/>',
	);
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return '<svg class="eoj-icon ' . esc_attr( $class ) . '" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/**
 * Official Eyes on Jobs logo. A Custom Logo uploaded in the Customizer takes over in the header.
 *
 * @param string $variant "dark" for navy backgrounds.
 */
function eoj_logo( $variant = '' ) {
	if ( 'dark' !== $variant && has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$class = 'eoj-logo' . ( 'dark' === $variant ? ' eoj-logo--badge' : '' );
	printf(
		'<a class="%1$s" href="%2$s" rel="home" aria-label="%3$s"><img src="%4$s" alt="%3$s" width="321" height="296" decoding="async"></a>',
		esc_attr( $class ),
		esc_url( home_url( '/' ) ),
		esc_attr( get_bloginfo( 'name' ) ),
		esc_url( EOJ_THEME_URI . ( 'dark' === $variant ? '/assets/img/logo-light.png' : '/assets/img/logo.png' ) )
	);
}

/**
 * Whether the current view shows the large page hero banner.
 */
function eoj_has_page_hero() {
	return ! is_front_page();
}

/**
 * Title and subtitle for the page hero.
 *
 * @return array{0:string,1:string,2:string} Title, subtitle, eyebrow.
 */
function eoj_hero_text() {
	$eyebrow = '';
	if ( is_singular( 'eoj_job' ) ) {
		$terms   = get_the_terms( get_the_ID(), 'eoj_job_category' );
		$eyebrow = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : __( 'Job opening', 'eyesonjobs' );
		return array( get_the_title(), (string) get_post_meta( get_the_ID(), '_eoj_company', true ), $eyebrow );
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		$sub  = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
		if ( 'post' === get_post_type( $post ) ) {
			$eyebrow = get_the_date( '', $post );
			$sub     = '';
		}
		$parent = $post->post_parent ? get_the_title( $post->post_parent ) : '';
		return array( get_the_title( $post ), $sub, $parent ? $parent : $eyebrow );
	}
	if ( is_post_type_archive( 'eoj_job' ) || is_tax( array( 'eoj_job_category', 'eoj_job_location', 'eoj_job_type' ) ) ) {
		$title = is_tax() ? single_term_title( '', false ) : __( 'Find your next job', 'eyesonjobs' );
		return array( $title, __( 'Fresh openings from verified employers across India. Search by role, city or industry and apply in a minute.', 'eyesonjobs' ), __( 'Jobs', 'eyesonjobs' ) );
	}
	if ( is_search() ) {
		/* translators: %s search query */
		return array( sprintf( __( 'Results for “%s”', 'eyesonjobs' ), get_search_query() ), '', __( 'Search', 'eyesonjobs' ) );
	}
	if ( is_home() ) {
		return array( __( 'Career insights', 'eyesonjobs' ), __( 'Hiring trends, interview tips and career advice from the Eyes on Jobs team.', 'eyesonjobs' ), __( 'Blog', 'eyesonjobs' ) );
	}
	if ( is_archive() ) {
		return array( get_the_archive_title(), wp_strip_all_tags( get_the_archive_description() ), '' );
	}
	if ( is_404() ) {
		return array( __( 'Page not found', 'eyesonjobs' ), __( 'The page you are looking for has moved or no longer exists.', 'eyesonjobs' ), '404' );
	}
	return array( get_bloginfo( 'name' ), '', '' );
}

/**
 * Fallback menu, used until a menu is assigned under Appearance → Menus.
 */
function eoj_fallback_menu() {
	$items = array(
		'/'                        => __( 'Home', 'eyesonjobs' ),
		'/about-us/'               => __( 'About', 'eyesonjobs' ),
		'/services/'               => __( 'Services', 'eyesonjobs' ),
		'/jobs/'                   => __( 'Jobs', 'eyesonjobs' ),
		'/job-preparation-package/' => __( 'Job Prep Package', 'eyesonjobs' ),
		'/contact-us/'             => __( 'Contact', 'eyesonjobs' ),
	);
	echo '<ul class="eoj-menu">';
	foreach ( $items as $path => $label ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( home_url( $path ) ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Social icon links from the Customizer.
 */
function eoj_social_links() {
	$networks = array(
		'facebook'  => 'Facebook',
		'twitter'   => 'X',
		'linkedin'  => 'LinkedIn',
		'instagram' => 'Instagram',
		'pinterest' => 'Pinterest',
	);
	echo '<ul class="eoj-social">';
	foreach ( $networks as $key => $label ) {
		$url = eoj_opt( 'eoj_' . $key );
		if ( $url ) {
			printf( '<li><a href="%1$s" target="_blank" rel="noopener" aria-label="%2$s">%3$s</a></li>', esc_url( $url ), esc_attr( $label ), eoj_icon( $key ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	echo '</ul>';
}

/**
 * Phone number to tel: link.
 *
 * @param string $phone Phone.
 */
function eoj_tel( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
}

/**
 * URL of the account page (falls back to wp-login).
 */
function eoj_account_url() {
	$page = get_page_by_path( 'account' );
	return $page ? get_permalink( $page ) : wp_login_url();
}

/**
 * First term name for a job taxonomy.
 *
 * @param int    $post_id  Job ID.
 * @param string $taxonomy Taxonomy.
 */
function eoj_job_term( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	return implode( ', ', wp_list_pluck( $terms, 'name' ) );
}

/**
 * Job card used on the jobs archive and the home page.
 */
function eoj_job_card() {
	$id       = get_the_ID();
	$company  = get_post_meta( $id, '_eoj_company', true );
	$location = eoj_job_term( $id, 'eoj_job_location' );
	$type     = eoj_job_term( $id, 'eoj_job_type' );
	$salary   = get_post_meta( $id, '_eoj_salary', true );
	$exp      = get_post_meta( $id, '_eoj_experience', true );
	$initial  = strtoupper( mb_substr( $company ? $company : get_the_title(), 0, 1 ) );
	?>
	<article class="eoj-job" data-reveal>
		<div class="eoj-job__logo" aria-hidden="true">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'thumbnail' );
			} else {
				echo esc_html( $initial );
			}
			?>
		</div>
		<div class="eoj-job__body">
			<h3 class="eoj-job__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php if ( $company ) : ?>
				<p class="eoj-job__company"><?php echo esc_html( $company ); ?></p>
			<?php endif; ?>
			<ul class="eoj-job__meta">
				<?php if ( $location ) : ?>
					<li><?php echo eoj_icon( 'pin' ); // phpcs:ignore ?><?php echo esc_html( $location ); ?></li>
				<?php endif; ?>
				<?php if ( $exp ) : ?>
					<li><?php echo eoj_icon( 'briefcase' ); // phpcs:ignore ?><?php echo esc_html( $exp ); ?></li>
				<?php endif; ?>
				<?php if ( $salary ) : ?>
					<li><?php echo eoj_icon( 'rupee' ); // phpcs:ignore ?><?php echo esc_html( $salary ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="eoj-job__side">
			<?php if ( $type ) : ?>
				<span class="eoj-tag"><?php echo esc_html( $type ); ?></span>
			<?php endif; ?>
			<span class="eoj-job__date"><?php echo esc_html( human_time_diff( get_the_time( 'U' ) ) . ' ' . __( 'ago', 'eyesonjobs' ) ); ?></span>
			<a class="eoj-btn eoj-btn--small eoj-btn--primary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View & apply', 'eyesonjobs' ); ?></a>
		</div>
	</article>
	<?php
}
