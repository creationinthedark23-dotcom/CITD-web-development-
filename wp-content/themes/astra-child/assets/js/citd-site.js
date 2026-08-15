/**
 * CREATION IN THE DARK HOLDINGS — site behaviour
 *
 * One job: reveal sections as they enter the viewport. That is the whole
 * motion system. Nothing on the site depends on this file — without it every
 * section is simply visible, which is why the reveal styles are gated behind
 * the `citd-has-js` class added below rather than applied by default.
 */

(function () {
	'use strict';

	var root = document.documentElement;
	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

	// Only opt into the hidden-then-revealed state once we know we can undo it.
	if (!('IntersectionObserver' in window) || reduced.matches) {
		return;
	}

	root.classList.add('citd-has-js');

	function reveal() {
		var targets = Array.prototype.slice.call(
			document.querySelectorAll('.citd-reveal, [data-citd-reveal]')
		);

		if (!targets.length) {
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
					}, Math.min(index, 4) * 70);

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

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', reveal);
	} else {
		reveal();
	}
})();
