const root=document.documentElement;const saved=localStorage.getItem('feature-heatmap-theme');if(saved)root.dataset.theme=saved;document.querySelectorAll('.theme-toggle').forEach(btn=>{const setIcon=()=>btn.querySelector('.theme-icon').textContent=root.dataset.theme==='dark'?'☀':'☾';setIcon();btn.addEventListener('click',()=>{root.dataset.theme=root.dataset.theme==='dark'?'light':'dark';localStorage.setItem('feature-heatmap-theme',root.dataset.theme);setIcon();updateShowcase();});});
document.querySelectorAll('.copy-block button').forEach(btn=>btn.addEventListener('click',async()=>{const block=btn.closest('.copy-block');const text=block.dataset.copy||block.querySelector('code')?.innerText||'';try{await navigator.clipboard.writeText(text);btn.textContent='Copied!';setTimeout(()=>btn.textContent='Copy',1300)}catch{btn.textContent='Select';}}));
const revealObserver=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');revealObserver.unobserve(e.target)}}),{threshold:.12});document.querySelectorAll('.reveal').forEach(el=>revealObserver.observe(el));
const shotButtons=[...document.querySelectorAll('.showcase-tabs button')],shotImage=document.querySelector('#showcase-image'),shotCaption=document.querySelector('#showcase-caption');function updateShowcase(){const active=document.querySelector('.showcase-tabs button.active');if(!active||!shotImage)return;shotImage.src=root.dataset.theme==='dark'?(active.dataset.darkShot||active.dataset.shot):active.dataset.shot;shotCaption.textContent=active.dataset.caption;}shotButtons.forEach(btn=>btn.addEventListener('click',()=>{shotButtons.forEach(b=>b.classList.remove('active'));btn.classList.add('active');updateShowcase()}));updateShowcase();
document.querySelectorAll('[data-code-tab]').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('[data-code-tab]').forEach(b=>b.classList.remove('active'));document.querySelectorAll('[data-code-panel]').forEach(p=>p.classList.remove('active'));btn.classList.add('active');document.querySelector(`[data-code-panel="${btn.dataset.codeTab}"]`)?.classList.add('active')}));
const search=document.querySelector('#docs-search');if(search){window.addEventListener('keydown',e=>{if(e.key==='/'&&document.activeElement!==search){e.preventDefault();search.focus()}});search.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();document.querySelectorAll('[data-searchable]').forEach(s=>s.classList.toggle('search-hidden',!!q&&!s.innerText.toLowerCase().includes(q)));});}
const sections=[...document.querySelectorAll('.docs-content section[id]')],sideLinks=[...document.querySelectorAll('.docs-sidebar a')];if(sections.length){const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){sideLinks.forEach(a=>a.classList.toggle('active',a.getAttribute('href')==='#'+e.target.id));}}),{rootMargin:'-20% 0px -70% 0px'});sections.forEach(s=>io.observe(s));}

// ── Mobile navigation drawer ──────────────────────────────────────────────────
(function () {
  const mobileBtn = document.querySelector('.mobile-menu');
  if (!mobileBtn) return;
  const overlay = document.createElement('div');
  overlay.className = 'mobile-nav-overlay';
  overlay.setAttribute('aria-hidden', 'true');
  overlay.innerHTML = `<div class="mobile-nav-drawer" role="dialog" aria-label="Mobile navigation"><div class="drawer-header"><a class="brand" href="index.html" aria-label="Feature Heatmap home"><span class="brand-icon"><span></span><span></span><span></span></span><span><strong>Feature Heatmap</strong><small>Laravel analytics</small></span></a><button class="drawer-close" aria-label="Close menu">✕</button></div><a href="index.html#features">Features</a><a href="index.html#screenshots">Screenshots</a><a href="docs.html">Documentation</a><div class="drawer-divider"></div><div class="drawer-actions"><a class="button button-secondary" href="https://github.com/Farukcoder/laravel-feature-usage">GitHub ↗</a><a class="button button-primary" href="docs.html#installation">Get started</a></div></div>`;
  document.body.appendChild(overlay);
  const openNav = () => { overlay.classList.add('open'); overlay.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; };
  const closeNav = () => { overlay.classList.remove('open'); overlay.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; };
  mobileBtn.addEventListener('click', openNav);
  overlay.querySelector('.drawer-close').addEventListener('click', closeNav);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeNav(); });
  overlay.querySelectorAll('a').forEach(a => a.addEventListener('click', closeNav));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNav(); });
})();

// ── Docs mobile sidebar drawer ────────────────────────────────────────────────
(function () {
  const docsContent = document.querySelector('.docs-content');
  const docsSidebar = document.querySelector('.docs-sidebar');
  if (!docsContent || !docsSidebar) return;
  const toggleBtn = document.createElement('button');
  toggleBtn.className = 'docs-sidebar-toggle';
  toggleBtn.setAttribute('aria-label', 'Open docs navigation');
  toggleBtn.innerHTML = '<span style="font-size:16px">☰</span>&nbsp; Navigation';
  docsContent.insertBefore(toggleBtn, docsContent.firstChild);
  const overlay = document.createElement('div');
  overlay.className = 'docs-sidebar-overlay';
  overlay.setAttribute('aria-hidden', 'true');
  const mobileSidebar = document.createElement('div');
  mobileSidebar.className = 'docs-sidebar-mobile';
  mobileSidebar.innerHTML = docsSidebar.innerHTML;
  overlay.appendChild(mobileSidebar);
  document.body.appendChild(overlay);
  const openSidebar = () => { overlay.classList.add('open'); overlay.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; };
  const closeSidebar = () => { overlay.classList.remove('open'); overlay.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; };
  toggleBtn.addEventListener('click', openSidebar);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeSidebar(); });
  mobileSidebar.querySelectorAll('a').forEach(a => a.addEventListener('click', closeSidebar));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
})();

