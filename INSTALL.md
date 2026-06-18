# SimpleOrder — Multi-Tenant SaaS Installation Guide

## Requirements
- PHP 8.3+ with extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, gd
- MySQL 8.0+
- Apache with mod_rewrite enabled
- Composer 2.x
- Node.js 20+ and npm

## Server Setup

### 1. Apache VirtualHost (wildcard subdomain)
```apache
<VirtualHost *:80>
    ServerName simpleorder.com
    ServerAlias *.simpleorder.com
    DocumentRoot /var/www/simpleorder/public

    <Directory /var/www/simpleorder/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Enable `mod_rewrite` and `mod_headers`:
```bash
a2enmod rewrite headers
systemctl restart apache2
```

### 2. DNS
- `simpleorder.com` → your server IP
- `*.simpleorder.com` → same server IP (wildcard A record)

### 3. SSL (Let's Encrypt with wildcard)
```bash
certbot certonly --dns-cloudflare -d simpleorder.com -d *.simpleorder.com
```

---

## Application Setup

### 4. Clone & install dependencies
```bash
cd /var/www
git clone <repo> simpleorder
cd simpleorder
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### 5. Environment
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` and fill in:
- `APP_URL=https://simpleorder.com`
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
- `RESEND_API_KEY`
- `GOOGLE_MAPS_API_KEY`
- Reverb credentials

### 6. Create central database
```bash
mysql -u root -p -e "CREATE DATABASE simpleorder_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 7. Run central migrations & seed plans
```bash
php artisan migrate --path=database/migrations/central
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=PlatformAdminSeeder
```

### 8. Storage
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Queue worker (add to crontab)
```bash
# /etc/cron.d/simpleorder
* * * * * www-data cd /var/www/simpleorder && php artisan schedule:run >> /dev/null 2>&1
```
For production use Supervisor to keep queue worker running:
```ini
[program:simpleorder-worker]
command=php /var/www/simpleorder/artisan queue:work database --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=2
```

### 10. Laravel Reverb (WebSockets)
```bash
php artisan reverb:start --daemon
```
Or via Supervisor:
```ini
[program:simpleorder-reverb]
command=php /var/www/simpleorder/artisan reverb:start
autostart=true
autorestart=true
user=www-data
```

### 11. Stripe Webhook
Register webhook at https://dashboard.stripe.com/webhooks pointing to:
`https://simpleorder.com/webhook/stripe`

Events to send:
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `invoice.payment_failed`
- `invoice.payment_succeeded`

### 12. Stripe Connect
Register your platform at https://dashboard.stripe.com/settings/connect
Set OAuth redirect to: `https://simpleorder.com/platform/stripe/callback`
Add `STRIPE_CLIENT_ID` to `.env`

---

## First Tenant

Visit `https://simpleorder.com/register` to create your first tenant.
- Choose a subdomain (e.g. `mytacoplace`)
- Select a plan
- Your store will be at `https://mytacoplace.simpleorder.com`

## Custom Domains (per tenant)

Tenant sets a CNAME: `order.theirdomain.com → simpleorder.com`
Add the domain in the tenant settings panel, then add to Apache:
```apache
ServerAlias order.theirdomain.com
```
Or use a wildcard SSL + dynamic vhost approach with `mod_vhost_alias`.

---

## Key URLs

| URL | Purpose |
|-----|---------|
| `simpleorder.com` | Marketing / signup |
| `simpleorder.com/login` | Tenant owner login |
| `simpleorder.com/dashboard` | Tenant dashboard (billing, Stripe Connect) |
| `simpleorder.com/superadmin` | Platform super admin |
| `{tenant}.simpleorder.com` | Customer ordering storefront |
| `{tenant}.simpleorder.com/admin` | Tenant admin panel (orders, menu, settings, reports) |

---

## Architecture Summary

```
Central DB: simpleorder_central
  - tenants, plans, domains, platform_admins

Per-Tenant DB: simpleorder_tenant_{slug}
  - users, items, categories, item_options, item_option_values
  - carts, cart_items, cart_item_options, cart_item_option_values
  - orders, order_items, order_item_options, order_item_option_values
  - settings, media, pages, address_geocodes
  - jobs, failed_jobs, sessions
```
