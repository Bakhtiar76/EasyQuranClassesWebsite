/**
 * Easy Quran Classes — Contact / Free Trial form enhancements.
 * Enqueued only on those two pages (see functions.php). Purely additive to
 * Fluent Forms Lite:
 *   1. a leading icon inside each field,
 *   2. real-time per-field validation + character-level input filtering,
 *   3. a console log of the submitted field object on every submit click,
 *   4. a designed success modal when Fluent Forms' AJAX submit succeeds.
 * Fluent Forms keeps its own "required" validation as the server-side
 * backstop — this script never calls preventDefault on submit. Respects
 * prefers-reduced-motion. No framework; matches the style of assets/js/eqc.js.
 */
( function () {
	'use strict';

	var wraps = document.querySelectorAll( '.eqc-form-wrap' );
	if ( ! wraps.length ) {
		return;
	}

	var SVGNS = 'http://www.w3.org/2000/svg';

	/* field name -> icon sprite id (see inc/icon-sprite.php) */
	var ICONS = {
		full_name: 'person',
		student_name: 'person',
		guardian_name: 'person',
		email: 'mail',
		phone_country_code: 'phone',
		whatsapp_phone: 'phone',
		subject: 'sparkle',
		message: 'quote',
		age_range: 'users',
		current_level: 'graduation-cap',
		preferred_course: 'book-open',
		country: 'globe'
	};

	/* per-field validation rules, keyed by the field's data-name / name.
	 * type: name    -> letters + spaces + ' . - only, >= min letters
	 *       letters -> no digits (punctuation allowed)
	 *       digits  -> digits only, min..max long
	 *       email   -> strict format
	 *       select  -> a non-empty option must be chosen */
	var RULES = {
		full_name:       { type: 'name', min: 3 },
		student_name:    { type: 'name', min: 3 },
		guardian_name:   { type: 'name', min: 3, optional: true },
		subject:         { type: 'letters', optional: true },
		message:         { type: 'letters' },
		email:           { type: 'email' },
		whatsapp_phone:  { type: 'digits', min: 6, max: 15 },
		age_range:        { type: 'select' },
		current_level:    { type: 'select' },
		preferred_course: { type: 'select' },
		country:          { type: 'select' },
		'country-list':   { type: 'select' },
		phone_country_code: { type: 'select' }
	};

	/* Strict-ish email: real local part, dotted domain labels, TLD 2-24.
	 * Rejects a@b, a@b.c, spaces, @@, leading/trailing dots. A well-formed
	 * address on an unusual TLD (e.g. .co) is still accepted — only a mail
	 * server can prove deliverability. */
	var EMAIL_RE = /^[A-Za-z0-9](?:[A-Za-z0-9._%+-]{0,62}[A-Za-z0-9])?@(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,24}$/;

	var NAME_BAD = /[^\p{L}\p{M}\s'.\-]/u;
	var FORM_NAMES = { '3': 'Contact', '4': 'Free Trial' };

	function fieldKey( control ) {
		return control.getAttribute( 'data-name' ) || control.name || control.id || '';
	}

	function disallowedIn( str, type ) {
		if ( 'name' === type ) {
			return NAME_BAD.test( str );
		}
		if ( 'letters' === type ) {
			return /\d/.test( str );
		}
		if ( 'digits' === type ) {
			return /\D/.test( str );
		}
		return false;
	}

	function sanitize( str, type ) {
		if ( 'name' === type ) {
			return str.replace( new RegExp( NAME_BAD.source, 'gu' ), '' );
		}
		if ( 'letters' === type ) {
			return str.replace( /\d/g, '' );
		}
		if ( 'digits' === type ) {
			return str.replace( /\D/g, '' );
		}
		return str;
	}

	function svgUse( id, cls ) {
		var svg = document.createElementNS( SVGNS, 'svg' );
		if ( cls ) {
			svg.setAttribute( 'class', cls );
		}
		svg.setAttribute( 'aria-hidden', 'true' );
		svg.setAttribute( 'focusable', 'false' );
		var use = document.createElementNS( SVGNS, 'use' );
		use.setAttribute( 'href', '#eqc-icon-' + id );
		use.setAttributeNS( 'http://www.w3.org/1999/xlink', 'xlink:href', '#eqc-icon-' + id );
		svg.appendChild( use );
		return svg;
	}

	function insertAtCaret( el, text ) {
		var s = el.selectionStart;
		var e = el.selectionEnd;
		if ( typeof el.setRangeText === 'function' ) {
			el.setRangeText( text, s, e, 'end' );
		} else {
			el.value = el.value.slice( 0, s ) + text + el.value.slice( e );
			el.selectionStart = el.selectionEnd = s + text.length;
		}
		el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	}

	/* ---------- 1. Field icons ---------- */
	function addFieldIcons( wrap ) {
		wrap.querySelectorAll( '.ff-el-form-control' ).forEach( function ( control ) {
			var iconId = ICONS[ fieldKey( control ) ];
			if ( ! iconId ) {
				return;
			}
			var content = control.closest( '.ff-el-input--content' );
			var group = control.closest( '.ff-el-group' );
			if ( ! content || content.querySelector( '.eqc-field-icon' ) ) {
				return;
			}
			var span = document.createElement( 'span' );
			span.className = 'eqc-field-icon';
			span.appendChild( svgUse( iconId ) );
			content.insertBefore( span, content.firstChild );
			if ( group ) {
				group.classList.add( 'has-field-icon' );
			}
		} );
	}

	/* ---------- 2. Validation ---------- */
	function validateControl( control ) {
		var group = control.closest( '.ff-el-group' );
		if ( ! group ) {
			return true;
		}
		var content = control.closest( '.ff-el-input--content' );
		var key = fieldKey( control );
		var rule = RULES[ key ] || {};
		var reqd = 'true' === control.getAttribute( 'aria-required' );
		var value = ( control.value || '' ).trim();
		var isSelect = 'SELECT' === control.tagName;
		var msg = '';

		if ( reqd && ! value ) {
			msg = isSelect ? 'Please choose an option.' : 'This field is required.';
		} else if ( value ) {
			if ( 'email' === rule.type ) {
				if ( ! EMAIL_RE.test( value ) ) {
					msg = 'Enter a valid email address.';
				}
			} else if ( 'name' === rule.type ) {
				if ( NAME_BAD.test( value ) ) {
					msg = 'Use letters only — no numbers or symbols.';
				} else if ( ( value.match( /\p{L}/gu ) || [] ).length < ( rule.min || 3 ) ) {
					msg = 'Enter at least ' + ( rule.min || 3 ) + ' letters.';
				}
			} else if ( 'letters' === rule.type ) {
				if ( /\d/.test( value ) ) {
					msg = 'Letters only — please remove the numbers.';
				}
			} else if ( 'digits' === rule.type ) {
				var digits = value.replace( /\D/g, '' );
				if ( digits.length < ( rule.min || 6 ) || digits.length > ( rule.max || 15 ) ) {
					msg = 'Enter ' + ( rule.min || 6 ) + '–' + ( rule.max || 15 ) + ' digits.';
				}
			}
		}

		var errId = 'eqc-err-' + ( control.id || key );
		var existing = group.querySelector( '.eqc-field-error' );

		if ( msg ) {
			group.classList.add( 'eqc-field--invalid' );
			group.classList.remove( 'eqc-field--valid' );
			control.setAttribute( 'aria-invalid', 'true' );
			control.setAttribute( 'aria-describedby', errId );
			if ( ! existing ) {
				existing = document.createElement( 'div' );
				existing.className = 'eqc-field-error';
				existing.id = errId;
				existing.setAttribute( 'role', 'alert' );
				( content || group ).appendChild( existing );
			}
			existing.textContent = msg;
			removeValidMark( content );
			return false;
		}

		group.classList.remove( 'eqc-field--invalid' );
		control.setAttribute( 'aria-invalid', 'false' );
		control.removeAttribute( 'aria-describedby' );
		if ( existing ) {
			existing.remove();
		}
		if ( value && rule.type ) {
			group.classList.add( 'eqc-field--valid' );
			addValidMark( content );
		} else {
			group.classList.remove( 'eqc-field--valid' );
			removeValidMark( content );
		}
		return true;
	}

	function addValidMark( content ) {
		if ( ! content || content.querySelector( '.eqc-field-valid-mark' ) ) {
			return;
		}
		var span = document.createElement( 'span' );
		span.className = 'eqc-field-valid-mark';
		span.appendChild( svgUse( 'check' ) );
		content.appendChild( span );
	}

	function removeValidMark( content ) {
		if ( ! content ) {
			return;
		}
		var m = content.querySelector( '.eqc-field-valid-mark' );
		if ( m ) {
			m.remove();
		}
	}

	function wireValidation( wrap ) {
		wrap.querySelectorAll( '.ff-el-form-control' ).forEach( function ( control ) {
			var rule = RULES[ fieldKey( control ) ] || {};
			var filtered = 'name' === rule.type || 'letters' === rule.type || 'digits' === rule.type;
			var touched = false;

			if ( filtered ) {
				// Block a disallowed character as it is typed (no caret jump).
				// A paste that mixes allowed + disallowed is cleaned, not dropped.
				control.addEventListener( 'beforeinput', function ( e ) {
					if ( null == e.data || 0 !== ( e.inputType || '' ).indexOf( 'insert' ) ) {
						return;
					}
					if ( ! disallowedIn( e.data, rule.type ) ) {
						return;
					}
					e.preventDefault();
					var clean = sanitize( e.data, rule.type );
					if ( clean ) {
						insertAtCaret( control, clean );
					}
				} );
			}

			control.addEventListener( 'blur', function () {
				touched = true;
				validateControl( control );
			} );
			control.addEventListener( 'input', function () {
				if ( filtered && disallowedIn( control.value, rule.type ) ) {
					// Catch anything beforeinput missed (autofill, drag, IME).
					var pos = control.selectionStart;
					var before = control.value;
					var after = sanitize( before, rule.type );
					control.value = after;
					var removed = before.length - after.length;
					try {
						control.setSelectionRange( Math.max( 0, pos - removed ), Math.max( 0, pos - removed ) );
					} catch ( err ) {}
				}
				if ( touched ) {
					validateControl( control );
				}
			} );
			control.addEventListener( 'change', function () {
				if ( 'SELECT' === control.tagName ) {
					touched = true;
					validateControl( control );
				}
			} );
		} );

		var form = wrap.querySelector( 'form' );
		if ( ! form ) {
			return;
		}
		var formId = form.getAttribute( 'data-form_id' ) || '';

		// Capture phase, display-only: log what the form holds, reveal every
		// field's state, jump to the first problem. Fluent Forms still runs
		// (and can block) its own submit — we never preventDefault here.
		form.addEventListener( 'submit', function () {
			var payload = {};
			wrap.querySelectorAll( '.ff-el-form-control' ).forEach( function ( control ) {
				var key = fieldKey( control );
				if ( key ) {
					payload[ key ] = control.value;
				}
			} );
			console.log( '[EQC ' + ( FORM_NAMES[ formId ] || 'Form' ) + ' form submission]', payload );

			var firstBad = null;
			wrap.querySelectorAll( '.ff-el-form-control' ).forEach( function ( control ) {
				if ( ! validateControl( control ) && ! firstBad ) {
					firstBad = control;
				}
			} );
			if ( firstBad ) {
				firstBad.focus( { preventScroll: true } );
				firstBad.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}
		}, true );
	}

	/* ---------- 3. Success modal ---------- */
	var SUCCESS_TITLES = { '3': 'Message sent', '4': 'Request received' };
	var modal = null;
	var lastFocused = null;

	function buildModal() {
		if ( modal ) {
			return modal;
		}
		modal = document.createElement( 'div' );
		modal.className = 'eqc-modal';
		modal.hidden = true;
		modal.setAttribute( 'role', 'dialog' );
		modal.setAttribute( 'aria-modal', 'true' );
		modal.setAttribute( 'aria-labelledby', 'eqc-modal-title' );

		var card = document.createElement( 'div' );
		card.className = 'eqc-modal__card';

		var badge = document.createElement( 'div' );
		badge.className = 'eqc-modal__badge';
		badge.appendChild( svgUse( 'check' ) );

		var h = document.createElement( 'h2' );
		h.id = 'eqc-modal-title';

		var p = document.createElement( 'p' );

		var actions = document.createElement( 'div' );
		actions.className = 'eqc-btn-group';

		var home = document.createElement( 'a' );
		home.className = 'eqc-btn eqc-btn--primary';
		home.href = '/';
		home.textContent = 'Back to Home';

		var close = document.createElement( 'button' );
		close.type = 'button';
		close.className = 'eqc-btn eqc-btn--secondary';
		close.textContent = 'Close';
		close.addEventListener( 'click', closeModal );

		actions.appendChild( home );
		actions.appendChild( close );
		card.appendChild( badge );
		card.appendChild( h );
		card.appendChild( p );
		card.appendChild( actions );
		modal.appendChild( card );

		modal.addEventListener( 'click', function ( e ) {
			if ( e.target === modal ) {
				closeModal();
			}
		} );
		modal.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				closeModal();
				return;
			}
			if ( 'Tab' === e.key ) {
				trapTab( e );
			}
		} );

		document.body.appendChild( modal );
		return modal;
	}

	function trapTab( e ) {
		var focusables = modal.querySelectorAll( 'a[href], button:not([disabled])' );
		if ( ! focusables.length ) {
			return;
		}
		var first = focusables[ 0 ];
		var last = focusables[ focusables.length - 1 ];
		if ( e.shiftKey && document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		} else if ( ! e.shiftKey && document.activeElement === last ) {
			e.preventDefault();
			first.focus();
		}
	}

	function openModal( formId, message ) {
		buildModal();
		modal.querySelector( '#eqc-modal-title' ).textContent =
			SUCCESS_TITLES[ String( formId ) ] || 'Thank you!';
		modal.querySelector( 'p' ).textContent =
			message || "Thank you. We'll be in touch shortly.";
		lastFocused = document.activeElement;
		modal.hidden = false;
		document.body.style.overflow = 'hidden';
		void modal.offsetWidth;
		modal.classList.add( 'is-open' );
		modal.querySelector( 'button' ).focus();
	}

	function closeModal() {
		if ( ! modal || modal.hidden ) {
			return;
		}
		modal.classList.remove( 'is-open' );
		document.body.style.overflow = '';
		var hide = function () {
			modal.hidden = true;
		};
		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			hide();
		} else {
			setTimeout( hide, 220 );
		}
		if ( lastFocused && lastFocused.focus ) {
			lastFocused.focus();
		}
	}

	function watchSuccess( wrap ) {
		var container = wrap.querySelector( '[class*="fluentform_wrapper_"]' ) || wrap;
		var form = wrap.querySelector( 'form[data-form_id]' );
		var formId = form ? form.getAttribute( 'data-form_id' ) : '';

		var obs = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( m ) {
				m.addedNodes.forEach( function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}
					var succ = node.classList && node.classList.contains( 'ff-message-success' )
						? node
						: node.querySelector && node.querySelector( '.ff-message-success' );
					if ( succ ) {
						openModal( formId, ( succ.textContent || '' ).trim() );
					}
				} );
			} );
		} );
		obs.observe( container, { childList: true, subtree: true } );
	}

	/* ---------- init ---------- */
	wraps.forEach( function ( wrap ) {
		addFieldIcons( wrap );
		wireValidation( wrap );
		watchSuccess( wrap );
	} );
} )();
