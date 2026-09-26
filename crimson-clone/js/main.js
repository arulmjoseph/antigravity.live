/**
 * Crimson Education - Main Interactive Scripts
 * Handles: Admissions Calculators, Modal, Mobile Nav, Accordions, Form Submissions
 */

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initConsultationModal();
  initAdmissionsCalculator();
  initTargetGoalCalculator();
  initFaqAccordions();
  initFormHandlers();
});

/* ==========================================================================
   1. Mobile Navigation Menu
   ========================================================================== */
function initMobileMenu() {
  const menuBtn = document.getElementById('mobile-menu-btn');
  const menuContainer = document.getElementById('mobile-menu');

  if (!menuBtn || !menuContainer) return;

  menuBtn.addEventListener('click', () => {
    const isExpanded = menuBtn.getAttribute('aria-expanded') === 'true';
    menuBtn.setAttribute('aria-expanded', !isExpanded);
    menuContainer.classList.toggle('hidden');
    menuContainer.classList.toggle('block');
  });

  // Close mobile menu on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !menuContainer.classList.contains('hidden')) {
      menuBtn.setAttribute('aria-expanded', 'false');
      menuContainer.classList.add('hidden');
      menuContainer.classList.remove('block');
      menuBtn.focus();
    }
  });
}

/* ==========================================================================
   2. Consultation Modal
   ========================================================================== */
function initConsultationModal() {
  const modal = document.getElementById('consultation-modal');
  const openBtns = document.querySelectorAll('.js-open-modal');
  const closeBtns = document.querySelectorAll('.js-close-modal');

  if (!modal) return;

  const openModal = () => {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    
    // Focus first input
    const firstInput = modal.querySelector('input, button, select');
    if (firstInput) firstInput.focus();
  };

  const closeModal = () => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  openBtns.forEach(btn => btn.addEventListener('click', (e) => {
    e.preventDefault();
    openModal();
  }));

  closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      closeModal();
    }
  });
}

/* ==========================================================================
   3. Admissions Chance Estimator Widget (Home Page)
   ========================================================================== */
const UNIVERSITY_DATA = {
  harvard: { name: 'Harvard University', nationalRate: 3.4, crimsonRate: 15.2, avgSat: 1540, multiplier: 4.5 },
  stanford: { name: 'Stanford University', nationalRate: 3.6, crimsonRate: 16.1, avgSat: 1530, multiplier: 4.4 },
  mit: { name: 'MIT', nationalRate: 3.9, crimsonRate: 18.5, avgSat: 1560, multiplier: 4.7 },
  yale: { name: 'Yale University', nationalRate: 4.3, crimsonRate: 19.8, avgSat: 1535, multiplier: 4.6 },
  columbia: { name: 'Columbia University', nationalRate: 3.8, crimsonRate: 17.4, avgSat: 1520, multiplier: 4.5 },
  oxford: { name: 'Oxford University', nationalRate: 14.1, crimsonRate: 32.5, avgSat: 1490, multiplier: 2.3 },
  cambridge: { name: 'Cambridge University', nationalRate: 15.7, crimsonRate: 35.0, avgSat: 1500, multiplier: 2.2 },
  princeton: { name: 'Princeton University', nationalRate: 4.4, crimsonRate: 20.1, avgSat: 1540, multiplier: 4.5 }
};

function initAdmissionsCalculator() {
  const form = document.getElementById('admissions-calc-form');
  if (!form) return;

  const uniSelect = document.getElementById('calc-university');
  const gpaInput = document.getElementById('calc-gpa');
  const satInput = document.getElementById('calc-sat');
  const gpaVal = document.getElementById('calc-gpa-val');
  const satVal = document.getElementById('calc-sat-val');

  const natRateEl = document.getElementById('res-national-rate');
  const crimRateEl = document.getElementById('res-crimson-rate');
  const multiplierEl = document.getElementById('res-multiplier');
  const progressBarEl = document.getElementById('res-progress-bar');
  const uniNameEl = document.getElementById('res-uni-name');
  const adviceEl = document.getElementById('res-advice');

  // Update slider output labels
  if (gpaInput && gpaVal) {
    gpaInput.addEventListener('input', (e) => gpaVal.textContent = parseFloat(e.target.value).toFixed(2));
  }
  if (satInput && satVal) {
    satInput.addEventListener('input', (e) => satVal.textContent = e.target.value);
  }

  function calculateOdds() {
    const uniKey = uniSelect.value;
    const gpa = parseFloat(gpaInput.value);
    const sat = parseInt(satInput.value, 10);
    const uni = UNIVERSITY_DATA[uniKey] || UNIVERSITY_DATA.harvard;

    // Academic score multiplier based on GPA & SAT relative to target standard
    let academicFactor = 1.0;

    if (sat >= uni.avgSat) academicFactor += 0.25;
    else if (sat < uni.avgSat - 100) academicFactor -= 0.3;

    if (gpa >= 3.9) academicFactor += 0.2;
    else if (gpa < 3.5) academicFactor -= 0.3;

    // Calculate baseline student chance
    let baseRate = (uni.nationalRate * academicFactor);
    if (baseRate < 1.0) baseRate = 1.0;
    if (baseRate > 15.0) baseRate = 15.0;

    let crimsonOdds = (uni.crimsonRate * academicFactor);
    if (crimsonOdds < 5.0) crimsonOdds = 5.0;
    if (crimsonOdds > 48.0) crimsonOdds = 48.0;

    const multiplier = (crimsonOdds / baseRate).toFixed(1);

    // Update DOM
    if (uniNameEl) uniNameEl.textContent = uni.name;
    if (natRateEl) natRateEl.textContent = `${baseRate.toFixed(1)}%`;
    if (crimRateEl) crimRateEl.textContent = `${crimsonOdds.toFixed(1)}%`;
    if (multiplierEl) multiplierEl.textContent = `${multiplier}x Higher Odds`;
    if (progressBarEl) {
      setTimeout(() => {
        progressBarEl.style.width = `${Math.min(crimsonOdds * 1.8, 100)}%`;
      }, 50);
    }

    if (adviceEl) {
      if (sat < uni.avgSat) {
        adviceEl.innerHTML = `<strong>Key Strategy:</strong> Increasing your SAT by 50+ points and building 2 high-impact leadership projects will push your admission odds into the top bracket.`;
      } else {
        adviceEl.innerHTML = `<strong>Key Strategy:</strong> Strong academic baseline! Focus now on authentic passion projects, essay positioning, and early action strategy.`;
      }
    }
  }

  if (form) {
    form.addEventListener('input', calculateOdds);
    form.addEventListener('change', calculateOdds);
    calculateOdds(); // initial run
  }
}

/* ==========================================================================
   4. Target SAT/ACT & GPA Goal Calculator (US Inner Page)
   ========================================================================== */
function initTargetGoalCalculator() {
  const schoolSelect = document.getElementById('target-school-select');
  if (!schoolSelect) return;

  const targetSatEl = document.getElementById('target-sat-res');
  const targetActEl = document.getElementById('target-act-res');
  const targetGpaEl = document.getElementById('target-gpa-res');
  const targetApEl = document.getElementById('target-ap-res');
  const schoolNameTitle = document.getElementById('target-school-title');

  const TARGET_REQUIREMENTS = {
    ivy: { name: 'Ivy League Tier (Harvard, Yale, Princeton)', sat: '1540 - 1580', act: '34 - 36', gpa: '3.90+ Unweighted', ap: '8 - 12 AP/IB Honors' },
    top15: { name: 'Top 15 Tier (Stanford, MIT, Duke, Chicago)', sat: '1520 - 1570', act: '34 - 35', gpa: '3.88+ Unweighted', ap: '7 - 10 AP/IB Honors' },
    top30: { name: 'Top 30 Tier (NYU, Georgetown, UCLA, Michigan)', sat: '1450 - 1530', act: '32 - 34', gpa: '3.80+ Unweighted', ap: '5 - 8 AP/IB Honors' },
    top50: { name: 'Top 50 Tier (Boston Univ, UIUC, Wisconsin)', sat: '1380 - 1470', act: '30 - 32', gpa: '3.65+ Unweighted', ap: '4 - 6 AP/IB Honors' }
  };

  function updateTargetGoals() {
    const key = schoolSelect.value;
    const data = TARGET_REQUIREMENTS[key] || TARGET_REQUIREMENTS.ivy;

    if (schoolNameTitle) schoolNameTitle.textContent = data.name;
    if (targetSatEl) targetSatEl.textContent = data.sat;
    if (targetActEl) targetActEl.textContent = data.act;
    if (targetGpaEl) targetGpaEl.textContent = data.gpa;
    if (targetApEl) targetApEl.textContent = data.ap;
  }

  schoolSelect.addEventListener('change', updateTargetGoals);
  updateTargetGoals();
}

/* ==========================================================================
   5. FAQ Accordions (US Inner Page)
   ========================================================================== */
function initFaqAccordions() {
  const faqButtons = document.querySelectorAll('.js-faq-btn');

  faqButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      const targetId = btn.getAttribute('aria-controls');
      const contentEl = document.getElementById(targetId);
      const icon = btn.querySelector('.js-faq-icon');

      btn.setAttribute('aria-expanded', !expanded);

      if (contentEl) {
        if (expanded) {
          contentEl.classList.add('hidden');
        } else {
          contentEl.classList.remove('hidden');
        }
      }

      if (icon) {
        icon.classList.toggle('rotate-180');
      }
    });
  });
}

/* ==========================================================================
   6. General Form Handlers (Consultation & Lead Capture)
   ========================================================================== */
function initFormHandlers() {
  const forms = document.querySelectorAll('.js-lead-form');

  forms.forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.innerHTML : 'Submit';

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg> Submitting...`;
      }

      setTimeout(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }

        showToast('Thank you! Your free admissions consultation request has been received. An advisor will contact you within 24 hours.');
        form.reset();

        // Close modal if form was inside modal
        const modal = document.getElementById('consultation-modal');
        if (modal && !modal.classList.contains('hidden')) {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          document.body.style.overflow = '';
        }
      }, 1200);
    });
  });
}

/* Helper: Accessible Toast Notification */
function showToast(message) {
  let toast = document.getElementById('global-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'global-toast';
    toast.className = 'fixed bottom-6 right-6 z-50 max-w-md bg-navy-900 text-white p-4 rounded-xl shadow-2xl border border-crimson-600/30 transition-all transform duration-300 opacity-0 translate-y-4';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);
  }

  toast.innerHTML = `
    <div class="flex items-start space-x-3">
      <div class="flex-shrink-0 text-emerald-400 mt-0.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <div class="text-sm font-medium text-slate-100">${message}</div>
    </div>
  `;

  setTimeout(() => {
    toast.classList.remove('opacity-0', 'translate-y-4');
    toast.classList.add('opacity-100', 'translate-y-0');
  }, 10);

  setTimeout(() => {
    toast.classList.remove('opacity-100', 'translate-y-0');
    toast.classList.add('opacity-0', 'translate-y-4');
  }, 4500);
}
