# Boachat / Advanced Ecommerce — Application Functionality

This document describes the application **by functional area**: what it does, which routes and code power it, and how pieces connect. It is intended for developers and operators deploying or extending the project.

---

## 1. Technology stack

| Layer | Technology |
|-------|------------|
| Backend | **Laravel 12** (PHP 8.2+) |
| Frontend | **Vue 3** + **Inertia.js** + **TypeScript** |
| Styling | **Tailwind CSS** v4 |
| Auth | **Laravel Fortify** (login, register, password reset, email verification, optional 2FA) |
| Payments | **Stripe** (Payment Intents on checkout; webhooks) |
| Background work | **Laravel Queues** + **Horizon** (Redis) for jobs |
| Routing helpers | **Laravel Wayfinder** (generated TS under `resources/js/actions` and `resources/js/routes`; gitignored, regenerate with `php artisan wayfinder:generate`) |

---

## 2. High-level architecture

```text
Browser (Vue/Inertia pages)
    ↔ Laravel web routes (web.php, settings.php)
        ↔ Controllers → Models / Jobs / Stripe / Cache
            ↔ SQLite (or other DB) + session + queue backend
```

- **Storefront** (`/`, `/products`, `/cart`, checkout, orders): public or `auth` + `verified` where required.
- **Account area** (`/dashboard`, `/settings/*`): authenticated user.
- **Shop admin** (`/admin/*`): authenticated + `verified` + **`shop.admin`** middleware (email allowlist).
- **Stripe webhook** (`POST /stripe/webhook`): CSRF-exempt; verifies Stripe signature.

---

## 3. Functional areas

### 3.1 Branding & global UI

| Concern | Details |
|---------|---------|
| App name | `config('app.name')` from `APP_NAME` (default **Boachat** in `config/app.php`). Shared to Inertia as `name` in `HandleInertiaRequests`. |
| Document title | `resources/views/app.blade.php` uses `config('app.name')` for `<title>`. |
| Shop header/footer | `resources/js/layouts/ShopLayout.vue` — store nav, cart badge, flash messages, footer. |
| Dashboard shell logo | `AppLogo.vue`, `AppLogoIcon.vue` — sidebar/header branding for the **Fortify dashboard** layout (not the public shop header text). |
| Welcome page | `resources/js/pages/Welcome.vue` — marketing-style entry; links to store and auth only. |

**Shared Inertia props** (`app/Http/Middleware/HandleInertiaRequests.php`):

- `name` — app name  
- `auth.user` — current user or null  
- `sidebarOpen` — UI preference cookie  
- `cartCount` — session-backed cart line count  
- `isShopAdmin` — from `User::isShopAdmin()`  
- `shop.currency` — from `config('shop.currency')`  
- `flash.success` / `flash.error` — one-off messages  

---

### 3.2 Authentication & account security

Powered by **Fortify** (see `app/Providers/FortifyServiceProvider.php`, `config/fortify.php`).

| Feature | Typical routes | Notes |
|---------|----------------|-------|
| Login / logout | Fortify routes | Inertia pages under `resources/js/pages/auth/`. |
| Registration | Fortify | `CreateNewUser` creates a linked **Customer** profile after signup (`app/Actions/Fortify/CreateNewUser.php`). |
| Password reset | Fortify | |
| Email verification | Fortify | `verified` middleware on checkout, orders, admin. |
| Two-factor (optional) | `/settings/two-factor` | `TwoFactorAuthenticationController`, Vue `settings/TwoFactor.vue`. |
| Profile & password | `routes/settings.php` | `ProfileController`, `PasswordController`; Vue `settings/Profile.vue`, `settings/Password.vue`. |
| Appearance (theme) | `GET settings/appearance` | Inertia page `settings/Appearance.vue`; `HandleAppearance` middleware. |

**User model** (`app/Models/User.php`):

- `orders()` — purchases  
- `customer()` — CRM-style profile (address, spend stats)  
- `isShopAdmin()` — compares email to `config('shop.admin_emails')` (case-insensitive)  

---

### 3.3 Storefront — home & catalog

| Route | Controller | Inertia page | Purpose |
|-------|------------|--------------|---------|
| `GET /` | `HomeController` | `Store/Home.vue` | Featured products, categories, CTAs. |
| `GET /products` | `ProductController@index` | `Products/Index.vue` | Paginated/filtered product list; uses caching (see §6). |
| `GET /products/{slug}` | `ProductController@show` | `Products/Show.vue` | Product detail, reviews, add-to-cart. |

**Models**: `Product`, `Category`, `Review`.

**Caching**: product list and detail keys managed via `App\Support\ShopCache` (see §6).

---

### 3.4 Shopping cart

Cart is **session-backed** (not necessarily a DB row per request; implementation lives with cart resolution in `CartController` / `Cart` model).

| Route | Method | Controller | Purpose |
|-------|--------|------------|---------|
| `/cart` | GET | `CartController@index` | `Cart/Index.vue` — view cart. |
| `/cart/add` | POST | `CartController@add` | Add line item. |
| `/cart/update` | PATCH | `CartController@update` | Change quantities. |
| `/cart/remove` | DELETE | `CartController@remove` | Remove line. |
| `/cart/clear` | POST | `CartController@clear` | Empty cart. |

**UX**: `cartCount` in shared props updates header badge after navigation.

---

### 3.5 Checkout & payments (Stripe)

Requires **authenticated + verified** user.

| Route | Method | Controller | Page |
|-------|--------|------------|------|
| `/checkout` | GET | `CheckoutController@index` | `Checkout/Index.vue` |
| `/checkout` | POST | `CheckoutController@process` | Creates order after payment |
| `/checkout/success/{order}` | GET | `CheckoutController@success` | `Checkout/Success.vue` |

**Flow (summary)**:

1. **GET checkout** — Ensures non-empty cart and logged-in user; creates Stripe **PaymentIntent**; returns `clientSecret` and `stripeKey` to the frontend for Stripe.js / Elements.  
2. **POST checkout** — Validates addresses and `payment_intent_id`; verifies PaymentIntent with Stripe; wraps order creation in a DB transaction; dispatches **`ProcessOrder`** job; clears cart; redirects to success.

**Configuration**: `config/services.php` → `STRIPE_KEY`, `STRIPE_SECRET`; `config/shop.php` → `currency`, `tax_rate` for totals.

**Webhook**: `POST /stripe/webhook` → `StripeWebhookController` (signature-verified; CSRF excluded in `bootstrap/app.php`).

**Related models**: `Order`, `OrderItem`, `Customer` (stats may be updated on order completion — confirm in `CheckoutController` / jobs).

---

### 3.6 Customer orders (post-purchase)

| Route | Controller | Page |
|-------|------------|------|
| `GET /orders` | `OrderController@index` | `Orders/Index.vue` |
| `GET /orders/{orderNumber}` | `OrderController@show` | `Orders/Show.vue` |

Authenticated + verified. Shows the signed-in user’s orders.

---

### 3.7 Product reviews

| Route | Method | Controller | Middleware |
|-------|--------|------------|------------|
| `POST /reviews` | POST | `ReviewController@store` | `auth`, `verified` |

Customers submit reviews (validated against purchase rules in controller/model as implemented).

**Admin moderation**: see §3.9 (`/admin/reviews`).

---

### 3.8 Shop admin (`/admin`)

**Access**: `middleware(['auth', 'verified', 'shop.admin'])`.  
**Allowlist**: `SHOP_ADMIN_EMAILS` in `.env` (comma-separated). In `local` / `testing`, empty `SHOP_ADMIN_EMAILS` falls back to `admin@example.com` in `config/shop.php`.  
**Middleware**: `EnsureShopAdmin` — non-admins redirected to `dashboard` with flash error.

| Area | Routes (prefix `/admin`, name `admin.*`) | Controller | Typical Vue pages |
|------|--------------------------------------------|------------|-------------------|
| Dashboard | `GET /admin/` | `Admin\DashboardController` | `Admin/Dashboard.vue` |
| Products | RESTful `products` (no show) | `Admin\ProductController` | `Admin/Products/*` |
| Categories | RESTful `categories` (no show) | `Admin\CategoryController` | `Admin/Categories/*` |
| Orders | `GET orders`, `GET orders/{order}`, `PATCH orders/{order}` | `Admin\OrderController` | `Admin/Orders/*` |
| Accounts (users) | `GET users`, `GET users/{user}`, `PATCH users/{user}` | `Admin\UserController` | `Admin/Users/*` |
| Customers | `GET customers`, `POST customers`, `GET customers/{customer}`, `PATCH customers/{customer}` | `Admin\CustomerController` | `Admin/Customers/*` |
| Reviews | `GET reviews`, `PATCH reviews/{review}`, `DELETE reviews/{review}` | `Admin\ReviewController` | `Admin/Reviews/Index.vue` |

**Product images**: Admin can paste URL lines and/or upload files; files stored on `public` disk under `products/`; merged into `images` JSON. Run `php artisan storage:link` on the server.

**Layout**: `AdminLayout.vue`, sidebar/header links in `AppSidebar.vue`, `AppHeader.vue` (shop admin block when `isShopAdmin`).

---

### 3.9 User dashboard (non-admin app shell)

| Route | Handler | Page |
|-------|---------|------|
| `GET /dashboard` | Inertia route | `Dashboard.vue` |

This is the **default authenticated landing** from the starter kit (separate from `/admin`). Shop admins may use both `/dashboard` and `/admin`.

---

## 4. Data model (core entities)

| Model | Role |
|-------|------|
| `User` | Authentication; `orders`; `customer`; `isShopAdmin()`. |
| `Customer` | Profile linked to `user_id` (phone, address, totals); used at checkout / CRM. |
| `Category` | Product taxonomy; slug, sort order, optional image. |
| `Product` | Sellable item: pricing, SKU, inventory flags, `images` JSON, HTML description, `category_id`. |
| `Order` | Placed order; line items; status; ties to user/customer as implemented. |
| `OrderItem` | Single line on an order (product snapshot). |
| `Review` | Product review; moderation in admin. |
| `Cart` | Session cart representation (see cart implementation). |

**Factories / seeding**: `database/seeders/DatabaseSeeder.php` creates admin + demo users, Nigerian grocery **categories** and **sample products** (names + image URLs). Run `php artisan migrate:fresh --seed` only when you can wipe the database.

---

## 5. Background jobs & notifications

Jobs live in `app/Jobs/`. Examples:

| Job | Typical purpose |
|-----|-----------------|
| `ProcessOrder` | Post-checkout order processing pipeline. |
| `SendOrderConfirmation` | Email to customer. |
| `SendShippingNotification` | Shipping updates. |
| `NotifyAdmin` | Admin alerts. |
| `NotifyLowStock` | Triggered from product model logic when stock is low. |
| `SendReviewRequest` | Ask for reviews after delivery. |
| `GenerateProductReport` | Reporting / cache (Redis). |
| `ProcessBulkProductImport` | Bulk import pipeline. |

**Horizon**: Redis-backed queue dashboard (routes under `/horizon` when installed/configured). Ensure `QUEUE_CONNECTION` and Redis match your environment.

---

## 6. Caching (`ShopCache`)

`App\Support\ShopCache`:

- **`bumpProductListVersion()`** — invalidates logical product list cache by incrementing a version key.  
- **`productListCacheKey($url)`** — versioned key for list pages.  
- **`forgetProductDetail($slug)`** — forgets detail + related keys when products change.

Admin product/category mutations call these helpers so storefront lists stay fresh.

---

## 7. Configuration reference (environment)

| Variable | Purpose |
|----------|---------|
| `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_DEBUG`, `APP_KEY` | Core Laravel. |
| `VITE_APP_NAME` | Usually `${APP_NAME}` for frontend title defaults. |
| `DB_*` | Database (project ships with SQLite-friendly defaults in `.env.example`). |
| `SESSION_*` | Session driver (often `database` in example). |
| `QUEUE_CONNECTION` | `database` or `redis` for jobs. |
| `REDIS_*` | If using Redis / Horizon. |
| `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` | Stripe Checkout + webhooks. |
| `SHOP_CURRENCY`, `SHOP_TAX_RATE` | Checkout totals. |
| `SHOP_ADMIN_EMAILS` | Comma-separated emails allowed into `/admin`. |

See `.env.example` for the full template.

---

## 8. Frontend structure (Inertia pages)

| Path | Role |
|------|------|
| `resources/js/pages/Store/` | Public shop home. |
| `resources/js/pages/Products/` | Catalog list + detail. |
| `resources/js/pages/Cart/` | Cart UI. |
| `resources/js/pages/Checkout/` | Payment + addresses. |
| `resources/js/pages/Orders/` | Customer order history. |
| `resources/js/pages/Admin/` | Shop admin CRUD and dashboards. |
| `resources/js/pages/auth/` | Login, register, etc. |
| `resources/js/pages/settings/` | Profile, password, 2FA, appearance. |
| `resources/js/layouts/` | `AppLayout` (dashboard), `AdminLayout`, `ShopLayout`, auth layouts. |
| `resources/js/components/` | Reusable UI (sidebar, product card, pagination, etc.). |

---

## 9. HTTP / security notes

- **CSRF**: Web routes use CSRF; **`stripe/webhook`** is excluded from verification (Stripe signs payloads).  
- **Middleware stack** (`bootstrap/app.php`): `HandleAppearance`, `HandleInertiaRequests`, `AddLinkHeadersForPreloadedAssets` on `web`.  
- **Health check**: `GET /up` (Laravel default).  

---

## 10. Operational checklist (deploy / maintain)

1. Copy `.env.example` → `.env`, set `APP_KEY`, `APP_URL`, database, Redis (if used), Stripe, mail.  
2. `composer install` (production: `--no-dev --optimize-autoloader`).  
3. `npm ci && npm run build` (do not deploy `public/hot` from dev machines).  
4. `php artisan migrate --force`  
5. `php artisan storage:link`  
6. `php artisan config:cache` + `route:cache` + `view:cache` in production.  
7. Run queue workers (or Horizon) if jobs must execute asynchronously.  
8. Configure Stripe webhook endpoint to your live `POST /stripe/webhook` URL.  
9. Set `SHOP_ADMIN_EMAILS` to real admin addresses in production.  

---

## 11. Route map (quick reference)

Defined in `routes/web.php` (unless noted):

- **Public**: `/`, `/products`, `/products/{slug}`, `/cart` + cart mutations, `POST /stripe/webhook`.  
- **Auth + verified**: checkout, orders, `POST /reviews`.  
- **Auth + verified + shop.admin**: `/admin/*` resource and custom routes.  
- **Auth**: `routes/settings.php` — profile (partial), password, appearance, 2FA.  
- **Auth + verified**: `GET /dashboard` (Inertia).  

---

## 12. Further reading in-repo

- `config/shop.php` — currency, tax, admin email resolution.  
- `app/Http/Controllers/CheckoutController.php` — full payment + order creation rules.  
- `database/seeders/DatabaseSeeder.php` — sample Nigerian grocery catalog.  

---

*Document generated for the Boachat / advanced-ecommerce codebase. Update this file when you add major features or change route structure.*
