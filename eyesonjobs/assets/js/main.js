/**
 * Eyes on Jobs theme interactions. No dependencies.
 */
( function () {
	'use strict';

	var doc = document.documentElement;
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	doc.classList.add( 'eoj-ready' );

	/* Header shadow and back-to-top button */
	var header = document.querySelector( '.eoj-header' );
	var toTop = document.querySelector( '.eoj-totop' );
	function onScroll() {
		var y = window.scrollY || window.pageYOffset;
		if ( header ) {
			header.classList.toggle( 'is-scrolled', y > 10 );
		}
		if ( toTop ) {
			toTop.classList.toggle( 'is-visible', y > 600 );
		}
	}
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();

	/* Mobile menu */
	var burger = document.querySelector( '.eoj-burger' );
	var nav = document.getElementById( 'eoj-nav' );
	var mobile = window.matchMedia( '(max-width: 1024px)' );
	function setMenu( open ) {
		if ( ! burger || ! nav ) {
			return;
		}
		burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		nav.classList.toggle( 'is-open', open );
		document.body.classList.toggle( 'eoj-menu-open', open );
	}
	if ( burger && nav ) {
		burger.addEventListener( 'click', function () {
			setMenu( burger.getAttribute( 'aria-expanded' ) !== 'true' );
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				setMenu( false );
			}
		} );
		nav.querySelectorAll( '.menu-item-has-children > a' ).forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				var li = link.parentNode;
				if ( mobile.matches && ! li.classList.contains( 'is-open' ) ) {
					e.preventDefault();
					li.classList.add( 'is-open' );
				}
			} );
		} );
		nav.querySelectorAll( 'a[href*="#"]' ).forEach( function ( link ) {
			link.addEventListener( 'click', function () {
				setMenu( false );
			} );
		} );
	}

	/* Scroll reveal: tag common blocks automatically, then stagger siblings */
	var autoTargets = [
		'.eoj-head',
		'.eoj-grid > *',
		'.eoj-split > .wp-block-column',
		'.eoj-media-cols > .wp-block-column',
		'.eoj-promo__cols > .wp-block-column',
		'.eoj-section .eoj-chiplist li',
		'.eoj-testimonial',
		'.eoj-cta',
		'.eoj-faq',
		'.eoj-note',
		'.eoj-formcols > .wp-block-column',
		'.eoj-postcard'
	];
	document.querySelectorAll( autoTargets.join( ',' ) ).forEach( function ( el ) {
		if ( ! el.hasAttribute( 'data-reveal' ) ) {
			el.setAttribute( 'data-reveal', '' );
		}
	} );
	var revealEls = document.querySelectorAll( '[data-reveal]' );
	revealEls.forEach( function ( el ) {
		var parent = el.parentNode;
		if ( ! parent ) {
			return;
		}
		var siblings = Array.prototype.filter.call( parent.children, function ( c ) {
			return c.hasAttribute( 'data-reveal' );
		} );
		var index = siblings.indexOf( el );
		if ( index > 0 ) {
			el.style.setProperty( '--d', Math.min( index * 0.08, 0.6 ) + 's' );
		}
	} );

	function countUp( el ) {
		var target = parseFloat( el.getAttribute( 'data-target' ) ) || 0;
		var decimals = ( el.getAttribute( 'data-target' ).split( '.' )[ 1 ] || '' ).length;
		if ( reduceMotion ) {
			el.textContent = target.toLocaleString( 'en-IN', { minimumFractionDigits: decimals } );
			return;
		}
		var start = null;
		var duration = 1600;
		function step( ts ) {
			if ( ! start ) {
				start = ts;
			}
			var p = Math.min( ( ts - start ) / duration, 1 );
			var eased = 1 - Math.pow( 1 - p, 3 );
			el.textContent = ( target * eased ).toLocaleString( 'en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals } );
			if ( p < 1 ) {
				window.requestAnimationFrame( step );
			}
		}
		window.requestAnimationFrame( step );
	}

	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}
				entry.target.classList.add( 'is-visible' );
				entry.target.querySelectorAll( '.eoj-count' ).forEach( countUp );
				io.unobserve( entry.target );
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 } );
		revealEls.forEach( function ( el ) {
			io.observe( el );
		} );
	} else {
		revealEls.forEach( function ( el ) {
			el.classList.add( 'is-visible' );
		} );
		document.querySelectorAll( '.eoj-count' ).forEach( countUp );
	}

	/* Rotating word in the hero */
	document.querySelectorAll( '.eoj-rotator' ).forEach( function ( el ) {
		var words;
		try {
			words = JSON.parse( el.getAttribute( 'data-words' ) || '[]' );
		} catch ( e ) {
			words = [];
		}
		if ( words.length < 2 || reduceMotion ) {
			return;
		}
		var i = 0;
		setInterval( function () {
			el.classList.add( 'is-out' );
			setTimeout( function () {
				i = ( i + 1 ) % words.length;
				el.textContent = words[ i ];
				el.classList.remove( 'is-out' );
			}, 300 );
		}, 2400 );
	} );

	/* Infinite logo marquee: duplicate items once */
	document.querySelectorAll( '.eoj-marquee' ).forEach( function ( list ) {
		Array.prototype.slice.call( list.children ).forEach( function ( li ) {
			var clone = li.cloneNode( true );
			clone.setAttribute( 'aria-hidden', 'true' );
			list.appendChild( clone );
		} );
	} );

	/* Testimonials: dots + gentle autoplay */
	document.querySelectorAll( '.eoj-testimonials' ).forEach( function ( track ) {
		var cards = track.querySelectorAll( '.eoj-testimonial' );
		if ( cards.length < 2 ) {
			return;
		}
		var dots = document.createElement( 'div' );
		dots.className = 'eoj-dotsnav';
		var buttons = [];
		cards.forEach( function ( card, i ) {
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.setAttribute( 'aria-label', 'Show testimonial ' + ( i + 1 ) );
			b.addEventListener( 'click', function () {
				go( i );
				paused = true;
			} );
			dots.appendChild( b );
			buttons.push( b );
		} );
		track.parentNode.insertBefore( dots, track.nextSibling );

		var current = 0;
		var paused = false;
		function go( i ) {
			current = i;
			track.scrollTo( { left: cards[ i ].offsetLeft - track.offsetLeft, behavior: reduceMotion ? 'auto' : 'smooth' } );
		}
		function mark() {
			var left = track.scrollLeft;
			var best = 0;
			cards.forEach( function ( card, i ) {
				if ( Math.abs( card.offsetLeft - track.offsetLeft - left ) < Math.abs( cards[ best ].offsetLeft - track.offsetLeft - left ) ) {
					best = i;
				}
			} );
			current = best;
			buttons.forEach( function ( b, i ) {
				b.classList.toggle( 'is-active', i === best );
			} );
		}
		track.addEventListener( 'scroll', function () {
			window.requestAnimationFrame( mark );
		}, { passive: true } );
		track.addEventListener( 'pointerenter', function () {
			paused = true;
		} );
		track.addEventListener( 'pointerleave', function () {
			paused = false;
		} );
		mark();
		if ( ! reduceMotion ) {
			setInterval( function () {
				if ( paused || document.hidden ) {
					return;
				}
				var maxLeft = track.scrollWidth - track.clientWidth - 4;
				go( track.scrollLeft >= maxLeft ? 0 : current + 1 );
			}, 5000 );
		}
	} );
}() );
