<?php /* Autor: Raul Rubio - Pt04 | Cabecera HTML común (Tailwind por CDN) */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Umamusume: Pretty Derby') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;700&display=swap" rel="stylesheet">

    <!-- Tailwind por CDN (necesita internet) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { blue: '#4543FF', pink: '#B8349F', paper: '#F3F0F0' } },
                    fontFamily: { sans: ['"M PLUS Rounded 1c"', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex flex-col bg-brand-paper font-sans text-black">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50 focus:rounded-full focus:bg-white focus:px-4 focus:py-2 focus:font-bold">
    Skip to main content
</a>
<?php include __DIR__ . '/flash.php'; ?>
