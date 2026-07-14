# NASHAAD-Style POS/ERP — CodeIgniter 4 Build

Custom CI4 + MySQL system for a restaurant/retail client. Built on the actual
CI4 framework (pulled from GitHub's `codeload.github.com`, not Packagist — see
note below), tested end-to-end in a sandboxed environment against SQLite
before this handoff.

## What's done (Days 1-3 — tested, working)

**Day 1**: migrations, seeder, auth filter, permissions, Items/Categories/Units
CRUD, Dashboard.

**Day 2**: Brands (mirrors Units), Stock Manager with a full audit trail
(`stock_adjustments` table via `StockAdjustmentModel::record()` — the single
entry point every module uses for stock movement, so nothing touches
`current_stock` without leaving a paper trail), dedicated Stock Alert page,
Print Labels with real CODE128 barcodes (JsBarcode via CDN) on a print-ready
sheet.

**Day 3**: Suppliers CRUD, Purchases with a dynamic line-item cart (vanilla
JS — add/remove rows, live tax/discount/total calculation), Purchase detail
view, Purchase Returns (select an original purchase, its lines load via a
small JSON endpoint, choose return quantities). Every purchase line posts a
stock-IN movement; every return line posts a stock-OUT movement — both
through the same `StockAdjustmentModel` from Day 2, so Stock Manager's
history shows purchases and returns alongside manual corrections.

**Verified live in the sandbox** (not just written): full purchase flow —
supplier → purchase with 30 units @ 0.75 + 16% tax → confirmed the line
total (22.50 → 26.10 with tax) and grand total matched by hand; confirmed
stock went 5 → 25 (manual +20) → 55 (purchase +30) → 50 (return -5); confirmed
`items.purchase_price` synced to the latest purchase cost; confirmed the
Stock Manager audit trail shows all three movement types with correct
reasons and notes.

## A bug worth knowing about (already fixed here)

CI4's `$this->request->getMethod()` **always returns uppercase** (`'POST'`,
not `'post'`). Every controller here checks `=== 'POST'`. If you or another
dev writes new controllers and checks lowercase `'post'`, the form-submit
branch will silently never fire — you'll just see the empty form re-render
with no error, which is a nasty one to debug blind. Worth a comment in any
new controller you write.

## Composer note (read before you panic)

This sandbox can't reach `getcomposer.org` or `packagist.org`, so I built
this without a `vendor/` folder — CI4's own copies of `Psr\Log` and
`Laminas\Escaper` under `system/ThirdParty/` are registered manually in
`app/Config/Autoload.php`. **This is a sandbox-only workaround.** Your real
cPanel server almost certainly has normal Composer/internet access, so once
you upload this, you can run a real `composer install` there and it'll pick
up an actual `vendor/autoload.php` — CI4 tolerates both paths fine, the
manual namespace registration doesn't conflict with a later real Composer
install.

## Deploy steps

1. Upload everything to your server (outside webroot ideally, with `public/`
   as the document root — or symlink/point your domain at `public/` directly
   if that's not possible on shared cPanel hosting).
2. `composer install` if you have Composer access on the server (recommended,
   replaces the manual ThirdParty namespace workaround with the real thing —
   harmless either way).
3. Set real DB credentials in `.env` (`database.default.*` block).
4. Set `app.baseURL` in `.env` to your real domain.
5. `php spark migrate --all`
6. `php spark db:seed InitialDataSeeder`
7. Log in at `/login` with `admin` / `Admin@123`, change the password immediately.
8. Set `CI_ENVIRONMENT = production` in `.env` before going live (currently
   left at whatever the framework default is — check `app.php`/`.env`).

## New migrations since Day 1 (run these on your live server)

If your server DB already has the Day 1 tables migrated, just run migrate
again — CI4 tracks what's already applied and only runs the new ones:
```bash
php spark migrate --all
```
This picks up `stock_adjustments`, `suppliers`, `purchases`, `purchase_items`,
`purchase_returns`, `purchase_return_items` — nothing destructive to existing data.

## Day-by-day roadmap (6 days total)

**Day 1 — DONE, tested**: migrations, seeder, auth filter, permissions,
Items/Categories/Units CRUD, Dashboard.

**Day 2 — DONE, tested**: Brands, Stock Manager, Stock Alert, Print Labels.

**Day 3 — DONE, tested**: Suppliers, Purchases (with tax/discount math),
Purchase Returns.

**Day 4 — POS & Sales**
- `sales` + `sale_items` + `customers` migrations
- POS screen: category tabs, item grid, cart, hold/recall
  (`sales.status = 'hold'`), discount, payment; checkout calls
  `StockAdjustmentModel::record($item, $qty, 'out', 'sale', ...)` per line —
  reuse the exact same model Purchase already uses, don't touch
  `ItemModel::adjustStock()` directly.
- Invoice numbering: `INV/YYYY/NNNNN` sequence — same pattern as your
  existing `RCP/YYYY/NNNNN` receipt design, reuse that logic here.

**Day 5 — Accounting**
- `accounts_type`, `sub_accounts_type`, `chart_of_accounts` migrations
  (3-tier structure matching the reference screenshots)
- `money_transactions` migration — every sale/purchase auto-posts a
  transaction row against the right GL account, so the UI stays simple
  while the ledger underneath is real double-entry-ready.

**Day 6 — Dashboard polish, Reports, Deploy**
- Sales/purchase/expense bar chart, top-5 fast movers, pending
  sales/accounts-receivable table
- Issued Products / Damaged Products (both call
  `StockAdjustmentModel::record(..., 'out', 'issued'|'damaged', ...)`)
- Full regression pass, deploy to client's live server, credential handover.


## Explicitly deferred (not in this 6-day build)

Manufacturing module, multi-branch UI switching, Happy Hour/promo engine,
Credit Aging Summary, Advance/Deposits, thermal receipt printing (you already
have the `72mm 200mm` `@page` fix from prior work — port it over once POS
is stable).
