# Gavitral Landing Page

A Laravel 13 product landing page with a database-backed order submission system.

## Local setup

Use PHP 8.3 for both the CLI and web server. Composer resolves dependencies against
PHP 8.3.0 so updates made on newer PHP versions remain compatible with PHP 8.3.
On machines with multiple PHP versions, run Composer and Artisan with `php8.3`.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

After configuring a MySQL or SQLite connection in `.env`:

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

Enable the PDO driver for your database (`pdo_mysql` or `pdo_sqlite`). The test
suite uses SQLite and requires `pdo_sqlite` and `sqlite3` for PHP 8.3.
Verify the runtime requirements and run the test suite:

```bash
composer check-platform-reqs
php artisan test
```

Orders are stored in the `orders` table with a `pending` status. The form endpoint is `POST /orders` and includes CSRF protection, English validation messages, request throttling, and server-side price calculation.

## Website content and responsive appearance

Use **Admin → Site Settings** to edit the storefront. Changes are saved in the database; Git pushes do not copy these settings to another server.

| Settings page | Controls |
| --- | --- |
| General Settings | Site name/logo, favicon, contact details, currency, brand colors, base/mobile font sizes, footer and mobile order bar |
| Social Media | WhatsApp destination, label and visibility |
| Individual sections | Copy, available images, visibility, desktop/mobile spacing, background images, background position/size, heading and description colors/sizes |
| Customer Reviews / Video Reviews | Review content or video links, autoplay, slide interval (2–60 seconds) and control labels |
| Video Section | YouTube/Shorts or direct video URL, upload and preview |
| Order Form | Checkout labels, delivery charges, confirmation popup text/image/phone and recommendation labels |
| Products / Modal | Product names, prices, badges, images, order and availability |
| SEO Settings | Page metadata and social sharing image |

Leave optional section appearance fields blank to retain the default design. A mobile background image overrides the desktop background only below 768px. Each video review URL goes on its own line. Button links accept an `https://` URL, a local `/path`, or a section anchor such as `#Order`.

The storefront and admin use responsive layouts; wide order tables scroll inside their panels. The admin navigation becomes a keyboard-accessible drawer on tablet and mobile screens.

## Automatic order risk detection

New storefront orders store a risk score and the matching reasons. Each rule adds
its points once per order:

| Rule | Points |
| --- | --- |
| Any earlier order with the same normalized phone | 40 |
| Any earlier order from the same IP within 30 minutes | 25 |
| Same phone ordered within 2 minutes | 15 |
| Name or address differs from that phone's latest order | 20 |
| Phone has an order previously marked Fake | 100 |

Names and addresses are compared ignoring case and repeated whitespace. The time
windows include their boundaries. Orders scoring at least
`ORDER_FAKE_RISK_THRESHOLD` (default `70`) are saved with status `fake`; lower
scores stay `pending`. The admin order tables show scores and reasons, and admins
can review and change status. Previously marked Fake history is retained even if
that order's status is later changed. Deleting the order deletes its history.
Existing orders keep their status; historical Fake orders are recorded by the
migration. Run migrations when deploying this feature.

## GitHub webhook deployment

### Live server setup

Point the domain's document root to this project's `public/` directory. Use PHP
8.3 or newer with the Composer-required extensions and your database's PDO driver.
Use `.env.production.example` as a reference for the server's `.env`: set the real
HTTPS domain, database credentials, `APP_ENV=production`, `APP_DEBUG=false`, and
`SESSION_SECURE_COOKIE=true`. Keep the existing `APP_KEY` when updating a live
installation; generate a key only for a new installation. Keep `SESSION_DOMAIN=null`
for host-only cookies. For video uploads, configure PHP `upload_max_filesize=50M`
and `post_max_size=64M` and the web server's request body limit accordingly.

After uploading/pulling the project, run from its root:

```bash
composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader
php artisan optimize:clear
php artisan package:discover --ansi
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan app:production-check
```

The check command is read-only and returns a failure status for incomplete hosting
configuration. The web server user needs write access to `storage/` and
`bootstrap/cache/`. Do not upload a local `public/hot` file. The storefront/admin
use committed `public/asset` files, so these pages do not require a Node build.

Back up the live database and uploads before updating. Preserve the server's
`.env` and `storage/app/public/`; deploy migrations without `migrate:fresh` or
reseeding. Local content changes and uploaded images are not copied by Git:
edit live Site Settings or explicitly transfer the intended settings/uploads.
Finally check `/up`, login, uploaded images, videos and a test checkout on the
real HTTPS domain. A passing local test is not a live-server verification.

The webhook endpoint validates GitHub's `X-Hub-Signature-256` signature using the
secret embedded in `deploy.php`; it does not require server `.env` configuration.
It accepts only pushes from `Shobahan758/s` to `master`. The web server user must
have the required permissions for project files, the `storage` directory, and the
Git SSH key.

In the GitHub repository's **Settings → Webhooks → Add webhook** form:

- Payload URL: `https://your-domain.com/deploy.php`
- Content type: `application/json`
- Secret: use the exact `DEPLOY_WEBHOOK_SECRET` value from `deploy.php`
- SSL verification: enabled
- Event: just the push event

On every push to `master`, the endpoint fetches and resets the checkout to
`origin/master`, then runs a production
Composer install, database migrations, the public storage link, and Laravel optimization. Results are
written to `storage/logs/deploy.log`.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Site tracking

Site Tracking supports enabling/disabling each provider while retaining its saved
IDs. Blank IDs disable that integration. GA4 uses a `G-` measurement ID; GTM uses
`GTM-`; Google Ads uses `AW-` plus a purchase conversion label. Other Tracking
loads Clarity, LinkedIn, Pinterest, Snapchat and the configured custom snippets.

Browser events include product views and checkout starts. Saved, non-Fake orders
return a server-calculated conversion payload (BDT, product value, shipping,
quantity, and transaction ID). The browser queues one purchase per order in the
current session; failed/Fake orders do not generate purchase conversions. No
customer name, phone or address is included in the conversion payload. This
cash-on-delivery conversion records an order being placed, not payment collected.

Meta/TikTok receive ViewContent, InitiateCheckout and Purchase. GA4 receives
view_item, begin_checkout and purchase; Google Ads receives conversion when both
ID and label are configured. GTM receives store_view_item, store_begin_checkout
and store_purchase, with ecommerce data; configure GTM triggers explicitly and
avoid installing duplicate tags for a directly configured GA4 property.

Visitor Tracking shows local visitor/activity counts and supports activity-period
filtering. Browser requests cannot create order_completed events; the checkout
backend owns this event. Provider delivery must be verified with the account's
Test Events/Realtime tools after saving real IDs and deploying.

Event references: [GA4 ecommerce](https://developers.google.com/analytics/devguides/collection/ga4/ecommerce),
[TikTok standard events](https://ads.tiktok.com/help/article/standard-events-parameters?lang=en),
[Meta's official event mapping](https://github.com/facebook/GoogleTagManager-WebTemplate-For-FacebookPixel/blob/main/template.tpl).
