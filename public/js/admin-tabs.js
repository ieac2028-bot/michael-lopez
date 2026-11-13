(() => {
  const root = document.querySelector('[data-admin-tabs]');
  if (!root) return;

  const tabs = [...root.querySelectorAll('[role="tab"]')];
  const panels = tabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));

  const activate = (index, updateHash = true) => {
    tabs.forEach((tab, current) => {
      const selected = current === index;
      tab.setAttribute('aria-selected', String(selected));
      tab.tabIndex = selected ? 0 : -1;
      panels[current].hidden = !selected;
    });

    sessionStorage.setItem('gpmo-admin-active-tab', panels[index].id);

    if (updateHash && window.location.hash !== `#${panels[index].id}`) {
      history.replaceState(null, '', `#${panels[index].id}`);
    }
  };

  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => activate(index));
    tab.addEventListener('keydown', (event) => {
      let next = index;
      if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      else if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
      else if (event.key === 'Home') next = 0;
      else if (event.key === 'End') next = tabs.length - 1;
      else return;

      event.preventDefault();
      activate(next);
      tabs[next].focus();
    });
  });

  const activateFromLocation = () => {
    const hashIndex = panels.findIndex((panel) => `#${panel.id}` === window.location.hash);
    const savedId = sessionStorage.getItem('gpmo-admin-active-tab');
    const savedIndex = panels.findIndex((panel) => panel.id === savedId);
    const index = hashIndex >= 0 ? hashIndex : savedIndex;
    if (index >= 0) activate(index, hashIndex >= 0);
  };

  window.addEventListener('hashchange', activateFromLocation);
  activateFromLocation();
})();
