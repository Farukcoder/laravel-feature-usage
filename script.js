const root=document.documentElement;const saved=localStorage.getItem('feature-heatmap-theme');if(saved)root.dataset.theme=saved;document.querySelectorAll('.theme-toggle').forEach(btn=>{const setIcon=()=>btn.querySelector('.theme-icon').textContent=root.dataset.theme==='dark'?'☀':'☾';setIcon();btn.addEventListener('click',()=>{root.dataset.theme=root.dataset.theme==='dark'?'light':'dark';localStorage.setItem('feature-heatmap-theme',root.dataset.theme);setIcon();updateShowcase();});});
document.querySelectorAll('.copy-block button').forEach(btn=>btn.addEventListener('click',async()=>{const block=btn.closest('.copy-block');const text=block.dataset.copy||block.querySelector('code')?.innerText||'';try{await navigator.clipboard.writeText(text);btn.textContent='Copied!';setTimeout(()=>btn.textContent='Copy',1300)}catch{btn.textContent='Select';}}));
const revealObserver=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');revealObserver.unobserve(e.target)}}),{threshold:.12});document.querySelectorAll('.reveal').forEach(el=>revealObserver.observe(el));
const shotButtons=[...document.querySelectorAll('.showcase-tabs button')],shotImage=document.querySelector('#showcase-image'),shotCaption=document.querySelector('#showcase-caption');function updateShowcase(){const active=document.querySelector('.showcase-tabs button.active');if(!active||!shotImage)return;shotImage.src=root.dataset.theme==='dark'?(active.dataset.darkShot||active.dataset.shot):active.dataset.shot;shotCaption.textContent=active.dataset.caption;}shotButtons.forEach(btn=>btn.addEventListener('click',()=>{shotButtons.forEach(b=>b.classList.remove('active'));btn.classList.add('active');updateShowcase()}));updateShowcase();
document.querySelectorAll('[data-code-tab]').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('[data-code-tab]').forEach(b=>b.classList.remove('active'));document.querySelectorAll('[data-code-panel]').forEach(p=>p.classList.remove('active'));btn.classList.add('active');document.querySelector(`[data-code-panel="${btn.dataset.codeTab}"]`)?.classList.add('active')}));
const search=document.querySelector('#docs-search');if(search){window.addEventListener('keydown',e=>{if(e.key==='/'&&document.activeElement!==search){e.preventDefault();search.focus()}});search.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();document.querySelectorAll('[data-searchable]').forEach(s=>s.classList.toggle('search-hidden',!!q&&!s.innerText.toLowerCase().includes(q)));});}
const sections=[...document.querySelectorAll('.docs-content section[id]')],sideLinks=[...document.querySelectorAll('.docs-sidebar a')];if(sections.length){const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){sideLinks.forEach(a=>a.classList.toggle('active',a.getAttribute('href')==='#'+e.target.id));}}),{rootMargin:'-20% 0px -70% 0px'});sections.forEach(s=>io.observe(s));}
/* ---- Mobile nav drawer (index page only) ---- */
(function(){
  /* Skip on docs page — the sidebar panel handles the hamburger there */
  if(document.querySelector('.docs-sidebar'))return;
  const mobileMenuBtn=document.querySelector('.mobile-menu');
  if(!mobileMenuBtn)return;
  const overlay=document.createElement('div');overlay.className='mobile-nav-overlay';
  const drawer=document.createElement('div');drawer.className='mobile-nav-drawer';
  const closeBtn=document.createElement('button');closeBtn.className='close-btn';closeBtn.setAttribute('aria-label','Close menu');closeBtn.textContent='✕';
  const navLinks=document.querySelector('.nav-links');
  const headerActions=document.querySelector('.header-actions');
  drawer.appendChild(closeBtn);
  if(navLinks){navLinks.querySelectorAll('a').forEach(a=>{const cl=a.cloneNode(true);drawer.appendChild(cl);});}
  const div=document.createElement('div');div.className='drawer-divider';drawer.appendChild(div);
  const drawerActions=document.createElement('div');drawerActions.className='drawer-actions';
  if(headerActions){headerActions.querySelectorAll('a.button,button.icon-button').forEach(el=>{const cl=el.cloneNode(true);drawerActions.appendChild(cl);});}drawer.appendChild(drawerActions);
  document.body.appendChild(overlay);document.body.appendChild(drawer);
  const open=()=>{drawer.classList.add('open');overlay.classList.add('open');document.body.style.overflow='hidden';};
  const close=()=>{drawer.classList.remove('open');overlay.classList.remove('open');document.body.style.overflow='';};
  mobileMenuBtn.addEventListener('click',open);
  closeBtn.addEventListener('click',close);
  overlay.addEventListener('click',close);
  drawer.querySelectorAll('a').forEach(a=>a.addEventListener('click',close));
})();
/* ---- Docs sidebar mobile panel (slides from LEFT, opened by header ☰) ---- */
(function(){
  const sidebar=document.querySelector('.docs-sidebar');
  if(!sidebar)return;
  /* Build the panel */
  const panel=document.createElement('div');panel.className='docs-sidebar-panel';
  const panelOverlay=document.createElement('div');panelOverlay.className='docs-sidebar-panel-overlay';
  const inner=document.createElement('div');inner.className='docs-sidebar-panel-inner';
  /* Close button inside the panel */
  const closeBtn=document.createElement('button');closeBtn.className='close-btn';closeBtn.setAttribute('aria-label','Close navigation');
  closeBtn.innerHTML='<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
  inner.appendChild(closeBtn);
  /* Panel heading */
  const heading=document.createElement('div');heading.className='docs-sidebar-panel-heading';heading.textContent='Navigation';
  inner.appendChild(heading);
  /* Clone sidebar nav links */
  sidebar.querySelectorAll('p,a').forEach(el=>{const cl=el.cloneNode(true);inner.appendChild(cl);});
  panel.appendChild(panelOverlay);panel.appendChild(inner);document.body.appendChild(panel);
  const open=()=>{panel.classList.add('open');document.body.style.overflow='hidden';};
  const close=()=>{panel.classList.remove('open');document.body.style.overflow='';};
  /* Wire the header hamburger button */
  const mobileMenuBtn=document.querySelector('.mobile-menu');
  if(mobileMenuBtn)mobileMenuBtn.addEventListener('click',open);
  closeBtn.addEventListener('click',close);
  panelOverlay.addEventListener('click',close);
  inner.querySelectorAll('a').forEach(a=>a.addEventListener('click',close));
  /* Keep active state in sync */
  const syncActive=()=>{
    const activeSide=document.querySelector('.docs-sidebar a.active');
    if(!activeSide)return;
    const href=activeSide.getAttribute('href');
    inner.querySelectorAll('a').forEach(a=>a.classList.toggle('active',a.getAttribute('href')===href));
  };
  const io2=new MutationObserver(syncActive);
  document.querySelectorAll('.docs-sidebar a').forEach(a=>io2.observe(a,{attributes:true,attributeFilter:['class']}));
})();
