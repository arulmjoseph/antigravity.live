/**
 * JP ASSOCIATES W.L.L. — Interactive Script
 * Master Prompt Conformance:
 * - Pure Vanilla JS (No GSAP, No AOS library, No jQuery)
 * - Custom IntersectionObserver reveal system
 * - Swiper.js initializations for Hero Banner, Trust Strip, and Testimonials
 * - Single Service Page: Title, Paragraph, Includes (List), and FAQ Accordion
 * - Dynamic Sticky Sidebar Menu for all 11 Services
 * - Accessible form validation and dynamic state handling
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  /* ==========================================================================
     1. Vanilla JS IntersectionObserver Reveal System
     ========================================================================== */
  const initScrollReveal = () => {
    const revealElements = document.querySelectorAll('[data-aos]');
    if (!revealElements.length) return;

    if ('IntersectionObserver' in window) {
      const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('aos-animate');
            observer.unobserve(entry.target);
          }
        });
      }, {
        root: null,
        rootMargin: '0px 0px -40px 0px',
        threshold: 0.1
      });

      revealElements.forEach(el => revealObserver.observe(el));
    } else {
      revealElements.forEach(el => el.classList.add('aos-animate'));
    }
  };

  /* ==========================================================================
     2. Sticky Header Scroll Indicator (Light Header)
     ========================================================================== */
  const initHeaderScroll = () => {
    const header = document.querySelector('.site-header-light');
    if (!header) return;

    const handleScroll = () => {
      if (window.scrollY > 20) {
        header.classList.add('site-header--scrolled');
      } else {
        header.classList.remove('site-header--scrolled');
      }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  };

  /* ==========================================================================
     3. Swiper.js Carousel Initializations
     ========================================================================== */
  const initSwiperCarousels = () => {
    // Hero Banner Swiper Carousel
    if (document.querySelector('.hero-cinematic-swiper')) {
      new Swiper('.hero-cinematic-swiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        effect: 'fade',
        fadeEffect: {
          crossFade: true
        },
        speed: 800,
        autoplay: {
          delay: 5500,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.hero-vertical-pagination',
          clickable: true,
          renderBullet: function (index, className) {
            const num = (index + 1 < 10) ? '0' + (index + 1) : (index + 1);
            return '<span class="' + className + '">' + num + '</span>';
          }
        },
      });
    }

    // Partner & Accreditation Strip Swiper
    if (document.querySelector('.partner-swiper')) {
      new Swiper('.partner-swiper', {
        slidesPerView: 2,
        spaceBetween: 24,
        loop: true,
        autoplay: {
          delay: 3500,
          disableOnInteraction: false,
        },
        breakpoints: {
          576: { slidesPerView: 3, spaceBetween: 28 },
          768: { slidesPerView: 4, spaceBetween: 36 },
          1024: { slidesPerView: 5, spaceBetween: 44 },
        },
      });
    }

    // Testimonials Swiper (2 items per screen)
    if (document.querySelector('.testimonial-swiper')) {
      new Swiper('.testimonial-swiper', {
        slidesPerView: 1,
        spaceBetween: 24,
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        navigation: {
          nextEl: '.testimonial-next',
          prevEl: '.testimonial-prev',
        },
        pagination: {
          el: '.testimonial-pagination',
          clickable: true,
        },
        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 28,
          },
          1200: {
            slidesPerView: 2,
            spaceBetween: 32,
          }
        }
      });
    }
  };

  /* ==========================================================================
     4. Service Interactive Filter System (Homepage Grid)
     ========================================================================== */
  const initServiceFilter = () => {
    const filterButtons = document.querySelectorAll('[data-filter]');
    const serviceItems = document.querySelectorAll('.service-grid-item');

    if (!filterButtons.length || !serviceItems.length) return;

    filterButtons.forEach(button => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const targetGroup = button.getAttribute('data-filter');

        // Update active class
        filterButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        // Filter cards
        serviceItems.forEach(item => {
          const itemGroup = item.getAttribute('data-group');
          if (targetGroup === 'all' || itemGroup === targetGroup) {
            item.style.display = 'block';
            setTimeout(() => {
              item.style.opacity = '1';
              item.style.transform = 'translateY(0)';
            }, 10);
          } else {
            item.style.opacity = '0';
            item.style.transform = 'translateY(10px)';
            setTimeout(() => {
              item.style.display = 'none';
            }, 150);
          }
        });
      });
    });
  };

  /* ==========================================================================
     5. Single Service Dynamic Content & Sidebar Menu System
        Structure: Title -> Paragraph -> Includes (List) -> FAQ Accordion
     ========================================================================== */
  const initServiceSidebar = () => {
    const sidebarLinks = document.querySelectorAll('.service-sidebar-link');
    const contentContainer = document.getElementById('serviceContentContainer');
    const breadcrumbItem = document.getElementById('breadcrumbActiveItem');

    if (!sidebarLinks.length || !contentContainer) return;

    // Deep Service Database for all 11 Practices
    const servicesDb = {
      'business-consulting': {
        index: '01',
        title: 'Business & Management Consulting',
        lead: 'Strategic advisory guiding market entry, corporate restructuring, financial modeling, and operational scaling for commercial entities.',
        paragraph: 'In competitive regional markets, enterprises require agile, data-backed strategic planning. JP Associates W.L.L. works alongside owners, boards, and executive leaders to conduct corporate diagnostic assessments, eliminate operational bottlenecks, and formulate scalable commercial expansion plans. We provide financial modeling, pre-feasibility analysis, organizational restructuring, and KPI management systems that transform high-level vision into auditable balance sheet performance.',
        includes: [
          'Corporate Strategic Planning & Long-Term Restructuring',
          'Commercial Feasibility Studies & Detailed Business Plans',
          'Organizational Architecture & Functional Chart Design',
          'Departmental Performance Scorecards & KPI Systems',
          'Executive Decision Packages for Board of Directors',
          'Post-Merger & Acquisition Operational Integration'
        ],
        faqs: [
          {
            q: 'How does JP Associates approach business restructuring?',
            a: 'We begin with a comprehensive operational and financial audit to identify underperforming business lines, optimize debt structures, and reallocate working capital toward high-yield activities.'
          },
          {
            q: 'What is included in a commercial feasibility study?',
            a: 'Our studies incorporate 5-year financial projections, market sensitivity analyses, CapEx/OpEx breakdowns, break-even timelines, and regulatory licensing assessments.'
          },
          {
            q: 'Can you assist with post-merger integration?',
            a: 'Yes, we synchronize accounting systems, harmonize chart of accounts, consolidate SOPs, and establish joint reporting dashboards for merged entities.'
          }
        ]
      },
      'tax-advisory': {
        index: '02',
        title: 'Tax Advisory Services',
        lead: 'Comprehensive Corporate Tax compliance, VAT filing, withholding tax assessments, and cross-border tax planning adhering to regional legislation across the GCC.',
        paragraph: 'With regional tax landscapes transitioning toward formalized Corporate Income Tax and stringent Value Added Tax enforcement, enterprises face heightened statutory obligations. JP Associates W.L.L. acts as your dedicated tax fiduciary, ensuring full compliance while optimizing allowable deductions, input tax credits, and group entity structures. Our senior tax practitioners perform transactional audits, prepare and submit statutory returns, and represent clients during official audits or reassessment reviews.',
        includes: [
          'Corporate Tax Registration, Planning & Annual Return Filings',
          'Periodic VAT Preparation, Input-Tax Optimization & Settlement',
          'Withholding Tax (WHT) Assessment on Foreign Remittances',
          'Reverse Charge Mechanism & Export VAT Recovery Governance',
          'Tax Health Checks & Historical Exposure Remediation',
          'Official Tax Authority Audit Representation & Defense'
        ],
        faqs: [
          {
            q: 'What is the mandatory threshold for VAT and Corporate Tax in Bahrain?',
            a: 'VAT registration is mandatory for businesses with taxable supplies exceeding BHD 37,500 annually. We assess your revenue lines to determine registration timelines and applicable tax rate categories.'
          },
          {
            q: 'How do you handle tax authority audits and inquiries?',
            a: 'We prepare complete reconciliations between your audited financial statements and VAT/tax returns, manage official portal correspondence, and defend input deductions.'
          },
          {
            q: 'Can you assist with cross-border GCC transactions?',
            a: 'Yes, we structure cross-border supply chains, customs clearance documentation, and double taxation treaty benefits to prevent duplicate tax assessments.'
          }
        ]
      },
      'accounting-control': {
        index: '03',
        title: 'Accounting & Control',
        lead: 'Rigorous general ledger management, monthly closing, IFRS financial statements, and internal financial controls to safeguard balance sheet integrity.',
        paragraph: 'Accurate financial statements form the bedrock of corporate solvency and stakeholder confidence. JP Associates manages your full accounting lifecycle, from transactional bookkeeping and bank reconciliations to monthly financial reporting and accrual accounting. We institute robust internal control checklists, ensuring all journal entries, depreciation schedules, and revenue recognition policies comply strictly with IFRS standards.',
        includes: [
          'IFRS-Compliant Bookkeeping & General Ledger Administration',
          'Monthly MIS Reporting & Executive Variance Analysis',
          'Bank, Credit Card & Merchant Gateway Reconciliations',
          'Fixed Asset Register (FAR) Maintenance & Depreciation Schedules',
          'Prepaid Expenses, Accruals & Provisions Management',
          'Year-End Trial Balance Substantiation & Financial Close'
        ],
        faqs: [
          {
            q: 'How often are financial MIS reports delivered to leadership?',
            a: 'We typically close monthly books within 5 to 7 business days following month-end, delivering complete P&L, Balance Sheet, and cash flow variance packs.'
          },
          {
            q: 'Do you work with cloud accounting software like Zoho or QuickBooks?',
            a: 'Yes, our team is certified in Zoho Books, QuickBooks Online, Xero, Odoo, and SAP Business One, ensuring real-time cloud data access.'
          },
          {
            q: 'How do you ensure compliance with IFRS standards?',
            a: 'All balance sheet items, leases (IFRS 16), revenue recognition (IFRS 15), and provisions (IAS 37) are audited against IFRS guidelines before finalizing accounts.'
          }
        ]
      },
      'audit-support': {
        index: '04',
        title: 'Audit Support Services',
        lead: 'Preparation of comprehensive audit working papers, lead schedules, and active liaison with external statutory auditors to ensure smooth year-end sign-offs.',
        paragraph: 'Year-end statutory audits can consume significant internal resources if working papers are incomplete. JP Associates bridges the gap between your finance department and external audit firms. We build complete Prepared-by-Client (PBC) files, reconcile complex general ledger accounts, and resolve technical accounting inquiries raised by external auditors to avoid audit qualifications and delay penalties.',
        includes: [
          'Statutory Audit Lead Schedule Generation & File Assembly',
          'Prepared-by-Client (PBC) Documentation & Evidence Gathering',
          'Technical Liaison with Big 4 & Accredited Regional Audit Firms',
          'Management Letter Point (MLP) Remediation Programs',
          'Going Concern & Subsequent Event Disclosure Preparation',
          'Prior Period Error Corrections & Retrospective Restatements'
        ],
        faqs: [
          {
            q: 'Does JP Associates act as the statutory auditor or the audit preparation partner?',
            a: 'We act as your Audit Support & Preparation partner, building the complete audit file and liaising directly with your chosen independent statutory auditor to accelerate sign-off.'
          },
          {
            q: 'How much time can our team save by using audit support services?',
            a: 'Clients typically reduce internal executive audit burden by 60-70%, as our practitioners handle all PBC requests, sampling inquiries, and schedule reconciliations.'
          },
          {
            q: 'What happens if the auditor identifies a discrepancy?',
            a: 'We investigate the root transaction, provide supporting substantiation, or draft the requisite adjusting journal entries for auditor concurrence.'
          }
        ]
      },
      'financial-strategy': {
        index: '05',
        title: 'Financial Strategy & Cash Flow Management',
        lead: 'Dynamic 13-week liquidity forecasting, capital allocation modeling, and working capital optimization to protect solvency and fuel growth.',
        paragraph: 'Profitable businesses can fail due to illiquidity. Our financial strategy practice constructs rolling 13-week direct cash flow forecast models that track incoming collections against statutory, operational, and debt service obligations. We advise management on capital allocation, loan facility covenants, debt refinancing, and dividend distribution thresholds.',
        includes: [
          '13-Week Direct Cash Flow Rolling Models & Variance Tracking',
          'Working Capital & Treasury Liquidity Strategy Optimization',
          'Banking Covenant Compliance & Credit Line Negotiations',
          'CapEx Feasibility & Return on Investment (ROI) Modeling',
          'Stress Testing & Liquidity Scenario Analysis',
          'Cost of Capital (WACC) & Debt Restructuring Advisory'
        ],
        faqs: [
          {
            q: 'Why is a 13-week cash flow model critical for businesses?',
            a: 'A 13-week horizon covers a full financial quarter, providing high-resolution visibility into payroll, vendor obligations, and tax liabilities before liquidity crunches occur.'
          },
          {
            q: 'Can you assist us with securing bank credit facilities?',
            a: 'Yes, we prepare comprehensive Information Memorandums (CIM), historical debt-service coverage ratios (DSCR), and bank proposal presentations.'
          },
          {
            q: 'How is working capital optimized?',
            a: 'We synchronize debtor collection cycles (DSO) with creditor terms (DPO) and inventory holding periods to unlock tied-up operating cash.'
          }
        ]
      },
      'receivables-payables': {
        index: '06',
        title: 'Receivables & Payables Management',
        lead: 'Structured credit control frameworks, debtor aging recovery strategies, vendor invoice approval workflows, and DSO/DPO metric enhancement.',
        paragraph: 'Efficient cash conversion requires proactive credit management and disciplined vendor scheduling. We establish formal credit underwriting rules, debtor recovery workflows, and automated aging reports. On the payables side, we implement 3-way matching controls (PO, Delivery Note, Invoice) to prevent duplicate payments and safeguard vendor goodwill.',
        includes: [
          'Accounts Receivable Aging Ledger Reconciliation & Recovery',
          'Accounts Payable 3-Way Match & Disbursement Schedules',
          'Customer Credit Scoring & Exposure Limit Policies',
          'Days Sales Outstanding (DSO) & DPO Cycle Optimization',
          'Disputed Invoice Escalation & Settlement Protocols',
          'Vendor Statement Reconciliations & Early Settlement Discounts'
        ],
        faqs: [
          {
            q: 'How do you reduce overdue debtor aging without hurting client relationships?',
            a: 'We institute polite, structured milestone reminders, formal statement reconciliation cycles, and escalation protocols that preserve commercial relationships while ensuring timely settlement.'
          },
          {
            q: 'What is a 3-way match in Accounts Payable?',
            a: 'It verifies that the Purchase Order, Goods Receipt Note (GRN), and Vendor Invoice agree on price, quantities, and terms before payment release.'
          },
          {
            q: 'How do we track supplier payment priority?',
            a: 'We build an aging-based disbursement schedule categorized by critical operational vendors, statutory payments, and discretionary accounts.'
          }
        ]
      },
      'inventory-management': {
        index: '07',
        title: 'Inventory Management & Cost of Goods Sold',
        lead: 'Stock valuation modeling (FIFO, Weighted Average), physical stock count verification, slow-moving inventory write-downs, and shrinkage controls.',
        paragraph: 'Inventory ties up vital enterprise liquidity. JP Associates provides rigorous inventory valuation frameworks conforming to IAS 2, ensuring landed costs, customs tariffs, and direct handling charges are accurately attributed to COGS. We conduct and supervise periodic physical inventory counts to identify variances, pilferage, and obsolete stock items.',
        includes: [
          'Inventory Valuation Model Setup (FIFO, AVCO under IAS 2)',
          'Physical Stocktaking Supervision & Variance Reconciliation',
          'Slow-Moving & Obsolete Inventory Write-Down Assessment',
          'Landed Cost Calculation & Import Duty Apportionment',
          'Re-Order Point (ROP) & Safety Stock Mathematical Modeling',
          'Perpetual Inventory to General Ledger Reconciliation'
        ],
        faqs: [
          {
            q: 'Which inventory valuation method is best under IFRS/IAS 2?',
            a: 'IAS 2 permits FIFO (First-In, First-Out) or Weighted Average Cost (AVCO). LIFO is strictly prohibited. We help configure the optimal method for your ERP.'
          },
          {
            q: 'How often should physical stock counts be conducted?',
            a: 'We recommend periodic cyclical counts for high-value A-class items and comprehensive annual wall-to-wall counts for statutory audit sign-off.'
          },
          {
            q: 'How are landed costs calculated for imported inventory?',
            a: 'We establish freight, insurance, customs tariff, and clearance apportionment formulas so unit costs reflect total landed cost accurately.'
          }
        ]
      },
      'operational-efficiency': {
        index: '08',
        title: 'Operational Efficiency & Cost Rationalization',
        lead: 'Business Process Re-engineering (BPR), overhead cost rationalization, procurement audit, and institutional KPI scorecards to maximize profitability.',
        paragraph: 'Unchecked operational friction and administrative overhead erode enterprise margins. We conduct comprehensive activity-based costing audits to uncover waste, duplicate tasks, and underperforming assets. We restructure business workflows to shorten delivery cycles, reduce procurement spending, and elevate workforce productivity.',
        includes: [
          'Activity-Based Costing (ABC) Audits & Margin Analysis',
          'Overhead Cost Rationalization & Waste Elimination',
          'Procurement Spend Review & Supplier Contract Renegotiation',
          'Workflow Bottleneck Eradication & Process Streamlining',
          'Departmental Operational KPI Scorecard Deployment',
          'Resource Utilization & Capacity Planning Models'
        ],
        faqs: [
          {
            q: 'What is Activity-Based Costing (ABC)?',
            a: 'ABC assigns indirect overhead costs directly to the specific activities and products consuming those resources, highlighting true product profitability.'
          },
          {
            q: 'How do you identify workflow bottlenecks?',
            a: 'We map end-to-end departmental handoffs, calculate cycle times, and pinpoint steps where documentation or approvals cause operational delays.'
          },
          {
            q: 'What savings can be expected from procurement audits?',
            a: 'Enterprises typically realize 8-15% in direct cost rationalization through volume consolidation, contract renegotiation, and elimination of maverick spending.'
          }
        ]
      },
      'software-services': {
        index: '09',
        title: 'Software Services & ERP Implementation',
        lead: 'ERP selection, cloud accounting deployment (Zoho Books, QuickBooks, Odoo, SAP Business One), data migration, and third-party API financial integration.',
        paragraph: 'Transitioning to modern digital accounting eliminates manual entry errors and provides real-time executive reporting. JP Associates guides software evaluation, chart of accounts design, and full ERP data migration. We ensure your financial software is fully configured for regional e-invoicing, multi-currency reporting, and automated bank feeds.',
        includes: [
          'ERP Requirements Scoping & Software Selection Advisory',
          'Chart of Accounts Setup & User Role Permissions Design',
          'Historical Financial Data Migration, Cleansing & Audit',
          'E-Invoicing & VAT Compliance Software Configuration',
          'Third-Party POS & Payment Gateway Financial Integrations',
          'Staff Training & Standard Operating Manuals for Software Use'
        ],
        faqs: [
          {
            q: 'Which accounting software is best for mid-sized GCC companies?',
            a: 'Zoho Books, QuickBooks Online, and Odoo ERP provide excellent cloud capabilities, VAT compliance, and affordable scalability for regional businesses.'
          },
          {
            q: 'How do you safeguard historical financial data during migration?',
            a: 'We conduct full trial balance reconciliations, customer/vendor opening balance audits, and sandbox trial migrations before executing live cutover.'
          },
          {
            q: 'Is staff training included in software deployment?',
            a: 'Yes, we provide hands-on role-based training and step-by-step software operating guides for your accounting and billing teams.'
          }
        ]
      },
      'system-design': {
        index: '10',
        title: 'System Design & Standard Operating Procedures',
        lead: 'Formal Standard Operating Procedures (SOP) documentation, Delegation of Authority (DOA) matrices, and enterprise internal control architecture.',
        paragraph: 'Sustainable enterprises run on documented systems, not ad-hoc individual habits. We engineer comprehensive Standard Operating Procedure manuals covering every key financial and operational workflow. We build clear Delegation of Authority (DOA) thresholds, establishing dual-authorization rules that safeguard corporate assets against fraud and negligence.',
        includes: [
          'Corporate Standard Operating Procedure (SOP) Manuals',
          'Delegation of Financial & Operational Authority (DOA) Matrix',
          'Segregation of Duties (SoD) Risk Matrix & Conflict Checks',
          'Petty Cash, Travel & Expense Reimbursement Policies',
          'Internal Financial Control Framework Design (COSO Aligned)',
          'Periodic Policy Compliance Audits & Revision Frameworks'
        ],
        faqs: [
          {
            q: 'What is a Delegation of Authority (DOA) matrix?',
            a: 'A DOA matrix defines spending limits, contract signature rights, and hiring approval hierarchies across executive and departmental levels.'
          },
          {
            q: 'Why are formal SOP manuals necessary?',
            a: 'SOPs ensure operational continuity during staff transitions, reduce human error, and satisfy statutory governance requirements for licensed entities.'
          },
          {
            q: 'How does Segregation of Duties (SoD) prevent fraud?',
            a: 'SoD ensures that no single individual has complete control over a transaction lifecycle (e.g. initiating, approving, and reconciling payments).'
          }
        ]
      },
      'admin-secretarial': {
        index: '11',
        title: 'Administrative & Secretarial Support',
        lead: 'Commercial Registration (CR) renewals, statutory shareholder resolutions, board minute transcription, and regulatory filings for W.L.L. entities.',
        paragraph: 'Maintaining corporate legal standing is essential for bank accounts, commercial contracts, and licensing. JP Associates provides ongoing company secretarial services for W.L.L. and foreign branch entities. We draft AGM/EGM shareholder resolutions, submit Commercial Registration amendments, and ensure compliance with Ultimate Beneficial Ownership (UBO) reporting mandates.',
        includes: [
          'Annual Commercial Registration (CR) Renewals & Amendments',
          'Shareholder Resolutions, AGM / EGM Minutes Transcription',
          'Ultimate Beneficial Owner (UBO) Registration & Declarations',
          'Commercial Address, Branch Addition & Activity Change Filings',
          'Statutory Ministry Filings & Certificate Maintenance',
          'Corporate Document Vault & Archival Administration'
        ],
        faqs: [
          {
            q: 'What are the annual corporate filing requirements for a W.L.L. in Bahrain?',
            a: 'W.L.L. entities must renew their CR annually, file audited financial statements with the Ministry (MOIC), submit UBO declarations, and lodge tax/VAT returns.'
          },
          {
            q: 'Can you assist with changing commercial activities or adding branches?',
            a: 'Yes, we draft the requisite shareholder resolutions, liaise with regulatory authorities, and update the Commercial Registration certificate.'
          },
          {
            q: 'What is the Ultimate Beneficial Owner (UBO) filing requirement?',
            a: 'UBO regulations require entities to disclose natural persons holding direct or indirect ownership of 10% or more to maintain transparency.'
          }
        ]
      }
    };

    const renderService = (key) => {
      const data = servicesDb[key];
      if (!data) return;

      // Update Breadcrumb
      if (breadcrumbItem) breadcrumbItem.textContent = data.title;

      // Generate HTML matching required structure: Title -> Paragraph -> Includes (List) -> FAQ
      contentContainer.innerHTML = `
        <!-- 1. Title -->
        <span class="eyebrow">Corporate Practice ${data.index}</span>
        <h1 class="display-5 text-navy fw-bold mb-3">${data.title}</h1>
        
        <!-- 2. Paragraph -->
        <p class="lead text-grey">${data.lead}</p>
        <p class="text-grey mb-4">${data.paragraph}</p>

        <!-- 3. Includes (List) -->
        <div class="service-deliverables-box">
          <h2 class="h4 text-navy fw-bold mb-3">
            What This Practice Includes:
          </h2>
          <div class="row g-3">
            ${data.includes.map(item => `
              <div class="col-md-6">
                <div class="d-flex align-items-start gap-2">
                  <i class="fa-solid fa-circle-check text-navy mt-1"></i>
                  <span class="small text-charcoal fw-medium">${item}</span>
                </div>
              </div>
            `).join('')}
          </div>
        </div>

        <!-- 4. FAQ Accordion -->
        <div class="service-faq-wrap">
          <h2 class="h4 text-navy fw-bold mb-3">
            Frequently Asked Questions
          </h2>
          <div class="accordion" id="serviceFaqAccordion">
            ${data.faqs.map((faq, idx) => `
              <div class="faq-accordion-item">
                <button class="faq-accordion-btn ${idx === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse${idx}" aria-expanded="${idx === 0 ? 'true' : 'false'}" aria-controls="faqCollapse${idx}">
                  <span>${faq.q}</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div id="faqCollapse${idx}" class="collapse ${idx === 0 ? 'show' : ''}" data-bs-parent="#serviceFaqAccordion">
                  <div class="faq-accordion-body">
                    ${faq.a}
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>

        <!-- Engagement Action Card -->
        <div class="mt-5 p-4 rounded bg-subtle border border-line d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div>
            <h3 class="h5 text-navy fw-bold mb-1">Commission ${data.title}</h3>
            <p class="small text-grey mb-0">Receive a tailored engagement proposal and diagnostic assessment for your organization.</p>
          </div>
          <a href="contact.html" class="btn-jp btn-jp--primary flex-shrink-0">
            <span>Inquire Now</span>
            <i class="fa-solid fa-arrow-right ms-1"></i>
          </a>
        </div>
      `;

      // Update sidebar active link
      sidebarLinks.forEach(link => {
        if (link.getAttribute('data-service-target') === key) {
          link.classList.add('active');
        } else {
          link.classList.remove('active');
        }
      });
    };

    // Listen to sidebar clicks
    sidebarLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const target = link.getAttribute('data-service-target');
        if (target && servicesDb[target]) {
          renderService(target);
          window.location.hash = target;
          // Smooth scroll to top of service content on mobile
          if (window.innerWidth < 992) {
            contentContainer.scrollIntoView({ behavior: 'smooth' });
          }
        }
      });
    });

    // Check URL Hash on Load
    const initialHash = window.location.hash.replace('#', '');
    if (initialHash && servicesDb[initialHash]) {
      renderService(initialHash);
    } else {
      // Default to Tax Advisory
      renderService('tax-advisory');
    }
  };

  /* ==========================================================================
     6. Consultation Inquiry Form Validation & State Handling
     ========================================================================== */
  const initConsultationForm = () => {
    const form = document.getElementById('consultationForm') || document.getElementById('studioContactForm');
    if (!form) return;

    const alertSuccess = document.getElementById('formAlertSuccess');
    const alertError = document.getElementById('formAlertError');
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', (e) => {
      e.preventDefault();

      let isValid = true;
      const formGroups = form.querySelectorAll('.form-jp-group');
      formGroups.forEach(group => group.classList.remove('is-invalid'));
      if (alertSuccess) alertSuccess.style.display = 'none';
      if (alertError) alertError.style.display = 'none';

      // Validate Fields
      const nameInput = document.getElementById('clientName');
      const emailInput = document.getElementById('clientEmail');
      const detailsInput = document.getElementById('projectDetails');

      if (nameInput && !nameInput.value.trim()) {
        nameInput.closest('.form-jp-group').classList.add('is-invalid');
        isValid = false;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (emailInput && (!emailInput.value.trim() || !emailRegex.test(emailInput.value.trim()))) {
        emailInput.closest('.form-jp-group').classList.add('is-invalid');
        isValid = false;
      }

      if (detailsInput && (!detailsInput.value.trim() || detailsInput.value.trim().length < 8)) {
        detailsInput.closest('.form-jp-group').classList.add('is-invalid');
        isValid = false;
      }

      if (!isValid) {
        if (alertError) alertError.style.display = 'block';
        return;
      }

      // Simulate submission loading state
      const originalHtml = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-2"></i> Transmitting Advisory Brief...';

      setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
        if (alertSuccess) {
          alertSuccess.style.display = 'block';
          form.reset();
        }
      }, 800);
    });
  };

  /* ==========================================================================
     7. Mobile Navigation Enhancements (Auto-Close Drawer on Link Click)
     ========================================================================== */
  const initMobileNavClose = () => {
    const navCollapse = document.getElementById('primaryNavigation');
    if (!navCollapse) return;

    const navLinks = navCollapse.querySelectorAll('.nav-link-jp, .btn-jp');
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992 && navCollapse.classList.contains('show')) {
          const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
          if (bsCollapse) {
            bsCollapse.hide();
          }
        }
      });
    });
  };

  // Initialize all functions
  initScrollReveal();
  initHeaderScroll();
  initSwiperCarousels();
  initServiceFilter();
  initServiceSidebar();
  initConsultationForm();
  initMobileNavClose();
});
