# Tailwind CSS Build

Public page styling is written as Tailwind utility classes in the PHP templates' HTML `class` attributes. `public/assets/css/input.css` only imports Tailwind and defines the brand colors and font. The generated `public/assets/css/site.css` is the static, minified stylesheet the browser loads; it is committed and uploaded with the website. Production cPanel/shared hosting does not need Node.js, npm, a build process, or a long-running process.

## Source and output

- Source: `public/assets/css/input.css`
- Scanned templates: `resources/views/**/*.php`
- Output: `public/assets/css/site.css`
- The old static Phase 2 mockups load `design/preview.css`; production PHP pages do not use its component selectors.

## Rebuild on Windows without Node.js

1. Use the official Windows x64 standalone executable (kept locally at `work/tools/tailwindcss.exe`; download it from the [Tailwind CSS releases](https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-windows-x64.exe) if it is missing).
2. From the project root, run:

```powershell
.\work\tools\tailwindcss.exe -i .\public\assets\css\input.css -o .\public\assets\css\site.css --minify
```

3. Upload the updated `site.css` together with the website files.

The checked-in `site.css` is already compiled. Do not load Tailwind's Play CDN in production. For production pages, add responsive Tailwind utility classes directly to HTML elements; keep custom selectors out of the page styling source.

