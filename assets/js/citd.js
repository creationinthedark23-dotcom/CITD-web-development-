/* ==========================================================================
   Creation in the Dark Holdings — interaction layer

   Motion hierarchy
     Level 1  micro       hover, cursor light, magnetic CTAs, nav underlines
     Level 2  section     reveals, line staggers, image masks, parallax
     Level 3  feature     loader handoff, hero entrance, hero cover, blooms,
                          page transitions

   Everything here is enhancement. With JS disabled or a library missing the
   page renders complete and static, and every link still works.
   ========================================================================== */

(function () {
  "use strict";

  var body = document.body;

  var reduced = window.matchMedia("(prefers-reduced-motion: reduce)");
  var finePointer = window.matchMedia("(pointer: fine)");
  var hasGsap = typeof window.gsap !== "undefined";
  var hasST = hasGsap && typeof window.ScrollTrigger !== "undefined";
  var hasLenis = typeof window.Lenis !== "undefined";

  /* Sections that sit above the fold on load. Their reveals are driven by
     the loader handoff rather than by scroll. */
  var ABOVE_FOLD = ".hero, .jhead";

  /* Native CSS scroll-driven animation. Where it exists the browser owns the
     line work in "How we think" and JS stays out of the way. */
  var sda =
    typeof CSS !== "undefined" &&
    CSS.supports &&
    CSS.supports("animation-timeline", "view()");

  function motionOK() {
    return !reduced.matches;
  }

  function headerOffset() {
    var h = document.getElementById("siteHeader");
    return h ? h.offsetHeight : 64;
  }

  /* sessionStorage throws in some privacy modes, so never trust it bare */
  function session(key, value) {
    try {
      if (typeof value === "undefined") return window.sessionStorage.getItem(key);
      window.sessionStorage.setItem(key, value);
    } catch (e) {
      /* no-op */
    }
    return null;
  }

  /* ------------------------------------------------------------------------
     1. Smooth scrolling (Lenis)
     ------------------------------------------------------------------------ */

  var lenis = null;

  function initLenis() {
    if (!hasLenis || !motionOK() || !finePointer.matches) return;

    lenis = new window.Lenis({
      duration: 1.1,
      easing: function (t) {
        return Math.min(1, 1.001 - Math.pow(2, -10 * t));
      },
      smoothWheel: true,
      syncTouch: false, // native scrolling stays native on touch
      touchMultiplier: 1.6
    });

    if (hasST) {
      lenis.on("scroll", window.ScrollTrigger.update);
      window.gsap.ticker.add(function (time) {
        lenis.raf(time * 1000);
      });
      window.gsap.ticker.lagSmoothing(0);
    } else {
      var raf = function (time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
      };
      requestAnimationFrame(raf);
    }
  }

  function scrollToTarget(target, hash) {
    var top =
      hash === "#top"
        ? 0
        : target.getBoundingClientRect().top + window.scrollY - headerOffset();

    if (lenis) {
      lenis.scrollTo(top, { duration: 1.25 });
    } else {
      window.scrollTo({
        top: top,
        behavior: motionOK() ? "smooth" : "auto"
      });
    }
    return top;
  }

  /* ------------------------------------------------------------------------
     2. Navigation — in-page anchors and cross-page transitions
     ------------------------------------------------------------------------ */

  function samePage(url) {
    return (
      url.pathname.replace(/index\.html$/, "") ===
        location.pathname.replace(/index\.html$/, "") &&
      url.search === location.search
    );
  }

  function initAnchors() {
    document.addEventListener("click", function (event) {
      /* Let the browser handle modified clicks and new-tab intent */
      if (
        event.defaultPrevented ||
        event.button !== 0 ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey
      ) {
        return;
      }

      var link = event.target.closest("a[href]");
      if (!link || link.target === "_blank" || link.hasAttribute("download")) {
        return;
      }

      var raw = link.getAttribute("href");
      if (!raw || /^(mailto:|tel:|sms:|https?:\/\/wa\.me)/i.test(raw)) return;

      var url;
      try {
        url = new URL(link.href, location.href);
      } catch (e) {
        return;
      }

      if (url.origin !== location.origin) return;

      /* --- in-page anchor ------------------------------------------------ */
      if (url.hash && samePage(url)) {
        var target =
          url.hash === "#top" ? document.body : document.querySelector(url.hash);
        if (!target) return;

        event.preventDefault();
        closeMenu();
        scrollToTarget(target, url.hash);
        moveFocusTo(target, url.hash);
        if (history.replaceState) history.replaceState(null, "", url.hash);
        return;
      }

      /* --- same page, no hash: nothing to do ----------------------------- */
      if (!url.hash && samePage(url)) {
        event.preventDefault();
        closeMenu();
        scrollToTarget(document.body, "#top");
        return;
      }

      /* --- cross-page: short veil, then navigate ------------------------- */
      if (!motionOK()) return; // let it navigate immediately

      event.preventDefault();
      closeMenu();
      body.classList.add("is-leaving");

      var go = function () {
        location.href = url.href;
      };
      /* The veil is a courtesy, not a gate: navigate regardless */
      window.setTimeout(go, 380);
    });
  }

  /* Move document focus so keyboard and screen reader users land where
     sighted users land. */
  function moveFocusTo(target, hash) {
    if (hash === "#top" || !target || target === document.body) return;
    var had = target.hasAttribute("tabindex");
    if (!had) target.setAttribute("tabindex", "-1");
    target.focus({ preventScroll: true });
    if (!had) {
      target.addEventListener(
        "blur",
        function () {
          target.removeAttribute("tabindex");
        },
        { once: true }
      );
    }
  }

  /* Restoring from the back/forward cache must not leave the veil up */
  function initPageshow() {
    /* No beforeunload listener here: it would make the page ineligible for
       the back/forward cache in some browsers. */
    window.addEventListener("pageshow", function (event) {
      body.classList.remove("is-leaving");
      if (event.persisted && hasST) window.ScrollTrigger.refresh();
    });
  }

  /* Mark the nav link for the document we are actually on */
  function initCurrentPage() {
    document
      .querySelectorAll(".primary-nav a[href], .menu-panel a[href]")
      .forEach(function (link) {
        var url;
        try {
          url = new URL(link.href, location.href);
        } catch (e) {
          return;
        }
        /* Only a hash-less link to this same document is "the current page";
           in-page anchors are handled by the scroll spy. */
        if (!url.hash && samePage(url)) {
          link.classList.add("is-current");
          link.setAttribute("aria-current", "page");
        }
      });
  }

  /* ------------------------------------------------------------------------
     3. Loading transition -> content handoff
     ------------------------------------------------------------------------ */

  function initLoader() {
    var loader = document.getElementById("loader");
    var hero = document.getElementById("hero");
    var done = false;

    /* The loader is a first-impression, not a toll gate. Once this session
       has seen it, every later page arrives on the page transition instead. */
    var seen = session("citd:loaded") === "1";

    function finish(skipBeat) {
      if (done) return;
      done = true;

      session("citd:loaded", "1");

      if (loader) loader.classList.add("is-done");
      body.classList.remove("is-locked");
      body.classList.add("is-ready");
      if (hero) hero.classList.add("is-lit");

      revealAboveFold();
      heroEntrance();
      initHeroVideo();
      honourInitialHash();

      if (loader) {
        window.setTimeout(
          function () {
            if (loader.parentNode) loader.parentNode.removeChild(loader);
          },
          skipBeat ? 0 : 1000
        );
      }
      if (hasST) window.setTimeout(function () {
        window.ScrollTrigger.refresh();
      }, 60);
    }

    if (!loader || seen || !motionOK()) {
      if (loader) loader.remove();
      if (motionOK()) {
        body.classList.add("is-arriving");
        window.setTimeout(function () {
          body.classList.remove("is-arriving");
        }, 700);
      }
      finish(true);
      return;
    }

    body.classList.add("is-locked");

    /* Whichever comes first: the page is ready, or we stop waiting. The
       visitor is never held behind the loader. */
    var ceiling = window.setTimeout(finish, 2600);
    window.setTimeout(finish, 1150);

    if (document.readyState !== "complete") {
      window.addEventListener(
        "load",
        function () {
          window.clearTimeout(ceiling);
          window.setTimeout(finish, 420);
        },
        { once: true }
      );
    }
  }

  function revealAboveFold() {
    document
      .querySelectorAll(ABOVE_FOLD)
      .forEach(function (section) {
        section.querySelectorAll("[data-reveal]").forEach(function (el) {
          el.classList.add("is-in");
        });
      });
  }

  /* The hero background video is opt-in, never a cost the visitor did not
     agree to: it is skipped for reduced motion, for Save-Data, and on slow
     connections. The approved still stays visible until it actually plays. */
  function initHeroVideo() {
    var video = document.getElementById("heroVideo");
    if (!video || !motionOK()) return;

    var src = video.getAttribute("data-src");
    if (!src) return;

    var conn =
      navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (conn) {
      if (conn.saveData) return;
      if (/2g/.test(conn.effectiveType || "")) return;
    }

    video.addEventListener(
      "playing",
      function () {
        video.classList.add("is-playing");
      },
      { once: true }
    );

    /* If it cannot play, leave the still in place and stop trying */
    video.addEventListener(
      "error",
      function () {
        video.classList.remove("is-playing");
        video.removeAttribute("src");
      },
      { once: true }
    );

    video.src = src;

    var attempt = video.play();
    if (attempt && typeof attempt.catch === "function") {
      attempt.catch(function () {
        /* Autoplay refused: the still is already doing the job */
      });
    }

    /* Stop decoding while the hero is off screen */
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(
        function (entries) {
          if (entries[0].isIntersecting) {
            var r = video.play();
            if (r && typeof r.catch === "function") r.catch(function () {});
          } else if (!video.paused) {
            video.pause();
          }
        },
        { threshold: 0.05 }
      ).observe(video);
    }
  }

  function heroEntrance() {
    if (!hasGsap || !motionOK()) return;
    var media = document.querySelector(".hero__media");
    if (!media) return;

    /* Level 3: the scene settles from 1.06 to 1. Slow enough to read as
       atmosphere rather than a transition. */
    window.gsap.fromTo(
      media,
      { scale: 1.06 },
      { scale: 1, duration: 2.6, ease: "power2.out" }
    );
  }

  /* Landing on a deep link must not leave the sections above it blank */
  function honourInitialHash() {
    var hash = location.hash;
    if (!hash || hash.length < 2) return;

    var target;
    try {
      target = document.querySelector(hash);
    } catch (e) {
      return;
    }
    if (!target) return;

    var top = target.getBoundingClientRect().top + window.scrollY - headerOffset();
    revealAbove(top + window.innerHeight);

    if (lenis) {
      lenis.scrollTo(top, { immediate: true });
    } else {
      window.scrollTo({ top: top, behavior: "auto" });
    }
  }

  function revealAbove(y) {
    document.querySelectorAll("[data-reveal]").forEach(function (el) {
      if (el.getBoundingClientRect().top + window.scrollY < y) {
        el.classList.add("is-in");
      }
    });
    document.querySelectorAll(".finale, .story__media").forEach(function (el) {
      if (el.getBoundingClientRect().top + window.scrollY < y) {
        el.classList.add("is-in");
      }
    });
  }

  /* ------------------------------------------------------------------------
     4. Section reveals
     ------------------------------------------------------------------------ */

  function initReveals() {
    var items = document.querySelectorAll("[data-reveal]");
    if (!items.length) return;

    /* Reduced motion, or no observer: nothing is ever withheld. */
    if (!motionOK() || !("IntersectionObserver" in window)) {
      items.forEach(function (el) {
        el.classList.add("is-in");
      });
      document.querySelectorAll(".finale, .story__media").forEach(function (el) {
        el.classList.add("is-in");
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-in");
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.08, rootMargin: "0px 0px -8% 0px" }
    );

    items.forEach(function (item) {
      /* Above-the-fold sections are driven by the loader handoff */
      if (item.closest(ABOVE_FOLD)) return;
      observer.observe(item);
    });

    /* Elements that light their own section rather than themselves */
    var lit = document.querySelectorAll(".finale, .story__media");
    if (!lit.length) return;

    var litObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-in");
          litObserver.unobserve(entry.target);
        });
      },
      { threshold: 0.2 }
    );
    lit.forEach(function (el) {
      litObserver.observe(el);
    });
  }

  /* ------------------------------------------------------------------------
     5. Header: scrolled state, inversion over paper chapters, scroll spy
     ------------------------------------------------------------------------ */

  function initHeader() {
    var header = document.getElementById("siteHeader");
    if (!header) return;

    /* Pages without a hero begin with content directly under the header,
       so the header carries its solid treatment from the start. */
    var hasHero = !!document.querySelector(".hero");
    var ticking = false;

    /* The floating WhatsApp button steps aside once the footer — which
       carries the same details in full — is on screen. */
    var footerInView = false;

    function update() {
      header.classList.toggle("is-scrolled", !hasHero || window.scrollY > 24);
      body.classList.toggle(
        "show-whatsapp",
        !footerInView && window.scrollY > window.innerHeight * 0.5
      );
      ticking = false;
    }

    var footer = document.querySelector(".site-footer");
    if (footer && "IntersectionObserver" in window) {
      new IntersectionObserver(function (entries) {
        footerInView = entries[0].isIntersecting;
        update();
      }).observe(footer);
    }

    update();
    window.addEventListener(
      "scroll",
      function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(update);
      },
      { passive: true }
    );

    /* Invert the header wherever a light chapter crosses the header line. */
    var lightSections = document.querySelectorAll('[data-header="light"]');
    if (!lightSections.length || !("IntersectionObserver" in window)) return;

    var active = new Set();
    var observer = null;

    function build() {
      if (observer) observer.disconnect();

      var line = header.offsetHeight * 0.55;
      var bottom = Math.max(0, window.innerHeight - line - 2);

      observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) active.add(entry.target);
            else active.delete(entry.target);
          });
          header.classList.toggle("is-light", active.size > 0);
          body.classList.toggle("on-light", active.size > 0);
        },
        {
          rootMargin:
            "-" + Math.round(line) + "px 0px -" + Math.round(bottom) + "px 0px"
        }
      );

      active.clear();
      lightSections.forEach(function (section) {
        observer.observe(section);
      });
    }

    build();

    var resizeTimer;
    window.addEventListener("resize", function () {
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(build, 180);
    });
  }

  function initScrollSpy() {
    if (!("IntersectionObserver" in window)) return;

    /* Only links that point at a section of THIS document */
    var owned = [];
    var map = new Map();

    document.querySelectorAll(".primary-nav .nav-link").forEach(function (link) {
      var url;
      try {
        url = new URL(link.href, location.href);
      } catch (e) {
        return;
      }
      if (!url.hash || !samePage(url)) return;

      var section;
      try {
        section = document.querySelector(url.hash);
      } catch (e) {
        return;
      }
      if (!section) return;

      owned.push(link);
      map.set(section, link);
    });

    if (!map.size) return;

    var visible = new Map();

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            visible.set(entry.target, entry.intersectionRatio);
          } else {
            visible.delete(entry.target);
          }
        });

        var best = null;
        var bestRatio = 0;
        visible.forEach(function (ratio, section) {
          if (ratio > bestRatio) {
            bestRatio = ratio;
            best = section;
          }
        });

        /* Clear only the links this spy owns, so a current-page link
           marked by initCurrentPage is never stripped. */
        owned.forEach(function (link) {
          link.classList.remove("is-current");
        });
        if (best && map.has(best)) map.get(best).classList.add("is-current");
      },
      { threshold: [0.12, 0.3, 0.6], rootMargin: "-18% 0px -35% 0px" }
    );

    map.forEach(function (link, section) {
      observer.observe(section);
    });
  }

  /* ------------------------------------------------------------------------
     6. Mobile menu
     ------------------------------------------------------------------------ */

  var menuOpen = false;
  var lastFocused = null;

  function menuParts() {
    return {
      panel: document.getElementById("menuPanel"),
      toggle: document.getElementById("menuToggle"),
      close: document.getElementById("menuClose")
    };
  }

  function siblingsOf(panel) {
    var out = [];
    var nodes = body.children;
    for (var i = 0; i < nodes.length; i++) {
      var node = nodes[i];
      if (node === panel || node.tagName === "SCRIPT") continue;
      out.push(node);
    }
    return out;
  }

  function openMenu() {
    var parts = menuParts();
    if (!parts.panel || menuOpen) return;

    menuOpen = true;
    lastFocused = document.activeElement;

    parts.panel.removeAttribute("inert");
    parts.panel.classList.add("is-open");
    if (parts.toggle) parts.toggle.setAttribute("aria-expanded", "true");

    body.classList.add("is-locked");
    if (lenis) lenis.stop();

    siblingsOf(parts.panel).forEach(function (node) {
      node.setAttribute("inert", "");
    });

    /* The panel is still visibility:hidden on this frame, and a hidden
       element cannot take focus. Wait for the style to land. */
    if (parts.close) {
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          if (menuOpen) parts.close.focus();
        });
      });
    }
  }

  function closeMenu() {
    var parts = menuParts();
    if (!parts.panel || !menuOpen) return;

    menuOpen = false;

    parts.panel.classList.remove("is-open");
    parts.panel.setAttribute("inert", "");
    if (parts.toggle) parts.toggle.setAttribute("aria-expanded", "false");

    body.classList.remove("is-locked");
    if (lenis) lenis.start();

    siblingsOf(parts.panel).forEach(function (node) {
      node.removeAttribute("inert");
    });

    if (lastFocused && document.contains(lastFocused)) {
      lastFocused.focus();
    } else if (parts.toggle) {
      parts.toggle.focus();
    }
  }

  function initMenu() {
    var parts = menuParts();
    if (!parts.panel) return;

    if (parts.toggle) parts.toggle.addEventListener("click", openMenu);
    if (parts.close) parts.close.addEventListener("click", closeMenu);

    document.addEventListener("keydown", function (event) {
      if (!menuOpen) return;

      if (event.key === "Escape") {
        event.preventDefault();
        closeMenu();
        return;
      }

      /* Backstop trap for browsers without inert */
      if (event.key !== "Tab") return;

      var focusable = parts.panel.querySelectorAll(
        'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
      );
      if (!focusable.length) return;

      var first = focusable[0];
      var last = focusable[focusable.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });

    /* A menu left open across a resize into desktop would trap focus */
    window.addEventListener("resize", function () {
      if (menuOpen && window.innerWidth > 1020) closeMenu();
    });
  }

  /* ------------------------------------------------------------------------
     7. Cursor light + magnetic CTAs  (fine pointers only)
     ------------------------------------------------------------------------ */

  function initSpotlight() {
    var light = document.getElementById("spotlight");
    if (!light || !finePointer.matches || !motionOK()) return;

    body.classList.add("has-spotlight");

    var tx = window.innerWidth / 2;
    var ty = window.innerHeight / 2;
    var cx = tx;
    var cy = ty;
    var running = false;

    function frame() {
      cx += (tx - cx) * 0.11;
      cy += (ty - cy) * 0.11;
      light.style.transform =
        "translate3d(" + cx.toFixed(1) + "px," + cy.toFixed(1) + "px,0)";

      if (Math.abs(tx - cx) > 0.4 || Math.abs(ty - cy) > 0.4) {
        requestAnimationFrame(frame);
      } else {
        running = false;
      }
    }

    window.addEventListener(
      "pointermove",
      function (event) {
        if (event.pointerType !== "mouse") return;
        tx = event.clientX;
        ty = event.clientY;
        if (!running) {
          running = true;
          requestAnimationFrame(frame);
        }
      },
      { passive: true }
    );
  }

  function initMagnetic() {
    if (!finePointer.matches || !motionOK()) return;

    document.querySelectorAll("[data-magnetic]").forEach(function (el) {
      var raf = null;
      var x = 0;
      var y = 0;

      function apply() {
        raf = null;
        el.style.transform =
          "translate3d(" + x.toFixed(2) + "px," + y.toFixed(2) + "px,0)";
      }

      el.addEventListener("pointermove", function (event) {
        if (event.pointerType !== "mouse") return;
        var rect = el.getBoundingClientRect();
        /* A nudge, never a jump: capped at 6px */
        x = ((event.clientX - (rect.left + rect.width / 2)) / rect.width) * 12;
        y = ((event.clientY - (rect.top + rect.height / 2)) / rect.height) * 10;
        x = Math.max(-6, Math.min(6, x));
        y = Math.max(-5, Math.min(5, y));
        if (!raf) raf = requestAnimationFrame(apply);
      });

      function settle() {
        x = 0;
        y = 0;
        if (!raf) raf = requestAnimationFrame(apply);
      }

      el.addEventListener("pointerleave", settle);
      el.addEventListener("blur", settle);
    });
  }

  /* ------------------------------------------------------------------------
     8. Scroll choreography (GSAP ScrollTrigger)
     ------------------------------------------------------------------------ */

  var PARALLAX = { hero: 8, mid: 6, slow: 3.5 };

  function initScroll() {
    if (!hasST || !motionOK()) return;

    window.gsap.registerPlugin(window.ScrollTrigger);

    var mm = window.gsap.matchMedia();

    /* --- Level 3: the next chapter rises over the hero ------------------- */
    var hero = document.getElementById("hero");
    if (hero) {
      window.gsap.to(hero, {
        opacity: 0.22,
        scale: 0.955,
        ease: "none",
        scrollTrigger: {
          trigger: hero,
          start: "top top",
          end: "bottom top",
          scrub: true
        }
      });

      var heroInner = hero.querySelector(".hero__inner");
      if (heroInner) {
        window.gsap.to(heroInner, {
          yPercent: -9,
          ease: "none",
          scrollTrigger: {
            trigger: hero,
            start: "top top",
            end: "bottom top",
            scrub: true
          }
        });
      }

      /* Once it is fully covered there is nothing to composite */
      window.ScrollTrigger.create({
        trigger: hero,
        start: "bottom top",
        onEnter: function () {
          hero.style.visibility = "hidden";
        },
        onLeaveBack: function () {
          hero.style.visibility = "";
        }
      });
    }

    /* --- Level 2: image parallax ---------------------------------------- */
    document.querySelectorAll("[data-parallax]").forEach(function (frame) {
      var amount = PARALLAX[frame.getAttribute("data-parallax")] || 5;
      var container = frame.parentElement;

      window.gsap.fromTo(
        frame,
        { yPercent: -amount },
        {
          yPercent: amount,
          ease: "none",
          scrollTrigger: {
            trigger: container,
            start: "top bottom",
            end: "bottom top",
            scrub: true,
            invalidateOnRefresh: true
          }
        }
      );
    });

    /* --- Level 2: line work in "How we think" ---------------------------
       Only where the browser cannot do it natively via animation-timeline. */
    if (!sda && document.querySelector(".stages")) {
      mm.add("(min-width: 1021px)", function () {
        var fill = document.querySelector(".stages__fill");
        var stages = document.querySelector(".stages");

        if (fill && stages) {
          window.gsap.to(fill, {
            scaleX: 1,
            ease: "none",
            scrollTrigger: {
              trigger: stages,
              start: "top 88%",
              end: "top 32%",
              scrub: true
            }
          });
        }

        document.querySelectorAll(".stage__drop").forEach(function (drop) {
          window.gsap.fromTo(
            drop,
            { scaleY: 0 },
            {
              scaleY: 1,
              duration: 0.9,
              ease: "power3.out",
              scrollTrigger: { trigger: drop.parentElement, start: "top 82%" }
            }
          );
        });
      });

      mm.add("(max-width: 1020px)", function () {
        var fill = document.querySelector(".stages__fill");
        if (fill) window.gsap.set(fill, { scaleX: 1 });
      });
    }

    /* Late-loading images change the page height */
    window.addEventListener("load", function () {
      window.ScrollTrigger.refresh();
    });
  }

  /* When motion is reduced, static line work must still read as complete. */
  function settleStaticLinework() {
    if (motionOK() && (hasST || sda)) return;

    var fill = document.querySelector(".stages__fill");
    if (fill) fill.style.transform = "scaleX(1)";

    document.querySelectorAll(".stage__drop").forEach(function (drop) {
      drop.style.transform = "scaleY(1)";
    });
  }

  /* ------------------------------------------------------------------------
     9. Boot
     ------------------------------------------------------------------------ */

  function boot() {
    initLenis();
    initAnchors();
    initPageshow();
    initCurrentPage();
    initReveals();
    initHeader();
    initScrollSpy();
    initMenu();
    initSpotlight();
    initMagnetic();
    initScroll();
    settleStaticLinework();
    initLoader();

    /* Turning reduced motion on mid-session should take effect immediately,
       and must not leave elements parked mid-parallax. */
    var onPrefChange = function () {
      if (!reduced.matches) return;

      if (lenis) {
        lenis.destroy();
        lenis = null;
      }
      body.classList.remove("has-spotlight", "is-leaving", "is-arriving");

      if (hasST) {
        window.ScrollTrigger.getAll().forEach(function (t) {
          t.kill(false);
        });
      }

      if (hasGsap) {
        window.gsap.set("[data-parallax], .hero, .hero__inner, .hero__media", {
          clearProps: "all"
        });
      }

      var hero = document.getElementById("hero");
      if (hero) hero.style.visibility = "";

      var video = document.getElementById("heroVideo");
      if (video) {
        video.pause();
        video.classList.remove("is-playing");
      }

      document.querySelectorAll("[data-reveal]").forEach(function (el) {
        el.classList.add("is-in");
      });
      document.querySelectorAll(".finale, .story__media").forEach(function (el) {
        el.classList.add("is-in");
      });
      settleStaticLinework();
    };

    if (typeof reduced.addEventListener === "function") {
      reduced.addEventListener("change", onPrefChange);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot, { once: true });
  } else {
    boot();
  }
})();
