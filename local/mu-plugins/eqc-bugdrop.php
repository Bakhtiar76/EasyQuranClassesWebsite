<?php
/**
 * Plugin Name: Easy Quran Classes Local BugDrop
 * Description: Adds BugDrop to the Docker development site only.
 * Version: 1.0.0
 *
 * @package Easy_Quran_Classes
 */

defined( 'ABSPATH' ) || exit;

// Defence in depth: this file must remain inert if it is ever copied outside
// the local Docker environment by mistake.
if ( 'local' !== wp_get_environment_type() ) {
	return;
}

/**
 * Warm and memoize BugDrop's repository-installation check for this page.
 *
 * The hosted widget otherwise waits for the same cross-origin GET every time
 * it opens. On the local QA site that round trip is much slower than the page
 * itself. Only this exact, read-only endpoint is memoized, only for the life
 * of the current document. A failed warm-up falls through to BugDrop's normal
 * fetch, and feedback submissions are never intercepted.
 */
function eqc_local_prime_bugdrop_check() {
	?>
	<link rel="preconnect" href="https://bugdrop.neonwatty.workers.dev" crossorigin>
	<script>
	(function () {
		var checkUrl = 'https://bugdrop.neonwatty.workers.dev/api/check/Bakhtiar76/EasyQuranClassesWebsite';
		var nativeFetch = window.fetch.bind(window);
		var warmedResponse = nativeFetch(checkUrl, { headers: { Accept: 'application/json' } })
			.then(function (response) {
				return response.ok ? response : null;
			})
			.catch(function () {
				return null;
			});

		window.fetch = function (input, options) {
			var requestUrl = 'string' === typeof input ? input : input.url;
			var requestMethod = (options && options.method) || (input && input.method) || 'GET';

			if (checkUrl === requestUrl && 'GET' === requestMethod.toUpperCase()) {
				return warmedResponse.then(function (response) {
					return response ? response.clone() : nativeFetch(input, options);
				});
			}

			return nativeFetch(input, options);
		};

		window.__eqcBugDropCheckReady = warmedResponse;
	}());
	</script>
	<?php
}
add_action( 'wp_head', 'eqc_local_prime_bugdrop_check', 1 );

/**
 * Render the pinned BugDrop widget at the end of the local public document.
 *
 * BugDrop reads its configuration from the currently executing script, so the
 * tag intentionally has neither async nor defer. Text-entry controls are
 * marked for screenshot masking before the widget initializes.
 */
function eqc_local_print_bugdrop_widget() {
	?>
	<script>
	(function () {
		document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea, [contenteditable="true"]').forEach(function (field) {
			field.setAttribute('data-bugdrop-mask', '');
		});
	}());
	</script>
	<script
		src="https://bugdrop.neonwatty.workers.dev/widget.v1.56.4.js"
		data-repo="Bakhtiar76/EasyQuranClassesWebsite"
		data-theme="light"
		data-position="bottom-left"
		data-color="#1B3A2D"
		data-font="inherit"
		data-locale="en"
		data-label="Report a website bug"
		data-welcome="never"
		data-screenshot="optional"
		data-show-issue-link="always"
	></script>
	<script>
	(function () {
		if (!window.BugDrop || !window.__eqcBugDropCheckReady) {
			return;
		}

		window.BugDrop.hide();
		window.__eqcBugDropCheckReady.finally(function () {
			window.BugDrop.show();
			delete window.__eqcBugDropCheckReady;
		});
	}());
	</script>
	<?php
}
add_action( 'wp_footer', 'eqc_local_print_bugdrop_widget', 100 );
