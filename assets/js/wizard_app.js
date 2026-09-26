/**
 * Antigravity Web Studio - Step-by-Step App Controller
 */

document.addEventListener("DOMContentLoaded", () => {
  let currentStep = 1;
  const totalSteps = 6;

  // App State Data
  const state = {
    siteTitle: "Antigravity Live",
    niche: "Creative Tech & Healthcare",
    goals: "Brand Showcase & Client Conversions",
    style: "Minimalist Modern",
    convertUrl: "",
    convertData: null,
    selectedFeatures: ["hero", "contact", "team", "gallery", "badges"],
    aiPlan: null,
    wpInstalled: false,
    seedsExecuted: false
  };

  // Elements
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const progressLine = document.getElementById("progressLine");

  // Step Navigation Handler
  function goToStep(step) {
    if (step < 1 || step > totalSteps) return;

    // Hide active step
    document.querySelectorAll(".wizard-step").forEach((el) => {
      el.classList.remove("active");
    });

    // Show target step
    const targetStepEl = document.getElementById(`step-${step}`);
    if (targetStepEl) {
      targetStepEl.classList.add("active");
    }

    currentStep = step;
    updatePillsAndProgress();
    updateButtons();

    // Trigger step specific actions
    if (currentStep === 4 && !state.aiPlan) {
      generateAIPlan();
    } else if (currentStep === 5 && !state.wpInstalled) {
      runWordPressInstallation();
    }
  }

  function updatePillsAndProgress() {
    for (let i = 1; i <= totalSteps; i++) {
      const pill = document.getElementById(`pill-${i}`);
      if (!pill) continue;
      pill.classList.remove("active", "completed");
      if (i === currentStep) {
        pill.classList.add("active");
      } else if (i < currentStep) {
        pill.classList.add("completed");
      }
    }

    if (progressLine) {
      const percent = ((currentStep - 1) / (totalSteps - 1)) * 100;
      progressLine.style.width = `${percent}%`;
    }
  }

  function updateButtons() {
    if (prevBtn) {
      prevBtn.style.visibility = currentStep === 1 ? "hidden" : "visible";
    }
    if (nextBtn) {
      if (currentStep === totalSteps) {
        nextBtn.style.display = "none";
      } else {
        nextBtn.style.display = "inline-flex";
        if (currentStep === 3) {
          nextBtn.textContent = "Generate AI Blueprint →";
        } else if (currentStep === 4) {
          nextBtn.textContent = "Deploy WordPress Site →";
        } else {
          nextBtn.textContent = "Continue →";
        }
      }
    }
  }

  // Event Listeners for Nav
  if (prevBtn) {
    prevBtn.addEventListener("click", () => goToStep(currentStep - 1));
  }
  if (nextBtn) {
    nextBtn.addEventListener("click", () => {
      saveCurrentStepData();
      goToStep(currentStep + 1);
    });
  }

  // Feature Toggles
  document.querySelectorAll(".feature-option").forEach((card) => {
    card.addEventListener("click", () => {
      const val = card.dataset.feature;
      card.classList.toggle("selected");
      if (card.classList.contains("selected")) {
        if (!state.selectedFeatures.includes(val)) state.selectedFeatures.push(val);
      } else {
        state.selectedFeatures = state.selectedFeatures.filter((f) => f !== val);
      }
    });
  });

  // Save state from inputs
  function saveCurrentStepData() {
    const titleInput = document.getElementById("siteTitleInput");
    if (titleInput) state.siteTitle = titleInput.value || state.siteTitle;

    const nicheInput = document.getElementById("siteNicheInput");
    if (nicheInput) state.niche = nicheInput.value || state.niche;

    const urlInput = document.getElementById("convertUrlInput");
    if (urlInput) state.convertUrl = urlInput.value.trim();
  }

  // Step 2: Website Converter Action
  const runConvertBtn = document.getElementById("runConvertBtn");
  if (runConvertBtn) {
    runConvertBtn.addEventListener("click", async () => {
      saveCurrentStepData();
      const convertStatus = document.getElementById("convertStatus");
      if (!state.convertUrl) {
        if (convertStatus) convertStatus.innerHTML = "<span class='text-amber-400'>Please enter a valid website URL to convert.</span>";
        return;
      }

      if (convertStatus) convertStatus.innerHTML = "<span class='text-blue-400 animate-pulse'>Fetching & converting website content...</span>";

      try {
        const response = await fetch("api/convert_website.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ url: state.convertUrl })
        });
        const data = await response.json();
        if (data.success) {
          state.convertData = data.extracted;
          if (convertStatus) {
            convertStatus.innerHTML = `<span class='text-green-400'>✓ Extracted structure for "${data.extracted.title || state.convertUrl}". ${data.extracted.pageCount} assets found!</span>`;
          }
        } else {
          if (convertStatus) convertStatus.innerHTML = `<span class='text-red-400'>Error: ${data.message}</span>`;
        }
      } catch (err) {
        if (convertStatus) convertStatus.innerHTML = `<span class='text-red-400'>Extraction failed: ${err.message}</span>`;
      }
    });
  }

  // Step 4: AI Plan Generation
  async function generateAIPlan() {
    const planOutput = document.getElementById("aiPlanOutput");
    if (planOutput) {
      planOutput.innerHTML = `<div class="p-6 text-center text-blue-400 animate-pulse">
        <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Gemini Antigravity AI Engine is generating site blueprint...
      </div>`;
    }

    try {
      const response = await fetch("api/generate_plan.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(state)
      });
      const data = await response.json();
      if (data.success) {
        state.aiPlan = data.blueprint;
        renderAIPlan(data.blueprint);
      } else {
        if (planOutput) planOutput.innerHTML = `<div class="p-4 text-red-400">Failed to generate AI plan: ${data.message}</div>`;
      }
    } catch (err) {
      if (planOutput) planOutput.innerHTML = `<div class="p-4 text-red-400">Error: ${err.message}</div>`;
    }
  }

  function renderAIPlan(bp) {
    const planOutput = document.getElementById("aiPlanOutput");
    if (!planOutput) return;

    let html = `
      <div class="space-y-4 text-sm">
        <div class="p-4 bg-white/5 rounded-xl border border-white/10">
          <div class="font-semibold text-blue-400 mb-1">Site Architecture & Sitemap</div>
          <div class="text-gray-300">${bp.summary}</div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div class="p-3 bg-white/5 rounded-lg border border-white/10">
            <span class="text-xs text-gray-400 uppercase tracking-wider">Primary Theme</span>
            <div class="font-medium text-white mt-1">${bp.themeName}</div>
          </div>
          <div class="p-3 bg-white/5 rounded-lg border border-white/10">
            <span class="text-xs text-gray-400 uppercase tracking-wider">Pages Blueprint</span>
            <div class="font-medium text-white mt-1">${bp.pages.join(", ")}</div>
          </div>
        </div>
        <div class="p-4 bg-white/5 rounded-xl border border-white/10">
          <div class="font-semibold text-green-400 mb-2">Automated Seed Modules Ready</div>
          <ul class="list-disc list-inside text-gray-300 space-y-1">
            ${bp.seedModules.map((m) => `<li>${m}</li>`).join("")}
          </ul>
        </div>
      </div>
    `;
    planOutput.innerHTML = html;
  }

  // Step 5: WordPress Installation & Seeding Execution
  async function runWordPressInstallation() {
    const logBox = document.getElementById("installConsoleLog");
    const statusText = document.getElementById("installStatusText");

    function log(msg) {
      if (logBox) {
        logBox.innerHTML += `<div>[${new Date().toLocaleTimeString()}] ${msg}</div>`;
        logBox.scrollTop = logBox.scrollHeight;
      }
    }

    log("Starting Antigravity WordPress Installation Sequence...");

    try {
      log("1. Verifying Database & Environment...");
      const installRes = await fetch("api/install_wordpress.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(state)
      });
      const installData = await installRes.json();
      log(installData.message);

      if (installData.success) {
        state.wpInstalled = true;
        log("2. Running Database Seeding Scripts...");

        const seedRes = await fetch("api/run_seeds.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" }
        });
        const seedData = await seedRes.json();
        log(seedData.message);
        if (seedData.logs) {
          seedData.logs.forEach((l) => log(`   → ${l}`));
        }
        state.seedsExecuted = true;

        if (statusText) statusText.innerHTML = "<span class='text-green-400 font-semibold'>✓ Installation & Seeding Complete!</span>";

        // Auto move to dashboard step after 1.5 seconds
        setTimeout(() => goToStep(6), 1500);
      } else {
        if (statusText) statusText.innerHTML = "<span class='text-red-400 font-semibold'>Installation halted. See log above.</span>";
      }
    } catch (err) {
      log(`Error: ${err.message}`);
    }
  }

  // Initial step setup
  goToStep(1);
});
