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

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (document.startViewTransition && !prefersReducedMotion) {
      const bounds = button.getBoundingClientRect();
      themeRoot.style.setProperty('--theme-origin-x', `${bounds.left + bounds.width / 2}px`);
      themeRoot.style.setProperty('--theme-origin-y', `${bounds.top + bounds.height / 2}px`);
      const clearTransitionOrigin = () => {
        themeRoot.style.removeProperty('--theme-origin-x');
        themeRoot.style.removeProperty('--theme-origin-y');
      };
      try {
        const transition = document.startViewTransition(applyTheme);
        transition.finished.then(clearTransitionOrigin, clearTransitionOrigin);
      } catch (_) {
        applyTheme();
        clearTransitionOrigin();
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

// Translate non-English live chat messages and keep client/staff threads current.
const liveChatTranslationCache = new Map();
function isLikelyNonEnglish(text) {
  if (/[^\p{Script=Latin}\p{M}\p{N}\p{P}\p{Z}\p{S}]/u.test(text)) return true;
  if (/[\uAC00-\uD7AF\u1100-\u11FF]/.test(text)) return true;
  if (/[\u3040-\u30FF\u4E00-\u9FFF]/.test(text)) return true;
  if (/[¿¡áéíóúñü]/i.test(text) || /\b(hola|gracias|abogado|consulta|familia|trabajo|donde|como)\b/i.test(text)) return true;
  return /\b(ang|mga|ako|ko|po|opo|hindi|salamat|paano|ano|saan|kailangan|pamilya|trabaho|batas|konsultasyon)\b/i.test(text);
}

async function translateLiveChatMessage(item, body, thread) {
  const messageId = item.dataset.messageId || body;
  if (!thread.dataset.translateUrl || !isLikelyNonEnglish(body)) return;

  const translation = document.createElement('small');
  translation.className = 'consultation-chat__translation';
  translation.setAttribute('aria-live', 'polite');
  item.appendChild(translation);

  if (liveChatTranslationCache.has(messageId)) {
    translation.textContent = liveChatTranslationCache.get(messageId);
    return;
  }

  translation.textContent = 'Translating to English…';
  try {
    const response = await fetch(thread.dataset.translateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ message: body }),
    });
    const result = await response.json();
    if (!response.ok || typeof result.translation !== 'string' || !result.translation.trim()) {
      throw new Error(result.message || 'English translation is unavailable.');
    }
    const translatedText = 'English translation: ' + result.translation;
    liveChatTranslationCache.set(messageId, translatedText);
    translation.textContent = translatedText;
  } catch (_) {
    const fallback = 'English translation is unavailable right now. The original message is shown above.';
    liveChatTranslationCache.set(messageId, fallback);
    translation.textContent = fallback;
  }
}

document.querySelectorAll('[data-message-thread][data-poll-url]').forEach((thread) => {
  thread.querySelectorAll('.consultation-chat__message').forEach((item) => {
    const body = item.querySelector('p')?.textContent || '';
    translateLiveChatMessage(item, body, thread);
  });
  const poll = async () => {
    try {
      const response = await fetch(thread.dataset.pollUrl, { headers: { Accept: 'application/json' } });
      if (!response.ok) return;
      const payload = await response.json();
      if (payload.status && thread.dataset.status && payload.status !== thread.dataset.status) {
        window.location.reload();
        return;
      }
      if (String(payload.attorney_id || '') !== (thread.dataset.assignedAttorney || '')) {
        window.location.reload();
        return;
      }
      const messages = Array.isArray(payload) ? payload : payload.messages;
      const latestMessageId = String(messages.at(-1)?.id || '');
      if (latestMessageId === String(thread.dataset.latestMessageId || '')) return;
      thread.dataset.latestMessageId = latestMessageId;
      thread.replaceChildren();
      if (!messages.length) {
        const empty = document.createElement('p');
        empty.textContent = 'No messages yet.';
        thread.append(empty);
        return;
      }
      messages.forEach((message) => {
        const item = document.createElement('article');
        item.className = 'consultation-chat__message';
        item.dataset.role = message.sender_role;
        item.dataset.messageId = String(message.id || '');
        const sender = document.createElement('strong');
        sender.textContent = message.sender_role === 'admin'
          ? 'Firm staff'
          : (message.sender_role === 'assistant' ? 'GPMO Assistant' : (thread.closest('.professional-live-chat') ? 'Client' : 'You'));
        const body = document.createElement('p');
        body.textContent = message.body;
        const time = document.createElement('small');
        time.textContent = new Date(message.created_at).toLocaleString();
        item.append(sender, time, body);
        thread.append(item);
        translateLiveChatMessage(item, message.body, thread);
      });
    } catch (error) {
      // The existing messages remain visible if a poll is temporarily unavailable.
    }
  };
  poll();
  window.setInterval(poll, 5000);
});

document.querySelectorAll('[data-inquiry-inbox][data-poll-url]').forEach((inbox) => {
  window.setInterval(async () => {
    try {
      const response = await fetch(inbox.dataset.pollUrl, { headers: { Accept: 'application/json' } });
      if (!response.ok) return;
      const result = await response.json();
      if (Number(result.latest_id) > Number(inbox.dataset.latestId)) window.location.reload();
    } catch (error) {
      // A later poll retries if the inbox endpoint is temporarily unavailable.
    }
  }, 7000);
});

document.querySelectorAll('[data-admin-presence][data-status-url]').forEach((presence) => {
  const updatePresence = async () => {
    try {
      const response = await fetch(presence.dataset.statusUrl, { headers: { Accept: 'application/json' } });
      if (!response.ok) return;
      const { online } = await response.json();
      const dot = presence.querySelector('.consultation-admin-presence__dot');
      const label = presence.querySelector('[data-presence-label]');
      const note = presence.querySelector('[data-presence-note]');
      presence.dataset.online = online ? 'true' : 'false';
      dot.classList.toggle('is-online', online);
      dot.classList.toggle('is-offline', !online);
      label.textContent = online ? 'Admin is online' : 'Admin is offline';
      note.textContent = online
        ? 'A staff member can reply here.'
        : 'The automated assistant will reply until staff are available.';
    } catch (error) {
      // Retain the last known admin status while the next poll retries.
    }
  };
  updatePresence();
  window.setInterval(updatePresence, 7000);
});

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

// ---- GPMO site assistant ----
(function () {
  const chatPanel = document.getElementById('chatPanel');
  const launcher = document.getElementById('launcher');
  const closeChat = document.getElementById('closeChat');
  const chatBody = document.getElementById('chatBody');
  const chatInput = document.getElementById('chatInput');
  const sendBtn = document.getElementById('sendBtn');
  const quickReplies = document.getElementById('quickReplies');
  const quickRepliesPanel = document.getElementById('quickRepliesPanel');
  const quickReplyCount = document.getElementById('quickReplyCount');
  const languageSelect = document.getElementById('chatLanguage');
  const chatScope = document.getElementById('chatScope');
  const chatInfo = document.getElementById('chatInfo');
  const chatInfoToggle = document.getElementById('chatInfoToggle');
  const languageLabel = document.querySelector('.chat-language-label');
  if (!chatPanel) return;

  const CHAT_ENDPOINT = chatPanel.dataset.endpoint;
  const TRANSLATE_ENDPOINT = chatPanel.dataset.translateEndpoint;
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
  const LANGUAGES = {
    en: { greeting: "Hi, I'm GPMO's site assistant. I can answer basic questions about this website and guide you through its sample inquiry flow. I can't provide legal advice.", scope: 'I provide general information about the firm and this website. I can’t give legal advice or assess an individual case.', send: 'Send', placeholder: 'Ask something…', prompts: ['Help me find a practice area', 'What practice areas are available?', 'How do I request a consultation?', 'Where is the office?', 'What are the office hours?', 'How do I contact the firm?', 'How do I track my case?', 'How is my information handled?'] },
    es: { greeting: 'Hola, soy el asistente del sitio de GPMO. Puedo responder preguntas básicas sobre este sitio y sus servicios. No puedo ofrecer asesoría legal.', scope: 'Ofrezco información general sobre el bufete y este sitio. No puedo dar asesoría legal ni evaluar un caso individual.', send: 'Enviar', placeholder: 'Escribe tu pregunta…', prompts: ['Ayúdame a encontrar un área legal', '¿Qué áreas legales hay?', '¿Cómo solicito una consulta?', '¿Dónde está la oficina?', '¿Cuál es el horario de atención?', '¿Cómo contacto al bufete?', '¿Cómo consulto el estado de mi caso?', '¿Cómo se maneja mi información?'] },
    fil: { greeting: 'Kumusta, ako ang site assistant ng GPMO. Makakasagot ako sa mga simpleng tanong tungkol sa website. Hindi ako makapagbibigay ng legal na payo.', scope: 'Nagbibigay ako ng pangkalahatang impormasyon tungkol sa law firm at website. Hindi ako makapagbibigay ng legal na payo o susuri ng partikular na kaso.', send: 'Ipadala', placeholder: 'I-type ang iyong tanong…', prompts: ['Tulungan akong humanap ng larangan ng batas', 'Anong mga larangan ng batas ang mayroon?', 'Paano ako hihiling ng konsultasyon?', 'Nasaan ang opisina?', 'Ano ang oras ng opisina?', 'Paano ko makokontak ang law firm?', 'Paano ko masusubaybayan ang kaso ko?', 'Paano pinangangasiwaan ang impormasyon ko?'] },
    'zh-CN': { greeting: '您好，我是GPMO网站助手。我可以回答有关本网站的基本问题，但不能提供法律意见。', scope: '我提供有关律师事务所和本网站的一般信息，不能提供法律意见或评估个案。', send: '发送', placeholder: '请输入问题…', prompts: ['帮我查找法律业务领域', '有哪些法律业务领域？', '如何申请咨询？', '办公室在哪里？', '办公时间是什么？', '如何联系律师事务所？', '如何查询案件进度？', '我的信息会如何处理？'] },
    ko: { greeting: '안녕하세요. GPMO 사이트 도우미입니다. 웹사이트에 관한 기본 질문에 답할 수 있지만 법률 자문은 제공하지 않습니다.', scope: '법률 사무소와 웹사이트에 대한 일반 정보를 제공합니다. 법률 자문이나 개별 사건 평가는 할 수 없습니다.', send: '보내기', placeholder: '질문을 입력하세요…', prompts: ['적합한 법률 분야를 찾아주세요', '어떤 법률 분야가 있나요?', '상담은 어떻게 신청하나요?', '사무실은 어디에 있나요?', '업무 시간이 어떻게 되나요?', '법률 사무소에 어떻게 연락하나요?', '사건 진행 상황은 어떻게 확인하나요?', '개인정보는 어떻게 처리되나요?'] },
    ja: { greeting: 'こんにちは。GPMOのサイトアシスタントです。ウェブサイトについての基本的な質問にお答えします。法律相談はできません。', scope: '法律事務所とウェブサイトに関する一般情報を提供します。法律相談や個別案件の評価はできません。', send: '送信', placeholder: '質問を入力してください…', prompts: ['法律分野を探したい', 'どのような法律分野がありますか？', '相談を申し込むにはどうすればよいですか？', '事務所はどこですか？', '営業時間を教えてください', '事務所への連絡方法を教えてください', '案件の進捗を確認するにはどうすればよいですか？', '個人情報はどのように扱われますか？'] },
  };
  const LANGUAGE_NAMES = { en: 'English', fil: 'Filipino', es: 'Spanish', 'zh-CN': 'Mandarin Chinese', ko: 'Korean', ja: 'Japanese' };
  const LANGUAGE_NOTES = { es: 'Las traducciones son orientativas; confirme la información legal importante con la oficina.', fil: 'Pangkalahatang gabay lamang ang salin; kumpirmahin sa opisina ang mahalagang impormasyong legal.', 'zh-CN': '译文仅供参考；重要法律信息请向办公室确认。', ko: '번역은 참고용입니다. 중요한 법률 정보는 사무실에 확인하세요.', ja: '翻訳は参考情報です。重要な法律情報は事務所にご確認ください。' };
  function applyChatLanguage() {
    const activeLanguage = languageSelect.value === 'auto' ? 'en' : languageSelect.value;
    const pack = LANGUAGES[activeLanguage] || LANGUAGES.en;
    const note = LANGUAGE_NOTES[activeLanguage];
    chatScope.textContent = pack.scope + (note ? ' ' + note : '');
    languageLabel.textContent = ({ es: 'Idioma', fil: 'Wika', 'zh-CN': '语言', ko: '언어', ja: '言語' })[activeLanguage] || (languageSelect.value === 'auto' ? 'Auto detect' : 'Language');
    chatInput.placeholder = pack.placeholder;
    sendBtn.textContent = pack.send;
  }
  languageSelect.value = 'auto';
  function detectLanguage(text) {
    if (/[\uAC00-\uD7AF]/.test(text)) return 'ko';
    if (/[\u3040-\u30FF]/.test(text)) return 'ja';
    if (/[\u4E00-\u9FFF]/.test(text)) return 'zh-CN';
    const lower = text.toLowerCase();
    if (/[¿¡áéíóúñü]/i.test(text) || /\b(hola|que|como|donde|consulta|familia|trabajo|abogado|gracias)\b/.test(lower)) return 'es';
    if (/\b(ang|mga|ako|mo|ko|sa|po|opo|kayo|hindi|salamat|paano|ano|saan|kamusta|kumusta|bata|ito|si|ng|ngayon|kailangan|wala|para|pamilya|trabaho|lupa|batas|bakit|pwede|puwede|gusto|mahal|konsultasyon)\b/.test(lower)) return 'fil';
    return 'en';
  }
  function updateDetectedLanguage(text) {
    if (!text.trim()) { languageSelect.value = 'auto'; applyChatLanguage(); return 'en'; }
    const detected = detectLanguage(text);
    languageSelect.value = detected;
    applyChatLanguage();
    return detected;
  }
  chatInput.addEventListener('input', () => updateDetectedLanguage(chatInput.value));
  languageSelect.addEventListener('change', () => {

    applyChatLanguage();
  });
  applyChatLanguage();

  launcher.addEventListener('click', () => {
    launcher.classList.add('has-opened');
    chatPanel.classList.add('open');
    launcher.setAttribute('aria-expanded', 'true');
    if (chatBody.children.length === 0) startConversation();
  });
  function closeAssistant(returnFocus = false) {
    chatPanel.classList.remove('open');
    launcher.setAttribute('aria-expanded', 'false');
    if (returnFocus) launcher.focus();
  }

  closeChat.addEventListener('click', () => closeAssistant(true));
  chatInfoToggle.addEventListener('click', () => {
    const expanded = chatInfoToggle.getAttribute('aria-expanded') === 'true';
    chatInfo.hidden = expanded;
    chatInfoToggle.setAttribute('aria-expanded', String(!expanded));
    chatInfoToggle.setAttribute('aria-label', expanded ? 'Show assistant information and language settings' : 'Hide assistant information and language settings');
  });
  document.addEventListener('pointerdown', (event) => {
    if (!chatPanel.classList.contains('open')) return;
    if (chatPanel.contains(event.target) || launcher.contains(event.target)) return;
    closeAssistant();
  });
  document.addEventListener('focusin', (event) => {
    if (!chatPanel.classList.contains('open')) return;
    if (chatPanel.contains(event.target) || launcher.contains(event.target)) return;
    closeAssistant();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && chatPanel.classList.contains('open')) closeAssistant(true);
  });

  function providerLanguage(code) {
    const normalized = (code || '').toLowerCase();
    if (normalized === 'tl' || normalized === 'fil') return 'fil';
    if (normalized === 'zh' || normalized === 'zh-cn') return 'zh-CN';
    return ['en', 'es', 'ko', 'ja'].includes(normalized) ? normalized : null;
  }

  function addMsg(text, who, detail = '') {
    const div = document.createElement('div');
    div.className = 'msg ' + who;
    div.textContent = text;
    let languageNote = null;
    if (detail) {
      languageNote = document.createElement('small');
      languageNote.className = 'msg-translation';
      languageNote.textContent = detail;
      div.appendChild(languageNote);
    }

    let translateButton = null;
    if (who === 'user') {
      translateButton = document.createElement('button');
      translateButton.type = 'button';
      translateButton.className = 'msg-translate-btn';
      translateButton.textContent = 'A 文';
      translateButton.title = 'Translate this message into English';
      translateButton.setAttribute('aria-label', 'Translate this message into English');
      translateButton.setAttribute('aria-expanded', 'false');

      const translation = document.createElement('small');
      translation.className = 'msg-translation msg-translation--result';
      translation.hidden = true;
      translateButton.addEventListener('click', async () => {
        if (translation.dataset.loaded === 'true') {
          translation.hidden = !translation.hidden;
          translateButton.setAttribute('aria-expanded', String(!translation.hidden));
          return;
        }
        translateButton.disabled = true;
        translation.hidden = false;
        translateButton.setAttribute('aria-expanded', 'true');
        translation.textContent = 'Translating…';
        try {
          const response = await fetch(TRANSLATE_ENDPOINT, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ message: text }),
          });
          const result = await response.json();
          if (!response.ok || typeof result.translation !== 'string') {
            throw new Error(result.message || 'Translation is unavailable. Please try again.');
          }
          translation.textContent = 'English: ' + result.translation;
          translation.dataset.loaded = 'true';
          const detected = providerLanguage(result.detectedLanguage);
          if (detected) {
            languageSelect.value = detected;
            applyChatLanguage();
            if (languageNote) languageNote.textContent = 'Detected language: ' + LANGUAGE_NAMES[detected];
          }
        } catch (error) {
          translation.textContent = error.message || 'Translation service is unavailable. Please try again.';
        } finally {
          translateButton.disabled = false;
          chatBody.scrollTop = chatBody.scrollHeight;
        }
      });
      div.append(translateButton, translation);
    }

    chatBody.appendChild(div);
    chatBody.scrollTop = chatBody.scrollHeight;
    return { div, translateButton, languageNote };
  }
  function addCta(cta, answerBubble) {
    const link = document.createElement('a');
    link.className = 'cta-inline';
    link.href = cta.route;
    link.textContent = cta.label;
    answerBubble.appendChild(link);
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function setQuickReplies(options) {
    quickReplies.innerHTML = '';
    const suggestions = options || [];
    quickRepliesPanel.hidden = suggestions.length === 0;
    quickRepliesPanel.open = false;

    quickReplyCount.textContent = suggestions.length ? String(suggestions.length) : '';

    suggestions.forEach((opt) => {
      const button = document.createElement('button');
      button.className = 'qr-btn';
      button.textContent = opt;
      button.onclick = () => handleInput(opt);
      quickReplies.appendChild(button);
    });
  }
  function startConversation() {
    const activeLanguage = languageSelect.value === 'auto' ? 'en' : languageSelect.value;
    const pack = LANGUAGES[activeLanguage] || LANGUAGES.en;
    addMsg(pack.greeting, 'bot');
    setQuickReplies(pack.prompts);
  }

  async function handleInput(raw) {
    const text = (raw || '').trim();
    if (!text) return;
    const detectedLanguage = updateDetectedLanguage(text);
    const messageUi = addMsg(text, 'user', 'Detected language: ' + LANGUAGE_NAMES[detectedLanguage]);
    if (detectedLanguage !== 'en') messageUi.translateButton?.click();
    chatInput.value = '';
    const pendingReply = addMsg('Looking up the site information…', 'bot');
    pendingReply.div.dataset.loading = 'true';

    try {
      const res = await fetch(CHAT_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({ message: text, language: detectedLanguage }),
      });
      let data;
      try {
        data = await res.json();
      } catch (_) {
        throw new Error(`The chat endpoint returned a non-JSON page (HTTP ${res.status}). Check the local app URL and Apache rewrite settings.`);
      }
      if (res.status === 419) throw new Error('Your chat session expired (HTTP 419). Refresh the page and try again.');
      if (!res.ok) throw new Error(data.message || `The chat request failed (HTTP ${res.status}).`);
      if (typeof data.reply !== 'string' || data.reply.trim() === '') {
        throw new Error('The chat service returned an empty answer. Please refresh and try again.');
      }

      pendingReply.div.remove();
      const answerBubble = addMsg(data.reply, 'bot', data.sourceReply && detectedLanguage !== 'en' ? 'English: ' + data.sourceReply : '');
      if (data.cta) {
        addCta(data.cta, answerBubble.div);
      }
      const persistentPrompts = (LANGUAGES[detectedLanguage] || LANGUAGES.en).prompts;
      const contextualPrompts = Array.isArray(data.quickReplies) ? data.quickReplies : [];
      setQuickReplies([...new Set([...persistentPrompts, ...contextualPrompts])]);
    } catch (error) {
      pendingReply.div.remove();
      console.error('GPMO chat request failed:', error);
      addMsg(error.message || 'The chat could not load an answer. Please try again.', 'bot');
      setQuickReplies((LANGUAGES[detectedLanguage] || LANGUAGES.en).prompts);
    }
  }

  sendBtn.addEventListener('click', () => handleInput(chatInput.value));
  chatInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') handleInput(chatInput.value);
  });
})();
