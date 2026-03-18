<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars((string) ($title ?? 'Pricing Management Portal')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <?= $content ?>
  <script>
    window.__PRICING_BOOTSTRAP__ = <?= json_encode([
        'defaultYear' => $defaultYear ?? 2026,
        'defaultMonth' => $defaultMonth ?? 2,
        'defaultCodes' => $defaultCodes ?? ['CCAR'],
        'appName' => $title ?? 'Pricing Management Portal',
    ], JSON_UNESCAPED_SLASHES) ?>;
  </script>
  <script src="/assets/app.js" defer></script>
</body>
</html>
