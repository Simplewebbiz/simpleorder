<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Storefront Not Configured</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f8fafc; color: #111827; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        main { width: min(92vw, 560px); background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px; box-shadow: 0 20px 60px rgba(15, 23, 42, .08); }
        .eyebrow { color: #e85d04; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 10px; }
        h1 { font-size: 28px; line-height: 1.15; margin: 0 0 12px; }
        p { color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 18px; }
        code { background: #f3f4f6; border-radius: 6px; padding: 2px 6px; color: #111827; }
        a { display: inline-flex; align-items: center; background: #111827; color: #fff; text-decoration: none; padding: 10px 14px; border-radius: 8px; font-weight: 700; font-size: 14px; }
    </style>
</head>
<body>
    <main>
        <div class="eyebrow">SimpleOrder</div>
        <h1>Storefront not configured</h1>
        <p>No tenant is connected to <code>{{ $domain }}</code> yet. If this should be a live storefront, add this domain to the tenant account in Super Admin.</p>
        <a href="{{ $platformUrl }}">Go to SimpleOrder</a>
    </main>
</body>
</html>
