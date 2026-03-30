---
name: page-function
description: Creates a procedural PHP page function file under `src/` and registers it in `getRequirements()`. Use when user says 'add page', 'new view', 'add function', or 'create page handler'. Follows the `add_page_requirement()` path pattern and uses `get_module_db`, `TFTable`, `add_output`. Do NOT use for class-based code or plugin settings registration.
---
# Page Function

## Critical

- **Never use PDO.** Always use `$db = get_module_db($module)` or `$db = clone $GLOBALS['tf']->db`.
- **Always escape user input** before interpolating into queries: `$db->real_escape($GLOBALS['tf']->variables->request['foo'])`.
- **Admin guard required** for privileged views: check `$GLOBALS['tf']->ima == 'admin'` before any sensitive operation.
- Page functions are **procedural** — no classes, no namespaces inside the file.
- The function name must exactly match the key passed to `add_page_requirement()`.

## Instructions

1. **Create the page function file in `src/`** (e.g., `src/view_google_order.php`) with this skeleton:
   ```php
   <?php
   function view_google_order()
   {
       $module = get_module_name(($GLOBALS['tf']->variables->request['module'] ?? 'default'));
       $settings = get_module_settings($module);
       $db = get_module_db($module);
       // ... logic here ...
       add_output($table->get_table());
   }
   ```
   Verify the filename matches the function name before proceeding.

2. **For admin-only views**, wrap all logic in the admin guard:
   ```php
   if ($GLOBALS['tf']->ima == 'admin') {
       // privileged logic
   } else {
       add_output('This functionality is for administrators only');
   }
   ```

3. **For DB queries**, follow this exact pattern:
   ```php
   $transaction = $db->real_escape($GLOBALS['tf']->variables->request['transaction']);
   $db->query("SELECT * FROM gcheckout WHERE google_order='{$transaction}'", __LINE__, __FILE__);
   if ($db->num_rows() > 0) {
       $db->next_record(MYSQL_ASSOC);
       $row = $db->Record;
   }
   ```
   Pass `__LINE__, __FILE__` as the second and third args to every `$db->query()` call.

4. **For tabular output**, use `TFTable`:
   ```php
   $table = new TFTable();
   $table->set_title('My View Title');
   $table->add_field('Column A');
   $table->add_field('Column B', 'r'); // 'r' = right-align
   $table->add_row();
   while ($db->next_record(MYSQL_ASSOC)) {
       $table->add_field($db->Record['col_a']);
       $table->add_field($db->Record['col_b'], 'r');
       $table->add_row();
   }
   add_output($table->get_table());
   ```

5. **For logging**, always use `'billing'` as the module for payment-related pages:
   ```php
   myadmin_log('billing', 'info', 'My function called', __LINE__, __FILE__);
   ```

6. **Register the function in `src/Plugin.php`** inside `getRequirements()`:
   ```php
   $loader->add_page_requirement('view_google_order', '/../vendor/detain/myadmin-googlecheckout-payments/src/view_google_order.php');
   ```
   Verify the path string starts with `'/../vendor/detain/myadmin-googlecheckout-payments/src/'`.

7. **Run tests** to confirm nothing is broken:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```

## Examples

**User says:** "Add a page to show Google refund details for admins"

**Actions taken:**
1. Create `src/view_google_refund.php`:
   ```php
   <?php
   function view_google_refund()
   {
       if ($GLOBALS['tf']->ima == 'admin') {
           $module = get_module_name(($GLOBALS['tf']->variables->request['module'] ?? 'default'));
           $db = get_module_db($module);
           $transaction = $db->real_escape($GLOBALS['tf']->variables->request['transaction']);
           $db->query("SELECT * FROM gcheckout WHERE google_order='{$transaction}' AND _type='refund'", __LINE__, __FILE__);
           if ($db->num_rows() > 0) {
               $table = new TFTable();
               $table->set_title('Refund Information');
               while ($db->next_record(MYSQL_ASSOC)) {
                   $table->add_field('Order');
                   $table->add_field($db->Record['google_order'], 'r');
                   $table->add_row();
               }
               add_output($table->get_table());
           }
       } else {
           add_output('This functionality is for administrators only');
       }
   }
   ```
2. Add to `src/Plugin.php` `getRequirements()`:
   ```php
   $loader->add_page_requirement('view_google_refund', '/../vendor/detain/myadmin-googlecheckout-payments/src/view_google_refund.php');
   ```

**Result:** `view_google_refund` is callable via the page loader and renders a `TFTable` for admins.

## Common Issues

- **Function not found at runtime:** The key in `add_page_requirement()` must exactly match the PHP function name and the filename (minus `.php`). Check all three match.
- **"Call to undefined function get_module_db()"** in tests: The bootstrap `vendor/autoload.php` does not load MyAdmin globals. Mock or skip DB calls in unit tests; use integration tests for DB coverage.
- **Blank output / no table rendered:** Forgetting `add_output($table->get_table())` at the end. Every code path that builds a table must call `add_output()`.
- **SQL injection risk:** Never interpolate `$_GET`/`$_POST` directly. Always go through `$db->real_escape()` first, then interpolate the escaped variable.
- **Path typo in `add_page_requirement()`:** The path must begin with `'/../vendor/detain/myadmin-googlecheckout-payments/src/'` — missing the leading `'/../'` will cause a file-not-found load failure.
