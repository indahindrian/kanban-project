<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Project Management</title>
<style>
  :root { --bg:#f4f5f7; --card-bg:#fff; --border:#dfe1e6; --text:#172b4d; --muted:#6b778c; --accent:#0052cc; }
  * { box-sizing:border-box; }
  body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--text); }
  .card { background:var(--card-bg); border:1px solid var(--border); border-radius:8px; padding:32px; width:100%; max-width:340px; box-shadow:0 2px 8px rgba(9,30,66,.1); }
  .card h1 { font-size:18px; margin:0 0 4px; }
  .card p { font-size:13px; color:var(--muted); margin:0 0 20px; }
  label { display:block; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; margin-bottom:6px; }
  input { width:100%; padding:9px 10px; border:1px solid var(--border); border-radius:4px; font-size:14px; margin-bottom:16px; }
  button { width:100%; border:none; border-radius:4px; padding:10px; font-size:14px; font-weight:600; background:var(--accent); color:#fff; cursor:pointer; }
  button:hover { background:#0747a6; }
  .error { background:#ffebe6; color:#bf2600; font-size:13px; padding:8px 10px; border-radius:4px; margin-bottom:16px; display:none; }
  .hint { margin-top:18px; font-size:12px; color:var(--muted); line-height:1.6; }
  .hint b { color:var(--text); }
</style>
</head>
<body>

<div class="card">
  <h1>Masuk</h1>
  <p>Project Management &amp; Kanban</p>

  <div class="error" id="login-error"></div>

  <form id="login-form">
    <label>Email</label>
    <input type="email" id="f-email" required>
    <label>Password</label>
    <input type="password" id="f-password" required>
    <button type="submit">Login</button>
  </form>

  <!-- <div class="hint">
    Demo akun (password sama: <b>password123</b>):<br>
    Super Admin &rarr; <b>admin@example.com</b><br>
    Atasan / Monitoring &rarr; <b>siti@example.com</b><br>
    R&amp;D / MDS &rarr; <b>budi@example.com</b> / <b>andi@example.com</b>
  </div> -->
</div>

<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('f-email').value;
  const password = document.getElementById('f-password').value;
  const errBox = document.getElementById('login-error');
  errBox.style.display = 'none';

  const res = await fetch('<?= site_url('auth/login') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ email, password }).toString()
  });
  const json = await res.json();

  if (json.code !== '00') {
    errBox.innerText = json.message;
    errBox.style.display = 'block';
    return;
  }
  window.location.href = '<?= site_url('project') ?>';
});
</script>

</body>
</html>
