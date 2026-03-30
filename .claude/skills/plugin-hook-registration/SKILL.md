---
name: plugin-hook-registration
description: Adds a new Symfony EventDispatcher hook to src/Plugin.php. Use when user says 'add hook', 'register event', 'add settings', 'add page requirement', or 'add menu item'. Handles getHooks() return array, GenericEvent $event handler signature, and getRequirements() page registration. Do NOT use for modifying existing hooks or creating new plugin packages from scratch.
---
# Plugin Hook Registration

## Critical

- Every handler method MUST be `public static` and accept exactly one `GenericEvent $event` parameter — no exceptions.
- The hook key in `getHooks()` must be registered BEFORE the handler method exists — verify the method is added to the class before testing.
- `getRequirements()` paths MUST start with `'/../vendor/detain/<package-name>/src/'` — never use absolute or relative paths.
- Never use PDO. Never add instance methods — all Plugin methods are static.

## Instructions

1. **Identify the hook event name.** Common events:
   - `system.settings` → subject is `\MyAdmin\Settings`
   - `function.requirements` → subject is `\MyAdmin\Plugins\Loader`
   Verify the event name matches what the MyAdmin core dispatches before proceeding.

2. **Register the hook in `getHooks()`.** Add one entry to the returned array in `src/Plugin.php`:
   ```php
   public static function getHooks()
   {
       return [
           'system.settings'      => [__CLASS__, 'getSettings'],
           'function.requirements' => [__CLASS__, 'getRequirements'],
       ];
   }
   ```
   The value must be `[__CLASS__, 'methodName']` — never a closure or string callable.
   Verify the key does not already exist in the array before adding.

3. **Add the handler method to the Plugin class.** Place it after the last existing handler. Always type-hint `GenericEvent`:

   **For `system.settings`:**
   ```php
   public static function getSettings(GenericEvent $event)
   {
       /** @var \MyAdmin\Settings $settings **/
       $settings = $event->getSubject();
       $settings->add_text_setting(_('Billing'), _('Section'), 'setting_key', _('Label'), _('Description'), (defined('SETTING_CONST') ? SETTING_CONST : ''));
       $settings->add_password_setting(_('Billing'), _('Section'), 'setting_key_secret', _('Secret'), _('Secret'), (defined('SETTING_CONST_SECRET') ? SETTING_CONST_SECRET : ''));
       $settings->add_radio_setting(_('Billing'), _('Section'), 'setting_enabled', _('Enable'), _('Enable'), SETTING_ENABLED, [true, false], ['Enabled', 'Disabled']);
   }
   ```

   **For `function.requirements`:**
   ```php
   public static function getRequirements(GenericEvent $event)
   {
       /** @var \MyAdmin\Plugins\Loader $this->loader */
       $loader = $event->getSubject();
       $loader->add_page_requirement('function_name', '/../vendor/detain/<package-name>/src/function_name.php');
   }
   ```

4. **If adding a `function.requirements` entry**, create the corresponding page file in `src/` with a procedural function of the same name.
   Verify the file exists before running tests.

5. **Run tests** to confirm the hook is registered and the method signature is correct:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   Verify all hook-related assertions pass (`testGetHooksContains*`, `testAllHookHandlerMethodsExist`).

## Examples

**User says:** "Add a function.requirements hook to register a new page function"

**Actions taken:**
1. Add `'function.requirements' => [__CLASS__, 'getRequirements']` to `getHooks()` return array in `src/Plugin.php`.
2. Add `getRequirements(GenericEvent $event)` static method that calls `$loader->add_page_requirement()`.
3. Create the corresponding page file in `src/`.
4. Run `vendor/bin/phpunit tests/ -v`.

**Result** — `src/Plugin.php` diff:
```php
 public static function getHooks()
 {
     return [
         'system.settings'      => [__CLASS__, 'getSettings'],
+        'function.requirements' => [__CLASS__, 'getRequirements'],
     ];
 }
+
+public static function getRequirements(GenericEvent $event)
+{
+    /** @var \MyAdmin\Plugins\Loader $loader */
+    $loader = $event->getSubject();
+    $loader->add_page_requirement('my_function', '/../vendor/detain/<package-name>/src/my_function.php');
+}
```

## Common Issues

- **"Call to undefined method ... getSubject()"**: You passed the wrong type as the event subject. `GenericEvent` must be constructed as `new GenericEvent($subject)` — the subject is what `$event->getSubject()` returns.
- **"Method does not exist" test failure**: You added the key to `getHooks()` but forgot to add the method. Add the static method to the Plugin class.
- **`add_page_requirement` path not found at runtime**: Path must begin with `'/../vendor/detain/<package>/src/'`. Absolute paths or paths starting with `__DIR__` will fail in the MyAdmin loader context.
- **`testGetRequirementsRegistersPageRequirements` count mismatch**: Each new `add_page_requirement()` call increments the expected count in `assertCount()` — update that assertion in `tests/PluginTest.php` to match.
