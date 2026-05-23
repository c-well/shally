{{-- SHALOM_SITE_CSS — Phase B (2026-05-23): single shared stylesheet replaces
     per-blade design tokens, fonts, and theme overrides. Edit /public/css/shalom.css
     to change colors/fonts site-wide. Loaded last in <head> so its values override
     any drifted local :root copies still living in individual blade <style> blocks. --}}
<link rel="stylesheet" href="{{ asset('css/shalom.css') }}?v={{ filemtime(public_path('css/shalom.css')) }}">
