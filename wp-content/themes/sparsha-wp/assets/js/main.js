// Main script for Sparsha Ayurveda Centre

document.addEventListener('DOMContentLoaded', () => {
  
  // 1. Navigation Scroll Behavior (transparent to solid white)
  const nav = document.getElementById('nav');
  if (nav) {
    const onScroll = () => {
      nav.classList.toggle('scrolled', window.scrollY > 60);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // 2. Mobile Menu Toggle
  const btn = document.getElementById('menu-btn');
  const menu = document.getElementById('mobile-menu');
  const iconOpen = document.getElementById('menu-icon-open') || document.getElementById('icon-open');
  const iconClose = document.getElementById('menu-icon-close') || document.getElementById('icon-close');
  if (btn && menu) {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = menu.classList.toggle('hidden');
      if (iconOpen) {
        iconOpen.classList.toggle('hidden', !isHidden);
        iconOpen.style.display = !isHidden ? 'none' : '';
      }
      if (iconClose) {
        iconClose.classList.toggle('hidden', isHidden);
        iconClose.style.display = isHidden ? 'none' : 'inline-block';
      }
    });
  }

  // 2b. Mobile accordion dropdowns (inject arrow + toggle sub-menus)
  const mobileNav = document.querySelector('.sparsha-mobile-nav');
  if (mobileNav) {
    mobileNav.querySelectorAll('.menu-item-has-children').forEach(li => {
      const link = li.querySelector(':scope > a');
      const sub  = li.querySelector(':scope > .sub-menu');
      if (!link || !sub) return;

      // Inject arrow icon
      const arrow = document.createElement('span');
      arrow.className = 'mobile-arrow';
      arrow.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>';
      link.appendChild(arrow);

      // Prevent navigation, toggle sub-menu
      link.addEventListener('click', e => {
        // Only intercept if this item HAS children (toggle accordion)
        e.preventDefault();
        const isOpen = sub.classList.toggle('open');
        link.classList.toggle('open', isOpen);
      });
    });
  }

  // 3. Scroll Reveal Animations (using Intersection Observer)
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => {
      observer.observe(el);
    });

    // Stagger child reveals within grid layouts
    document.querySelectorAll('.grid').forEach(grid => {
      grid.querySelectorAll('.reveal').forEach((el, i) => {
        el.style.transitionDelay = `${i * 80}ms`;
      });
    });
  }

  // 4. Swiper Initialization (for Swiper elements on home page)
  if (typeof Swiper !== 'undefined') {
    // Facts Swiper
    if (document.querySelector('.facts-swiper')) {
      const factsSwiper = new Swiper('.facts-swiper', {
        slidesPerView: 1,
        spaceBetween: 32,
        loop: true,
        speed: 600,
        pagination: {
          el: '.facts-pagination',
          clickable: true,
        },
      });

      const factsPrevBtn = document.getElementById('facts-prev');
      const factsNextBtn = document.getElementById('facts-next');
      if (factsPrevBtn) {
        factsPrevBtn.addEventListener('click', () => factsSwiper.slidePrev());
      }
      if (factsNextBtn) {
        factsNextBtn.addEventListener('click', () => factsSwiper.slideNext());
      }
    }

    // Hero Swiper
    if (document.querySelector('.hero-swiper')) {
      new Swiper('.hero-swiper', {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        loop: true,
        speed: 1000,
        autoplay: { delay: 5500, disableOnInteraction: false },
        pagination: { el: '.hero-swiper .swiper-pagination', clickable: true },
        navigation: {
          nextEl: '.hero-swiper .swiper-button-next',
          prevEl: '.hero-swiper .swiper-button-prev',
        },
      });
    }
  }

  // 5. Contact Form URL Parameters Parsing & Form Pre-filling
  const selectEl = document.getElementById('service-select');
  const textareaEl = document.getElementById('message-textarea');
  if (selectEl && textareaEl) {
    const params = new URLSearchParams(window.location.search);
    const treatment = params.get('treatment');
    const selection = params.get('selection');
    const voucher = params.get('voucher');

    if (voucher) {
      textareaEl.value = `Hi, I am interested in purchasing the following Gift Voucher: ${voucher}.\n\n`;
      for (let i = 0; i < selectEl.options.length; i++) {
        if (selectEl.options[i].text.includes("Gift Voucher")) {
          selectEl.selectedIndex = i;
          break;
        }
      }
    } else if (treatment) {
      textareaEl.value = `Hi, I am interested in booking the following treatment: ${treatment}.\n\n`;
      const lowerTreatment = treatment.toLowerCase();
      let matchedIndex = 0;

      for (let i = 0; i < selectEl.options.length; i++) {
        const optionText = selectEl.options[i].text.toLowerCase();
        if (lowerTreatment.includes("consultation") && optionText.includes("consultation")) {
          matchedIndex = i;
          break;
        } else if (lowerTreatment.includes("panchakarma") && optionText.includes("panchakarma")) {
          matchedIndex = i;
          break;
        } else if (lowerTreatment.includes("slimming") && optionText.includes("slimming")) {
          matchedIndex = i;
          break;
        } else if (lowerTreatment.includes("joint") && optionText.includes("joint")) {
          matchedIndex = i;
          break;
        } else if (lowerTreatment.includes("women") && optionText.includes("women")) {
          matchedIndex = i;
          break;
        } else if (lowerTreatment.includes("facial") && optionText.includes("facial")) {
          matchedIndex = i;
          break;
        } else if (lowerTreatment.includes("relaxation") && optionText.includes("relaxation")) {
          matchedIndex = i;
          break;
        } else if ((lowerTreatment.includes("abhyanga") || lowerTreatment.includes("massage") || lowerTreatment.includes("kizhi") || lowerTreatment.includes("swedana")) && optionText.includes("massage")) {
          matchedIndex = i;
          break;
        }
      }
      if (matchedIndex > 0) {
        selectEl.selectedIndex = matchedIndex;
      }
    } else if (selection) {
      textareaEl.value = `Hi, I have selected the following treatments for my custom Wellness Plan:\n${selection}\n\n`;
      if (selection.includes("Pure Relaxation")) {
        for (let i = 0; i < selectEl.options.length; i++) {
          if (selectEl.options[i].text.includes("Pure Relaxation")) {
            selectEl.selectedIndex = i;
            break;
          }
        }
      } else {
        for (let i = 0; i < selectEl.options.length; i++) {
          if (selectEl.options[i].text.includes("Massage")) {
            selectEl.selectedIndex = i;
            break;
          }
        }
      }
    }
  }

  // 5b. Language switcher dropdown
  document.querySelectorAll('.sparsha-lang-switcher').forEach(sw => {
    const btn = sw.querySelector('.sparsha-lang-toggle');
    const list = sw.querySelector('.sparsha-lang-list');
    if (!btn || !list) return;
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const open = !list.classList.toggle('hidden');
      btn.setAttribute('aria-expanded', open);
      sw.dataset.state = open ? 'open' : 'closed';
    });
  });
  document.addEventListener('click', e => {
    document.querySelectorAll('.sparsha-lang-switcher').forEach(sw => {
      if (!sw.contains(e.target)) {
        sw.querySelector('.sparsha-lang-list')?.classList.add('hidden');
        sw.querySelector('.sparsha-lang-toggle')?.setAttribute('aria-expanded', 'false');
        sw.dataset.state = 'closed';
      }
    });
  });

  // 6. Lightbox Gallery
  const lbGallery = document.querySelector('.sparsha-lightbox-gallery');
  if (lbGallery) {
    const items = Array.from(lbGallery.querySelectorAll('.sparsha-lightbox-item'));
    let current = 0;
    let overlay = null;

    function buildOverlay() {
      overlay = document.createElement('div');
      overlay.className = 'sparsha-lightbox';
      overlay.innerHTML = `
        <button class="sparsha-lightbox-close" aria-label="Close">&times;</button>
        <button class="sparsha-lightbox-prev" aria-label="Previous">&#10094;</button>
        <button class="sparsha-lightbox-next" aria-label="Next">&#10095;</button>
        <div class="sparsha-lightbox-stage"><img class="sparsha-lightbox-img" alt=""></div>
        <div class="sparsha-lightbox-counter"></div>
      `;
      document.body.appendChild(overlay);
      overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
      overlay.querySelector('.sparsha-lightbox-close').addEventListener('click', close);
      overlay.querySelector('.sparsha-lightbox-prev').addEventListener('click', prev);
      overlay.querySelector('.sparsha-lightbox-next').addEventListener('click', next);
    }

    function show(i) {
      current = (i + items.length) % items.length;
      const link = items[current];
      const img = overlay.querySelector('.sparsha-lightbox-img');
      const counter = overlay.querySelector('.sparsha-lightbox-counter');
      img.src = link.href;
      img.alt = link.getAttribute('aria-label') || '';
      counter.textContent = `${current + 1} / ${items.length}`;
    }
    function open(i) {
      if (!overlay) buildOverlay();
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      show(i);
    }
    function close() {
      if (!overlay) return;
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
    }
    function next() { show(current + 1); }
    function prev() { show(current - 1); }

    items.forEach((link, i) => {
      link.addEventListener('click', e => { e.preventDefault(); open(i); });
    });

    document.addEventListener('keydown', e => {
      if (!overlay || !overlay.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowRight') next();
      else if (e.key === 'ArrowLeft') prev();
    });
  }

});
