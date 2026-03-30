# MyAdmin Google Checkout Payments Plugin

Composer plugin package for the MyAdmin billing platform. Integrates Google Checkout payment gateway via event-driven hooks.

## Commands

```bash
composer install                          # install deps including phpunit/phpunit ^9.6
vendor/bin/phpunit tests/ -v   # run tests
vendor/bin/phpunit tests/ -v --coverage-clover coverage.xml --whitelist src/  # with coverage
```

## Architecture

**Namespace:** `Detain\MyAdminGooglecheckout\` → `src/` · **Tests:** `Detain\MyAdminGooglecheckout\Tests\` → `tests/`

**CI/IDE:** `.github/` contains CI/CD workflows for automated testing (`.github/workflows/tests.yml`) · `.idea/` contains IDE configuration including `inspectionProfiles/`, `deployment.xml`, and `encodings.xml`

**Entry:** `src/Plugin.php` — registers Symfony `EventDispatcher` hooks via `getHooks()`:
- `system.settings` → `getSettings()` — adds `google_checkout_*` settings via `add_text_setting()`, `add_password_setting()`, `add_radio_setting()`
- `function.requirements` → `getRequirements()` — registers page functions via `add_page_requirement()`

**Page functions** (procedural, loaded on demand):
- `src/pay_balance_google.php` — `pay_balance_google()`: builds Google Checkout cart via cURL against `invoices` table, handles sandbox/live toggle via `GOOGLE_CHECKOUT_SANDBOX`
- `src/view_google_order.php` — `view_google_order()`: looks up `gcheckout` table by `google_order`, redirects
- `src/view_google_transaction.php` — `view_google_transaction()`: admin-only transaction viewer querying `gcheckout` table

**DB pattern** (use throughout, never PDO):
```php
$db = get_module_db($module);                        // module-scoped DB
$db->query("SELECT * FROM gcheckout WHERE google_order='{$transaction}'", __LINE__, __FILE__);
$db->real_escape($userInput);                        // escape before interpolation
if ($db->num_rows() > 0) {
    $db->next_record(MYSQL_ASSOC);
    $row = $db->Record;
}
```

**cURL / Google API pattern** (in `src/pay_balance_google.php`):
```php
$goptions = [
    CURLOPT_USERPWD => GOOGLE_CHECKOUT_MERCHANT_ID.':'.GOOGLE_CHECKOUT_MERCHANT_KEY,
    CURLOPT_POST => true,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC
];
$gresponse = getcurlpage('https://checkout.google.com/api/checkout/v2/merchantCheckoutForm/Merchant/'.GOOGLE_CHECKOUT_MERCHANT_ID, $gpost, $goptions);
```

**Settings constants** registered in `getSettings()`: `GOOGLE_CHECKOUT_ENABLED` · `GOOGLE_CHECKOUT_SANDBOX` · `GOOGLE_CHECKOUT_MERCHANT_ID` · `GOOGLE_CHECKOUT_MERCHANT_KEY` · `GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID` · `GOOGLE_CHECKOUT_SANDBOX_MERCHANT_KEY`

## Conventions

- Logging: `myadmin_log('billing', 'info', $message, __LINE__, __FILE__)` — always use `'billing'` module for payment logs
- Admin guard: check `$GLOBALS['tf']->ima == 'admin'` before privileged operations
- Module resolution: `get_module_name($module)` → `get_module_settings($module)` → `get_module_db($module)`
- Invoice queries: filter by `invoices_paid=0`, `invoices_type=1`, `invoices_custid`, `invoices_module`
- Tabs for indentation (see `.scrutinizer.yml` coding style)
- Constants: `UPPERCASE_CONSTANTS` — never lowercase boolean constants

## Security

- Always `$db->real_escape()` on `$_GET`/`$_POST`/request vars before DB interpolation
- Use `array_map('intval', ...)` + `array_filter` for numeric ID arrays (see `src/pay_balance_google.php`)
- Merchant keys are password settings — never log or output `GOOGLE_CHECKOUT_MERCHANT_KEY` or `GOOGLE_CHECKOUT_SANDBOX_MERCHANT_KEY`
- Admin-only views must gate on `$GLOBALS['tf']->ima == 'admin'`

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
