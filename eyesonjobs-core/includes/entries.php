<?php
/**
 * Submissions: every form entry is stored as a private "eoj_entry" post so
 * nothing is lost if an email bounces. Dashboard → Submissions.
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Entry types and their labels.
 */
function eoj_entry_types() {
	return array(
		'jpp'         => __( 'Job Prep Package', 'eyesonjobs-core' ),
		'application' => __( 'Job application', 'eyesonjobs-core' ),
		'resume'      => __( 'Resume', 'eyesonjobs-core' ),
		'requirement' => __( 'Employer requirement', 'eyesonjobs-core' ),
		'contact'     => __( 'Contact message', 'eyesonjobs-core' ),
	);
}

/**
 * Register the post type.
 */
function eoj_register_entry_type() {
	register_post_type(
		'eoj_entry',
		array(
			'labels'          => array(
				'name'          => __( 'Submissions', 'eyesonjobs-core' ),
				'singular_name' => __( 'Submission', 'eyesonjobs-core' ),
				'edit_item'     => __( 'Submission details', 'eyesonjobs-core' ),
				'search_items'  => __( 'Search submissions', 'eyesonjobs-core' ),
				'not_found'     => __( 'No submissions yet.', 'eyesonjobs-core' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 26,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'eoj_register_entry_type' );

/**
 * Save an entry.
 *
 * @param string $type   Type key.
 * @param array  $fields Label => value.
 * @param array  $meta   Extra meta.
 * @return int Post ID.
 */
function eoj_save_entry( $type, $fields, $meta = array() ) {
	$types = eoj_entry_types();
	$name  = isset( $meta['name'] ) ? $meta['name'] : '';
	$id    = wp_insert_post(
		array(
			'post_type'   => 'eoj_entry',
			'post_status' => 'publish',
			'post_title'  => trim( ( isset( $types[ $type ] ) ? $types[ $type ] : $type ) . ' · ' . $name, ' ·' ),
			'post_author' => get_current_user_id(),
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_eoj_type', $type );
	update_post_meta( $id, '_eoj_fields', $fields );
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, '_eoj_' . $key, $value );
	}
	return $id;
}

/**
 * Admin list columns.
 */
add_filter(
	'manage_eoj_entry_posts_columns',
	function () {
		return array(
			'cb'         => '<input type="checkbox">',
			'title'      => __( 'Submission', 'eyesonjobs-core' ),
			'eoj_type'   => __( 'Type', 'eyesonjobs-core' ),
			'eoj_email'  => __( 'Email', 'eyesonjobs-core' ),
			'eoj_phone'  => __( 'Phone', 'eyesonjobs-core' ),
			'eoj_status' => __( 'Status', 'eyesonjobs-core' ),
			'date'       => __( 'Received', 'eyesonjobs-core' ),
		);
	}
);

add_action(
	'manage_eoj_entry_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'eoj_type':
				$types = eoj_entry_types();
				$type  = get_post_meta( $post_id, '_eoj_type', true );
				echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : $type );
				break;
			case 'eoj_email':
				$email = get_post_meta( $post_id, '_eoj_email', true );
				echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '';
				break;
			case 'eoj_phone':
				echo esc_html( get_post_meta( $post_id, '_eoj_phone', true ) );
				break;
			case 'eoj_status':
				$status = get_post_meta( $post_id, '_eoj_status', true );
				if ( 'paid' === $status ) {
					echo '<strong style="color:#15803d">' . esc_html__( 'Paid', 'eyesonjobs-core' ) . '</strong>';
				} elseif ( 'pending' === $status ) {
					if ( get_post_meta( $post_id, '_eoj_claimed_ref', true ) ) {
						echo '<strong style="color:#1d4ed8">' . esc_html__( 'Payment submitted: verify', 'eyesonjobs-core' ) . '</strong>';
					} else {
						echo '<span style="color:#b45309">' . esc_html__( 'Payment pending', 'eyesonjobs-core' ) . '</span>';
					}
				} else {
					echo esc_html__( 'Received', 'eyesonjobs-core' );
				}
				break;
		}
	},
	10,
	2
);

/**
 * Filter submissions by type.
 */
add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'eoj_entry' !== $post_type ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current = isset( $_GET['eoj_type'] ) ? sanitize_key( wp_unslash( $_GET['eoj_type'] ) ) : '';
		echo '<select name="eoj_type"><option value="">' . esc_html__( 'All types', 'eyesonjobs-core' ) . '</option>';
		foreach ( eoj_entry_types() as $key => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $label ) );
		}
		echo '</select>';
	}
);

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'eoj_entry' !== $query->get( 'post_type' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset( $_GET['eoj_type'] ) ? sanitize_key( wp_unslash( $_GET['eoj_type'] ) ) : '';
		if ( $type ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'   => '_eoj_type',
						'value' => $type,
					),
				)
			);
		}
	}
);

/**
 * Details box on the submission screen.
 */
add_action(
	'add_meta_boxes_eoj_entry',
	function () {
		add_meta_box( 'eoj-entry-details', __( 'Details', 'eyesonjobs-core' ), 'eoj_entry_details_box', 'eoj_entry', 'normal', 'high' );
		remove_meta_box( 'submitdiv', 'eoj_entry', 'side' );
	}
);

/**
 * Render details.
 *
 * @param WP_Post $post Entry.
 */
function eoj_entry_details_box( $post ) {
	$fields = (array) get_post_meta( $post->ID, '_eoj_fields', true );
	echo '<table class="widefat striped"><tbody>';
	foreach ( $fields as $label => $value ) {
		echo '<tr><th style="width:30%">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ) ) . '</td></tr>';
	}
	$extra = array(
		'_eoj_status'     => __( 'Payment status', 'eyesonjobs-core' ),
		'_eoj_amount'     => __( 'Amount (₹)', 'eyesonjobs-core' ),
		'_eoj_order_id'   => __( 'Razorpay order', 'eyesonjobs-core' ),
		'_eoj_payment_id' => __( 'Razorpay payment', 'eyesonjobs-core' ),
		'_eoj_claimed_method' => __( 'Customer says they paid by', 'eyesonjobs-core' ),
		'_eoj_claimed_ref' => __( 'Customer payment reference', 'eyesonjobs-core' ),
		'_eoj_claimed_at' => __( 'Payment submitted at', 'eyesonjobs-core' ),
		'_eoj_paid_at'    => __( 'Paid at', 'eyesonjobs-core' ),
	);
	foreach ( $extra as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( '' !== $value ) {
			if ( '_eoj_paid_at' === $key || '_eoj_claimed_at' === $key ) {
				$value = wp_date( 'j M Y, g:i a', (int) $value );
			}
			echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
		}
	}
	echo '</tbody></table>';
	if ( get_post_meta( $post->ID, '_eoj_file', true ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=eoj_download&entry=' . $post->ID ), 'eoj_download_' . $post->ID );
		echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Download attached resume', 'eyesonjobs-core' ) . '</a></p>';
	}
	if ( 'pending' === get_post_meta( $post->ID, '_eoj_status', true ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=eoj_mark_paid&entry=' . $post->ID ), 'eoj_mark_paid_' . $post->ID );
		echo '<p><a class="button" href="' . esc_url( $url ) . '" onclick="return confirm(\'Mark as paid? Use this only for payments received offline.\')">' . esc_html__( 'Mark as paid (offline payment)', 'eyesonjobs-core' ) . '</a></p>';
	}
}

/**
 * Secure resume download for staff.
 */
add_action(
	'admin_post_eoj_download',
	function () {
		$id = isset( $_GET['entry'] ) ? absint( $_GET['entry'] ) : 0;
		check_admin_referer( 'eoj_download_' . $id );
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'Not allowed.', 'eyesonjobs-core' ) );
		}
		$file = get_post_meta( $id, '_eoj_file', true );
		$base = realpath( eoj_private_upload_dir() );
		$real = $file ? realpath( $file ) : false;
		if ( ! $real || ! $base || 0 !== strpos( $real, $base ) ) {
			wp_die( esc_html__( 'File not found.', 'eyesonjobs-core' ) );
		}
		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $real ) . '"' );
		header( 'Content-Length: ' . filesize( $real ) );
		readfile( $real ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}
);
