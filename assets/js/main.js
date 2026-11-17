// Joule Machine Hub - Core Client Logic for Direct Calling & Modals

let currentSimTimer = null;
let currentSimSeconds = 0;

// Open Direct Call Modal - Redirects immediately to Inquiry Form Fill
function openCallModal(machine) {
  openInquiryModal(machine);
}

function closeCallModal() {
  const modal = document.getElementById('callModal');
  if (modal) modal.classList.add('hidden');
}

// Simulated In-browser Call Connect
function startSimulatedCall() {
  const screen = document.getElementById('callSimScreen');
  const status = document.getElementById('simCallStatus');
  const timer = document.getElementById('simCallTimer');
  
  screen.classList.remove('hidden');
  status.innerText = "Connecting to Seller's Mobile...";
  timer.innerText = "Ringing...";
  currentSimSeconds = 0;

  if (currentSimTimer) clearInterval(currentSimTimer);

  setTimeout(function() {
    status.innerText = "Call Connected • Speaking with Owner";
    currentSimTimer = setInterval(function() {
      currentSimSeconds++;
      const mins = Math.floor(currentSimSeconds / 60).toString().padStart(2, '0');
      const secs = (currentSimSeconds % 60).toString().padStart(2, '0');
      timer.innerText = mins + ":" + secs;
    }, 1000);
  }, 2500);
}

function endSimulatedCall() {
  if (currentSimTimer) {
    clearInterval(currentSimTimer);
    currentSimTimer = null;
  }
  const screen = document.getElementById('callSimScreen');
  if (screen) screen.classList.add('hidden');
}

// Open Inquiry / Get Best Price Modal
function openInquiryModal(machine) {
  if (typeof machine === 'string') {
    if (window.JMH_MACHINES && window.JMH_MACHINES[machine]) {
      machine = window.JMH_MACHINES[machine];
    } else {
      try {
        machine = JSON.parse(machine);
      } catch (e) {
        if (window.JMH_MACHINES && window.JMH_MACHINES[machine]) {
          machine = window.JMH_MACHINES[machine];
        }
      }
    }
  }
  if (!machine) {
    machine = {
      title: "Direct Plant Machinery Inquiry",
      price_display: "Factory Direct Asking Price",
      seller: { name: "Technical Procurement Desk" },
      images: ["assets/images/machines/1/used-dingli-jcpt1412hd-scissor-lift-01.jpg"],
      id: "GENERAL"
    };
  }

  document.getElementById('inquiryModalTitle').innerText = machine.title;
  document.getElementById('inquiryModalPrice').innerText = machine.price_display;
  document.getElementById('inquiryModalSeller').innerText = machine.seller.name;
  document.getElementById('inquiryModalImg').src = machine.images[0];

  // Set hidden inputs for machine context
  const inqTitle = document.getElementById('inqMachineTitle');
  const inqId = document.getElementById('inqMachineId');
  const inqPrice = document.getElementById('inqMachinePrice');
  const inqSeller = document.getElementById('inqSellerName');
  if (inqTitle) inqTitle.value = machine.title || '';
  if (inqId) inqId.value = machine.id || '';
  if (inqPrice) inqPrice.value = machine.price_display || '';
  if (inqSeller) inqSeller.value = (machine.seller && machine.seller.name) || '';

  document.getElementById('inquiryForm').classList.remove('hidden');
  document.getElementById('inquirySuccess').classList.add('hidden');

  document.getElementById('inquiryModal').classList.remove('hidden');
}

function closeInquiryModal() {
  document.getElementById('inquiryModal').classList.add('hidden');
}

function submitInquiry(e) {
  e.preventDefault();
  const form = document.getElementById('inquiryForm');
  const btn = document.getElementById('inqSubmitBtn');
  const origHtml = btn ? btn.innerHTML : '';

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Sending Inquiry...</span>';
  }

  const formData = new FormData(form);
  const machineTitle = formData.get('machine_title') || '';

  fetch('/api/submit-lead.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const ref = data && data.lead_ref ? data.lead_ref : '';
    window.location.href = 'thank-you.php?type=inquiry&ref=' + encodeURIComponent(ref) + '&machine=' + encodeURIComponent(machineTitle);
  })
  .catch(err => {
    console.warn('Submission network fallback:', err);
    window.location.href = 'thank-you.php?type=inquiry&machine=' + encodeURIComponent(machineTitle);
  });
}

// Post Machine Modal
function openPostModal() {
  document.getElementById('postForm').classList.remove('hidden');
  document.getElementById('postSuccess').classList.add('hidden');
  document.getElementById('postModal').classList.remove('hidden');
}

function closePostModal() {
  document.getElementById('postModal').classList.add('hidden');
}

function submitPostMachine(e) {
  e.preventDefault();
  const form = document.getElementById('postForm');
  const btn = document.getElementById('postSubmitBtn');
  const origHtml = btn ? btn.innerHTML : '';

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Publishing Machine...</span>';
  }

  const formData = new FormData(form);
  const machineName = formData.get('machine_name') || '';

  fetch('/api/submit-lead.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const ref = data && data.lead_ref ? data.lead_ref : '';
    window.location.href = 'thank-you.php?type=post_machine&ref=' + encodeURIComponent(ref) + '&machine=' + encodeURIComponent(machineName);
  })
  .catch(err => {
    console.warn('Submission network fallback:', err);
    window.location.href = 'thank-you.php?type=post_machine&machine=' + encodeURIComponent(machineName);
  });
}

// Post Requirement Modal
function openRequirementModal() {
  document.getElementById('requirementForm').classList.remove('hidden');
  document.getElementById('requirementSuccess').classList.add('hidden');
  document.getElementById('requirementModal').classList.remove('hidden');
}

function closeRequirementModal() {
  document.getElementById('requirementModal').classList.add('hidden');
}

function submitRequirement(e) {
  e.preventDefault();
  const form = document.getElementById('requirementForm');
  const btn = document.getElementById('reqSubmitBtn');
  const origHtml = btn ? btn.innerHTML : '';

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Broadcasting RFQ...</span>';
  }

  const formData = new FormData(form);
  const reqMachine = formData.get('required_machine') || '';

  fetch('/api/submit-lead.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const ref = data && data.lead_ref ? data.lead_ref : '';
    window.location.href = 'thank-you.php?type=requirement&ref=' + encodeURIComponent(ref) + '&machine=' + encodeURIComponent(reqMachine);
  })
  .catch(err => {
    console.warn('Submission network fallback:', err);
    window.location.href = 'thank-you.php?type=requirement&machine=' + encodeURIComponent(reqMachine);
  });
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeCallModal();
    closeInquiryModal();
    closePostModal();
    closeRequirementModal();
  }
});

// Mobile Header Navigation Drawer Toggle
function toggleMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const icon = document.getElementById('mobileMenuIcon');
  if (drawer) {
    drawer.classList.toggle('hidden');
    if (icon) {
      if (drawer.classList.contains('hidden')) {
        icon.className = 'fa-solid fa-bars';
      } else {
        icon.className = 'fa-solid fa-xmark';
      }
    }
  }
}

// Mobile Catalog Filter Sidebar Toggle
function toggleMobileFilters() {
  const sidebar = document.getElementById('filterSidebar');
  const icon = document.getElementById('filterToggleIcon');
  if (sidebar) {
    sidebar.classList.toggle('hidden');
    if (icon) {
      if (sidebar.classList.contains('hidden')) {
        icon.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
      } else {
        icon.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
      }
    }
  }
}

// Simple FAQ Accordion Toggle
function toggleFaq(id) {
  const ans = document.getElementById('faq-ans-' + id);
  const icon = document.getElementById('faq-icon-' + id);
  
  if (ans) {
    const isHidden = ans.classList.contains('hidden');
    
    // Close other FAQs for clean single-view accordion experience
    for (let i = 1; i <= 10; i++) {
      const otherAns = document.getElementById('faq-ans-' + i);
      const otherIcon = document.getElementById('faq-icon-' + i);
      if (otherAns && i !== id) {
        otherAns.classList.add('hidden');
      }
      if (otherIcon && i !== id) {
        otherIcon.classList.remove('rotate-180');
      }
    }

    if (isHidden) {
      ans.classList.remove('hidden');
      if (icon) icon.classList.add('rotate-180');
    } else {
      ans.classList.add('hidden');
      if (icon) icon.classList.remove('rotate-180');
    }
  }
}

// Toggle View All Machines on Homepage (Show 10 vs All 20)
function toggleViewAllMachines() {
  const extraCards = document.querySelectorAll('.machine-card-extra');
  const btnText = document.getElementById('viewAllText');
  const btnArrow = document.getElementById('viewAllArrow');
  if (!extraCards.length) return;

  const isHidden = extraCards[0].classList.contains('hidden');

  extraCards.forEach(card => {
    if (isHidden) {
      card.classList.remove('hidden');
    } else {
      card.classList.add('hidden');
    }
  });

  if (isHidden) {
    if (btnText) btnText.innerText = "Show Less (Show 10 Machines Only)";
    if (btnArrow) {
      btnArrow.classList.remove('fa-chevron-down');
      btnArrow.classList.add('fa-chevron-up');
    }
  } else {
    const totalCount = extraCards.length + 10;
    if (btnText) btnText.innerText = "View All Machines (" + totalCount + " Available - Load More)";
    if (btnArrow) {
      btnArrow.classList.remove('fa-chevron-up');
      btnArrow.classList.add('fa-chevron-down');
    }
    const catalogEl = document.getElementById('catalog');
    if (catalogEl) {
      catalogEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
}

// Switch between Global Country Hubs and India City Hubs
function filterHubs(type) {
  const globalSec = document.getElementById('globalHubsSection');
  const indiaSec = document.getElementById('indiaHubsSection');
  const tabAll = document.getElementById('hubTabAll');
  const tabGlobal = document.getElementById('hubTabGlobal');
  const tabIndia = document.getElementById('hubTabIndia');

  [tabAll, tabGlobal, tabIndia].forEach(tab => {
    if (tab) {
      tab.classList.remove('bg-joule-blue', 'text-joule-yellow', 'shadow-md', 'font-black');
      tab.classList.add('bg-white', 'text-slate-700', 'font-bold');
    }
  });

  if (type === 'global') {
    if (globalSec) globalSec.classList.remove('hidden');
    if (indiaSec) indiaSec.classList.add('hidden');
    if (tabGlobal) {
      tabGlobal.classList.add('bg-joule-blue', 'text-joule-yellow', 'shadow-md', 'font-black');
      tabGlobal.classList.remove('bg-white', 'text-slate-700', 'font-bold');
    }
  } else if (type === 'india') {
    if (globalSec) globalSec.classList.add('hidden');
    if (indiaSec) indiaSec.classList.remove('hidden');
    if (tabIndia) {
      tabIndia.classList.add('bg-joule-blue', 'text-joule-yellow', 'shadow-md', 'font-black');
      tabIndia.classList.remove('bg-white', 'text-slate-700', 'font-bold');
    }
  } else {
    if (globalSec) globalSec.classList.remove('hidden');
    if (indiaSec) indiaSec.classList.remove('hidden');
    if (tabAll) {
      tabAll.classList.add('bg-joule-blue', 'text-joule-yellow', 'shadow-md', 'font-black');
      tabAll.classList.remove('bg-white', 'text-slate-700', 'font-bold');
    }
  }
}

// Mobile Bottom-Sheet Filter Drawer
function openMobileFilterDrawer() {
  const modal = document.getElementById('mobileFilterDrawerModal');
  if (modal) {
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
}

function closeMobileFilterDrawer() {
  const modal = document.getElementById('mobileFilterDrawerModal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }
}

// ==========================================
// 1. MULTI-CURRENCY CONVERTER (INR / USD / EUR)
// ==========================================
// ==========================================
// 1. MULTI-CURRENCY CONVERTER & LOCATION AUTO-DETECTION
// ==========================================
const CURRENCY_RATES = {
  INR: { rate: 1, symbol: '₹', flag: '🇮🇳', label: 'INR (₹)', prefix: '₹' },
  USD: { rate: 84.5, symbol: '$', flag: '🇺🇸', label: 'USD ($)', prefix: '$' },
  EUR: { rate: 91.2, symbol: '€', flag: '🇪🇺', label: 'EUR (€)', prefix: '€' }
};

const EUR_COUNTRY_CODES = [
  'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'CH', 'IE', 'PT', 
  'GR', 'FI', 'SE', 'NO', 'DK', 'PL', 'CZ', 'RO', 'HU', 'SK',
  'EE', 'LV', 'LT', 'SI', 'BG', 'HR', 'CY', 'LU', 'MT'
];

function getCookie(name) {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
}

function getActiveCurrency() {
  const saved = localStorage.getItem('jmh_currency') || getCookie('jmh_currency');
  if (saved && CURRENCY_RATES[saved]) return saved;

  // 1. Read Server-Side Geo-Detection attribute
  const serverDetected = document.documentElement.getAttribute('data-detected-currency');
  if (serverDetected && CURRENCY_RATES[serverDetected]) return serverDetected;

  // 2. Client-side Timezone / Location heuristics
  try {
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    if (tz === 'Asia/Kolkata' || tz === 'Asia/Calcutta' || tz.includes('India')) {
      return 'INR';
    }
    if (tz.startsWith('Europe/') || tz === 'Atlantic/Reykjavik') {
      return 'EUR';
    }
  } catch (e) {}

  return 'USD';
}

function formatInrPrice(inr) {
  if (inr >= 10000000) {
    return '₹' + (inr / 10000000).toFixed(2) + ' Crore';
  } else if (inr >= 100000) {
    return '₹' + (inr / 100000).toFixed(2) + ' Lakhs';
  } else {
    return '₹' + inr.toLocaleString('en-IN');
  }
}

function togglePagesDropdown(e) {
  if (e) e.stopPropagation();
  const menu = document.getElementById('pagesDropdownMenu');
  if (menu) menu.classList.toggle('hidden');
}

function closePagesDropdown() {
  const menu = document.getElementById('pagesDropdownMenu');
  if (menu) menu.classList.add('hidden');
}

function toggleCurrencyDropdown(e) {
  if (e) e.stopPropagation();
  const menu = document.getElementById('currencyDropdownMenu');
  if (menu) menu.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
  const wrap = document.getElementById('currencySwitcherWrap');
  if (wrap && !wrap.contains(e.target)) {
    const menu = document.getElementById('currencyDropdownMenu');
    if (menu) menu.classList.add('hidden');
  }

  const pagesWrap = document.getElementById('pagesDropdownWrap');
  if (pagesWrap && !pagesWrap.contains(e.target)) {
    const pagesMenu = document.getElementById('pagesDropdownMenu');
    if (pagesMenu) pagesMenu.classList.add('hidden');
  }
});

function setCurrency(curr, silent = false) {
  if (!CURRENCY_RATES[curr]) curr = 'INR';
  try {
    localStorage.setItem('jmh_currency', curr);
    document.cookie = "jmh_currency=" + encodeURIComponent(curr) + "; path=/; max-age=31536000; SameSite=Lax";
  } catch (e) {}

  const cfg = CURRENCY_RATES[curr];
  const codeEl = document.getElementById('currentCurrencyCode');
  const flagEl = document.getElementById('currentCurrencyFlag');
  const mobLabel = document.getElementById('mobileActiveCurrencyLabel');

  if (codeEl) codeEl.innerHTML = `${curr}<span class="hidden sm:inline"> (${cfg.symbol})</span>`;
  if (flagEl) flagEl.innerText = cfg.flag;
  if (mobLabel) mobLabel.innerText = cfg.label;

  const menu = document.getElementById('currencyDropdownMenu');
  if (menu) menu.classList.add('hidden');

  // Update all machine price elements on page
  document.querySelectorAll('.machine-price-display').forEach(el => {
    const inr = parseFloat(el.getAttribute('data-price-inr'));
    const usd = parseFloat(el.getAttribute('data-price-usd'));

    if (!isNaN(inr) && inr > 0) {
      if (!silent) {
        el.classList.add('price-update-flash');
        setTimeout(() => el.classList.remove('price-update-flash'), 400);
      }

      if (curr === 'INR') {
        el.innerText = formatInrPrice(inr);
      } else if (curr === 'USD') {
        const usdVal = !isNaN(usd) && usd > 0 ? usd : Math.round(inr / CURRENCY_RATES.USD.rate);
        el.innerText = '$' + usdVal.toLocaleString('en-US') + ' USD';
      } else if (curr === 'EUR') {
        const baseUsd = !isNaN(usd) && usd > 0 ? usd : (inr / CURRENCY_RATES.USD.rate);
        const eurVal = Math.round(baseUsd * 0.92);
        el.innerText = '€' + eurVal.toLocaleString('de-DE') + ' EUR';
      }
    }
  });

  if (!silent) {
    showToast(`Currency changed to ${cfg.label}`);
  }
}

// Auto-detect location & set appropriate currency
function initLocationCurrency() {
  const saved = localStorage.getItem('jmh_currency') || getCookie('jmh_currency');
  if (saved) {
    setCurrency(saved, true);
    return;
  }

  // Detect via ultra-fast CORS IP endpoint if available
  fetch('https://api.country.is/')
    .then(res => res.json())
    .then(data => {
      if (data && data.country) {
        const c = data.country.toUpperCase();
        let targetCurr = 'USD';
        if (c === 'IN') {
          targetCurr = 'INR';
        } else if (EUR_COUNTRY_CODES.includes(c)) {
          targetCurr = 'EUR';
        } else {
          targetCurr = 'USD';
        }
        setCurrency(targetCurr, true);
      }
    })
    .catch(() => {
      // Fallback to timezone heuristics
      setCurrency(getActiveCurrency(), true);
    });
}

document.addEventListener('DOMContentLoaded', function() {
  initLocationCurrency();
});

// ==========================================
// 2. TOAST NOTIFICATION SYSTEM
// ==========================================
function showToast(msg, icon = 'fa-circle-check') {
  let toast = document.getElementById('jmhToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'jmhToast';
    toast.className = 'fixed top-5 right-5 z-50 transform -translate-y-16 opacity-0 transition-all duration-300 pointer-events-none bg-slate-900 text-white border-2 border-joule-yellow px-4 py-2.5 rounded-2xl shadow-2xl flex items-center gap-2.5 text-xs font-bold';
    document.body.appendChild(toast);
  }

  toast.innerHTML = `<i class="fa-solid ${icon} text-joule-yellow text-sm"></i> <span>${msg}</span>`;
  toast.classList.remove('-translate-y-16', 'opacity-0', 'pointer-events-none');
  toast.classList.add('translate-y-0', 'opacity-100');

  setTimeout(() => {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('-translate-y-16', 'opacity-0', 'pointer-events-none');
  }, 2500);
}

// Share Machine Link
function shareMachine(title, url) {
  if (navigator.share) {
    navigator.share({ title: title, url: url || window.location.href }).catch(() => {});
  } else {
    navigator.clipboard.writeText(url || window.location.href);
    showToast('Listing link copied to clipboard!', 'fa-link');
  }
}

// ==========================================
// 3. ANIMATED METRIC COUNTERS
// ==========================================
function initMetricCounters() {
  const counters = document.querySelectorAll('.counter-metric');
  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseFloat(el.getAttribute('data-target') || '0');
        const isDecimal = target % 1 !== 0;
        const duration = 1200;
        const startTime = performance.now();

        function updateCounter(currentTime) {
          const progress = Math.min((currentTime - startTime) / duration, 1);
          const currentVal = target * (1 - Math.pow(1 - progress, 3));
          el.innerText = isDecimal ? currentVal.toFixed(1) : Math.floor(currentVal);

          if (progress < 1) {
            requestAnimationFrame(updateCounter);
          } else {
            el.innerText = isDecimal ? target.toFixed(1) : target;
          }
        }

        requestAnimationFrame(updateCounter);
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.3 });

  counters.forEach(c => observer.observe(c));
}

// ==========================================
// 4. MACHINE COMPARISON SYSTEM (Up to 3 machines)
// ==========================================
let comparedMachines = [];

function toggleCompareMachine(machine) {
  if (typeof machine === 'string') {
    if (window.JMH_MACHINES && window.JMH_MACHINES[machine]) {
      machine = window.JMH_MACHINES[machine];
    } else {
      try { machine = JSON.parse(machine); } catch(e) {
        if (window.JMH_MACHINES && window.JMH_MACHINES[machine]) {
          machine = window.JMH_MACHINES[machine];
        }
      }
    }
  }
  if (!machine) return;

  const idx = comparedMachines.findIndex(m => m.id === machine.id);
  if (idx > -1) {
    comparedMachines.splice(idx, 1);
    showToast(`Removed from comparison`, 'fa-trash-can');
  } else {
    if (comparedMachines.length >= 3) {
      showToast('Maximum 3 machines can be compared at once', 'fa-triangle-exclamation');
      return;
    }
    comparedMachines.push(machine);
    showToast(`Added to comparison (${comparedMachines.length}/3)`, 'fa-code-compare');
  }

  updateCompareUI();
}

function updateCompareUI() {
  const dock = document.getElementById('compareFloatingBar');
  const countBadge = document.getElementById('compareCountBadge');
  
  if (dock && countBadge) {
    countBadge.innerText = comparedMachines.length;
    if (comparedMachines.length > 0) {
      dock.classList.remove('hidden');
    } else {
      dock.classList.add('hidden');
    }
  }

  // Update button states on cards
  document.querySelectorAll('[data-compare-btn-id]').forEach(btn => {
    const id = btn.getAttribute('data-compare-btn-id');
    const isSelected = comparedMachines.some(m => m.id === id);
    if (isSelected) {
      btn.classList.add('bg-joule-blue', 'text-joule-yellow', 'border-joule-blue');
      btn.classList.remove('text-slate-600', 'bg-white');
      btn.innerHTML = `<i class="fa-solid fa-check text-joule-yellow"></i> <span>Compared</span>`;
    } else {
      btn.classList.remove('bg-joule-blue', 'text-joule-yellow', 'border-joule-blue');
      btn.classList.add('text-slate-600', 'bg-white');
      btn.innerHTML = `<i class="fa-solid fa-code-compare text-blue-500"></i> <span>Compare</span>`;
    }
  });
}

function clearComparison() {
  comparedMachines = [];
  updateCompareUI();
  closeComparisonModal();
  showToast('Comparison cleared');
}

function openComparisonModal() {
  if (comparedMachines.length === 0) {
    showToast('Select at least 1 machine to compare');
    return;
  }

  const modal = document.getElementById('comparisonModal');
  const container = document.getElementById('comparisonTableBody');
  if (!modal || !container) return;

  const curr = getActiveCurrency();

  let html = `<div class="grid grid-cols-1 md:grid-cols-${comparedMachines.length} gap-4">`;
  comparedMachines.forEach(m => {
    let displayPrice = m.price_display;
    if (curr === 'USD') displayPrice = '$' + Math.round(m.price / CURRENCY_RATES.USD.rate).toLocaleString('en-US') + ' USD';
    if (curr === 'EUR') displayPrice = '€' + Math.round(m.price / CURRENCY_RATES.EUR.rate).toLocaleString('de-DE') + ' EUR';

    html += `
      <div class="bg-white rounded-2xl border-2 border-slate-200 p-4 space-y-3 flex flex-col justify-between">
        <div class="space-y-2">
          <div class="relative aspect-video rounded-xl overflow-hidden bg-slate-900">
            <img src="${m.images[0]}" alt="${m.title}" class="w-full h-full object-cover">
            <span class="absolute top-2 left-2 bg-joule-blue text-white text-[9px] font-black px-2 py-0.5 rounded">${m.category}</span>
          </div>
          <div class="text-[10px] font-black uppercase text-blue-600">${m.brand}</div>
          <h4 class="font-black text-slate-950 text-sm leading-snug line-clamp-2">${m.title}</h4>
          <div class="text-lg font-black text-slate-950">${displayPrice}</div>
          
          <div class="space-y-1.5 text-xs border-t border-slate-100 pt-2 text-slate-700">
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Mfg Year</span><span class="font-bold">${m.year}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Hours Run</span><span class="font-bold">${m.hours_run || 'N/A'}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Location</span><span class="font-bold truncate max-w-[140px]">${m.city}, ${m.country || 'Global'}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-slate-400">Condition</span><span class="font-bold text-emerald-600 truncate max-w-[140px]">${m.condition}</span></div>
          </div>
        </div>

        <div class="pt-2 border-t border-slate-100 space-y-2">
          <button type="button" onclick="closeQuickViewModal(); openInquiryModal();" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
            <i class="fa-solid fa-file-signature"></i> Enquire Now (Fill Form)
          </button>
          <button type="button" onclick="toggleCompareMachine('${m.id}')" class="w-full text-center text-xs text-rose-600 font-bold hover:underline py-1">
            Remove
          </button>
        </div>
      </div>
    `;
  });
  html += `</div>`;

  container.innerHTML = html;
  modal.classList.remove('hidden');
}

function closeComparisonModal() {
  const modal = document.getElementById('comparisonModal');
  if (modal) modal.classList.add('hidden');
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
  const savedCurrency = getActiveCurrency();
  if (savedCurrency && savedCurrency !== 'INR') {
    setCurrency(savedCurrency);
  }
  initMetricCounters();
});
