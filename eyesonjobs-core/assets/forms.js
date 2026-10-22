/**
 * Eyes on Jobs Core: forms, reCAPTCHA v3, account tabs, Razorpay Checkout.
 * No dependencies.
 */
( function () {
	'use strict';

	var cfg = window.eojForms || { recaptchaKey: '', maxUpload: 5242880, i18n: {} };
	cfg.maxUpload = Number( cfg.maxUpload ) || 5242880;
	var i18n = cfg.i18n || {};

	function each( list, fn ) {
		Array.prototype.forEach.call( list, fn );
	}

	/* ---------------------------------------------------------------- alerts */
	function showAlert( form, message ) {
		var box = form.querySelector( '[data-eoj-js-alert]' );
		if ( ! box ) {
			box = document.createElement( 'div' );
			box.className = 'eoj-alert eoj-alert--error';
			box.setAttribute( 'role', 'alert' );
			box.setAttribute( 'tabindex', '-1' );
			box.setAttribute( 'data-eoj-js-alert', '' );
			form.insertBefore( box, form.firstChild );
		}
		box.textContent = message;
		box.focus();
	}

	/* ------------------------------------------------------- file size check */
	function filesTooBig( form ) {
		var bad = false;
		each( form.querySelectorAll( 'input[type="file"]' ), function ( input ) {
			if ( input.files && input.files[ 0 ] && input.files[ 0 ].size > cfg.maxUpload ) {
				bad = true;
				input.value = '';
			}
		} );
		return bad;
	}

	/* --------------------------------------------------- submit + reCAPTCHA */
	function lockButton( form ) {
		var button = form.querySelector( 'button[type="submit"]' );
		if ( button ) {
			button.disabled = true;
			button.setAttribute( 'data-label', button.textContent );
			button.textContent = i18n.sending || 'Sending…';
		}
	}

	function unlockButton( form ) {
		var button = form.querySelector( 'button[type="submit"]' );
		if ( button && button.hasAttribute( 'data-label' ) ) {
			button.disabled = false;
			button.textContent = button.getAttribute( 'data-label' );
		}
	}

	function sendForm( form ) {
		form.setAttribute( 'data-eoj-sent', '1' );
		// form.submit() skips the submit event, so this cannot loop.
		HTMLFormElement.prototype.submit.call( form );
	}

	var loadedAt = Date.now();

	function onSubmit( event ) {
		var form = event.target;
		if ( ! form.matches || ! form.matches( 'form[data-recaptcha]' ) || form.getAttribute( 'data-eoj-sent' ) ) {
			return;
		}
		if ( filesTooBig( form ) ) {
			event.preventDefault();
			showAlert( form, i18n.tooBig || 'This file is too large. Please upload a file under 5 MB.' );
			return;
		}
		event.preventDefault();
		lockButton( form );
		// Forms sent within two seconds of loading look automated to the server, so wait it out.
		var wait = Math.max( 0, 2300 - ( Date.now() - loadedAt ) );
		if ( ! cfg.recaptchaKey || ! window.grecaptcha ) {
			window.setTimeout( function () {
				sendForm( form );
			}, wait );
			return;
		}

		var done = false;
		var finish = function ( token ) {
			if ( done ) {
				return;
			}
			done = true;
			var field = form.querySelector( 'input[name="eoj_recaptcha"]' );
			if ( field ) {
				field.value = token || '';
			}
			sendForm( form );
		};
		// If Google is slow or blocked, send anyway after a few seconds.
		var timer = window.setTimeout( finish, 6000 + wait );
		window.setTimeout( function () {
			window.grecaptcha.ready( function () {
				window.grecaptcha
					.execute( cfg.recaptchaKey, { action: form.getAttribute( 'data-recaptcha' ) } )
					.then( function ( token ) {
						window.clearTimeout( timer );
						finish( token );
					} )
					.catch( function () {
						window.clearTimeout( timer );
						finish( '' );
					} );
			} );
		}, wait );
	}
	document.addEventListener( 'submit', onSubmit );

	// Back/forward cache restores a locked button; reset it.
	window.addEventListener( 'pageshow', function ( e ) {
		if ( e.persisted ) {
			each( document.querySelectorAll( 'form[data-recaptcha]' ), function ( form ) {
				form.removeAttribute( 'data-eoj-sent' );
				unlockButton( form );
			} );
		}
	} );

	document.addEventListener( 'change', function ( event ) {
		var input = event.target;
		if ( input.type === 'file' && input.files && input.files[ 0 ] && input.files[ 0 ].size > cfg.maxUpload ) {
			input.value = '';
			showAlert( input.form, i18n.tooBig || 'This file is too large. Please upload a file under 5 MB.' );
		}
	} );

	/* ---------------------------------------------------------- account tabs */
	each( document.querySelectorAll( '[data-eoj-tabs]' ), function ( wrap ) {
		var tabs = wrap.querySelectorAll( '[role="tab"]' );
		function select( tab ) {
			each( tabs, function ( t ) {
				var on = t === tab;
				t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				var panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
				if ( panel ) {
					panel.hidden = ! on;
				}
			} );
		}
		each( tabs, function ( tab ) {
			tab.addEventListener( 'click', function () {
				select( tab );
			} );
			tab.addEventListener( 'keydown', function ( e ) {
				if ( e.key !== 'ArrowRight' && e.key !== 'ArrowLeft' ) {
					return;
				}
				var list = Array.prototype.slice.call( tabs );
				var next = list[ ( list.indexOf( tab ) + ( e.key === 'ArrowRight' ? 1 : list.length - 1 ) ) % list.length ];
				select( next );
				next.focus();
			} );
		} );
	} );

	/* ----------------------------------------- employer-only fields (by role) */
	each( document.querySelectorAll( 'form' ), function ( form ) {
		var roles = form.querySelectorAll( 'input[type="radio"][name="eoj_role"]' );
		var groups = form.querySelectorAll( '[data-eoj-show-for]' );
		if ( ! roles.length || ! groups.length ) {
			return;
		}
		function update() {
			var chosen = form.querySelector( 'input[name="eoj_role"]:checked' );
			var value = chosen ? chosen.value : '';
			each( groups, function ( group ) {
				var show = group.getAttribute( 'data-eoj-show-for' ) === value;
				group.hidden = ! show;
				each( group.querySelectorAll( '[data-eoj-req]' ), function ( input ) {
					input.required = show;
				} );
			} );
		}
		each( roles, function ( r ) {
			r.addEventListener( 'change', update );
		} );
		update();
	} );

	/* ----------------------------------------------------- focus status alerts */
	var focusTarget = document.querySelector( '[data-eoj-focus]' );
	if ( focusTarget ) {
		window.setTimeout( function () {
			focusTarget.scrollIntoView( { block: 'center' } );
			focusTarget.focus( { preventScroll: true } );
		}, 150 );
	}

	/* ------------------------------------------- Payment method picker and UPI QR */
	var qrTimer = null;
	var pollTimer = null;

	function stopQr() {
		window.clearInterval( qrTimer );
		window.clearInterval( pollTimer );
		qrTimer = null;
		pollTimer = null;
	}

	function showConfirm( method ) {
		var form = document.querySelector( '[data-eoj-confirm]' );
		if ( ! form ) {
			return;
		}
		form.hidden = ! method;
		if ( method ) {
			form.querySelector( 'input[name="eoj_method"]' ).value = method;
		}
	}

	function startQr( panel ) {
		var target = panel.querySelector( '[data-eoj-qr-target]' );
		var timer = panel.querySelector( '[data-eoj-timer]' );
		var bar = panel.querySelector( '[data-eoj-bar]' );
		var apps = panel.querySelector( '[data-eoj-apps]' );
		var upiId = panel.querySelector( '[data-eoj-upiid]' );
		var again = panel.querySelector( '[data-eoj-qr-new]' );
		var msg = panel.querySelector( '[data-eoj-qr-msg]' );
		var reg = panel.getAttribute( 'data-reg' );
		var key = panel.getAttribute( 'data-k' );

		stopQr();
		again.hidden = true;
		apps.hidden = true;
		upiId.hidden = true;
		msg.hidden = true;
		timer.textContent = '';
		bar.style.width = '100%';
		target.classList.remove( 'is-expired' );
		target.innerHTML = '<span class="eoj-qr__wait">' + ( i18n.qrWait || 'Generating QR…' ) + '</span>';

		function fail( text ) {
			target.innerHTML = '';
			msg.textContent = text;
			msg.hidden = false;
			again.hidden = false;
		}

		window.fetch( panel.getAttribute( 'data-eoj-qr-url' ), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify( { reg: reg, k: key } )
		} )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( data ) {
				if ( data.paid ) {
					window.location.reload();
					return;
				}
				if ( ! data.mode ) {
					fail( i18n.qrError || 'We could not create a QR code. Please try again.' );
					return;
				}
				if ( data.mode === 'razorpay' ) {
					var img = new Image();
					img.alt = i18n.qrAlt || 'UPI QR code';
					img.src = data.image_url;
					target.innerHTML = '';
					target.appendChild( img );
					showConfirm( '' );
				} else if ( window.qrcode ) {
					var qr = window.qrcode( 0, 'M' );
					qr.addData( data.uri );
					qr.make();
					target.innerHTML = qr.createSvgTag( { cellSize: 6, margin: 3, scalable: true } );
					apps.innerHTML = '<span class="eoj-muted">' + ( i18n.qrApps || 'On your phone, tap your app:' ) + '</span>';
					Object.keys( data.apps || {} ).forEach( function ( label ) {
						var a = document.createElement( 'a' );
						a.className = 'eoj-btn eoj-btn--outline eoj-btn--small';
						a.href = data.apps[ label ];
						a.textContent = label;
						apps.appendChild( a );
					} );
					apps.hidden = false;
					upiId.textContent = ( i18n.upiId || 'UPI ID' ) + ': ' + data.upi_id;
					upiId.hidden = false;
					showConfirm( 'UPI' );
				}

				var total = data.expires_in || 300;
				var end = Date.now() + total * 1000;
				function tick() {
					var left = Math.max( 0, Math.round( ( end - Date.now() ) / 1000 ) );
					var m = Math.floor( left / 60 );
					var s = left % 60;
					timer.textContent = ( i18n.qrExpires || 'Expires in' ) + ' ' + m + ':' + ( s < 10 ? '0' : '' ) + s;
					bar.style.width = ( left / total * 100 ) + '%';
					if ( left <= 0 ) {
						stopQr();
						target.classList.add( 'is-expired' );
						timer.textContent = i18n.qrExpired || 'This QR code has expired.';
						apps.hidden = true;
						again.hidden = false;
					}
				}
				tick();
				qrTimer = window.setInterval( tick, 1000 );

				// A Razorpay QR confirms itself, so watch for the payment.
				pollTimer = window.setInterval( function () {
					window.fetch( panel.getAttribute( 'data-eoj-status-url' ) + '?reg=' + encodeURIComponent( reg ) + '&k=' + encodeURIComponent( key ) )
						.then( function ( r ) {
							return r.json();
						} )
						.then( function ( s ) {
							if ( s.paid ) {
								window.location.reload();
							}
						} )
						.catch( function () {} );
				}, 4000 );
			} )
			.catch( function () {
				fail( i18n.qrError || 'We could not create a QR code. Please try again.' );
			} );
	}

	each( document.querySelectorAll( '.eoj-paybox' ), function ( box ) {
		var buttons = box.querySelectorAll( '[data-eoj-method]' );
		var panels = box.querySelectorAll( '[data-eoj-panel]' );
		each( buttons, function ( button ) {
			button.addEventListener( 'click', function () {
				var method = button.getAttribute( 'data-eoj-method' );
				stopQr();
				each( buttons, function ( b ) {
					b.setAttribute( 'aria-pressed', b === button ? 'true' : 'false' );
				} );
				each( panels, function ( p ) {
					p.hidden = p.getAttribute( 'data-eoj-panel' ) !== method;
				} );
				showConfirm( method === 'paypal' ? 'PayPal' : '' );
				if ( method === 'upi' ) {
					startQr( box.querySelector( '[data-eoj-panel="upi"]' ) );
				}
			} );
		} );
		var again = box.querySelector( '[data-eoj-qr-new]' );
		if ( again ) {
			again.addEventListener( 'click', function () {
				startQr( box.querySelector( '[data-eoj-panel="upi"]' ) );
			} );
		}
	} );

	/* ----------------------------------------------------------- Razorpay */
	var checkoutPromise = null;
	function loadCheckout() {
		if ( window.Razorpay ) {
			return Promise.resolve();
		}
		if ( ! checkoutPromise ) {
			checkoutPromise = new Promise( function ( resolve, reject ) {
				var script = document.createElement( 'script' );
				script.src = 'https://checkout.razorpay.com/v1/checkout.js';
				script.onload = resolve;
				script.onerror = function () {
					checkoutPromise = null;
					reject( new Error( 'checkout' ) );
				};
				document.head.appendChild( script );
			} );
		}
		return checkoutPromise;
	}

	each( document.querySelectorAll( '[data-eoj-pay]' ), function ( button ) {
		var options;
		try {
			options = JSON.parse( button.getAttribute( 'data-eoj-pay' ) );
		} catch ( e ) {
			return;
		}
		var message = document.querySelector( '[data-eoj-pay-msg]' );
		var verify = document.getElementById( 'eoj-verify' );

		function fail() {
			button.disabled = false;
			if ( message ) {
				message.innerHTML = '';
				var alert = document.createElement( 'div' );
				alert.className = 'eoj-alert eoj-alert--error';
				alert.setAttribute( 'role', 'alert' );
				alert.textContent = i18n.payFailed || 'Payment was not completed. You can try again.';
				message.appendChild( alert );
			}
		}

		function pay() {
			button.disabled = true;
			if ( message ) {
				message.innerHTML = '';
			}
			loadCheckout()
				.then( function () {
					var rzp = new window.Razorpay( {
						key: options.key,
						order_id: options.order_id,
						amount: options.amount,
						currency: options.currency,
						name: options.name,
						description: options.description,
						prefill: options.prefill,
						config: options.method === 'card' ? { display: { blocks: { cards: { name: ( i18n.cardTitle || 'Pay with card' ), instruments: [ { method: 'card' } ] } }, sequence: [ 'block.cards' ], preferences: { show_default_blocks: false } } } : undefined,
						theme: { color: '#1E3A8A' },
						handler: function ( response ) {
							if ( ! verify ) {
								return;
							}
							verify.elements.razorpay_payment_id.value = response.razorpay_payment_id || '';
							verify.elements.razorpay_order_id.value = response.razorpay_order_id || '';
							verify.elements.razorpay_signature.value = response.razorpay_signature || '';
							verify.submit();
						},
						modal: { ondismiss: fail }
					} );
					rzp.on( 'payment.failed', fail );
					rzp.open();
				} )
				.catch( fail );
		}
		button.addEventListener( 'click', pay );
	} );
}() );
