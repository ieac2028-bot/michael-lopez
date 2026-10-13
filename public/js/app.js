// ---- Mobile nav drawer ----
const mobileNav = document.getElementById('mobileNav');
const mnBackdrop = document.getElementById('mnBackdrop');
const hamburgerBtn = document.getElementById('hamburgerBtn');
const mnClose = document.getElementById('mnClose');

function openMobileNav() {
  mobileNav.classList.add('open');
  mnBackdrop.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeMobileNav() {
  mobileNav.classList.remove('open');
  mnBackdrop.classList.remove('open');
  document.body.style.overflow = '';
}
if (hamburgerBtn) {
  hamburgerBtn.addEventListener('click', openMobileNav);
  mnClose.addEventListener('click', closeMobileNav);
  mnBackdrop.addEventListener('click', closeMobileNav);
}

// Open the linked practice area and bring its full description into view.
function revealPracticeAreaHash() {
  const target = window.location.hash && document.querySelector(window.location.hash);
  if (!target || !target.matches('.area-card')) return;
  target.open = true;
  target.scrollIntoView({
    block: 'start',
    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
  });
}
revealPracticeAreaHash();
window.addEventListener('hashchange', revealPracticeAreaHash);

// ---- User-selected color theme ----
const themeRoot = document.documentElement;
const themeButtons = document.querySelectorAll('[data-theme-toggle]');
const systemDarkQuery = window.matchMedia('(prefers-color-scheme: dark)');
let hasSavedTheme = false;
try {
  hasSavedTheme = localStorage.getItem('abogadokonek-theme') === 'light'
    || localStorage.getItem('abogadokonek-theme') === 'dark';
} catch (_) {}

function currentTheme() {
  return themeRoot.dataset.theme || (systemDarkQuery.matches ? 'dark' : 'light');
}

function syncThemeButtons() {
  const activeTheme = currentTheme();
  const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
  document.querySelectorAll('[data-theme-logo]').forEach((logo) => {
    const themeLogo = activeTheme === 'light' ? logo.dataset.logoLight : logo.dataset.logoDark;
    if (themeLogo && logo.getAttribute('src') !== themeLogo) logo.src = themeLogo;
  });
  themeButtons.forEach((button) => {
    const icon = button.querySelector('.theme-toggle__icon');
    if (icon) icon.dataset.activeTheme = activeTheme;
    button.setAttribute('aria-pressed', String(activeTheme === 'dark'));
    button.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
    const label = button.querySelector('.theme-toggle__label');
    if (label) label.textContent = `${nextTheme === 'dark' ? 'Dark' : 'Light'} mode`;
  });
}

themeButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const nextTheme = currentTheme() === 'dark' ? 'light' : 'dark';
    const applyTheme = () => {
      themeRoot.dataset.theme = nextTheme;
      try {
        localStorage.setItem('abogadokonek-theme', nextTheme);
        hasSavedTheme = true;
      } catch (_) {}
      syncThemeButtons();
    };

    if (document.startViewTransition) {
      const bounds = button.getBoundingClientRect();
      themeRoot.style.setProperty('--theme-origin-x', `${bounds.left + bounds.width / 2}px`);
      themeRoot.style.setProperty('--theme-origin-y', `${bounds.top + bounds.height / 2}px`);
      try {
        document.startViewTransition(applyTheme);
      } catch (_) {
        applyTheme();
      }
    } else {
      applyTheme();
    }
  });
});
const handleSystemThemeChange = () => {
  if (!hasSavedTheme) syncThemeButtons();
};
if (systemDarkQuery.addEventListener) {
  systemDarkQuery.addEventListener('change', handleSystemThemeChange);
} else {
  systemDarkQuery.addListener(handleSystemThemeChange);
}
syncThemeButtons();

// ---- Project history timeline reveal ----
document.querySelectorAll('.timeline').forEach((timeline) => {
  const entries = [...timeline.querySelectorAll('.timeline-item')];
  const reducedMotion = document.documentElement.dataset.a11yMotion === 'true'
    || window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!entries.length || reducedMotion || !('IntersectionObserver' in window)) return;

  timeline.classList.add('timeline--animated');
  let highestHistoryStep = 0;
  entries.forEach((entry, index) => entry.style.setProperty('--history-step', index));
  const observer = new IntersectionObserver((observations) => {
    observations.forEach((observation) => {
      if (!observation.isIntersecting) return;
      const index = entries.indexOf(observation.target);
      observation.target.classList.add('is-visible');
      highestHistoryStep = Math.max(highestHistoryStep, index + 1);
      timeline.style.setProperty('--history-progress', `${(highestHistoryStep / entries.length) * 100}%`);
      observer.unobserve(observation.target);
    });
  }, { threshold: 0.22, rootMargin: '0px 0px -8% 0px' });
  entries.forEach((entry) => observer.observe(entry));
});

// ---- Homepage four-step impact reveal ----
document.querySelectorAll('.stat-row').forEach((row) => {
  const cards = [...row.querySelectorAll('.stat')];
  const reducedMotion = document.documentElement.dataset.a11yMotion === 'true'
    || window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!cards.length || reducedMotion || !('IntersectionObserver' in window)) return;

  row.classList.add('stat-row--animated');
  cards.forEach((card, index) => card.style.setProperty('--stat-step', index));
  const observer = new IntersectionObserver((entries) => {
    if (entries.some((entry) => entry.isIntersecting)) {
      row.classList.add('is-visible');
      observer.disconnect();
    }
  }, { threshold: 0.3, rootMargin: '0px 0px -6% 0px' });
  observer.observe(row);
});

// ---- Persistent accessibility options ----
(function () {
  const root = document.documentElement;
  const trigger = document.querySelector('[data-a11y-trigger]');
  const panel = document.querySelector('[data-a11y-panel]');
  if (!trigger || !panel) return;
  const defaults = { text: 'default', contrast: false, motion: false, links: false, spacing: false, font: false };
  let prefs = { ...defaults };
  try { prefs = { ...defaults, ...JSON.parse(localStorage.getItem('abogadokonek-a11y') || '{}') }; } catch (_) {}
  const apply = () => {
    root.dataset.a11yText = prefs.text;
    ['contrast', 'motion', 'links', 'spacing', 'font'].forEach((key) => {
      if (prefs[key]) root.dataset[`a11y${key[0].toUpperCase()}${key.slice(1)}`] = 'true';
      else delete root.dataset[`a11y${key[0].toUpperCase()}${key.slice(1)}`];
    });
    panel.querySelectorAll('[data-a11y-size]').forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.a11ySize === prefs.text)));
    panel.querySelectorAll('[data-a11y-toggle]').forEach((button) => button.setAttribute('aria-pressed', String(Boolean(prefs[button.dataset.a11yToggle]))));
    try { localStorage.setItem('abogadokonek-a11y', JSON.stringify(prefs)); } catch (_) {}
  };
  const close = () => { panel.hidden = true; trigger.setAttribute('aria-expanded', 'false'); trigger.focus(); };
  trigger.addEventListener('click', () => {
    panel.hidden = !panel.hidden;
    trigger.setAttribute('aria-expanded', String(!panel.hidden));
    if (!panel.hidden) panel.querySelector('button')?.focus();
  });
  panel.querySelector('[data-a11y-close]').addEventListener('click', close);
  panel.querySelectorAll('[data-a11y-size]').forEach((button) => button.addEventListener('click', () => { prefs.text = button.dataset.a11ySize; apply(); }));
  panel.querySelectorAll('[data-a11y-toggle]').forEach((button) => button.addEventListener('click', () => { const key = button.dataset.a11yToggle; prefs[key] = !prefs[key]; apply(); }));
  panel.querySelector('[data-a11y-reset]').addEventListener('click', () => { prefs = { ...defaults }; apply(); });
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !panel.hidden) close(); });
  document.addEventListener('pointerdown', (event) => { if (!panel.hidden && !event.target.closest('[data-accessibility]')) { panel.hidden = true; trigger.setAttribute('aria-expanded', 'false'); } });
  apply();
})();

// ---- Pointer-following ring and trailing line artwork ----
const pointerOrbit = document.querySelector('[data-pointer-orbit]');
if (pointerOrbit && !themeRoot.hasAttribute('data-a11y-motion') && window.matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)').matches) {
  const cursor = pointerOrbit.querySelector('.pointer-orbit__cursor');
  const ambientLinePaths = [...document.querySelectorAll('.ambient-lines path')];
  let targetX = window.innerWidth / 2;
  let targetY = window.innerHeight / 2;
  let trailX = targetX;
  let trailY = targetY;
  let frame = 0;

  const updateViewBox = () => pointerOrbit.setAttribute('viewBox', `0 0 ${window.innerWidth} ${window.innerHeight}`);
  updateViewBox();
  window.addEventListener('resize', updateViewBox);

  function animateTrail() {
    trailX += (targetX - trailX) * 0.14;
    trailY += (targetY - trailY) * 0.14;
    cursor.setAttribute('transform', `translate(${targetX} ${targetY})`);
    if (Math.abs(targetX - trailX) > 0.2 || Math.abs(targetY - trailY) > 0.2) {
      frame = requestAnimationFrame(animateTrail);
    } else {
      frame = 0;
    }
  }

  window.addEventListener('pointermove', (event) => {
    if (event.pointerType === 'touch') return;
    targetX = event.clientX;
    targetY = event.clientY;
    const pointerX = (event.clientX / window.innerWidth - 0.5) * 2;
    const pointerY = (event.clientY / window.innerHeight - 0.5) * 2;
    ambientLinePaths.forEach((path, index) => {
      const depth = (index % 2 === 0 ? 1 : -1) * (0.45 + index * 0.16);
      path.style.setProperty('--line-pointer-x', `${-pointerX * 38 * depth}px`);
      path.style.setProperty('--line-pointer-y', `${-pointerY * 28 * depth}px`);
    });
    pointerOrbit.classList.add('is-visible');
    if (!frame) frame = requestAnimationFrame(animateTrail);
  });
  window.addEventListener('blur', () => pointerOrbit.classList.remove('is-visible'));
  document.addEventListener('mouseout', (event) => {
    if (!event.relatedTarget) pointerOrbit.classList.remove('is-visible');
  });
}

// ---- Back to top ----
const backToTop = document.getElementById('backToTop');
if (backToTop) {
  window.addEventListener('scroll', () => {
    backToTop.classList.toggle('show', window.scrollY > 320);
  });
}

// Let the hero's soft justice-line motif follow a mouse without moving the copy.
document.querySelectorAll('[data-pointer-art]').forEach((art) => {
  const card = art.closest('.hero-card');
  if (!card || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

  card.addEventListener('pointermove', (event) => {
    const bounds = card.getBoundingClientRect();
    const x = ((event.clientX - bounds.left) / bounds.width) * 100;
    const y = ((event.clientY - bounds.top) / bounds.height) * 100;
    card.style.setProperty('--pointer-x', `${x}%`);
    card.style.setProperty('--pointer-y', `${y}%`);
    card.style.setProperty('--pointer-visible', '1');
  });

  card.addEventListener('pointerleave', () => {
    card.style.setProperty('--pointer-visible', '.42');
  });
});

// Magnify the homepage team image around the pointer on mouse/trackpad devices.
document.querySelectorAll('[data-pointer-zoom]').forEach((frame) => {
  const image = frame.querySelector('.firm-profile__image');
  if (!image || !window.matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)').matches) return;

  frame.addEventListener('pointermove', (event) => {
    const bounds = frame.getBoundingClientRect();
    const x = Math.max(0, Math.min(100, ((event.clientX - bounds.left) / bounds.width) * 100));
    const y = Math.max(0, Math.min(100, ((event.clientY - bounds.top) / bounds.height) * 100));
    frame.style.setProperty('--zoom-x', `${x}%`);
    frame.style.setProperty('--zoom-y', `${y}%`);
    frame.classList.add('is-zooming');
  });

  frame.addEventListener('pointerleave', () => frame.classList.remove('is-zooming'));
});

// ---- Abogadokonek FAQ assistant ----
(function () {
  const chatPanel = document.getElementById('chatPanel');
  const launcher = document.getElementById('launcher');
  const closeChat = document.getElementById('closeChat');
  const chatBody = document.getElementById('chatBody');
  const chatInput = document.getElementById('chatInput');
  const sendBtn = document.getElementById('sendBtn');
  const quickReplies = document.getElementById('quickReplies');
  if (!chatPanel) return;

  const CHAT_ENDPOINT = chatPanel.dataset.endpoint;
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

  launcher.addEventListener('click', () => {
    launcher.classList.add('has-opened');
    chatPanel.classList.add('open');
    launcher.setAttribute('aria-expanded', 'true');
    if (chatBody.children.length === 0) startConversation();
  });
  closeChat.addEventListener('click', () => {
    chatPanel.classList.remove('open');
    launcher.setAttribute('aria-expanded', 'false');
    launcher.focus();
  });

  function addMsg(text, who) {
    const div = document.createElement('div');
    div.className = 'msg ' + who;
    div.textContent = text;
    chatBody.appendChild(div);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function addCta(cta) {
    const div = document.createElement('div');
    div.className = 'msg bot';
    const link = document.createElement('a');
    link.className = 'cta-inline';
    link.href = cta.route;
    link.textContent = cta.label;
    div.appendChild(link);
    chatBody.appendChild(div);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function setQuickReplies(options) {
    quickReplies.innerHTML = '';
    (options || []).forEach((opt) => {
      const b = document.createElement('button');
      b.className = 'qr-btn';
      b.textContent = opt;
      b.onclick = () => handleInput(opt);
      quickReplies.appendChild(b);
    });
  }

  function startConversation() {
    addMsg(
      "Hi, I'm the Abogadokonek assistant. I can answer basic questions about this project prototype and guide you through its sample consultation flow. I can't provide legal advice.",
      'bot'
    );
    setQuickReplies(['Help me find a practice area', 'How does matching work?', 'Can I find free legal aid?']);
  }

  async function handleInput(raw) {
    const text = (raw || '').trim();
    if (!text) return;
    addMsg(text, 'user');
    chatInput.value = '';
    setQuickReplies([]);

    try {
      const res = await fetch(CHAT_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({ message: text }),
      });
      const data = await res.json();

      addMsg(data.reply, 'bot');
      if (data.cta) {
        addCta(data.cta);
      }
      setQuickReplies(data.quickReplies);
    } catch (e) {
      addMsg("Sorry, I'm having trouble connecting right now. Please use the Contact page directly.", 'bot');
    }
  }

  sendBtn.addEventListener('click', () => handleInput(chatInput.value));
  chatInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') handleInput(chatInput.value);
  });
})();
