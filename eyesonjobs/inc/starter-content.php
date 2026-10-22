<?php
/**
 * One-click site setup: Appearance → Eyes on Jobs Setup.
 * Creates the pages, menus and home page. Existing pages are only
 * overwritten when the admin ticks the box; WordPress keeps the old
 * text in the page's Revisions so nothing is lost.
 *
 * @package EyesOnJobs
 */

defined( 'ABSPATH' ) || exit;

require EOJ_THEME_DIR . '/inc/blocks.php';
require EOJ_THEME_DIR . '/inc/content-pages.php';

/**
 * Admin menu entry.
 */
function eoj_setup_menu() {
	add_theme_page( __( 'Eyes on Jobs Setup', 'eyesonjobs' ), __( 'Eyes on Jobs Setup', 'eyesonjobs' ), 'manage_options', 'eoj-setup', 'eoj_setup_screen' );
}
add_action( 'admin_menu', 'eoj_setup_menu' );

/**
 * Nudge the admin to run setup after activating the theme.
 */
function eoj_setup_notice() {
	if ( get_option( 'eoj_setup_done' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_eoj-setup' === $screen->id ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p><strong>%1$s</strong> %2$s <a class="button button-primary" href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Eyes on Jobs theme is active.', 'eyesonjobs' ),
		esc_html__( 'Create the pages, menus and home page in one click.', 'eyesonjobs' ),
		esc_url( admin_url( 'themes.php?page=eoj-setup' ) ),
		esc_html__( 'Run setup', 'eyesonjobs' )
	);
}
add_action( 'admin_notices', 'eoj_setup_notice' );

/**
 * Setup screen.
 */
function eoj_setup_screen() {
	$report = get_transient( 'eoj_setup_report' );
	delete_transient( 'eoj_setup_report' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Eyes on Jobs Setup', 'eyesonjobs' ); ?></h1>
		<?php if ( $report ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $report ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'This creates every page of the new site (Home, About, Services, Executive Search, Background Verification, Job Preparation Package and its registration page, Submit Resume, Employers, Contact, Account and the policy pages), builds the main and footer menus, and sets the home page.', 'eyesonjobs' ); ?></p>
		<?php if ( ! defined( 'EOJ_CORE_VERSION' ) ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Activate the “Eyes on Jobs Core” plugin first so the jobs, forms, login and payment features work.', 'eyesonjobs' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'eoj_setup' ); ?>
			<input type="hidden" name="action" value="eoj_setup">
			<p><label><input type="checkbox" name="overwrite" value="1"> <?php esc_html_e( 'Replace the content of pages that already exist with the same address (for example /about-us/). The old text stays available under Revisions in each page.', 'eyesonjobs' ); ?></label></p>
			<p><label><input type="checkbox" name="menus" value="1" checked> <?php esc_html_e( 'Create and assign the main, footer and legal menus', 'eyesonjobs' ); ?></label></p>
			<?php submit_button( __( 'Run setup', 'eyesonjobs' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle the setup form.
 */
function eoj_setup_handle() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'eyesonjobs' ) );
	}
	check_admin_referer( 'eoj_setup' );
	$result = eoj_run_setup( ! empty( $_POST['overwrite'] ), ! empty( $_POST['menus'] ) );
	/* translators: 1: created count, 2: updated count, 3: skipped count */
	set_transient( 'eoj_setup_report', sprintf( __( 'Done. %1$d pages created, %2$d updated, %3$d left unchanged.', 'eyesonjobs' ), $result['created'], $result['updated'], $result['skipped'] ), 60 );
	wp_safe_redirect( admin_url( 'themes.php?page=eoj-setup' ) );
	exit;
}
add_action( 'admin_post_eoj_setup', 'eoj_setup_handle' );

/**
 * Create pages, menus and reading settings.
 *
 * @param bool $overwrite Replace existing page content.
 * @param bool $menus     Build menus.
 * @return array Counts.
 */
function eoj_run_setup( $overwrite = false, $menus = true ) {
	$counts = array(
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
	);
	$ids    = array();

	foreach ( eoj_page_definitions() as $slug => $page ) {
		$parent_id = 0;
		$path      = $slug;
		if ( ! empty( $page['parent'] ) ) {
			$parent_id = isset( $ids[ $page['parent'] ] ) ? $ids[ $page['parent'] ] : 0;
			$path      = $page['parent'] . '/' . $slug;
		}
		$existing = get_page_by_path( $path );
		$data     = array(
			'post_title'   => $page['title'],
			'post_name'    => $slug,
			'post_content' => $page['content'],
			'post_excerpt' => isset( $page['excerpt'] ) ? $page['excerpt'] : '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_parent'  => $parent_id,
		);
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			if ( $overwrite ) {
				$data['ID'] = $existing->ID;
				wp_update_post( wp_slash( $data ) );
				++$counts['updated'];
			} else {
				++$counts['skipped'];
			}
		} else {
			$ids[ $slug ] = wp_insert_post( wp_slash( $data ) );
			++$counts['created'];
		}
	}

	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	if ( ! empty( $ids['privacy-policy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['privacy-policy'] );
	}

	if ( $menus ) {
		eoj_build_menus( $ids );
	}

	update_option( 'eoj_setup_done', time() );
	flush_rewrite_rules();
	return $counts;
}

/**
 * Build the three menus.
 *
 * @param array $ids Page IDs by slug.
 */
function eoj_build_menus( $ids ) {
	$jobs_url = post_type_exists( 'eoj_job' ) ? get_post_type_archive_link( 'eoj_job' ) : home_url( '/jobs/' );
	$menus    = array(
		'primary' => array(
			'name'  => 'Main menu',
			'items' => array(
				array( 'home', null ),
				array(
					'about-us',
					array( 'social-responsibility' ),
				),
				array(
					'services',
					array( 'executive-search', 'background-verification', 'job-preparation-package' ),
				),
				array(
					array( __( 'Jobs', 'eyesonjobs' ), $jobs_url ),
					array( array( __( 'Browse jobs', 'eyesonjobs' ), $jobs_url ), 'submit-resume' ),
				),
				array( 'employers', null ),
				array( 'contact-us', null ),
			),
		),
		'footer'  => array(
			'name'  => 'Footer quick links',
			'items' => array(
				array( 'about-us', null ),
				array( array( __( 'Jobs', 'eyesonjobs' ), $jobs_url ), null ),
				array( 'employers', null ),
				array( 'social-responsibility', null ),
				array( 'contact-us', null ),
			),
		),
		'legal'   => array(
			'name'  => 'Footer legal links',
			'items' => array(
				array( 'privacy-policy', null ),
				array( 'terms-and-conditions', null ),
				array( 'refund-policy', null ),
			),
		),
	);

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus as $location => $menu ) {
		$existing = wp_get_nav_menu_object( $menu['name'] );
		if ( $existing ) {
			wp_delete_nav_menu( $existing->term_id );
		}
		$menu_id = wp_create_nav_menu( $menu['name'] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		foreach ( $menu['items'] as $item ) {
			list( $top, $children ) = $item;
			$parent_item            = eoj_add_menu_item( $menu_id, $top, $ids, 0 );
			if ( $children && $parent_item ) {
				foreach ( $children as $child ) {
					eoj_add_menu_item( $menu_id, $child, $ids, $parent_item );
				}
			}
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Add one menu item (a page slug or a [label, url] pair).
 *
 * @param int          $menu_id Menu.
 * @param string|array $item    Slug or link.
 * @param array        $ids     Page IDs.
 * @param int          $parent  Parent item ID.
 * @return int Item ID.
 */
function eoj_add_menu_item( $menu_id, $item, $ids, $parent ) {
	if ( is_array( $item ) ) {
		$args = array(
			'menu-item-title'  => $item[0],
			'menu-item-url'    => $item[1],
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		);
	} else {
		if ( empty( $ids[ $item ] ) ) {
			return 0;
		}
		$args = array(
			'menu-item-object-id' => $ids[ $item ],
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		);
		if ( 'home' === $item ) {
			$args['menu-item-title'] = __( 'Home', 'eyesonjobs' );
		}
	}
	$args['menu-item-parent-id'] = $parent;
	$id                          = wp_update_nav_menu_item( $menu_id, 0, $args );
	return is_wp_error( $id ) ? 0 : $id;
}
