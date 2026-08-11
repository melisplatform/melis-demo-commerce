# Melis Demo Commerce

A ready-made demo e-commerce **site** for the [Melis Platform](https://www.melistechnology.com/).
It is the commerce counterpart of `melis-demo-cms`: a full storefront (catalog, product/variant
pages, cart, checkout, account, orders) plus a seeded demo catalog (products, variants, categories,
attributes, prices, stock, a coupon, and sample clients/orders).

This module is a *site* module — it is referenced from `vendor/` and registered with the platform,
then its demo data is installed through a one-time **setup wizard**.

---

## Requirements

Before installing this module you need a **working Melis Platform** with:

- PHP **8.3** or **8.5**
- A platform already installed (database created, the `melis_core_platform` row present, and the
  DbDeploy migrations run). Visiting `/melis` should give you a working back office.
- The **MelisCommerce** engine installed and its DbDeploy run, so the `melis_ecom_*` tables and the
  commerce reference data (countries, currencies, commerce languages, attribute types, document
  types) already exist.
- These dependency modules installed (pulled automatically by Composer):
  `melis-commerce`, `melis-cms`, `melis-cms-slider`, `melis-cms-prospects`, `melis-cms-news`,
  `melis-cms-page-script-editor`.

> The setup wizard **adds a new site** to your platform. It does not overwrite existing sites, and it
> resolves all database IDs at runtime — so it can be installed onto a platform that already hosts
> other sites (e.g. the CMS demo).

---

## How the install works (in short)

1. Composer places the module in `vendor/melisplatform/melis-demo-commerce` and records its
   `extra.module-name` so the platform can find it.
2. You register the module so the platform loads it.
3. You open `/MelisDemoCommerce/setup` on a domain that is **not yet bound to another site**.
4. The wizard runs 15 steps, creating the site, pages, templates, sliders, news, the commerce
   catalog (attributes → products → categories → variants → prices → stock), a coupon and sample
   clients/orders. Unknown IDs are resolved as rows are inserted and written back into the site's
   generated config files (the `[:token]` placeholders in `config/*.dist`).

---

## Manual installation

### 1. Get the module with Composer

If it is already listed in your project's `composer.json`, just install dependencies:

```bash
composer install
```

Otherwise add it:

```bash
composer require melisplatform/melis-demo-commerce
```

If you edited the module after installing (e.g. pulled a branch), refresh Composer's autoload so the
`Module` class and namespace map are picked up:

```bash
composer dump-autoload
```

### 2. Register the module's path with the platform

This is a **site** module — it is loaded for its own front domain through the vhost's
`MELIS_MODULE` env (step 4), **not** through the back-office module list.

> ⚠️ **Do NOT add `MelisDemoCommerce` to `config/melis.module.load.php`.** That file is the
> **back-office** module list; adding a site module there makes the platform treat every
> `/MelisDemoCommerce/*` URL as a back-office request and redirect it to `/melis/login`, so the
> setup wizard becomes unreachable.

The module only needs to be in the **path map** so the platform can locate it. Add it to
`config/melis.modules.path.php`:

```php
'MelisDemoCommerce' => '/vendor/melisplatform/melis-demo-commerce',
```

(Or simply delete `config/melis.modules.path.php` — it is regenerated on the next request from the
installed packages, picking up this module via its `composer.json` `extra.module-name`.) Then clear
the platform cache:

```bash
rm -rf cache/meliscore_platform_cache-* cache/config/*
```

### 3. Make the required paths writable

The wizard copies product/category images and writes the site's generated config files, so these
must be writable by the web server:

- `public/media/` (the wizard creates `public/media/commerce/` inside it)
- `vendor/melisplatform/melis-demo-commerce/config/` (it generates `module.config.php`,
  `melis.plugins.config.php` and `MelisDemoCommerce.config.php` from the `*.dist` templates)

### 4. Set up a domain / virtual host

Point a virtual host at the project's `public/` directory using a domain that is **not already used
by another site** on this platform (the front engine routes by domain — a domain bound to another
site will intercept `/MelisDemoCommerce/setup`). Declare the platform and this module via env vars:

```apache
<VirtualHost *:80>
    DocumentRoot "/path/to/project/public"
    ServerName www.mycommerce.local

    <Directory "/path/to/project/public">
        Options -Indexes +FollowSymLinks +ExecCGI
        DirectoryIndex index.php
        AllowOverride All
        Require all granted
    </Directory>

    SetEnv MELIS_PLATFORM "local"            # your platform/environment name
    SetEnv MELIS_MODULE   "MelisDemoCommerce"
</VirtualHost>
```

Add the domain to your hosts file:

```
127.0.0.1   www.mycommerce.local
```

Restart/reload the web server.

### 5. Run the setup wizard

Open:

```
http://www.mycommerce.local/MelisDemoCommerce/setup
```

Fill in:

- **Protocol** – `http` or `https`
- **Site Domain** – the domain you just configured (e.g. `www.mycommerce.local`)
- **Site Label** – a display name (e.g. `My Commerce Site`)

Click **Start setup**. The wizard runs these steps automatically (the *products* and *variants*
steps are large and take longest — let it finish):

`site → templates → pages → sliders → news → prospects_theme → document_types → attributes →
product_text_types → products → categories → variants → coupons → client_and_orders →
setup_main_page`

When you see **“MelisDemoCommerce setup completed.”** the install is done.

### 6. Verify

Browse to `http://www.mycommerce.local/` — you should see the demo storefront with categories,
products and prices.

---

## Re-running the install

Each run regenerates the site's config files from the `*.dist` templates, but it does **not** delete
data it previously inserted — re-running on the same database will create duplicate rows. To start
over cleanly, restore the database to its pre-install state first, then run the wizard again.

---

## Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `/MelisDemoCommerce/setup` redirects to `/melis/login` | `MelisDemoCommerce` was added to `config/melis.module.load.php` (the back-office list) — remove it. A site module is loaded via the vhost's `MELIS_MODULE`, not that list. |
| Blank page / 404 at `/MelisDemoCommerce/setup` | The domain is already bound to another site (use an unbound domain), or `MELIS_MODULE` isn't set on the vhost. Clear `cache/`. |
| `Access permission denied … make … writable` | Make `public/media/` and the module's `config/` directory writable. |
| `Current Platform "" has no data on database` | `MELIS_PLATFORM` isn't set for this vhost, or the platform isn't installed. Set `SetEnv MELIS_PLATFORM`. |
| Module won't initialize / class not found | Run `composer dump-autoload`. |
| Setup stops on `client_and_orders` | `MELIS_MODULE` isn't set for the vhost — add `SetEnv MELIS_MODULE "MelisDemoCommerce"`. |

---

## Support

* Email: contact@melistechnology.com
* Source: https://github.com/melisplatform/melis-demo-commerce
