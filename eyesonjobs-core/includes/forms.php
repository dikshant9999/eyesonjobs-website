<?php
/**
 * Public forms: contact, resume, employer requirement, job application.
 * One admin-post endpoint routes every form by its "eoj_form" value.
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL of a page by slug, with a fallback path.
 *
 * @param string $slug     Page path.
 * @param string $fallback Fallback path.
 */
function eoj_page_url( $slug, $fallback = '' ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( $fallback ? $fallback : '/' . $slug . '/' );
}

/**
 * Total experience choices.
 */
function eoj_experience_options() {
	return array(
		'Fresher'    => __( 'Fresher', 'eyesonjobs-core' ),
		'0-2 years'  => __( '0-2 years', 'eyesonjobs-core' ),
		'2-5 years'  => __( '2-5 years', 'eyesonjobs-core' ),
		'5-10 years' => __( '5-10 years', 'eyesonjobs-core' ),
		'10+ years'  => __( '10+ years', 'eyesonjobs-core' ),
	);
}

/**
 * Industry choices from the job categories.
 */
function eoj_industry_options() {
	$out = array();
	if ( taxonomy_exists( 'eoj_job_category' ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'eoj_job_category',
				'hide_empty' => false,
			)
		);
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$out[ $term->name ] = $term->name;
			}
		}
	}
	if ( ! $out ) {
		$defaults = eoj_job_term_defaults();
		foreach ( $defaults['eoj_job_category'] as $name ) {
			$out[ $name ] = $name;
		}
	}
	return $out;
}

/**
 * Field definitions per form.
 */
function eoj_contact_fields() {
	return array(
		array( 'name' => 'name', 'label' => __( 'Your name', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'autocomplete' => 'name' ),
		array( 'name' => 'email', 'label' => __( 'Email', 'eyesonjobs-core' ), 'type' => 'email', 'required' => true, 'half' => true, 'autocomplete' => 'email' ),
		array( 'name' => 'phone', 'label' => __( 'Phone', 'eyesonjobs-core' ), 'type' => 'tel', 'half' => true, 'autocomplete' => 'tel', 'placeholder' => '+91 98765 43210' ),
		array(
			'name'     => 'iam',
			'label'    => __( 'I am a', 'eyesonjobs-core' ),
			'type'     => 'select',
			'required' => true,
			'half'     => true,
			'options'  => array(
				'candidate' => __( 'Candidate', 'eyesonjobs-core' ),
				'employer'  => __( 'Employer', 'eyesonjobs-core' ),
				'other'     => __( 'Other', 'eyesonjobs-core' ),
			),
		),
		array( 'name' => 'subject', 'label' => __( 'Subject', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true ),
		array( 'name' => 'message', 'label' => __( 'Message', 'eyesonjobs-core' ), 'type' => 'textarea', 'required' => true, 'rows' => 5 ),
	);
}

/**
 * Resume form fields.
 */
function eoj_resume_fields() {
	return array(
		array( 'name' => 'name', 'label' => __( 'Full name', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'autocomplete' => 'name' ),
		array( 'name' => 'email', 'label' => __( 'Email', 'eyesonjobs-core' ), 'type' => 'email', 'required' => true, 'half' => true, 'autocomplete' => 'email' ),
		array( 'name' => 'phone', 'label' => __( 'Mobile number', 'eyesonjobs-core' ), 'type' => 'tel', 'required' => true, 'half' => true, 'autocomplete' => 'tel', 'placeholder' => '+91 98765 43210' ),
		array( 'name' => 'city', 'label' => __( 'Current city', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'autocomplete' => 'address-level2' ),
		array( 'name' => 'designation', 'label' => __( 'Current designation', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true ),
		array( 'name' => 'experience', 'label' => __( 'Total experience', 'eyesonjobs-core' ), 'type' => 'select', 'required' => true, 'half' => true, 'options' => eoj_experience_options() ),
		array( 'name' => 'industry', 'label' => __( 'Industry', 'eyesonjobs-core' ), 'type' => 'select', 'half' => true, 'options' => eoj_industry_options() ),
		array( 'name' => 'preferred_location', 'label' => __( 'Preferred location', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true, 'placeholder' => __( 'e.g. Gurugram, Pan India', 'eyesonjobs-core' ) ),
		array( 'name' => 'current_ctc', 'label' => __( 'Current CTC (per year)', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true, 'placeholder' => __( 'e.g. 6 LPA', 'eyesonjobs-core' ) ),
		array( 'name' => 'expected_ctc', 'label' => __( 'Expected CTC (per year)', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true, 'placeholder' => __( 'e.g. 8 LPA', 'eyesonjobs-core' ) ),
		array( 'name' => 'notice_period', 'label' => __( 'Notice period', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true, 'placeholder' => __( 'e.g. 30 days', 'eyesonjobs-core' ) ),
		array( 'name' => 'linkedin', 'label' => __( 'LinkedIn profile', 'eyesonjobs-core' ), 'type' => 'url', 'half' => true, 'placeholder' => 'https://linkedin.com/in/…' ),
		array( 'name' => 'resume', 'label' => __( 'Upload resume', 'eyesonjobs-core' ), 'type' => 'file', 'required' => true ),
		eoj_consent_field( 'resume' ),
	);
}

/**
 * Consent checkbox that links to the privacy policy.
 *
 * @param string $kind Form kind.
 */
function eoj_consent_field( $kind ) {
	/* translators: %s privacy policy link */
	$label = sprintf( __( 'I agree that Eyes on Jobs may store my details and contact me about suitable opportunities. See the %s.', 'eyesonjobs-core' ), '<a href="' . esc_url( eoj_page_url( 'privacy-policy' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'eyesonjobs-core' ) . '</a>' );
	if ( 'requirement' === $kind ) {
		$label = sprintf( __( 'I agree that Eyes on Jobs may store these details and contact me about this requirement. See the %s.', 'eyesonjobs-core' ), '<a href="' . esc_url( eoj_page_url( 'privacy-policy' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'eyesonjobs-core' ) . '</a>' );
	}
	return array( 'name' => 'consent', 'label' => $label, 'type' => 'checkbox', 'required' => true );
}

/**
 * Employer requirement fields.
 */
function eoj_requirement_fields() {
	return array(
		array( 'name' => 'company', 'label' => __( 'Company name', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'autocomplete' => 'organization' ),
		array( 'name' => 'name', 'label' => __( 'Contact person', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'autocomplete' => 'name' ),
		array( 'name' => 'email', 'label' => __( 'Work email', 'eyesonjobs-core' ), 'type' => 'email', 'required' => true, 'half' => true, 'autocomplete' => 'email' ),
		array( 'name' => 'phone', 'label' => __( 'Phone', 'eyesonjobs-core' ), 'type' => 'tel', 'required' => true, 'half' => true, 'autocomplete' => 'tel', 'placeholder' => '+91 98765 43210' ),
		array( 'name' => 'roles', 'label' => __( 'Roles you are hiring for', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'placeholder' => __( 'e.g. Sales Manager, Accountant', 'eyesonjobs-core' ) ),
		array( 'name' => 'positions', 'label' => __( 'Number of positions', 'eyesonjobs-core' ), 'type' => 'number', 'required' => true, 'half' => true ),
		array( 'name' => 'location', 'label' => __( 'Job location', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true ),
		array( 'name' => 'budget', 'label' => __( 'Budget / salary range', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true, 'placeholder' => __( 'e.g. 6-9 LPA', 'eyesonjobs-core' ) ),
		array(
			'name'     => 'service',
			'label'    => __( 'Service needed', 'eyesonjobs-core' ),
			'type'     => 'select',
			'required' => true,
			'half'     => true,
			'options'  => array(
				'Permanent hiring'        => __( 'Permanent hiring', 'eyesonjobs-core' ),
				'Executive search'        => __( 'Executive search', 'eyesonjobs-core' ),
				'Campus / bulk hiring'    => __( 'Campus / bulk hiring', 'eyesonjobs-core' ),
				'Background verification' => __( 'Background verification', 'eyesonjobs-core' ),
			),
		),
		array( 'name' => 'details', 'label' => __( 'Details of the requirement', 'eyesonjobs-core' ), 'type' => 'textarea', 'rows' => 4 ),
		array( 'name' => 'jd', 'label' => __( 'Job description (optional)', 'eyesonjobs-core' ), 'type' => 'file' ),
		eoj_consent_field( 'requirement' ),
	);
}

/**
 * Job application fields.
 */
function eoj_apply_fields() {
	return array(
		array( 'name' => 'name', 'label' => __( 'Full name', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'autocomplete' => 'name' ),
		array( 'name' => 'email', 'label' => __( 'Email', 'eyesonjobs-core' ), 'type' => 'email', 'required' => true, 'autocomplete' => 'email' ),
		array( 'name' => 'phone', 'label' => __( 'Mobile number', 'eyesonjobs-core' ), 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel', 'placeholder' => '+91 98765 43210' ),
		array( 'name' => 'experience', 'label' => __( 'Total experience', 'eyesonjobs-core' ), 'type' => 'select', 'required' => true, 'half' => true, 'options' => eoj_experience_options() ),
		array( 'name' => 'current_ctc', 'label' => __( 'Current CTC', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true, 'placeholder' => __( 'e.g. 6 LPA', 'eyesonjobs-core' ) ),
		array( 'name' => 'resume', 'label' => __( 'Upload resume', 'eyesonjobs-core' ), 'type' => 'file', 'required' => true ),
		array( 'name' => 'message', 'label' => __( 'Message (optional)', 'eyesonjobs-core' ), 'type' => 'textarea', 'rows' => 3 ),
	);
}

/**
 * Lead form configuration.
 *
 * @return array
 */
function eoj_lead_forms() {
	return array(
		'eoj_contact'     => array(
			'type'   => 'contact',
			'fields' => 'eoj_contact_fields',
			'ok'     => 'sent',
			'file'   => '',
		),
		'eoj_resume'      => array(
			'type'   => 'resume',
			'fields' => 'eoj_resume_fields',
			'ok'     => 'resume',
			'file'   => 'resume',
		),
		'eoj_requirement' => array(
			'type'   => 'requirement',
			'fields' => 'eoj_requirement_fields',
			'ok'     => 'requirement',
			'file'   => 'jd',
		),
		'eoj_apply'       => array(
			'type'   => 'application',
			'fields' => 'eoj_apply_fields',
			'ok'     => 'applied',
			'file'   => 'resume',
		),
	);
}

/**
 * Values to prefill for a logged-in user.
 */
function eoj_user_defaults() {
	if ( ! is_user_logged_in() ) {
		return array();
	}
	$user = wp_get_current_user();
	return array(
		'name'    => $user->display_name,
		'email'   => $user->user_email,
		'phone'   => get_user_meta( $user->ID, 'eoj_phone', true ),
		'company' => get_user_meta( $user->ID, 'eoj_company', true ),
	);
}

/**
 * Note about Google reCAPTCHA, required by Google's terms when the badge is hidden.
 */
function eoj_recaptcha_note() {
	if ( ! eoj_recaptcha_enabled() ) {
		return '';
	}
	return '<p class="eoj-recaptcha-note">' . wp_kses_post(
		sprintf(
			/* translators: 1: privacy link, 2: terms link */
			__( 'Protected by reCAPTCHA. Google’s %1$s and %2$s apply.', 'eyesonjobs-core' ),
			'<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'eyesonjobs-core' ) . '</a>',
			'<a href="https://policies.google.com/terms" target="_blank" rel="noopener">' . esc_html__( 'Terms of Service', 'eyesonjobs-core' ) . '</a>'
		)
	) . '</p>';
}

/**
 * Whether the visitor just completed this form successfully.
 *
 * @param string $form Form id.
 */
function eoj_form_succeeded( $form ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	return isset( $_GET['eoj_form'], $_GET['eoj_status'] ) && $form === $_GET['eoj_form'] && 'ok' === $_GET['eoj_status'];
	// phpcs:enable
}

/**
 * Render a lead form.
 *
 * @param string $form   Form id.
 * @param string $submit Button label.
 * @param string $hidden Extra hidden inputs (already escaped).
 * @param string $intro  HTML shown above the fields.
 * @return string
 */
function eoj_render_form( $form, $submit, $hidden = '', $intro = '' ) {
	eoj_enqueue_form_assets();
	$cfg = eoj_lead_forms();
	if ( ! isset( $cfg[ $form ] ) ) {
		return '';
	}
	$notice = eoj_form_notice( $form );
	if ( eoj_form_succeeded( $form ) ) {
		return '<div id="' . esc_attr( $form ) . '">' . $notice . '</div>';
	}
	$fields = call_user_func( $cfg[ $form ]['fields'] );

	$html  = '<form id="' . esc_attr( $form ) . '" class="eoj-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data" data-recaptcha="' . esc_attr( eoj_recaptcha_action( $form ) ) . '">';
	$html .= eoj_form_hidden( $form ) . $hidden;
	$html .= $notice . $intro;
	$html .= eoj_render_fields( $form, $fields, eoj_user_defaults() );
	$html .= '<button type="submit" class="eoj-btn eoj-btn--primary eoj-btn--block">' . esc_html( $submit ) . '</button>';
	$html .= eoj_recaptcha_note();
	$html .= '</form>';
	return $html;
}

add_shortcode(
	'eoj_contact_form',
	function () {
		return eoj_render_form( 'eoj_contact', __( 'Send message', 'eyesonjobs-core' ) );
	}
);

add_shortcode(
	'eoj_resume_form',
	function () {
		return eoj_render_form( 'eoj_resume', __( 'Submit resume', 'eyesonjobs-core' ) );
	}
);

add_shortcode(
	'eoj_requirement_form',
	function () {
		return eoj_render_form( 'eoj_requirement', __( 'Send requirement', 'eyesonjobs-core' ) );
	}
);

add_shortcode(
	'eoj_apply_form',
	function ( $atts ) {
		$atts   = shortcode_atts( array( 'job' => 0 ), $atts );
		$job_id = absint( $atts['job'] ) ? absint( $atts['job'] ) : get_the_ID();
		if ( ! $job_id || 'eoj_job' !== get_post_type( $job_id ) ) {
			return '';
		}
		return eoj_render_form( 'eoj_apply', __( 'Apply now', 'eyesonjobs-core' ), '<input type="hidden" name="eoj_job" value="' . esc_attr( $job_id ) . '">' );
	}
);

/**
 * The one submission endpoint. Other modules add their forms with the
 * "eoj_form_handlers" filter.
 */
function eoj_handle_submit() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- see eoj_check_request().
	$form     = isset( $_POST['eoj_form'] ) ? sanitize_key( wp_unslash( $_POST['eoj_form'] ) ) : '';
	$handlers = array();
	foreach ( array_keys( eoj_lead_forms() ) as $lead ) {
		$handlers[ $lead ] = 'eoj_submit_lead';
	}
	$handlers = apply_filters( 'eoj_form_handlers', $handlers );
	if ( ! $form || ! isset( $handlers[ $form ] ) || ! is_callable( $handlers[ $form ] ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	call_user_func( $handlers[ $form ], $form );
	wp_safe_redirect( home_url( '/' ) );
	exit;
}
add_action( 'admin_post_nopriv_eoj_submit', 'eoj_handle_submit' );
add_action( 'admin_post_eoj_submit', 'eoj_handle_submit' );

/**
 * Handle contact, resume, requirement and application forms.
 *
 * @param string $form Form id.
 */
function eoj_submit_lead( $form ) {
	$forms  = eoj_lead_forms();
	$cfg    = $forms[ $form ];
	$fields = call_user_func( $cfg['fields'] );

	$check = eoj_check_request( $form );
	if ( true !== $check ) {
		eoj_redirect_back( $form, false, $check );
	}

	$job = null;
	if ( 'eoj_apply' === $form ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$job_id = isset( $_POST['eoj_job'] ) ? absint( $_POST['eoj_job'] ) : 0;
		$job    = $job_id ? get_post( $job_id ) : null;
		if ( ! $job || 'eoj_job' !== $job->post_type || 'publish' !== $job->post_status ) {
			eoj_redirect_back( $form, false, 'error' );
		}
	}

	$result = eoj_collect_fields( $fields );
	if ( $result['errors'] ) {
		eoj_fail_with_fields( $form, $fields, $result );
	}

	$values = $result['values'];
	$rows   = eoj_labelled_values( $fields, $values );
	$file   = $cfg['file'] && isset( $result['files'][ $cfg['file'] ] ) ? $result['files'][ $cfg['file'] ] : '';
	$name   = isset( $values['name'] ) ? $values['name'] : '';
	$email  = isset( $values['email'] ) ? $values['email'] : '';
	$phone  = isset( $values['phone'] ) ? $values['phone'] : '';

	if ( $job ) {
		$rows = array_merge(
			array(
				__( 'Job', 'eyesonjobs-core' )    => get_the_title( $job ),
				__( 'Company', 'eyesonjobs-core' ) => (string) get_post_meta( $job->ID, '_eoj_company', true ),
				__( 'Job link', 'eyesonjobs-core' ) => get_permalink( $job ),
			),
			$rows
		);
	}

	$meta = array(
		'name'  => $name,
		'email' => $email,
		'phone' => $phone,
		'file'  => $file,
	);
	if ( $job ) {
		$meta['job_id'] = $job->ID;
	}
	if ( is_user_logged_in() ) {
		$meta['user'] = get_current_user_id();
	}
	$entry_id = eoj_save_entry( $cfg['type'], $rows, $meta );
	if ( ! $entry_id ) {
		eoj_discard_files( $result['files'] );
		eoj_redirect_back( $form, false, 'error' );
	}

	$types   = eoj_entry_types();
	if ( $job ) {
		$subject = sprintf( 'New application: %s · %s', get_the_title( $job ), $name );
	} else {
		$subject = 'New ' . strtolower( $types[ $cfg['type'] ] ) . ': ' . ( 'requirement' === $cfg['type'] && ! empty( $values['company'] ) ? $values['company'] . ' (' . $name . ')' : $name );
	}
	$attachments = array();
	foreach ( $result['files'] as $path ) {
		$attachments[] = $path;
	}

	$to = eoj_notify_emails();
	if ( $job ) {
		$apply_email = sanitize_email( (string) get_post_meta( $job->ID, '_eoj_apply_email', true ) );
		if ( $apply_email && is_email( $apply_email ) ) {
			$to[] = $apply_email;
		}
	}
	/* translators: %s submission edit link */
	$intro = sprintf( __( 'A new submission arrived on the website. <a href="%s">Open it in the dashboard</a>.', 'eyesonjobs-core' ), esc_url( admin_url( 'post.php?post=' . $entry_id . '&action=edit' ) ) );
	eoj_mail( array_unique( $to ), $subject, $intro, $rows, $attachments, $email );

	// Short acknowledgement to the person who submitted.
	$ack_intro = array(
		'contact'     => __( 'Thank you for writing to us. We have received your message and will reply within one working day.', 'eyesonjobs-core' ),
		'resume'      => __( 'Thank you for sharing your resume. A recruiter will contact you when a suitable opening comes up.', 'eyesonjobs-core' ),
		'requirement' => __( 'Thank you for sharing your hiring requirement. A consultant will call you within one working day.', 'eyesonjobs-core' ),
		'application' => __( 'Thank you for applying. The recruiter will review your profile and contact you if it is a match.', 'eyesonjobs-core' ),
	);
	$ack_subject = array(
		'contact'     => __( 'We received your message', 'eyesonjobs-core' ),
		'resume'      => __( 'We received your resume', 'eyesonjobs-core' ),
		'requirement' => __( 'We received your requirement', 'eyesonjobs-core' ),
		'application' => $job ? sprintf( /* translators: %s job title */ __( 'Your application for %s', 'eyesonjobs-core' ), get_the_title( $job ) ) : __( 'Your application', 'eyesonjobs-core' ),
	);
	eoj_mail( $email, $ack_subject[ $cfg['type'] ], '<p>' . esc_html( sprintf( /* translators: %s name */ __( 'Hi %s,', 'eyesonjobs-core' ), $name ) ) . '</p><p>' . esc_html( $ack_intro[ $cfg['type'] ] ) . '</p>' );

	eoj_redirect_back( $form, true, $cfg['ok'] );
}
