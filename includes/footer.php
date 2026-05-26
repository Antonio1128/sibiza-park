
<?php if (($_SESSION['rol'] ?? '') === 'client'): ?>
<footer class="site-footer">
  <div class="site-footer-links">
    <a onclick="openModal('Mențiuni legale')">Mențiuni legale</a>
    <a onclick="openModal('Termeni și condiții generale')">Termeni și condiții generale</a>
    <a onclick="openModal('Contactare')">Contactare</a>
    <a onclick="openModal('Confidențialitate')">Confidențialitate</a>
    <a onclick="openModal('Setările de confidențialitate')">Setările de confidențialitate</a>
    <a onclick="openModal('Declarația de Accesibilitate')">Declarația de Accesibilitate</a>
    <a onclick="openModal('Report Security Vulnerability')">Report Security Vulnerability (English)</a>
  </div>
  <div class="site-footer-divider"></div>
  <div class="site-footer-bottom">
    <div class="site-footer-select-wrap">
      <span class="site-footer-select-label">Limbă</span>
      <select class="site-footer-select"><option>Română</option><option>English</option><option>Français</option></select>
    </div>
    <div class="site-footer-select-wrap">
      <span class="site-footer-select-label">Reprezentare</span>
      <select class="site-footer-select"><option>Modul Sistem</option><option>Modul Întunecat</option><option>Modul Luminos</option></select>
    </div>
    <span class="site-footer-copy">&copy; <?= date('Y') ?> Sibiza Park</span>
  </div>
</footer>
<?php endif; ?>

<!-- Modal -->
<div class="modal-overlay" id="legalModal" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle"></span>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>

<script>
const loremFull = `<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.</p>
<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>`;

function openModal(title) {
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalBody').innerHTML = loremFull;
  document.getElementById('legalModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('legalModal').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

</body>
</html>
