    </div><!-- /.content -->
  </main>
</div><!-- /.app -->

<script>
/* ===== helper bersama untuk semua halaman sistem ===== */
function esc(s) {
  const d = document.createElement('div');
  d.textContent = s == null ? '' : String(s);
  return d.innerHTML;
}

// params: object; nilai array dikirim sebagai key[] (dibaca CI sebagai array)
async function api(url, params, method) {
  method = method || 'POST';
  const sp = new URLSearchParams();
  Object.keys(params || {}).forEach(k => {
    const v = params[k];
    if (Array.isArray(v)) v.forEach(x => sp.append(k + '[]', x)); else sp.append(k, v);
  });
  const opts = { method };
  if (method === 'POST') {
    opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
    opts.body = sp.toString();
  } else if (sp.toString()) {
    url += (url.indexOf('?') > -1 ? '&' : '?') + sp.toString();
  }
  const res = await fetch(url, opts);
  const json = await res.json();
  if (json.code === '03' && /login/i.test(json.message)) window.location.href = '<?= site_url('auth') ?>';
  return json;
}

function flash(id, ok, text) {
  const el = document.getElementById(id);
  if (!el) return;
  el.className = 'msg ' + (ok ? 'ok' : 'err');
  el.innerText = text;
}
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function initials(name) {
  return String(name || '?').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0)).join('').toUpperCase();
}
</script>
