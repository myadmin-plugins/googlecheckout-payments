---
name: gcheckout-db-query
description: Writes DB queries against the `gcheckout` or `invoices` tables using the MyAdmin DB pattern (`get_module_db`, `real_escape`, `next_record(MYSQL_ASSOC)`, `$db->Record`). Use when user says 'query gcheckout', 'look up transaction', 'fetch invoices', 'get google order', or 'DB query'. Do NOT use PDO, mysqli directly, or any ORM.
---
# gcheckout-db-query

## Critical

- **Never use PDO** — always use `get_module_db($module)` or `clone $GLOBALS['tf']->db`
- **Always escape user input** with `$db->real_escape()` before interpolating into queries
- **Always pass `__LINE__, __FILE__`** as the 2nd and 3rd args to `$db->query()` for error tracing
- **Always check `$db->num_rows() > 0`** before calling `next_record()`
- **Always use `MYSQL_ASSOC`** — never use numeric indices or bare `next_record()`
- For invoice queries, always filter by `invoices_paid=0`, `invoices_type=1`, `invoices_custid`, and `invoices_module`

## Instructions

1. **Get a DB handle.** Use the correct handle for the context:
   - Module-scoped (preferred for billing page functions):
     ```php
     $module = get_module_name($module);
     $db = get_module_db($module);
     ```
   - Global fallback (for non-module contexts like `view_google_order`):
     ```php
     $db = clone $GLOBALS['tf']->db;
     ```
   Verify `$module` is resolved via `get_module_name()` before calling `get_module_db()`.

2. **Escape all user-supplied values** before building the query string:
   ```php
   $transaction = $db->real_escape($GLOBALS['tf']->variables->request['transaction']);
   $custid      = $db->real_escape($GLOBALS['tf']->variables->request['custid']);
   ```
   Verify every interpolated variable that originates from request input is escaped.

3. **Run the query** with line/file context:
   ```php
   // gcheckout lookup by order ID
   $db->query("SELECT * FROM gcheckout WHERE google_order='{$transaction}'", __LINE__, __FILE__);

   // invoices lookup (unpaid, by module and customer)
   $db->query("SELECT * FROM invoices WHERE invoices_module='{$module}' AND invoices_paid=0 AND invoices_type=1 AND invoices_custid='{$custid}' ORDER BY invoices_id DESC", __LINE__, __FILE__);
   ```

4. **Single-row fetch** — use `if`, not `while`:
   ```php
   if ($db->num_rows() > 0) {
       $db->next_record(MYSQL_ASSOC);
       $row = $db->Record;
       // use $row['column_name']
   }
   ```

5. **Multi-row fetch** — use `while`:
   ```php
   while ($db->next_record(MYSQL_ASSOC)) {
       $row = $db->Record;
       // process $row['invoices_amount'], $row['invoices_description'], etc.
   }
   ```

6. **Log the query** for billing operations:
   ```php
   myadmin_log('billing', 'info', $query, __LINE__, __FILE__);
   ```
   Always use `'billing'` as the module argument for payment-related logs.

7. **Guard privileged queries** (e.g. admin-only transaction views):
   ```php
   if ($GLOBALS['tf']->ima == 'admin') {
       // run privileged query
   } else {
       add_output('This functionality is for administrators only');
   }
   ```

## Examples

**User says:** "Look up a gcheckout transaction by google_order and display its fields."

**Actions taken:**
```php
$db = clone $GLOBALS['tf']->db;
$transaction = $db->real_escape($GLOBALS['tf']->variables->request['transaction']);
$db->query("SELECT * FROM gcheckout WHERE google_order='{$transaction}'", __LINE__, __FILE__);
if ($db->num_rows() > 0) {
    while ($db->next_record(MYSQL_ASSOC)) {
        $row = $db->Record;
        // render $row fields
    }
}
```

**User says:** "Fetch all unpaid invoices for a customer in the vps module."

**Actions taken:**
```php
$module = get_module_name('vps');
$db     = get_module_db($module);
$custid = $db->real_escape($GLOBALS['tf']->session->account_id);
$query  = "SELECT * FROM invoices WHERE invoices_module='{$module}' AND invoices_paid=0 AND invoices_type=1 AND invoices_custid='{$custid}' ORDER BY invoices_id DESC";
myadmin_log('billing', 'info', $query, __LINE__, __FILE__);
$db->query($query, __LINE__, __FILE__);
while ($db->next_record(MYSQL_ASSOC)) {
    $row = $db->Record;
    // $row['invoices_id'], $row['invoices_amount'], $row['invoices_description']
}
```

## Common Issues

- **"Call to undefined function get_module_db()"** — the page function file was loaded without `function_requirements()`. Add `function_requirements('get_module_db')` at the top, or ensure the bootstrap/autoload for MyAdmin core is included.
- **`$db->Record` is empty after `query()`** — you forgot to call `$db->next_record(MYSQL_ASSOC)` before accessing `$db->Record`. Always call `next_record()` inside an `if`/`while` after checking `num_rows()`.
- **SQL errors with apostrophes in data** — raw `$_GET`/`$_POST` values were interpolated without `$db->real_escape()`. Escape every user-supplied variable before putting it in the query string.
- **Wrong rows returned for invoices** — missing `invoices_paid=0` or `invoices_type=1` filters. All invoice queries must include both to avoid returning paid or non-standard invoice types.
- **`clone $GLOBALS['tf']->db` vs `get_module_db()`** — use `clone` only when no module context exists (e.g. lightweight redirect handlers). Use `get_module_db($module)` everywhere billing page functions run so module-specific DB routing applies.