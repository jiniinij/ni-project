# NI — Interactive Sound Archive

Web-based interactive sound archive developed for Project NI.  
Designed as a digital performance platform where users play, layer, and manipulate sounds directly in the browser.

## Live
https://ni-project.com

## Stack
- Kirby CMS + PHP
- HTML Audio API (multi-track independent playback)
- Vanilla JavaScript
- CSS

## Structure
```
/
├── assets/
│   ├── css/          # styles.css + fonts
│   └── js/           # animations.js
├── content/          # Kirby content files
├── kirby/            # Kirby core
├── site/
│   ├── blueprints/
│   ├── config/
│   ├── snippets/     # header, footer, soundsamples
│   └── templates/    # compositions, members, records, soundsamples
├── vendor/
├── .htaccess
└── index.php
```

## Run locally
Requires [Laravel Herd](https://herd.laravel.com/).  
Clone into your Herd sites directory — available at `http://ni.digitale-grafik.com.test`

## Role
Planning, UX/UI design, frontend & backend development, deployment