// Perilaku antarmuka umum
document.addEventListener('DOMContentLoaded', () => {
  // Toast hilang otomatis
  document.querySelectorAll('.toast-x').forEach(t => setTimeout(() => { t.style.transition = 'opacity .4s'; t.style.opacity = 0; setTimeout(() => t.remove(), 450); }, 5000));
  // Sidebar: ciutkan (desktop) / buka (mobile)
  const ciut = document.querySelector('[data-ciut-sidebar]');
  if (ciut) {
    try { if (localStorage.getItem('sidebarCiut') === '1') document.body.classList.add('sidebar-ciut'); } catch (e) {}
    ciut.addEventListener('click', () => {
      if (window.innerWidth < 992) { document.body.classList.remove('sidebar-buka'); return; }
      document.body.classList.toggle('sidebar-ciut');
      try { localStorage.setItem('sidebarCiut', document.body.classList.contains('sidebar-ciut') ? '1' : '0'); } catch (e) {}
    });
  }
  document.querySelectorAll('[data-buka-sidebar]').forEach(b => b.addEventListener('click', () => document.body.classList.add('sidebar-buka')));
  document.addEventListener('click', e => { if (document.body.classList.contains('sidebar-buka') && !e.target.closest('.sidebar') && !e.target.closest('[data-buka-sidebar]')) document.body.classList.remove('sidebar-buka'); });
  // Tampil/sembunyi kata sandi
  document.querySelectorAll('.lihat-sandi').forEach(b => b.addEventListener('click', () => {
    const i = b.parentElement.querySelector('input'); i.type = i.type === 'password' ? 'text' : 'password';
    b.querySelector('i').className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
  }));
  // Isi modal dari atribut data-isi (JSON) pada tombol pemicu
  document.querySelectorAll('[data-bs-toggle="modal"][data-isi]').forEach(b => b.addEventListener('click', () => {
    const m = document.querySelector(b.dataset.bsTarget); const d = JSON.parse(b.dataset.isi);
    if (d._aksi) m.querySelector('form').action = d._aksi;
    Object.entries(d).forEach(([k, v]) => {
      m.querySelectorAll(`[name="${k}"]`).forEach(el => { if (el.type === 'hidden' && m.querySelector(`input[type=checkbox][name="${k}"]`)) return; if (el.type === 'checkbox') el.checked = !!v; else if (el.type === 'radio') el.checked = el.value == v; else el.value = v ?? ''; });
      m.querySelectorAll(`[data-teks="${k}"]`).forEach(el => el.textContent = v ?? '');
    });
  }));
});
