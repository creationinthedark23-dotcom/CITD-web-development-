/**
 * THE CREATION JOURNAL — front-end behaviour
 * Creation in the Dark Holdings
 *
 * Vanilla JavaScript. No dependencies, no build step, no polyfills required for
 * any browser that supports ES2017 and IntersectionObserver.
 *
 * Every module is defensive: if the markup it needs is absent, it returns
 * without touching the page. Nothing here is required for the content to be
 * readable — the stylesheet and templates stand on their own.
 *
 * Modules
 *   1.  Configuration and small helpers
 *   2.  Theme toggle
 *   3.  Masthead behaviour
 *   4.  Reading progress and time remaining
 *   5.  Table of contents scrollspy
 *   6.  Anchor navigation with focus management
 *   7.  Reveal animations
 *   8.  Share controls
 *   9.  Newsletter
 *   10. Navigation drawer
 *   11. Search disclosure
 *   12. Back to top
 */

(function () {
	'use strict';

	/* ======================================================================
	   1. Configuration and helpers
	   ====================================================================== */

	var config = window.CITDJournal || {};
	var strings = config.strings || {};
	var THEME_KEY = 'citd-journal-theme';

	var root = document.documentElement;
	var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

	/**
	 * Query a single element.
	 *
	 * @param {string} selector CSS selector.
	 * @param {Element|Document} [scope] Search root.
	 * @return {Element|null}
	 */
	function one(selector, scope) {
		return (scope || document).querySelector(selector);
	}

	/**
	 * Query all matching elements as a real array.
	 *
	 * @param {string} selector CSS selector.
	 * @param {Element|Document} [scope] Search root.
	 * @return {Array<Element>}
	 */
	function all(selector, scope) {
		return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
	}

	/**
	 * Read a localised string with a fallback.
	 *
	 * @param {string} key      String key.
	 * @param {string} fallback Default text.
	 * @return {string}
	 */
	function text(key, fallback) {
		return typeof strings[key] === 'string' && strings[key] !== '' ? strings[key] : fallback;
	}

	/**
	 * Coalesce repeated calls into one per animation frame.
	 *
	 * @param {Function} fn Callback.
	 * @return {Function}
	 */
	function onFrame(fn) {
		var queued = false;

		return function () {
			if (queued) {
				return;
			}

			queued = true;

			window.requestAnimationFrame(function () {
				queued = false;
				fn();
			});
		};
	}

	/**
	 * Show a transient message in the shared live region.
	 *
	 * @param {string} message Message to announce.
	 * @return {void}
	 */
	var showToast = (function () {
		var timer = null;

		return function (message) {
			var toast = one('#journal-toast');

			if (!toast) {
				return;
			}

			toast.textContent = message;
			toast.classList.add('is-visible');

			window.clearTimeout(timer);

			timer = window.setTimeout(function () {
				toast.classList.remove('is-visible');
			}, 3200);
		};
	})();

	/**
	 * Elements that can receive focus, in document order.
	 *
	 * @param {Element} scope Container.
	 * @return {Array<Element>}
	 */
	function focusable(scope) {
		return all(
			'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), summary, [tabindex]:not([tabindex="-1"])',
			scope
		).filter(function (element) {
			return element.offsetWidth > 0 || element.offsetHeight > 0 || element === document.activeElement;
		});
	}

	/* ======================================================================
	   2. Theme toggle
	   ====================================================================== */

	function initTheme() {
		var button = one('#journal-theme-toggle');

		if (!button) {
			return;
		}

		var label = one('[data-theme-label]', button);

		/**
		 * Reflect the current theme on the control.
		 *
		 * @return {void}
		 */
		function sync() {
			var isDark = root.getAttribute('data-theme') === 'dark';

			button.setAttribute('aria-pressed', isDark ? 'true' : 'false');

			if (label) {
				label.textContent = isDark
					? text('themeToLight', 'Switch to light theme')
					: text('themeToDark', 'Switch to dark theme');
			}
		}

		button.addEventListener('click', function () {
			var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

			root.setAttribute('data-theme', next);

			try {
				window.localStorage.setItem(THEME_KEY, next);
			} catch (error) {
				// Storage can be unavailable in private modes. The choice then
				// applies to this page view only, which is acceptable.
			}

			sync();
		});

		// Follow the operating system while the reader has expressed no
		// preference of their own.
		var systemDark = window.matchMedia('(prefers-color-scheme: dark)');

		var followSystem = function (event) {
			var stored = null;

			try {
				stored = window.localStorage.getItem(THEME_KEY);
			} catch (error) {
				stored = null;
			}

			if (stored === 'dark' || stored === 'light') {
				return;
			}

			root.setAttribute('data-theme', event.matches ? 'dark' : 'light');
			sync();
		};

		if (typeof systemDark.addEventListener === 'function') {
			systemDark.addEventListener('change', followSystem);
		} else if (typeof systemDark.addListener === 'function') {
			systemDark.addListener(followSystem);
		}

		sync();
	}

	/* ======================================================================
	   3. Masthead behaviour
	   ====================================================================== */

	function initMasthead() {
		var masthead = one('[data-masthead]');

		if (!masthead) {
			return;
		}

		var lastY = window.scrollY;
		var threshold = 12;

		var update = onFrame(function () {
			var y = window.scrollY;
			var delta = y - lastY;

			masthead.classList.toggle('is-stuck', y > 24);

			// Hide on a sustained downward scroll, restore immediately on any
			// upward movement. Never hide while a menu is open or while focus
			// is inside the masthead.
			var menuOpen = masthead.querySelector('[aria-expanded="true"]');
			var holdsFocus = masthead.contains(document.activeElement);

			if (!menuOpen && !holdsFocus && y > 240 && delta > threshold) {
				masthead.classList.add('is-hidden');
			} else if (delta < -threshold || y <= 240) {
				masthead.classList.remove('is-hidden');
			}

			lastY = y;
		});

		window.addEventListener('scroll', update, { passive: true });
	}

	/* ======================================================================
	   4. Reading progress and time remaining
	   ====================================================================== */

	function initProgress() {
		var track = one('#journal-progress');
		var bar = one('#journal-progress-bar');
		var body = one('[data-article-body]');

		if (!track || !bar || !body) {
			return;
		}

		track.hidden = false;

		var remaining = one('[data-time-remaining]');
		var wordsPerMinute = parseInt(config.wordsPerMin, 10) || 225;
		var totalWords = (body.textContent || '').trim().split(/\s+/).length;
		var lastAnnounced = -1;

		var update = onFrame(function () {
			var rect = body.getBoundingClientRect();
			var viewport = window.innerHeight || root.clientHeight;

			// Distance scrolled through the body, clamped to [0, 1].
			var scrolled = -rect.top;
			var scrollable = rect.height - viewport;
			var ratio = scrollable > 0 ? scrolled / scrollable : (rect.top <= 0 ? 1 : 0);

			ratio = Math.min(1, Math.max(0, ratio));

			var percent = Math.round(ratio * 100);

			bar.style.width = percent + '%';
			track.setAttribute('aria-valuenow', String(percent));

			if (!remaining) {
				return;
			}

			var wordsLeft = Math.max(0, Math.round(totalWords * (1 - ratio)));
			var minutesLeft = Math.ceil(wordsLeft / wordsPerMinute);

			if (minutesLeft === lastAnnounced) {
				return;
			}

			lastAnnounced = minutesLeft;

			if (ratio >= 0.995) {
				remaining.textContent = text('finished', 'End of article');
			} else if (minutesLeft <= 1) {
				remaining.textContent = text('almostDone', 'Less than a minute left');
			} else {
				remaining.textContent = text('minutesLeft', '%s min left').replace('%s', String(minutesLeft));
			}

			remaining.hidden = percent < 3;
		});

		window.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update, { passive: true });

		update();
	}

	/* ======================================================================
	   5. Table of contents scrollspy
	   ====================================================================== */

	function initTableOfContents() {
		var toc = one('[data-toc]');
		var body = one('[data-article-body]');

		if (!toc || !body || !('IntersectionObserver' in window)) {
			return;
		}

		var links = all('[data-toc-link]', toc);

		if (!links.length) {
			return;
		}

		var byId = {};

		links.forEach(function (link) {
			byId[link.getAttribute('data-toc-link')] = link;
		});

		var headings = links
			.map(function (link) {
				return document.getElementById(link.getAttribute('data-toc-link'));
			})
			.filter(Boolean);

		if (!headings.length) {
			return;
		}

		var visible = [];

		/**
		 * Mark one link as current and clear the rest.
		 *
		 * @param {string} id Heading id.
		 * @return {void}
		 */
		function setCurrent(id) {
			links.forEach(function (link) {
				var isCurrent = link.getAttribute('data-toc-link') === id;

				link.classList.toggle('is-current', isCurrent);

				if (isCurrent) {
					link.setAttribute('aria-current', 'true');
				} else {
					link.removeAttribute('aria-current');
				}
			});

			// Keep the active entry inside the sticky rail's scroll window.
			var active = byId[id];

			if (active && toc.scrollHeight > toc.clientHeight) {
				var activeTop = active.offsetTop;
				var activeBottom = activeTop + active.offsetHeight;

				if (activeTop < toc.scrollTop || activeBottom > toc.scrollTop + toc.clientHeight) {
					toc.scrollTop = activeTop - toc.clientHeight / 2;
				}
			}
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					var id = entry.target.id;
					var index = visible.indexOf(id);

					if (entry.isIntersecting && index === -1) {
						visible.push(id);
					} else if (!entry.isIntersecting && index !== -1) {
						visible.splice(index, 1);
					}
				});

				if (visible.length) {
					// Choose the heading nearest the top of the reading zone.
					var ordered = headings
						.filter(function (heading) {
							return visible.indexOf(heading.id) !== -1;
						})
						.map(function (heading) {
							return heading.id;
						});

					setCurrent(ordered[0]);

					return;
				}

				// Between headings: highlight the last one scrolled past.
				var passed = null;

				headings.forEach(function (heading) {
					if (heading.getBoundingClientRect().top < window.innerHeight * 0.3) {
						passed = heading.id;
					}
				});

				if (passed) {
					setCurrent(passed);
				}
			},
			{
				rootMargin: '-15% 0px -70% 0px',
				threshold: 0
			}
		);

		headings.forEach(function (heading) {
			observer.observe(heading);
		});

		// On narrow viewports the rail is a disclosure; collapse it after a
		// choice so the reader lands on the content rather than the list.
		var disclosure = one('.journal-toc__disclosure', toc);

		if (disclosure) {
			var narrow = window.matchMedia('(max-width: 67.999rem)');

			var applyState = function () {
				disclosure.open = !narrow.matches;
			};

			links.forEach(function (link) {
				link.addEventListener('click', function () {
					if (narrow.matches) {
						disclosure.open = false;
					}
				});
			});

			if (typeof narrow.addEventListener === 'function') {
				narrow.addEventListener('change', applyState);
			}

			applyState();
		}
	}

	/* ======================================================================
	   6. Anchor navigation with focus management
	   ====================================================================== */

	function initAnchors() {
		document.addEventListener('click', function (event) {
			var link = event.target.closest ? event.target.closest('a[href^="#"]') : null;

			if (!link) {
				return;
			}

			var hash = link.getAttribute('href');

			if (!hash || hash === '#' || link.hasAttribute('data-no-scroll')) {
				return;
			}

			var target = document.getElementById(hash.slice(1));

			if (!target) {
				return;
			}

			event.preventDefault();

			target.scrollIntoView({
				behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
				block: 'start'
			});

			// Move focus so keyboard and screen-reader users follow the jump.
			if (!target.hasAttribute('tabindex')) {
				target.setAttribute('tabindex', '-1');
			}

			target.focus({ preventScroll: true });

			if (window.history && typeof window.history.replaceState === 'function') {
				window.history.replaceState(null, '', hash);
			}
		});
	}

	/* ======================================================================
	   7. Reveal animations
	   ====================================================================== */

	function initReveal() {
		var targets = all('[data-reveal]');

		if (!targets.length) {
			return;
		}

		if (prefersReducedMotion.matches || !('IntersectionObserver' in window)) {
			targets.forEach(function (target) {
				target.classList.add('is-revealed');
			});

			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry, index) {
					if (!entry.isIntersecting) {
						return;
					}

					// A short stagger reads as intentional rather than mechanical.
					window.setTimeout(function () {
						entry.target.classList.add('is-revealed');
					}, Math.min(index, 5) * 70);

					observer.unobserve(entry.target);
				});
			},
			{
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.08
			}
		);

		targets.forEach(function (target) {
			observer.observe(target);
		});
	}

	/* ======================================================================
	   8. Share controls
	   ====================================================================== */

	function initShare() {
		all('[data-share-popup]').forEach(function (link) {
			link.addEventListener('click', function (event) {
				// Let modified clicks behave normally.
				if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
					return;
				}

				event.preventDefault();

				var width = 600;
				var height = 640;
				var left = Math.round(window.screenX + (window.outerWidth - width) / 2);
				var top = Math.round(window.screenY + (window.outerHeight - height) / 2.5);

				var popup = window.open(
					link.href,
					'citd-share',
					'noopener,noreferrer,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top
				);

				if (!popup) {
					window.location.href = link.href;
				}
			});
		});

		all('[data-copy-link]').forEach(function (button) {
			button.addEventListener('click', function () {
				var url = button.getAttribute('data-copy-link') || window.location.href;

				var announce = function (ok) {
					showToast(ok ? text('copied', 'Link copied to clipboard') : text('copyFailed', 'Press Ctrl or Cmd + C to copy the link'));
				};

				if (navigator.clipboard && window.isSecureContext) {
					navigator.clipboard.writeText(url).then(
						function () {
							announce(true);
						},
						function () {
							announce(fallbackCopy(url));
						}
					);

					return;
				}

				announce(fallbackCopy(url));
			});
		});
	}

	/**
	 * Clipboard fallback for insecure contexts and older browsers.
	 *
	 * @param {string} value Text to copy.
	 * @return {boolean} Whether the copy succeeded.
	 */
	function fallbackCopy(value) {
		var field = document.createElement('textarea');

		field.value = value;
		field.setAttribute('readonly', '');
		field.style.position = 'fixed';
		field.style.top = '-1000px';
		field.style.opacity = '0';

		document.body.appendChild(field);
		field.select();

		var ok = false;

		try {
			ok = document.execCommand('copy');
		} catch (error) {
			ok = false;
		}

		document.body.removeChild(field);

		return ok;
	}

	/* ======================================================================
	   9. Newsletter
	   ====================================================================== */

	function initNewsletter() {
		var form = one('#journal-newsletter-form');

		if (!form || !config.ajaxUrl || !config.nonce) {
			return;
		}

		var status = one('#journal-newsletter-status');
		var submit = one('.journal-newsletter__submit', form);
		var submitLabel = submit ? one('[data-submit-label]', submit) : null;
		var email = one('#journal-newsletter-email', form);
		var consent = one('#journal-newsletter-consent', form);
		var busy = false;

		/**
		 * Write a message into the live region.
		 *
		 * @param {string}  message Message text.
		 * @param {boolean} ok      Whether the outcome was successful.
		 * @return {void}
		 */
		function setStatus(message, ok) {
			if (!status) {
				return;
			}

			status.textContent = message;
			status.classList.toggle('is-success', Boolean(ok));
			status.classList.toggle('is-error', !ok);
		}

		/**
		 * Toggle the pending state of the submit button.
		 *
		 * @param {boolean} pending Whether a request is in flight.
		 * @return {void}
		 */
		function setBusy(pending) {
			busy = pending;

			if (!submit) {
				return;
			}

			submit.setAttribute('aria-disabled', pending ? 'true' : 'false');

			if (submitLabel) {
				submitLabel.textContent = pending
					? text('subscribing', 'Subscribing…')
					: text('subscribe', 'Subscribe');
			}
		}

		form.addEventListener('submit', function (event) {
			if (busy) {
				event.preventDefault();

				return;
			}

			// Validate before preventing the default so that, if anything below
			// throws, the no-JavaScript path still works.
			var address = email ? email.value.trim() : '';

			if (address === '' || address.indexOf('@') < 1 || address.lastIndexOf('.') < address.indexOf('@')) {
				event.preventDefault();
				setStatus(text('invalidEmail', 'Enter a valid email address.'), false);

				if (email) {
					email.focus();
				}

				return;
			}

			if (consent && !consent.checked) {
				event.preventDefault();
				setStatus(text('requiredConsent', 'Please confirm you would like to receive the journal.'), false);
				consent.focus();

				return;
			}

			event.preventDefault();
			setBusy(true);
			setStatus('', true);

			var payload = new window.FormData(form);

			payload.set('action', config.action || 'citd_journal_subscribe');
			payload.set('nonce', config.nonce);

			window
				.fetch(config.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: payload
				})
				.then(function (response) {
					return response.json().catch(function () {
						return { success: false, data: { message: text('networkError', 'We could not reach the server. Please try again.') } };
					});
				})
				.then(function (result) {
					var data = result && result.data ? result.data : {};
					var ok = Boolean(result && result.success);

					setStatus(data.message || text('networkError', 'We could not reach the server. Please try again.'), ok);

					if (ok) {
						form.reset();
					}
				})
				.catch(function () {
					setStatus(text('networkError', 'We could not reach the server. Please try again.'), false);
				})
				.then(function () {
					setBusy(false);
				});
		});
	}

	/* ======================================================================
	   10. Navigation drawer
	   ====================================================================== */

	function initDrawer() {
		var toggle = one('#journal-menu-toggle');
		var drawer = one('#journal-drawer');

		if (!toggle || !drawer) {
			return;
		}

		var label = one('[data-menu-label]', toggle);
		var lastFocused = null;

		/**
		 * Open or close the drawer.
		 *
		 * @param {boolean} open Desired state.
		 * @return {void}
		 */
		function setOpen(open) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			drawer.hidden = !open;
			document.body.style.overflow = open ? 'hidden' : '';

			if (label) {
				label.textContent = open ? text('menuClose', 'Close menu') : text('menuOpen', 'Open menu');
			}

			if (open) {
				lastFocused = document.activeElement;

				var first = focusable(drawer)[0];

				if (first) {
					first.focus();
				}
			} else if (lastFocused && typeof lastFocused.focus === 'function') {
				lastFocused.focus();
				lastFocused = null;
			}
		}

		toggle.addEventListener('click', function () {
			setOpen(toggle.getAttribute('aria-expanded') !== 'true');
		});

		document.addEventListener('keydown', function (event) {
			if (drawer.hidden) {
				return;
			}

			if (event.key === 'Escape') {
				setOpen(false);

				return;
			}

			if (event.key !== 'Tab') {
				return;
			}

			// Trap focus: the drawer covers the page while open.
			var items = focusable(drawer);

			if (!items.length) {
				return;
			}

			var first = items[0];
			var last = items[items.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});

		// A resize into the desktop layout should not leave the page locked.
		var wide = window.matchMedia('(min-width: 62rem)');

		var handleWide = function (event) {
			if (event.matches && !drawer.hidden && !document.body.classList.contains('journal--article')) {
				setOpen(false);
			}
		};

		if (typeof wide.addEventListener === 'function') {
			wide.addEventListener('change', handleWide);
		}
	}

	/* ======================================================================
	   11. Search disclosure
	   ====================================================================== */

	function initSearch() {
		var toggle = one('#journal-search-toggle');
		var panel = one('#journal-search');

		if (!toggle || !panel) {
			return;
		}

		var field = one('#journal-search-field', panel);

		/**
		 * Open or close the search panel.
		 *
		 * @param {boolean} open Desired state.
		 * @return {void}
		 */
		function setOpen(open) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			panel.hidden = !open;

			if (open && field) {
				field.focus();
			}
		}

		toggle.addEventListener('click', function () {
			setOpen(toggle.getAttribute('aria-expanded') !== 'true');
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && !panel.hidden) {
				setOpen(false);
				toggle.focus();
			}
		});

		document.addEventListener('click', function (event) {
			if (panel.hidden) {
				return;
			}

			if (panel.contains(event.target) || toggle.contains(event.target)) {
				return;
			}

			setOpen(false);
		});
	}

	/* ======================================================================
	   12. Back to top
	   ====================================================================== */

	function initBackToTop() {
		var button = one('#journal-totop');

		if (!button) {
			return;
		}

		button.addEventListener('click', function () {
			window.scrollTo({
				top: 0,
				behavior: prefersReducedMotion.matches ? 'auto' : 'smooth'
			});

			var skip = one('.journal-skip-link');

			if (skip) {
				skip.focus({ preventScroll: true });
			}
		});
	}

	/* ======================================================================
	   Boot
	   ====================================================================== */

	function boot() {
		initTheme();
		initMasthead();
		initProgress();
		initTableOfContents();
		initAnchors();
		initReveal();
		initShare();
		initNewsletter();
		initDrawer();
		initSearch();
		initBackToTop();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
