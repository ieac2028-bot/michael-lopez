(() => {
  const initializeAttorneyTabs = () => {
    const tabs = Array.from(document.querySelectorAll('[data-attorney-workspace-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-attorney-workspace-panel]'));
    if (!tabs.length || !panels.length) return;

    const activate = (name, focus = false) => {
      tabs.forEach((tab) => {
        const selected = tab.dataset.attorneyWorkspaceTab === name;
        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
        tab.tabIndex = selected ? 0 : -1;
        if (selected && focus) tab.focus();
      });
      panels.forEach((panel) => { panel.hidden = panel.dataset.attorneyWorkspacePanel !== name; });
      try { sessionStorage.setItem('attorney-workspace-tab', name); } catch (_) {}
    };

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', () => activate(tab.dataset.attorneyWorkspaceTab));
      tab.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1
          : (index + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length;
        activate(tabs[next].dataset.attorneyWorkspaceTab, true);
      });
    });

    let initial = 'matters';
    try {
      const saved = sessionStorage.getItem('attorney-workspace-tab');
      if (saved && panels.some((panel) => panel.dataset.attorneyWorkspacePanel === saved)) initial = saved;
    } catch (_) {}
    activate(initial);
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeAttorneyTabs, { once: true });
  else initializeAttorneyTabs();
})();
