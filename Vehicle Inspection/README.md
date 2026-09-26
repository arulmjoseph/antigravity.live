# Discern Vehicle Inspection (`DiscernVehicleInspection.com`)

> Bespoke, high-craft pre-purchase vehicle inspection and automotive diagnostics platform built with **Bootstrap 5.3**, **Vanilla JavaScript**, **Swiper.js 11**, and **Font Awesome 6**.

---

## 🚗 Project Overview

**Discern Vehicle Inspection** is a nationwide mobile pre-purchase vehicle inspection service connecting prospective used car buyers with ASE-certified master mechanics in all 50 US states. 

The website eliminates generic AI design tropes and SaaS clichés, adopting a high-craft, precision automotive telemetry visual style (obsidian slate `#0B0F17`, international safety orange `#E65100`, warning amber `#D97706`, and pass emerald `#059669`).

---

## 📁 File Structure

```
Vehicle Inspection/
│
├── index.html                   # Complete homepage with all 13 sections & interactive components
├── sample-report.html           # Full standalone 270-point interactive sample inspection report
├── README.md                    # Documentation & setup guide
└── assets/
    ├── css/
    │   └── style.css            # Custom tokens, anti-slop geometry, AOS transitions & BEM components
    ├── js/
    │   └── script.js            # Vanilla IntersectionObserver, Swiper inits, pricing calculator, booking wizard
    └── images/                  # Automotive assets & icons
```

---

## ⚙️ Tech Stack & CDNs

| Library | Version / Source | Purpose |
|---|---|---|
| **Bootstrap 5** | `5.3.3` (jsDelivr CDN) | Responsive layout grid, utility classes, modal & accordion components |
| **Font Awesome** | `6.6.0` (cdnjs) | UI icons, diagnostics status marks, star ratings, controls |
| **Swiper.js** | `11.x` (jsDelivr CDN) | Responsive testimonials & real defect showcase carousels |
| **Google Fonts** | `Manrope`, `JetBrains Mono` | Distinct automotive typography & telemetry readouts |
| **Vanilla Scroll Reveal** | Custom `IntersectionObserver` (no external AOS dependency) | High-performance scroll animations |

---

## 🌟 Key Features & Sections

1. **Header & Sticky Nav**: Crisp brand emblem, navigation anchors, inspector recruitment link, and primary "Book an Inspection" modal trigger.
2. **Hero Section with Live Telemetry**: Split layout showcasing real-time vehicle inspection data (2021 Porsche Macan GTS), live OBD-II readiness status, paint depth meters, brake thickness, and condition index.
3. **Trust Strip**: Verified social proof (`4.9★ Average Rating`, `12,850+ Completed`, `All 50 States`, `100% ASE Certified`).
4. **The Problem / Cost Breakdown**: Direct comparison between the cost of blind car buying ($3,420+ hidden repairs) vs. proactive inspection ($179 - $289).
5. **How It Works**: 3-step interactive progression (Vehicle Details → Specialist Match → 24–48h Digital Report).
6. **Inspection Packages & Add-on Calculator**: Essential ($179), Standard ($229), and Complete Diagnostic ($289) with live interactive add-on selection (Paint Depth Meter +$49, Carfax Title Audit +$35, Rush Dispatch +$65, Classic/Exotic +$95).
7. **Interactive Sample Report Widget**: Tabbed dossier switcher (Executive Summary, OBD-II Mode $06 Scans, 8-Panel Paint Depth Gauges, 270-Point Component Checklist, and Defect Photo Walkaround).
8. **Why Discern**: 6 core buyer advantages highlighting our 100% unbiased business model (zero repair kickbacks).
9. **Recent Defects Showcase**: Swiper carousel with real catches (Frame clamp marks, cleared CEL codes, salt subframe corrosion).
10. **Service Areas Directory**: Real-time searchable and filterable directory covering all 50 states and top metro areas.
11. **Verified Testimonials**: Multi-slide customer review carousel with specific car models and verified buyer savings.
12. **FAQ Accordion**: In-depth answers regarding seller coordination, cleared CEL detection, cancellation guarantees, and EV/classic coverage.
13. **Technician Recruitment Section**: Dedicated ASE-certified mechanic application portal and form.
14. **Multi-Step Booking Wizard**: 3-step interactive booking modal with vehicle information, location/seller details, dynamic package summary, and validation.
15. **Dedicated Sample Report Page (`sample-report.html`)**: Complete standalone vehicle dossier with printable view, telemetry readouts, audio debrief simulation, and negotiation credit calculations.

---

## 🚀 How to Run / Preview

Open `index.html` or `sample-report.html` in any modern web browser or start a local web server:

```bash
# Using Python
cd "Vehicle Inspection"
python3 -m http.server 8080

# Using Node / npx
npx serve .
```
Navigate to `http://localhost:8080` in your browser.
