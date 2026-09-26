/**
 * Ajith | Mortgage Adviser
 * Interactive Scripts & Mortgage Affordability Calculator
 */

// Global Calculator State
let currentAffordIntent = 'live';

function parseNumber(val) {
  if (!val) return 0;
  const clean = val.toString().replace(/[^0-9.]/g, '');
  const num = parseFloat(clean);
  return isNaN(num) ? 0 : num;
}

function formatNumber(num) {
  return Math.round(num).toLocaleString('en-GB');
}

function setAffordIntent(intent) {
  currentAffordIntent = intent;
  const btnLive = document.getElementById('btnLiveIn');
  const btnRent = document.getElementById('btnRentOut');
  const boxRent = document.getElementById('boxRentRequired');

  if (intent === 'live') {
    if (btnLive) btnLive.classList.add('active');
    if (btnRent) btnRent.classList.remove('active');
    if (boxRent) boxRent.classList.add('d-none');
  } else {
    if (btnLive) btnLive.classList.remove('active');
    if (btnRent) btnRent.classList.add('active');
    if (boxRent) boxRent.classList.remove('d-none');
  }
  calculateAffordability();
}

function calculateAffordability() {
  const depositInput = document.getElementById('inputDeposit');
  const incomeInput = document.getElementById('inputIncome');

  if (!depositInput || !incomeInput) return;

  const deposit = parseNumber(depositInput.value);
  const income = parseNumber(incomeInput.value);

  const dispMaxProp = document.getElementById('dispMaxPropertyValue');
  const dispMonthly = document.getElementById('dispMonthlyCost');
  const dispRent = document.getElementById('dispRentRequired');

  if (currentAffordIntent === 'live') {
    // UK Residential standard: 4.5x annual income
    const maxBorrowing = income * 4.5;
    const maxProperty = deposit + maxBorrowing;

    // Monthly repayment on borrowing (4.5% interest, 25 years = 300 months)
    const annualRate = 0.045;
    const monthlyRate = annualRate / 12;
    const months = 300;
    
    let monthlyCost = 0;
    if (maxBorrowing > 0) {
      monthlyCost = (maxBorrowing * monthlyRate * Math.pow(1 + monthlyRate, months)) / 
                    (Math.pow(1 + monthlyRate, months) - 1);
    }

    if (dispMaxProp) dispMaxProp.textContent = '£' + formatNumber(maxProperty);
    if (dispMonthly) dispMonthly.textContent = '£' + formatNumber(monthlyCost);

  } else {
    // Buy to Let: Minimum 25% deposit (75% LTV)
    let maxProperty = 0;
    let maxBorrowing = 0;
    if (deposit > 0) {
      maxProperty = deposit / 0.25;
      maxBorrowing = maxProperty - deposit;
    }

    // Monthly repayment/interest cost on BTL borrowing (~5.5% interest)
    const annualRate = 0.055;
    const monthlyRate = annualRate / 12;
    const months = 300;
    
    let monthlyCost = 0;
    if (maxBorrowing > 0) {
      monthlyCost = (maxBorrowing * monthlyRate * Math.pow(1 + monthlyRate, months)) / 
                    (Math.pow(1 + monthlyRate, months) - 1);
    }

    // UK BTL ICR (Interest Cover Ratio) stress test: 125% to 145% of 5.5% stress rate
    const monthlyInterestBase = (maxBorrowing * 0.055) / 12;
    const rentRequiredMin = monthlyInterestBase * 1.25;
    const rentRequiredMax = monthlyInterestBase * 1.45;

    if (dispMaxProp) dispMaxProp.textContent = '£' + formatNumber(maxProperty);
    if (dispMonthly) dispMonthly.textContent = '£' + formatNumber(monthlyCost);
    if (dispRent) {
      dispRent.textContent = `£${formatNumber(rentRequiredMin)} - £${formatNumber(rentRequiredMax)}`;
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // 1. Dynamic Year
  const yearElem = document.getElementById('currentYear');
  if (yearElem) {
    yearElem.textContent = new Date().getFullYear();
  }

  // 2. Navbar scroll glassmorphism effect
  const navbar = document.querySelector('.rental-navbar-style');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      navbar?.classList.add('is-sticky');
    } else {
      navbar?.classList.remove('is-sticky');
    }
  });

  // 3. Setup Calculator Input Handlers
  const depositInput = document.getElementById('inputDeposit');
  const incomeInput = document.getElementById('inputIncome');

  function setupInput(inputElem) {
    if (!inputElem) return;
    inputElem.addEventListener('input', (e) => {
      const cursorPosition = e.target.selectionStart;
      const originalLength = e.target.value.length;
      const num = parseNumber(e.target.value);
      
      if (!isNaN(num) && num >= 0) {
        e.target.value = formatNumber(num);
      }
      calculateAffordability();
    });
  }

  setupInput(depositInput);
  setupInput(incomeInput);
  calculateAffordability();

  // 4. Contact Form Handling
  const contactForm = document.getElementById('mortgageConsultationForm');
  const formSuccessAlert = document.getElementById('formSuccessAlert');

  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const name = document.getElementById('clientName')?.value || '';
      const phone = document.getElementById('clientPhone')?.value || '';
      const email = document.getElementById('clientEmail')?.value || '';
      const mortgageType = document.getElementById('clientMortgageType')?.value || '';
      const message = document.getElementById('clientMessage')?.value || '';

      // Visual feedback
      const submitBtn = contactForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
        submitBtn.disabled = true;

        setTimeout(() => {
          submitBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Sent Successfully';
          submitBtn.classList.add('btn-success');

          if (formSuccessAlert) {
            formSuccessAlert.classList.remove('d-none');
          }

          // WhatsApp direct prompt option
          const encodedMsg = encodeURIComponent(`Hi Ajith, I have requested a mortgage consultation.\nName: ${name}\nPhone: ${phone}\nType: ${mortgageType}\nDetails: ${message}`);
          const waLink = `https://wa.me/447378950650?text=${encodedMsg}`;
          
          const waCta = document.getElementById('waRedirectBtn');
          if (waCta) {
            waCta.href = waLink;
            waCta.classList.remove('d-none');
          }

          contactForm.reset();
        }, 800);
      }
    });
  }

  // 5. Initialize Services Swiper Carousel
  if (typeof Swiper !== 'undefined' && document.querySelector('.services-swiper')) {
    new Swiper('.services-swiper', {
      slidesPerView: 1.15,
      spaceBetween: 16,
      loop: true,
      autoplay: {
        delay: 4500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      navigation: {
        nextEl: '.swiper-btn-next',
        prevEl: '.swiper-btn-prev',
      },
      breakpoints: {
        576: {
          slidesPerView: 2,
          spaceBetween: 18,
        },
        768: {
          slidesPerView: 2.8,
          spaceBetween: 20,
        },
        992: {
          slidesPerView: 3.5,
          spaceBetween: 22,
        },
        1200: {
          slidesPerView: 4,
          spaceBetween: 24,
        }
      }
    });
  }

  // 6. Mobile Navbar auto-close on item click
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
  const navCollapse = document.getElementById('navbarMain');
  if (navCollapse) {
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992 && navCollapse.classList.contains('show')) {
          const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
          if (bsCollapse) bsCollapse.hide();
        }
      });
    });
  }
});
