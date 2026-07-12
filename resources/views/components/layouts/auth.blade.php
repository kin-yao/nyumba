<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-P5FWPX45');</script>
        <!-- End Google Tag Manager -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Nyumba') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @keyframes nyumba-spin {
                from { transform: rotate(0deg); }
                to   { transform: rotate(360deg); }
            }
            #nyumba-loader {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(245,244,240,0.85);
                z-index: 9999;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(2px);
            }
        </style>
    </head>
    <body style="font-family:'DM Sans',sans-serif;margin:0;padding:0;background:#f5f4f0;min-height:100vh">
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P5FWPX45"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->

        <div id="nyumba-loader">
            <div style="display:flex;flex-direction:column;align-items:center;gap:14px">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                     style="animation:nyumba-spin 0.9s linear infinite">
                    <circle cx="20" cy="20" r="16" stroke="#e5e3de" stroke-width="3"/>
                    <path d="M20 4a16 16 0 0116 16" stroke="#1a6b52" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <span id="nyumba-loader-text" style="font-size:13px;color:#1a6b52;font-family:'DM Sans',sans-serif;font-weight:500">
                    Loading...
                </span>
            </div>
        </div>

        {{ $slot }}

        <script>
            window.showLoader = function(text) {
                const el    = document.getElementById('nyumba-loader');
                const label = document.getElementById('nyumba-loader-text');
                if (label && text) label.textContent = text;
                el.style.display = 'flex';
            };
            window.hideLoader = function() {
                document.getElementById('nyumba-loader').style.display = 'none';
            };
            window.addEventListener('pageshow', hideLoader);
        </script>
    </body>
</html>