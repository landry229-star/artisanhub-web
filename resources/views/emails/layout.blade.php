<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<style>
  body{margin:0;padding:0;background:#F0EBE3;font-family:'Helvetica Neue',Arial,sans-serif;color:#2C1A0E}
  .wrapper{max-width:580px;margin:32px auto;background:#FDFAF7;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(44,26,14,.08)}
  .header{background:linear-gradient(135deg,#2C1A0E,#9E4A1E);padding:28px 32px;text-align:center}
  .logo{font-size:22px;font-weight:bold;color:#fff;letter-spacing:.02em}
  .logo span{color:#E8845A}
  .body{padding:32px}
  .title{font-size:20px;font-weight:bold;margin:0 0 8px;color:#2C1A0E}
  .subtitle{font-size:14px;color:#9A8070;margin:0 0 24px}
  .info-box{background:#F5EFE6;border-radius:10px;padding:16px 20px;margin:20px 0}
  .info-row{display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px}
  .info-label{color:#9A8070}
  .info-value{font-weight:600;color:#2C1A0E;text-align:right;max-width:60%}
  .amount{font-size:24px;font-weight:bold;color:#C4622D;text-align:center;margin:16px 0 4px}
  .amount-label{text-align:center;font-size:12px;color:#9A8070;margin-bottom:16px}
  .btn{display:block;width:fit-content;margin:24px auto;padding:12px 32px;background:#C4622D;color:#fff!important;text-decoration:none;border-radius:8px;font-weight:700;font-size:14px;text-align:center}
  .divider{border:none;border-top:1px solid #ECD8C6;margin:24px 0}
  .footer{background:#F5EFE6;padding:20px 32px;text-align:center;font-size:12px;color:#9A8070}
  .footer a{color:#C4622D;text-decoration:none}
  p{font-size:14px;line-height:1.7;margin:0 0 14px;color:#5C3D1E}
  .badge{display:inline-block;padding:3px 10px;border-radius:100px;font-size:12px;font-weight:600}
  .badge-pending{background:#FFF3CD;color:#856404}
  .badge-success{background:#D4EDDA;color:#155724}
  .badge-danger{background:#F8D7DA;color:#721C24}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="logo">🏺 Artisan<span>Hub</span></div>
    <div style="color:#C8B5A0;font-size:12px;margin-top:4px">Plateforme artisans Bénin 🇧🇯</div>
  </div>
  <div class="body">
    @yield('content')
  </div>
  <div class="footer">
    <p style="margin:0 0 6px">© 2026 ArtisanHub · Bénin</p>
    <p style="margin:0">
      <a href="{{ config('app.url') }}">artisanhub.bj</a> ·
      <a href="{{ route('support') }}">Support</a>
    </p>
  </div>
</div>
</body>
</html>
