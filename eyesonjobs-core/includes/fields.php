<?php
/**
 * Field definitions: one renderer, one sanitizer/validator for every form.
 *
 * A field is an array:
 *   name, label, type (text|email|tel|url|number|password|select|textarea|file|checkbox|radio|note),
 *   required, required_if (array( field, value )), options (value => label), half, placeholder,
 *   help, autocomplete, rows, show_for (role value that makes the field visible), style (roles).
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loose Indian phone check: 10 digits, optionally prefixed by +91, 91 or 0.
 *
 * @param string $phone Phone.
 * @return bool
 */
function eoj_phone_valid( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	if ( 12 === strlen( $digits ) && 0 === strpos( $digits, '91' ) ) {
		$digits = substr( $digits, 2 );
	} elseif ( 11 === strlen( $digits ) && 0 === strpos( $digits, '0' ) ) {
		$digits = substr( $digits, 1 );
	}
	return 10 === strlen( $digits );
}

/**
 * Name of the POST key for a field.
 *
 * @param string $name Field name.
 */
function eoj_field_key( $name ) {
	return 'eoj_' . $name;
}

/**
 * Fields that failed validation on the previous attempt.
 *
 * @return string[]
 */
function eoj_old_errors() {
	$err = eoj_old( '_err' );
	return $err ? explode( ',', $err ) : array();
}

/**
 * Render a single field.
 *
 * @param string $form  Form id.
 * @param array  $f     Field definition.
 * @param array  $defaults Fallback values (name => value) when nothing was typed before.
 * @return string HTML (escaped).
 */
function eoj_render_field( $form, $f, $defaults = array() ) {
	$type = isset( $f['type'] ) ? $f['type'] : 'text';

	if ( 'note' === $type ) {
		return '<p class="eoj-note eoj-note--small">' . wp_kses_post( $f['label'] ) . '</p>';
	}

	$name     = $f['name'];
	$key      = eoj_field_key( $name );
	$id       = $form . '-' . $name;
	$required = ! empty( $f['required'] );
	$cond     = ! empty( $f['required_if'] );
	$value    = eoj_old( $name );
	if ( '' === $value && isset( $defaults[ $name ] ) ) {
		$value = (string) $defaults[ $name ];
	}
	if ( 'password' === $type || 'file' === $type ) {
		$value = '';
	}
	$error = in_array( $name, eoj_old_errors(), true );
	$wrap  = 'eoj-field' . ( $error ? ' has-error' : '' );
	$show  = ! empty( $f['show_for'] ) ? ' data-eoj-show-for="' . esc_attr( $f['show_for'] ) . '" hidden' : '';
	$help  = ! empty( $f['help'] ) ? '<small id="' . esc_attr( $id ) . '-help">' . esc_html( $f['help'] ) . '</small>' : '';
	$attrs = ' id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '"';
	if ( $required ) {
		$attrs .= ' required';
	}
	if ( $cond ) {
		$attrs .= ' data-eoj-req';
	}
	if ( $error ) {
		$attrs .= ' aria-invalid="true"';
	}
	if ( $help ) {
		$attrs .= ' aria-describedby="' . esc_attr( $id ) . '-help"';
	}
	if ( ! empty( $f['autocomplete'] ) ) {
		$attrs .= ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"';
	}
	if ( ! empty( $f['placeholder'] ) ) {
		$attrs .= ' placeholder="' . esc_attr( $f['placeholder'] ) . '"';
	}

	$label = esc_html( $f['label'] ) . ( $required || $cond ? ' <span class="req" aria-hidden="true">*</span>' : '' );

	switch ( $type ) {
		case 'checkbox':
			$checked = '1' === $value ? ' checked' : '';
			return '<div class="' . esc_attr( $wrap ) . '"' . $show . '><label class="eoj-check" for="' . esc_attr( $id ) . '"><input type="checkbox" value="1"' . $attrs . $checked . '><span>' . wp_kses( $f['label'], array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ), 'strong' => array() ) ) . '</span></label></div>';

		case 'textarea':
			$rows = isset( $f['rows'] ) ? (int) $f['rows'] : 4;
			return '<div class="' . esc_attr( $wrap ) . '"' . $show . '><label for="' . esc_attr( $id ) . '">' . $label . '</label><textarea rows="' . $rows . '" maxlength="3000"' . $attrs . '>' . esc_textarea( $value ) . '</textarea>' . $help . '</div>';

		case 'select':
			$out = '<div class="' . esc_attr( $wrap ) . '"' . $show . '><label for="' . esc_attr( $id ) . '">' . $label . '</label><select' . $attrs . '><option value="">' . esc_html__( 'Select…', 'eyesonjobs-core' ) . '</option>';
			foreach ( (array) $f['options'] as $val => $text ) {
				$out .= '<option value="' . esc_attr( $val ) . '"' . selected( (string) $value, (string) $val, false ) . '>' . esc_html( $text ) . '</option>';
			}
			return $out . '</select>' . $help . '</div>';

		case 'radio':
			$out   = '<div class="' . esc_attr( $wrap ) . '"' . $show . ' role="radiogroup" aria-labelledby="' . esc_attr( $id ) . '-label"><span id="' . esc_attr( $id ) . '-label">' . $label . '</span><div class="eoj-roles">';
			$first = true;
			foreach ( (array) $f['options'] as $val => $text ) {
				$out  .= '<label><input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '"' . checked( (string) $value, (string) $val, false ) . ( $required && $first ? ' required' : '' ) . '> ' . esc_html( $text ) . '</label>';
				$first = false;
			}
			return $out . '</div>' . $help . '</div>';

		case 'file':
			$attrs .= ' accept=".pdf,.doc,.docx"';
			if ( empty( $f['help'] ) ) {
				$help = '<small id="' . esc_attr( $id ) . '-help">' . esc_html__( 'PDF, DOC or DOCX, up to 5 MB.', 'eyesonjobs-core' ) . '</small>';
			}
			return '<div class="' . esc_attr( $wrap ) . '"' . $show . '><label for="' . esc_attr( $id ) . '">' . $label . '</label><input type="file"' . $attrs . '>' . $help . '</div>';

		case 'password':
			return '<div class="' . esc_attr( $wrap ) . '"' . $show . '><label for="' . esc_attr( $id ) . '">' . $label . '</label><input type="password"' . $attrs . '>' . $help . '</div>';

		default:
			$allowed = array( 'text', 'email', 'tel', 'url', 'number' );
			$type    = in_array( $type, $allowed, true ) ? $type : 'text';
			$extra   = 'number' === $type ? ' min="1" inputmode="numeric"' : ' maxlength="200"';
			return '<div class="' . esc_attr( $wrap ) . '"' . $show . '><label for="' . esc_attr( $id ) . '">' . $label . '</label><input type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '"' . $attrs . $extra . '>' . $help . '</div>';
	}
}

/**
 * Render many fields. Consecutive "half" fields are paired in .eoj-row.
 *
 * @param string $form     Form id.
 * @param array  $fields   Definitions.
 * @param array  $defaults Fallback values.
 * @return string HTML.
 */
function eoj_render_fields( $form, $fields, $defaults = array() ) {
	$out    = '';
	$buffer = array();
	foreach ( $fields as $f ) {
		if ( ! empty( $f['half'] ) ) {
			$buffer[] = eoj_render_field( $form, $f, $defaults );
			if ( 2 === count( $buffer ) ) {
				$out   .= '<div class="eoj-row">' . implode( '', $buffer ) . '</div>';
				$buffer = array();
			}
			continue;
		}
		if ( $buffer ) {
			$out   .= '<div class="eoj-row">' . implode( '', $buffer ) . '</div>';
			$buffer = array();
		}
		$out .= eoj_render_field( $form, $f, $defaults );
	}
	if ( $buffer ) {
		$out .= '<div class="eoj-row">' . implode( '', $buffer ) . '</div>';
	}
	return $out;
}

/**
 * Sanitize and validate the posted fields.
 *
 * Uploads are stored only when every other field is valid.
 *
 * @param array $fields Definitions.
 * @return array { values: name => string, errors: name => message, files: name => path }
 */
function eoj_collect_fields( $fields ) {
	$values = array();
	$errors = array();
	$files  = array();

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in eoj_check_request().
	foreach ( $fields as $f ) {
		$type = isset( $f['type'] ) ? $f['type'] : 'text';
		if ( 'note' === $type || 'file' === $type || empty( $f['name'] ) ) {
			continue;
		}
		$name = $f['name'];
		$key  = eoj_field_key( $name );
		$raw  = ( isset( $_POST[ $key ] ) && ! is_array( $_POST[ $key ] ) ) ? wp_unslash( $_POST[ $key ] ) : '';

		switch ( $type ) {
			case 'email':
				$val = sanitize_email( $raw );
				break;
			case 'tel':
				$val = trim( preg_replace( '/[^0-9+\-() ]/', '', $raw ) );
				break;
			case 'url':
				$val = $raw ? esc_url_raw( trim( $raw ), array( 'http', 'https' ) ) : '';
				break;
			case 'number':
				$val = '' === trim( $raw ) ? '' : (string) absint( $raw );
				break;
			case 'textarea':
				$val = mb_substr( sanitize_textarea_field( $raw ), 0, 3000 );
				break;
			case 'select':
			case 'radio':
				$val = array_key_exists( $raw, (array) $f['options'] ) ? (string) $raw : '';
				break;
			case 'checkbox':
				$val = ! empty( $raw ) ? '1' : '';
				break;
			case 'password':
				$val = (string) $raw;
				break;
			default:
				$val = mb_substr( sanitize_text_field( $raw ), 0, 200 );
		}
		$values[ $name ] = $val;

		$required = ! empty( $f['required'] );
		if ( ! empty( $f['required_if'] ) ) {
			list( $other, $other_value ) = $f['required_if'];
			$required                    = isset( $values[ $other ] ) && (string) $values[ $other ] === (string) $other_value;
		}

		if ( '' === $val ) {
			if ( $required ) {
				$errors[ $name ] = __( 'This field is required.', 'eyesonjobs-core' );
			}
			continue;
		}
		if ( 'email' === $type && ! is_email( $val ) ) {
			$errors[ $name ] = __( 'Please enter a valid email address.', 'eyesonjobs-core' );
		} elseif ( 'tel' === $type && ! eoj_phone_valid( $val ) ) {
			$errors[ $name ] = __( 'Please enter a valid 10-digit mobile number.', 'eyesonjobs-core' );
		} elseif ( 'url' === $type && ! wp_http_validate_url( $val ) ) {
			$errors[ $name ] = __( 'Please enter a valid link.', 'eyesonjobs-core' );
		}
	}
	// phpcs:enable

	if ( ! $errors ) {
		foreach ( $fields as $f ) {
			if ( empty( $f['type'] ) || 'file' !== $f['type'] ) {
				continue;
			}
			$result = eoj_handle_upload( eoj_field_key( $f['name'] ), ! empty( $f['required'] ) );
			if ( false === $result ) {
				$errors[ $f['name'] ] = __( 'Please upload a PDF, DOC or DOCX file under 5 MB.', 'eyesonjobs-core' );
			} elseif ( $result ) {
				$files[ $f['name'] ] = $result;
			}
		}
		if ( $errors ) {
			eoj_discard_files( $files );
			$files = array();
		}
	}

	return array(
		'values' => $values,
		'errors' => $errors,
		'files'  => $files,
	);
}

/**
 * Delete stored uploads (used when a submission fails after upload).
 *
 * @param string[] $files Paths.
 */
function eoj_discard_files( $files ) {
	foreach ( (array) $files as $path ) {
		if ( $path && file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}
}

/**
 * Redirect back with the typed values and the failed field names.
 *
 * @param string $form   Form id.
 * @param array  $fields Definitions.
 * @param array  $result Result of eoj_collect_fields().
 * @param array  $extra  Extra query args.
 * @param string $code   Message code; derived from the errors when empty.
 */
function eoj_fail_with_fields( $form, $fields, $result, $extra = array(), $code = '' ) {
	$old = array();
	foreach ( $fields as $f ) {
		if ( empty( $f['name'] ) || ( isset( $f['type'] ) && in_array( $f['type'], array( 'password', 'file', 'note' ), true ) ) ) {
			continue;
		}
		if ( isset( $result['values'][ $f['name'] ] ) ) {
			$old[ $f['name'] ] = $result['values'][ $f['name'] ];
		}
	}
	$old['_err'] = implode( ',', array_keys( $result['errors'] ) );
	$only_files  = true;
	foreach ( array_keys( $result['errors'] ) as $name ) {
		foreach ( $fields as $f ) {
			if ( isset( $f['name'] ) && $f['name'] === $name && ( empty( $f['type'] ) || 'file' !== $f['type'] ) ) {
				$only_files = false;
			}
		}
	}
	if ( ! $code ) {
		$code = $only_files ? 'upload' : 'invalid';
	}
	eoj_redirect_back( $form, false, $code, $old, $extra );
}

/**
 * Label => readable value pairs for the stored entry and the email.
 *
 * @param array $fields Definitions.
 * @param array $values Collected values.
 * @return array
 */
function eoj_labelled_values( $fields, $values ) {
	$out = array();
	foreach ( $fields as $f ) {
		$type = isset( $f['type'] ) ? $f['type'] : 'text';
		if ( empty( $f['name'] ) || in_array( $type, array( 'note', 'file', 'password' ), true ) || ! isset( $values[ $f['name'] ] ) ) {
			continue;
		}
		$val = $values[ $f['name'] ];
		if ( in_array( $type, array( 'select', 'radio' ), true ) && isset( $f['options'][ $val ] ) ) {
			$val = $f['options'][ $val ];
		} elseif ( 'checkbox' === $type ) {
			$val = $val ? __( 'Yes', 'eyesonjobs-core' ) : __( 'No', 'eyesonjobs-core' );
		}
		$label         = 'checkbox' === $type ? __( 'Consent', 'eyesonjobs-core' ) : $f['label'];
		$out[ $label ] = (string) $val;
	}
	return $out;
}
