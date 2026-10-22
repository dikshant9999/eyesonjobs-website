<?php
/**
 * Jobs: post type, taxonomies, meta box, archive filtering, JobPosting
 * structured data and redirects from the old site's URLs.
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Starter terms.
 */
function eoj_job_term_defaults() {
	return array(
		'eoj_job_category' => array( 'IT & Software', 'Sales', 'Marketing', 'Finance & Accounts', 'HR', 'Fashion & Design', 'Manufacturing', 'Logistics & Supply Chain', 'Banking & Financial Services', 'Hospitality', 'Retail', 'Media & Advertising', 'Infrastructure', 'Consumer & Services' ),
		'eoj_job_location' => array( 'Delhi NCR', 'Gurugram', 'Noida', 'Delhi', 'Mumbai', 'Bengaluru', 'Pune', 'Hyderabad', 'Chennai', 'Kolkata', 'Ahmedabad', 'Pan India' ),
		'eoj_job_type'     => array( 'Full Time', 'Part Time', 'Contract', 'Internship', 'Freelance' ),
	);
}

/**
 * Register the job post type and its taxonomies.
 */
function eoj_register_job_types() {
	register_post_type(
		'eoj_job',
		array(
			'labels'       => array(
				'name'               => __( 'Jobs', 'eyesonjobs-core' ),
				'singular_name'      => __( 'Job', 'eyesonjobs-core' ),
				'add_new'            => __( 'Add New', 'eyesonjobs-core' ),
				'add_new_item'       => __( 'Add New Job', 'eyesonjobs-core' ),
				'edit_item'          => __( 'Edit Job', 'eyesonjobs-core' ),
				'new_item'           => __( 'New Job', 'eyesonjobs-core' ),
				'view_item'          => __( 'View Job', 'eyesonjobs-core' ),
				'search_items'       => __( 'Search Jobs', 'eyesonjobs-core' ),
				'not_found'          => __( 'No jobs found.', 'eyesonjobs-core' ),
				'all_items'          => __( 'All Jobs', 'eyesonjobs-core' ),
				'featured_image'     => __( 'Company logo', 'eyesonjobs-core' ),
				'set_featured_image' => __( 'Set company logo', 'eyesonjobs-core' ),
			),
			'public'       => true,
			'has_archive'  => 'jobs',
			'rewrite'      => array(
				'slug'       => 'jobs',
				'with_front' => false,
			),
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-businessperson',
			'menu_position' => 25,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		)
	);

	$taxonomies = array(
		'eoj_job_category' => array( __( 'Job Categories', 'eyesonjobs-core' ), __( 'Job Category', 'eyesonjobs-core' ), 'job-category' ),
		'eoj_job_location' => array( __( 'Job Locations', 'eyesonjobs-core' ), __( 'Job Location', 'eyesonjobs-core' ), 'job-location' ),
		'eoj_job_type'     => array( __( 'Job Types', 'eyesonjobs-core' ), __( 'Job Type', 'eyesonjobs-core' ), 'job-type' ),
	);
	foreach ( $taxonomies as $tax => $info ) {
		register_taxonomy(
			$tax,
			'eoj_job',
			array(
				'labels'            => array(
					'name'          => $info[0],
					'singular_name' => $info[1],
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => $info[2],
					'with_front' => false,
				),
			)
		);
	}
}
add_action( 'init', 'eoj_register_job_types' );

/**
 * Create the starter categories, locations and job types (skips existing).
 */
function eoj_seed_job_terms() {
	foreach ( eoj_job_term_defaults() as $taxonomy => $names ) {
		foreach ( $names as $name ) {
			if ( ! term_exists( $name, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy );
			}
		}
	}
}

/**
 * Job details meta fields.
 */
function eoj_job_meta_fields() {
	return array(
		'_eoj_company'       => array( __( 'Company', 'eyesonjobs-core' ), 'text', '' ),
		'_eoj_experience'    => array( __( 'Experience', 'eyesonjobs-core' ), 'text', __( 'e.g. 3-5 years', 'eyesonjobs-core' ) ),
		'_eoj_salary'        => array( __( 'Salary', 'eyesonjobs-core' ), 'text', __( 'e.g. ₹6-9 LPA', 'eyesonjobs-core' ) ),
		'_eoj_apply_email'   => array( __( 'Also email applications to', 'eyesonjobs-core' ), 'email', __( 'Optional. The Eyes on Jobs team always gets a copy.', 'eyesonjobs-core' ) ),
		'_eoj_valid_through' => array( __( 'Apply by (valid through)', 'eyesonjobs-core' ), 'date', __( 'Optional. After this date the job disappears from the listing.', 'eyesonjobs-core' ) ),
	);
}

// Keys are listed here (not read from eoj_job_meta_fields()) so no translation runs before "init".
foreach ( array( '_eoj_company', '_eoj_experience', '_eoj_salary', '_eoj_apply_email', '_eoj_valid_through' ) as $eoj_meta_key ) {
	add_action(
		'init',
		function () use ( $eoj_meta_key ) {
			register_post_meta(
				'eoj_job',
				$eoj_meta_key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	);
}
unset( $eoj_meta_key );

add_action(
	'add_meta_boxes_eoj_job',
	function () {
		add_meta_box( 'eoj-job-details', __( 'Job details', 'eyesonjobs-core' ), 'eoj_job_details_box', 'eoj_job', 'normal', 'high' );
	}
);

/**
 * Meta box output.
 *
 * @param WP_Post $post Job.
 */
function eoj_job_details_box( $post ) {
	wp_nonce_field( 'eoj_job_details', 'eoj_job_nonce' );
	echo '<table class="form-table"><tbody>';
	foreach ( eoj_job_meta_fields() as $key => $field ) {
		printf(
			'<tr><th><label for="%1$s">%2$s</label></th><td><input type="%3$s" class="regular-text" id="%1$s" name="%1$s" value="%4$s" placeholder="%5$s">%6$s</td></tr>',
			esc_attr( $key ),
			esc_html( $field[0] ),
			esc_attr( $field[1] ),
			esc_attr( get_post_meta( $post->ID, $key, true ) ),
			'date' === $field[1] ? '' : esc_attr( $field[2] ),
			'date' === $field[1] && $field[2] ? '<p class="description">' . esc_html( $field[2] ) . '</p>' : ''
		);
	}
	echo '</tbody></table><p class="description">' . esc_html__( 'Use the “Company logo” box on the right for the company logo. Choose a category, location and job type from the boxes on the right.', 'eyesonjobs-core' ) . '</p>';
}

add_action(
	'save_post_eoj_job',
	function ( $post_id ) {
		if ( ! isset( $_POST['eoj_job_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eoj_job_nonce'] ) ), 'eoj_job_details' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( eoj_job_meta_fields() as $key => $field ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			if ( 'email' === $field[1] ) {
				$val = sanitize_email( $raw );
			} elseif ( 'date' === $field[1] ) {
				$val = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
			} else {
				$val = sanitize_text_field( $raw );
			}
			update_post_meta( $post_id, $key, $val );
		}
	}
);

/**
 * Whether a query is a front-end job listing.
 *
 * @param WP_Query $query Query.
 */
function eoj_is_job_listing( $query ) {
	return ! is_admin() && $query->is_main_query() && ( $query->is_post_type_archive( 'eoj_job' ) || $query->is_tax( array( 'eoj_job_category', 'eoj_job_location', 'eoj_job_type' ) ) );
}

/**
 * Filter the listing: ?k= keyword, ?loc= and ?cat= term slugs, 10 per page,
 * and hide jobs past their valid-through date.
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! eoj_is_job_listing( $query ) ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$k   = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
		$loc = isset( $_GET['loc'] ) ? sanitize_title( wp_unslash( $_GET['loc'] ) ) : '';
		$cat = isset( $_GET['cat'] ) && is_string( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : '';
		// phpcs:enable

		$query->set( 'post_type', 'eoj_job' );
		$query->set( 'posts_per_page', 10 );

		if ( '' !== $k ) {
			$query->set( 's', $k );
			$query->set( 'eoj_job_search', 1 );
		}

		$tax_query = (array) $query->get( 'tax_query' );
		if ( $loc ) {
			$tax_query[] = array(
				'taxonomy' => 'eoj_job_location',
				'field'    => 'slug',
				'terms'    => $loc,
			);
		}
		if ( $cat ) {
			$tax_query[] = array(
				'taxonomy' => 'eoj_job_category',
				'field'    => 'slug',
				'terms'    => $cat,
			);
		}
		if ( $tax_query ) {
			$query->set( 'tax_query', $tax_query );
		}

		$meta_query   = (array) $query->get( 'meta_query' );
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_eoj_valid_through',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_eoj_valid_through',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => '_eoj_valid_through',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		);
		$query->set( 'meta_query', $meta_query );
	}
);

/**
 * The keyword box also matches the company name.
 */
add_filter(
	'posts_search',
	function ( $search, $query ) {
		global $wpdb;
		if ( ! $search || ! $query->get( 'eoj_job_search' ) ) {
			return $search;
		}
		$like = '%' . $wpdb->esc_like( (string) $query->get( 's' ) ) . '%';
		$like_sql = $wpdb->prepare( '%s', $like );
		return " AND ( (1=1 {$search}) OR EXISTS ( SELECT 1 FROM {$wpdb->postmeta} eojpm WHERE eojpm.post_id = {$wpdb->posts}.ID AND eojpm.meta_key = '_eoj_company' AND eojpm.meta_value LIKE {$like_sql} ) ) ";
	},
	10,
	2
);

/**
 * Google JobPosting structured data on single jobs.
 */
add_action(
	'wp_head',
	function () {
		if ( ! is_singular( 'eoj_job' ) ) {
			return;
		}
		$id      = get_queried_object_id();
		$company = (string) get_post_meta( $id, '_eoj_company', true );
		$valid   = (string) get_post_meta( $id, '_eoj_valid_through', true );
		$types   = get_the_terms( $id, 'eoj_job_type' );
		$locs    = get_the_terms( $id, 'eoj_job_location' );
		$map     = array(
			'full time'  => 'FULL_TIME',
			'part time'  => 'PART_TIME',
			'contract'   => 'CONTRACTOR',
			'internship' => 'INTERN',
			'freelance'  => 'CONTRACTOR',
		);
		$data    = array(
			'@context'           => 'https://schema.org/',
			'@type'              => 'JobPosting',
			'title'              => get_the_title( $id ),
			'description'        => wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) ),
			'datePosted'         => get_the_date( 'c', $id ),
			'hiringOrganization' => array(
				'@type' => 'Organization',
				'name'  => $company ? $company : get_bloginfo( 'name' ),
			),
			'directApply'        => true,
			'url'                => get_permalink( $id ),
		);
		if ( $valid ) {
			$data['validThrough'] = $valid . 'T23:59:59+05:30';
		}
		$logo = get_the_post_thumbnail_url( $id, 'medium' );
		if ( $logo ) {
			$data['hiringOrganization']['logo'] = $logo;
		}
		if ( $types && ! is_wp_error( $types ) ) {
			$slug = strtolower( $types[0]->name );
			if ( isset( $map[ $slug ] ) ) {
				$data['employmentType'] = $map[ $slug ];
			}
		}
		if ( $locs && ! is_wp_error( $locs ) ) {
			$data['jobLocation'] = array(
				'@type'   => 'Place',
				'address' => array(
					'@type'           => 'PostalAddress',
					'addressLocality' => $locs[0]->name,
					'addressCountry'  => 'IN',
				),
			);
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
	}
);

/**
 * URL of the account page.
 */
function eoj_core_account_url() {
	return eoj_page_url( 'account', '/account/' );
}

/**
 * 301 redirects from the old site: /jobs-search/ and /member/…
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$uri  = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		$path = isset( $uri['path'] ) ? $uri['path'] : '';
		$base = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path = '/' . trim( preg_replace( '#^' . preg_quote( untrailingslashit( (string) $base ), '#' ) . '#', '', $path ), '/' );
		$path = strtolower( $path );

		if ( '/jobs-search' === $path || 0 === strpos( $path, '/jobs-search/' ) ) {
			wp_safe_redirect( get_post_type_archive_link( 'eoj_job' ), 301 );
			exit;
		}
		if ( '/member' === $path || 0 === strpos( $path, '/member/' ) ) {
			$target = eoj_core_account_url();
			$query  = array();
			if ( ! empty( $uri['query'] ) ) {
				wp_parse_str( $uri['query'], $query );
			}
			if ( ( isset( $query['mode'] ) && 'register' === $query['mode'] ) || false !== strpos( $path, 'register' ) ) {
				$target = add_query_arg( 'tab', 'register', $target );
			}
			wp_safe_redirect( $target, 301 );
			exit;
		}
	},
	1
);
