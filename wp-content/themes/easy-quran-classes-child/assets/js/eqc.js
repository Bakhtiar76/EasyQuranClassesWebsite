/**
 * Easy Quran Classes — shared vanilla JS.
 * No framework, no animation library. Handles: mobile nav drawer, sticky
 * header state, scroll-reveal via IntersectionObserver, and the FAQ
 * accordion. Respects prefers-reduced-motion (see DESIGN.md §20).
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---------- Mobile nav drawer ---------- */
	function initNavDrawer() {
		var toggle = document.querySelector( '.eqc-nav-toggle' );
		var drawer = document.getElementById( 'eqc-nav-drawer' );
		var scrim = document.getElementById( 'eqc-nav-scrim' );
		if ( ! toggle || ! drawer || ! scrim ) {
			return;
		}

		function open() {
			drawer.classList.add( 'is-open' );
			scrim.classList.add( 'is-open' );
			drawer.setAttribute( 'aria-hidden', 'false' );
			toggle.setAttribute( 'aria-expanded', 'true' );
			document.body.style.overflow = 'hidden';
			var firstLink = drawer.querySelector( 'a' );
			if ( firstLink ) {
				firstLink.focus();
			}
		}

		function close() {
			drawer.classList.remove( 'is-open' );
			scrim.classList.remove( 'is-open' );
			drawer.setAttribute( 'aria-hidden', 'true' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			document.body.style.overflow = '';
		}

		toggle.addEventListener( 'click', function () {
			var isOpen = drawer.classList.contains( 'is-open' );
			if ( isOpen ) {
				close();
			} else {
				open();
			}
		} );

		scrim.addEventListener( 'click', close );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && drawer.classList.contains( 'is-open' ) ) {
				close();
				toggle.focus();
			}
		} );

		// Close the drawer on nav to avoid stale open state after a route change.
		drawer.querySelectorAll( 'a' ).forEach( function ( link ) {
			link.addEventListener( 'click', close );
		} );
	}

	/* ---------- Sticky header shadow state ---------- */
	function initHeaderScrollState() {
		var header = document.getElementById( 'eqc-header' );
		if ( ! header ) {
			return;
		}
		var ticking = false;
		function update() {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
			ticking = false;
		}
		window.addEventListener(
			'scroll',
			function () {
				if ( ! ticking ) {
					window.requestAnimationFrame( update );
					ticking = true;
				}
			},
			{ passive: true }
		);
		update();
	}

	/* ---------- Scroll reveal ---------- */
	function initScrollReveal() {
		var items = document.querySelectorAll( '[data-eqc-reveal]' );
		if ( ! items.length ) {
			return;
		}

		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			items.forEach( function ( el ) {
				el.classList.add( 'is-visible' );
			} );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.15, rootMargin: '0px 0px -8% 0px' }
		);

		items.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	/* ---------- Stat count-up ----------
	 * Elements marked data-eqc-countup="5000" (optional data-eqc-suffix="+")
	 * animate from 0 to the target once scrolled into view, e.g. the
	 * homepage "5,000+ Students Taught" stat. Purely additive motion (the
	 * final text is correct even if this never runs), and honors
	 * prefers-reduced-motion by jumping straight to the end value.
	 */
	function initCountUp() {
		var items = document.querySelectorAll( '[data-eqc-countup]' );
		if ( ! items.length ) {
			return;
		}

		function renderFinal( el, target, suffix ) {
			el.textContent = target.toLocaleString() + suffix;
		}

		function animate( el ) {
			var target = parseInt( el.getAttribute( 'data-eqc-countup' ), 10 ) || 0;
			var suffix = el.getAttribute( 'data-eqc-suffix' ) || '';

			if ( reduceMotion ) {
				renderFinal( el, target, suffix );
				return;
			}

			var duration = 1100;
			var start = null;

			function step( timestamp ) {
				if ( null === start ) {
					start = timestamp;
				}
				var progress = Math.min( ( timestamp - start ) / duration, 1 );
				var eased = 1 - Math.pow( 1 - progress, 3 );
				el.textContent = Math.round( target * eased ).toLocaleString() + suffix;
				if ( progress < 1 ) {
					window.requestAnimationFrame( step );
				}
			}
			window.requestAnimationFrame( step );
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			items.forEach( function ( el ) {
				animate( el );
			} );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						animate( entry.target );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.6 }
		);
		items.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	/* ---------- Testimonial slider ----------
	 * Dot-navigated, no autoplay (user-controlled — avoids the
	 * accessibility/motion-sensitivity issues of an auto-advancing
	 * carousel). Structure: .eqc-testimonial-slider > .eqc-testimonial-track
	 * (the sliding element) containing .eqc-testimonial-slide children,
	 * plus a sibling .eqc-slider-dots row of buttons.
	 */
	function initTestimonialSlider() {
		document.querySelectorAll( '.eqc-testimonial-slider' ).forEach( function ( slider ) {
			var track = slider.querySelector( '.eqc-testimonial-track' );
			var dots = slider.querySelectorAll( '.eqc-slider-dot' );
			if ( ! track || ! dots.length ) {
				return;
			}

			function goTo( index ) {
				track.style.transform = 'translateX(-' + ( index * 100 ) + '%)';
				dots.forEach( function ( dot, i ) {
					var isActive = i === index;
					dot.classList.toggle( 'is-active', isActive );
					dot.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
				} );
			}

			dots.forEach( function ( dot, i ) {
				dot.addEventListener( 'click', function () {
					goTo( i );
				} );
			} );
		} );
	}

	/* ---------- FAQ accordion ---------- */
	function initFaqAccordion() {
		var items = document.querySelectorAll( '.eqc-faq-item' );
		items.forEach( function ( item ) {
			var button = item.querySelector( '.eqc-faq-question' );
			var answer = item.querySelector( '.eqc-faq-answer' );
			if ( ! button || ! answer ) {
				return;
			}
			button.addEventListener( 'click', function () {
				var isOpen = 'true' === item.getAttribute( 'data-open' );
				// Accordion behavior is per-group (siblings inside the same
				// .eqc-faq-group close), never hides content from users who
				// disable JS since [data-open] starts closed only via CSS,
				// not by removing markup.
				var group = item.closest( '.eqc-faq-group' );
				if ( group ) {
					group.querySelectorAll( '.eqc-faq-item[data-open="true"]' ).forEach( function ( openItem ) {
						if ( openItem !== item ) {
							openItem.setAttribute( 'data-open', 'false' );
							openItem.querySelector( '.eqc-faq-question' ).setAttribute( 'aria-expanded', 'false' );
						}
					} );
				}
				item.setAttribute( 'data-open', isOpen ? 'false' : 'true' );
				button.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
			} );
		} );
	}

	function init() {
		initNavDrawer();
		initHeaderScrollState();
		initScrollReveal();
		initCountUp();
		initTestimonialSlider();
		initFaqAccordion();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
