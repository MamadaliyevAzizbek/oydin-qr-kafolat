# QR Kafolat (Oydin Electric)

Laravel + MySQL panel: Billz 2.0 sinxron, Telegram bot, hisobotlar, xodimlar/rollar.

## Git (qaytarish / restore)

Loyihada git bor. Katta o‘zgarishdan oldin commit qiling — Cursor chat restore o‘rniga:

```bash
git status
git add -A && git commit -m "Izoh: nima o‘zgardi"
git log --oneline -10          # nuqtalar
git checkout -- path/to/file   # bitta fayl
git restore .                  # oxirgi commitga (saqlanmagan o‘zgarishlar)
# yoki butun loyihani eski commitga:
# git reset --hard <commit>
```

`.env`, `vendor/`, `node_modules/` gitga kirmaydi.

## Lokal ishga tushirish

```bash
composer install
cp .env.example .env
php artisan key:generate

# MySQL tavsiya etiladi
# DB_CONNECTION=mysql
# DB_DATABASE=qr_kafolat
# DB_USERNAME=...
# DB_PASSWORD=...

php artisan migrate --seed
```

`.env` da:

```env
APP_URL=http://127.0.0.1:8000
```

**Ikki terminal** (Cloudflare / tunnel kerak emas):

```bash
# 1) Panel
php artisan serve

# 2) Telegram bot (long polling — webhook o‘rniga)
php artisan telegram:poll
```

Kirish: http://127.0.0.1:8000/login — `admin` / `admin123`  
**(Deploy dan keyin parolni almashtiring!)**

Panel → **Telegram bot** → guruh ID → **Test xabar** — guruhlarga yuborishni tekshiradi.  
Botga chek / `/chatid` yozish uchun `telegram:poll` ishlashi shart.

## Production deploy

1. Kodni serverga joylang, `composer install --no-dev --optimize-autoloader`
2. `.env` ni to‘ldiring:

```env
APP_NAME="QR Kafolat"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://SIZNING-DOMEN.uz

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=qr_kafolat
DB_USERNAME=...
DB_PASSWORD=...

BILLZ_SECRET_TOKEN=...
TELEGRAM_BOT_TOKEN=...
```

3. Migratsiya va keshlash:

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force   # faqat admin rol/user
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. **Xavfsiz parol:**

```bash
php artisan user:password admin 'KuchliParol123!'
```

5. **Cron** (har daqiqa — Laravel scheduler):

```cron
* * * * * cd /path/to/oydin_qr_kafolat && php artisan schedule:run >> /dev/null 2>&1
```

Shu orqali ishlaydi: Billz sync, so‘rovnomalar, kunlik/haftalik/oylik hisobotlar, SLA alertlar.

6. **Telegram:**
   - **Local:** `php artisan telegram:poll` (Cloudflare yo‘q). Panelda Test xabar.
   - **Production:** Panel → **Telegram bot** → token, 2 guruh ID → **Webhook o‘rnatish** (`APP_URL` HTTPS).

Guruhga botni admin qilib qo‘shing, guruh ID ni (odatda `-100…`) yozing.

## Artisan

```bash
php artisan billz:sync-sales
php artisan notify:daily
php artisan notify:weekly          # dushanba 09:00 (schedule)
php artisan notify:alerts
php artisan notify:monthly-sellers
php artisan surveys:dispatch
php artisan user:password admin 'YangiParol'
php artisan telegram:poll          # local bot (Cloudflare/webhook kerak emas)
php artisan telegram:set-webhook --delete
```

## Panel sozlamalar

1. **Tizim** — Billz secret / sync  
2. **Telegram bot** — token, 2 guruh ID; localda polling, serverda webhook  
3. **Xodimlar** — rollar, login; do‘kon menejeriga **Do‘kon** biriktiring → faqat o‘sha do‘kon ko‘rinadi  

Hujjatlar: `docs/` (TZ, Billz API).
