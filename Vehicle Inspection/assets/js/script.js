/**
 * DISCERN VEHICLE INSPECTION — INTERACTIVE LOGIC & CONTROLS
 * Pure Vanilla JavaScript (No jQuery dependency)
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. VANILLA JS INTERSECTION OBSERVER (CUSTOM AOS-STYLE SCROLL REVEAL)
  initScrollAnimations();

  // 2. SWIPER.JS INITIALIZATIONS
  initSwipers();

  // 3. SAMPLE REPORT INTERACTIVE VIEWER
  initSampleReportViewer();

  // 4. PRICING & ADD-ONS INTERACTIVE CALCULATOR
  initPricingCalculator();

  // 5. SERVICE AREA FILTER / SEARCH
  initServiceAreaSearch();

  // 6. MULTI-STEP BOOKING WIZARD MODAL
  initBookingWizard();

  // 7. INSPECTOR APPLICATION FORM
  initInspectorApplication();

  // 8. TOOLTIPS & UI POLISH
  initBootstrapComponents();
});

/* --- 1. Custom AOS-Style Scroll Observer --- */
function initScrollAnimations() {
  const animatedElements = document.querySelectorAll('[data-aos]');
  
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('aos-animate');
          obs.unobserve(entry.target); // Trigger once for performance
        }
      });
    }, {
      threshold: 0.12,
      rootMargin: '0px 0px -40px 0px'
    });

    animatedElements.forEach(el => observer.observe(el));
  } else {
    // Fallback if IntersectionObserver not supported
    animatedElements.forEach(el => el.classList.add('aos-animate'));
  }
}

/* --- 2. Swiper.js Sliders --- */
function initSwipers() {
  // Testimonials Slider
  if (document.querySelector('.testimonials-swiper')) {
    new Swiper('.testimonials-swiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      autoplay: {
        delay: 5500,
        disableOnInteraction: false,
      },
      pagination: {
        el: '.testimonials-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.testimonials-next',
        prevEl: '.testimonials-prev',
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
          spaceBetween: 24,
        },
        1200: {
          slidesPerView: 3,
          spaceBetween: 28,
        }
      }
    });
  }

  // Defects Showcase Slider
  if (document.querySelector('.defects-swiper')) {
    new Swiper('.defects-swiper', {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      autoplay: {
        delay: 4000,
        pauseOnMouseEnter: true,
      },
      pagination: {
        el: '.defects-pagination',
        clickable: true,
      },
      breakpoints: {
        640: {
          slidesPerView: 2,
          spaceBetween: 20,
        },
        992: {
          slidesPerView: 3,
          spaceBetween: 24,
        }
      }
    });
  }
}

/* --- 3. Interactive Sample Report Tabs --- */
function initSampleReportViewer() {
  const tabButtons = document.querySelectorAll('.report-tab-btn');
  const reportPanels = document.querySelectorAll('.report-panel-content');

  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-target');

      tabButtons.forEach(btn => btn.classList.remove('active'));
      reportPanels.forEach(panel => panel.classList.add('d-none'));

      button.classList.add('active');
      const activePanel = document.getElementById(targetId);
      if (activePanel) {
        activePanel.classList.remove('d-none');
      }
    });
  });
}

/* --- 4. Pricing & Add-on Calculator --- */
function initPricingCalculator() {
  const basePackages = {
    essential: 179,
    standard: 229,
    complete: 289
  };

  let selectedPackage = 'complete';
  let selectedAddons = new Set();

  const addonPrices = {
    paint: 49,
    carfax: 35,
    rush: 65,
    classic: 95
  };

  // Addon card click handler
  const addonCards = document.querySelectorAll('.addon-box');
  addonCards.forEach(box => {
    box.addEventListener('click', () => {
      const addonKey = box.getAttribute('data-addon');
      const checkbox = box.querySelector('input[type="checkbox"]');

      if (selectedAddons.has(addonKey)) {
        selectedAddons.delete(addonKey);
        box.classList.remove('selected');
        if (checkbox) checkbox.checked = false;
      } else {
        selectedAddons.add(addonKey);
        box.classList.add('selected');
        if (checkbox) checkbox.checked = true;
      }
      updateTotal();
    });
  });

  // Package select button clicks
  const selectPackageBtns = document.querySelectorAll('[data-select-package]');
  selectPackageBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const pkg = btn.getAttribute('data-select-package');
      selectedPackage = pkg;
      
      // Update modal selection
      const modalPkgSelect = document.getElementById('wizard-package-select');
      if (modalPkgSelect) {
        modalPkgSelect.value = pkg;
      }
      
      // Open Booking Modal
      const bookingModalEl = document.getElementById('bookingModal');
      if (bookingModalEl && window.bootstrap) {
        const modal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
        modal.show();
      }
    });
  });

  function updateTotal() {
    let total = basePackages[selectedPackage] || 289;
    selectedAddons.forEach(addon => {
      total += addonPrices[addon] || 0;
    });

    const displayTotal = document.getElementById('calc-total-display');
    if (displayTotal) {
      displayTotal.textContent = `$${total}`;
    }
  }
}

/* --- 5. Service Area Filter --- */
function initServiceAreaSearch() {
  const searchInput = document.getElementById('service-area-search');
  const metroBadges = document.querySelectorAll('.service-metro-item');
  const noResultEl = document.getElementById('service-area-empty');

  if (!searchInput) return;

  searchInput.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase().trim();
    let matchCount = 0;

    metroBadges.forEach(item => {
      const text = item.textContent.toLowerCase();
      const state = item.getAttribute('data-state')?.toLowerCase() || '';
      if (text.includes(term) || state.includes(term)) {
        item.style.display = 'inline-block';
        matchCount++;
      } else {
        item.style.display = 'none';
      }
    });

    if (noResultEl) {
      noResultEl.style.display = (matchCount === 0 && term !== '') ? 'block' : 'none';
    }
  });
}

/* --- 6. Multi-Step Booking Wizard --- */
function initBookingWizard() {
  let currentStep = 1;
  const totalSteps = 3;

  const stepIndicators = document.querySelectorAll('.wizard-step-item');
  const stepContainers = document.querySelectorAll('.wizard-step-container');
  const btnNext = document.getElementById('wizard-next-btn');
  const btnPrev = document.getElementById('wizard-prev-btn');
  const btnSubmit = document.getElementById('wizard-submit-btn');
  const successState = document.getElementById('wizard-success-state');
  const wizardForm = document.getElementById('booking-wizard-form');

  if (!btnNext || !btnPrev) return;

  function showStep(step) {
    currentStep = step;
    
    // Update Indicators
    stepIndicators.forEach((ind, index) => {
      ind.classList.remove('active', 'completed');
      if (index + 1 === step) {
        ind.classList.add('active');
      } else if (index + 1 < step) {
        ind.classList.add('completed');
      }
    });

    // Update Containers
    stepContainers.forEach((container, index) => {
      if (index + 1 === step) {
        container.classList.remove('d-none');
      } else {
        container.classList.add('d-none');
      }
    });

    // Toggle Buttons
    btnPrev.style.display = step > 1 ? 'inline-flex' : 'none';
    if (step === totalSteps) {
      btnNext.style.display = 'none';
      btnSubmit.style.display = 'inline-flex';
      populateReviewSummary();
    } else {
      btnNext.style.display = 'inline-flex';
      btnSubmit.style.display = 'none';
    }
  }

  function validateStep(step) {
    if (step === 1) {
      const make = document.getElementById('wizard-make')?.value.trim();
      const model = document.getElementById('wizard-model')?.value.trim();
      const year = document.getElementById('wizard-year')?.value.trim();
      if (!make || !model || !year) {
        alert('Please fill in Vehicle Year, Make, and Model.');
        return false;
      }
    } else if (step === 2) {
      const zip = document.getElementById('wizard-zip')?.value.trim();
      const sellerPhone = document.getElementById('wizard-seller-phone')?.value.trim();
      if (!zip) {
        alert('Please provide the vehicle ZIP code / seller location.');
        return false;
      }
    }
    return true;
  }

  function populateReviewSummary() {
    const year = document.getElementById('wizard-year')?.value || '2021';
    const make = document.getElementById('wizard-make')?.value || 'Porsche';
    const model = document.getElementById('wizard-model')?.value || 'Macan GTS';
    const vin = document.getElementById('wizard-vin')?.value || 'WP1AA2AY3MLA09482';
    const zip = document.getElementById('wizard-zip')?.value || '90210';
    const pkg = document.getElementById('wizard-package-select')?.value || 'complete';

    const reviewVehicle = document.getElementById('summary-vehicle');
    const reviewLocation = document.getElementById('summary-location');
    const reviewPackage = document.getElementById('summary-package');

    if (reviewVehicle) reviewVehicle.textContent = `${year} ${make} ${model} (VIN: ${vin ? vin : 'Provided later'})`;
    if (reviewLocation) reviewLocation.textContent = `ZIP: ${zip} (Mobile Inspector Dispatched to Seller)`;
    if (reviewPackage) reviewPackage.textContent = `${pkg.charAt(0).toUpperCase() + pkg.slice(1)} 270-Point Diagnostic Package`;
  }

  btnNext.addEventListener('click', () => {
    if (validateStep(currentStep)) {
      if (currentStep < totalSteps) {
        showStep(currentStep + 1);
      }
    }
  });

  btnPrev.addEventListener('click', () => {
    if (currentStep > 1) {
      showStep(currentStep - 1);
    }
  });

  if (wizardForm) {
    wizardForm.addEventListener('submit', (e) => {
      e.preventDefault();
      // Show Success State
      stepContainers.forEach(c => c.classList.add('d-none'));
      document.querySelector('.wizard-steps').classList.add('d-none');
      btnPrev.style.display = 'none';
      btnSubmit.style.display = 'none';
      if (successState) {
        successState.classList.remove('d-none');
      }
    });
  }
}

/* --- 7. Inspector Application Form --- */
function initInspectorApplication() {
  const form = document.getElementById('inspector-apply-form');
  const successMsg = document.getElementById('inspector-success-msg');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      form.classList.add('d-none');
      if (successMsg) {
        successMsg.classList.remove('d-none');
      }
    });
  }
}

/* --- 8. Bootstrap Component Inits --- */
function initBootstrapComponents() {
  if (window.bootstrap && window.bootstrap.Tooltip) {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
  }
}
