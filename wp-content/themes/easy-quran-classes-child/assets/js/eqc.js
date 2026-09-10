/**
 * Easy Quran Classes — shared vanilla JS.
 * No framework, no animation library. Handles: mobile nav drawer, sticky
 * header state, scroll-reveal via IntersectionObserver, stat count-up,
 * the carousel (teachers/testimonials), teacher-row dot navigation, and
 * the FAQ accordion. Respects prefers-reduced-motion (see DESIGN.md §20).
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---------- Mobile nav drawer ----------
	 * The drawer is a modal overlay, so while it is open the rest of the
	 * page is made `inert` (no pointer, no focus, hidden from assistive
	 * tech) and Tab is cycled inside the drawer as a fallback for browsers
	 * without `inert`. It always has its own close control, and it closes
	 * itself if the viewport grows past the desktop breakpoint where the
	 * drawer is display:none — otherwise focus and body scroll-lock would
	 * be stranded on an invisible element.
	 */
	function initNavDrawer() {
		var toggle = document.querySelector( '.eqc-nav-toggle' );
		var drawer = document.getElementById( 'eqc-nav-drawer' );
		var scrim = document.getElementById( 'eqc-nav-scrim' );
		if ( ! toggle || ! drawer || ! scrim ) {
			return;
		}

		var closeButton = drawer.querySelector( '[data-eqc-nav-close]' );
		var background = document.querySelectorAll( '.eqc-header, #eqc-content, .eqc-footer' );
		var desktop = window.matchMedia( '(min-width: 64rem)' );
		var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

		function isOpen() {
			return drawer.classList.contains( 'is-open' );
		}

		function setBackgroundInert( inert ) {
			background.forEach( function ( el ) {
				if ( inert ) {
					el.setAttribute( 'inert', '' );
				} else {
					el.removeAttribute( 'inert' );
				}
			} );
		}

		function open() {
			drawer.classList.add( 'is-open' );
			scrim.classList.add( 'is-open' );
			drawer.removeAttribute( 'inert' );
			drawer.setAttribute( 'aria-hidden', 'false' );
			toggle.setAttribute( 'aria-expanded', 'true' );
			document.body.style.overflow = 'hidden';
			setBackgroundInert( true );
			( closeButton || drawer.querySelector( 'a' ) || drawer ).focus();
		}

		function close( returnFocus ) {
			if ( ! isOpen() ) {
				return;
			}
			drawer.classList.remove( 'is-open' );
			scrim.classList.remove( 'is-open' );
			drawer.setAttribute( 'aria-hidden', 'true' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			document.body.style.overflow = '';
			// Un-inert before moving focus back: focusing an inert element
			// silently does nothing.
			setBackgroundInert( false );
			if ( returnFocus ) {
				toggle.focus();
			}
			drawer.setAttribute( 'inert', '' );
		}

		toggle.addEventListener( 'click', function () {
			if ( isOpen() ) {
				close( true );
			} else {
				open();
			}
		} );

		if ( closeButton ) {
			closeButton.addEventListener( 'click', function () {
				close( true );
			} );
		}

		scrim.addEventListener( 'click', function () {
			close( true );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				close( true );
			}
		} );

		drawer.addEventListener( 'keydown', function ( e ) {
			if ( 'Tab' !== e.key || ! isOpen() ) {
				return;
			}
			var items = Array.prototype.filter.call(
				drawer.querySelectorAll( FOCUSABLE ),
				function ( el ) {
					return null !== el.offsetParent;
				}
			);
			if ( ! items.length ) {
				return;
			}
			var first = items[ 0 ];
			var last = items[ items.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		} );

		// Close the drawer on nav to avoid stale open state after a route change.
		drawer.querySelectorAll( 'a' ).forEach( function ( link ) {
			link.addEventListener( 'click', function () {
				close( false );
			} );
		} );

		// Resizing past the desktop breakpoint hides the drawer in CSS.
		desktop.addEventListener( 'change', function ( e ) {
			if ( e.matches ) {
				close( false );
			}
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

	/* ---------- Carousel ----------
	 * One-card-at-a-time auto-advancing carousel, shared by the homepage
	 * teacher row and the testimonials section (see eqc_carousel() in
	 * tools/elementor-helpers.php and .eqc-carousel* in components.css).
	 * Structure: .eqc-carousel > .eqc-carousel-arrow--prev/next (siblings,
	 * absolutely positioned) + .eqc-carousel-viewport > .eqc-carousel-track
	 * (the sliding flex row) + .eqc-slider-dots (built here, since the
	 * number of "pages" depends on how many cards are visible at once,
	 * which changes per breakpoint).
	 *
	 * Auto-advances every 6s, pauses while the pointer or focus is inside
	 * the carousel, and never starts under prefers-reduced-motion.
	 */
	function initCarousel() {
		document.querySelectorAll( '.eqc-carousel' ).forEach( function ( carousel ) {
			var track = carousel.querySelector( '.eqc-carousel-track' );
			var dotsWrap = carousel.querySelector( '.eqc-slider-dots' );
			var prevBtn = carousel.querySelector( '.eqc-carousel-arrow--prev' );
			var nextBtn = carousel.querySelector( '.eqc-carousel-arrow--next' );
			var cardCount = track ? track.children.length : 0;
			if ( ! track || ! cardCount ) {
				return;
			}

			var index = 0;
			var pageCount = 1;
			var autoplayId = null;

			function visibleCount() {
				var v = parseInt( window.getComputedStyle( track ).getPropertyValue( '--_visible' ), 10 );
				return v || 1;
			}

			function buildDots() {
				pageCount = Math.max( 1, cardCount - visibleCount() + 1 );
				// With every card already on screen there is nothing to page
				// to: both arrows sit permanently disabled and the dot strip
				// shows a single dot. Reviews.jpeg draws no arrows for this
				// reason. Marking the carousel static lets CSS drop the whole
				// control set, and it comes back on its own as soon as the
				// card count or the visible count makes paging real.
				carousel.classList.toggle( 'eqc-carousel--static', pageCount <= 1 );
				if ( ! dotsWrap ) {
					return;
				}
				dotsWrap.innerHTML = '';
				for ( var i = 0; i < pageCount; i++ ) {
					var dot = document.createElement( 'button' );
					dot.type = 'button';
					dot.className = 'eqc-slider-dot';
					dot.setAttribute( 'role', 'tab' );
					dot.setAttribute( 'aria-label', 'Show slide ' + ( i + 1 ) + ' of ' + pageCount );
					( function ( slideIndex ) {
						dot.addEventListener( 'click', function () {
							goTo( slideIndex );
						} );
					} )( i );
					dotsWrap.appendChild( dot );
				}
			}

			function render() {
				if ( index > pageCount - 1 ) {
					index = pageCount - 1;
				}
				track.style.transform = 'translateX(-' + ( index * ( 100 / visibleCount() ) ) + '%)';
				if ( dotsWrap ) {
					Array.prototype.forEach.call( dotsWrap.children, function ( dot, i ) {
						var isActive = i === index;
						dot.classList.toggle( 'is-active', isActive );
						dot.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
					} );
				}
				if ( prevBtn ) {
					prevBtn.disabled = index <= 0;
				}
				if ( nextBtn ) {
					nextBtn.disabled = index >= pageCount - 1;
				}
			}

			function goTo( i ) {
				index = Math.max( 0, Math.min( i, pageCount - 1 ) );
				render();
			}

			if ( prevBtn ) {
				prevBtn.addEventListener( 'click', function () {
					goTo( index - 1 );
				} );
			}
			if ( nextBtn ) {
				nextBtn.addEventListener( 'click', function () {
					goTo( index + 1 );
				} );
			}

			function stopAutoplay() {
				if ( autoplayId ) {
					window.clearInterval( autoplayId );
					autoplayId = null;
				}
			}
			function startAutoplay() {
				if ( reduceMotion || autoplayId || pageCount <= 1 ) {
					return;
				}
				autoplayId = window.setInterval(
					function () {
						goTo( index >= pageCount - 1 ? 0 : index + 1 );
					},
					6000
				);
			}

			carousel.addEventListener( 'mouseenter', stopAutoplay );
			carousel.addEventListener( 'mouseleave', startAutoplay );
			carousel.addEventListener( 'focusin', stopAutoplay );
			carousel.addEventListener( 'focusout', function ( e ) {
				if ( ! carousel.contains( e.relatedTarget ) ) {
					startAutoplay();
				}
			} );

			window.addEventListener( 'resize', function () {
				buildDots();
				render();
			} );

			buildDots();
			render();
			startAutoplay();
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

	/**
	 * Teacher card rows: the snap-scrolling overflow container both the home
	 * aside and the /teachers/ page grid become below 61.25rem
	 * (components.css .eqc-teachers-cards / .eqc-grid--teachers).
	 *
	 * Two things live here:
	 *  - The home aside's prev/next buttons. These shipped with an aria-label
	 *    and a focus ring and no handler at all — a keyboard user could tab
	 *    into them and nothing happened (fixed round 9). The /teachers/ page
	 *    grid has no such buttons in its markup, so `buttons` is simply empty
	 *    there and this half is a no-op.
	 *  - A `.eqc-slider-dots` strip (round 10, client review: the native
	 *    `scrollbar-width: thin` bar read as "old and bad looking" — replace
	 *    it with the same dot pattern the reviews carousel already uses, not
	 *    a second implementation of it). The row itself stays plain
	 *    scroll-snap rather than becoming a `.eqc-carousel` transform slider —
	 *    that keeps real per-finger touch scrolling, which is the better
	 *    mobile behaviour; only the indicator was missing. Dots are built in
	 *    JS (mirroring how initCarousel() builds `.eqc-slider-dots` already)
	 *    so no page rebake is needed for this change, and they are removed
	 *    above 61.25rem, where every card is already visible and there is
	 *    nothing to page to.
	 */
	function initTeacherRows() {
		var entries = [];
		document.querySelectorAll( '.eqc-teachers-split > .eqc-teachers-cards' ).forEach( function ( row ) {
			var split = row.closest( '.eqc-teachers-split' );
			var nav = split ? split.querySelector( '.eqc-teachers-nav' ) : null;
			entries.push( { row: row, buttons: nav ? nav.querySelectorAll( '.eqc-nav-btn' ) : [] } );
		} );
		document.querySelectorAll( '.eqc-grid--teachers' ).forEach( function ( row ) {
			entries.push( { row: row, buttons: [] } );
		} );
		entries.forEach( setupTeacherRow );
	}

	function setupTeacherRow( entry ) {
		var row = entry.row;
		var buttons = entry.buttons;
		var dotsWrap = null;
		var mq = window.matchMedia( '(max-width: 61.25rem)' );

		function page( direction ) {
			var card = row.firstElementChild;
			// One card plus its gap, so a press lands on the next snap point
			// rather than an arbitrary offset; fall back to most of a screen.
			var step = card ? card.getBoundingClientRect().width + 16 : row.clientWidth * 0.8;
			row.scrollBy( { left: direction * step, behavior: reduceMotion ? 'auto' : 'smooth' } );
		}
		if ( buttons.length >= 2 ) {
			buttons[ 0 ].addEventListener( 'click', function () { page( -1 ); } );
			buttons[ 1 ].addEventListener( 'click', function () { page( 1 ); } );
		}

		function syncButtons() {
			if ( buttons.length < 2 ) {
				return;
			}
			var max = row.scrollWidth - row.clientWidth;
			buttons[ 0 ].disabled = row.scrollLeft <= 1;
			buttons[ 1 ].disabled = row.scrollLeft >= max - 1;
		}

		function ensureDots() {
			if ( ! dotsWrap ) {
				dotsWrap = document.createElement( 'div' );
				dotsWrap.className = 'eqc-slider-dots';
				// role="tab" on each dot (below) requires a role="tablist"
				// ancestor per WAI-ARIA — the PHP-rendered carousel dots this
				// mirrors set both on their own wrapper (eqc_carousel(),
				// elementor-helpers.php); this JS-built one needs the same
				// pair, or the tabs are orphaned and axe's
				// aria-required-parent check fails.
				dotsWrap.setAttribute( 'role', 'tablist' );
				dotsWrap.setAttribute( 'aria-label', 'Teachers' );
				row.insertAdjacentElement( 'afterend', dotsWrap );
			}
			return dotsWrap;
		}
		function removeDots() {
			if ( dotsWrap ) {
				dotsWrap.remove();
				dotsWrap = null;
			}
		}
		function buildDots() {
			if ( ! mq.matches ) {
				removeDots();
				return;
			}
			var cards = Array.prototype.slice.call( row.children );
			var wrap = ensureDots();
			wrap.innerHTML = '';
			cards.forEach( function ( card, i ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'eqc-slider-dot';
				dot.setAttribute( 'role', 'tab' );
				dot.setAttribute( 'aria-label', 'Show teacher ' + ( i + 1 ) + ' of ' + cards.length );
				dot.addEventListener( 'click', function () {
					card.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', inline: 'center', block: 'nearest' } );
				} );
				wrap.appendChild( dot );
			} );
			syncDots();
		}
		function syncDots() {
			if ( ! dotsWrap ) {
				return;
			}
			// Nearest-centre card, not scrollLeft math against card width — the
			// row's cards are minmax(72%/84%, 1fr), so their rendered width
			// isn't a fixed step, and this stays correct regardless.
			var cards = row.children;
			var rowBox = row.getBoundingClientRect();
			var center = rowBox.left + rowBox.width / 2;
			var closest = 0;
			var closestDist = Infinity;
			Array.prototype.forEach.call( cards, function ( card, i ) {
				var box = card.getBoundingClientRect();
				var dist = Math.abs( ( box.left + box.width / 2 ) - center );
				if ( dist < closestDist ) {
					closestDist = dist;
					closest = i;
				}
			} );
			Array.prototype.forEach.call( dotsWrap.children, function ( dot, i ) {
				var isActive = i === closest;
				dot.classList.toggle( 'is-active', isActive );
				dot.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
			} );
		}

		function syncAll() {
			syncButtons();
			syncDots();
		}

		// Throttled to one syncAll() per animation frame, same pattern as
		// initHeaderScrollState() above — syncDots() reads layout
		// (getBoundingClientRect on the row and every card) on every call,
		// which is a layout-thrash risk if it ran on every raw scroll event.
		var ticking = false;
		row.addEventListener(
			'scroll',
			function () {
				if ( ! ticking ) {
					window.requestAnimationFrame( function () {
						syncAll();
						ticking = false;
					} );
					ticking = true;
				}
			},
			{ passive: true }
		);
		// buildDots() already reads mq.matches internally, so a plain
		// resize already covers every breakpoint crossing — a separate
		// mq 'change' listener with the same body would only double the
		// rebuild cost on each crossing.
		window.addEventListener( 'resize', function () {
			buildDots();
			syncAll();
		} );

		buildDots();
		syncAll();
	}

	function init() {
		initNavDrawer();
		initTeacherRows();
		initHeaderScrollState();
		initScrollReveal();
		initCountUp();
		initCarousel();
		initFaqAccordion();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
