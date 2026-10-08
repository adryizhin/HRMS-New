# Contrast checks

Run `node tests/audit-contrast.cjs` from the project root with Playwright available on Node's module path and Microsoft Edge installed. Set `CONTRAST_BROWSER` to another installed Playwright browser channel if needed.

The audit renders local PHP templates with PHP expressions removed, in light and dark themes. It blocks network traffic and removes application scripts, adds sample status badges and sidebar navigation, and checks visible text at rest and during hover. Theme transitions are disabled to measure settled colors. The report is written to `tests/contrast-results.json`; failures return a nonzero exit code.

This is a template regression check, not a complete accessibility certification. It does not execute PHP, authenticate, inspect live database records, or verify every dynamic state. Gradient checks use the first color stop; image backgrounds and opacity compositing need visual review. Text thresholds are 4.5:1, or 3:1 for large text.

The final local run checked 5,416 text instances, including repeated hover checks, with no detected failures. A project-specific PHP 8.4 runtime has since been configured. See SETUP.md for the subsequent application checks.

Pre-existing merge conflicts were resolved in favor of the theme-enabled HEAD versions. Original conflicted files were saved outside the web root at `C:/Users/DOH/AppData/Local/Temp/hrms-contrast-20261008-132407`.
