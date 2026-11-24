# Uji end-to-end

Jalankan server (`php artisan serve`) dengan database hasil `migrate:fresh --seed`, lalu:

```bash
npm i playwright && npx playwright install chromium
node tests/e2e/e2e.js
```
