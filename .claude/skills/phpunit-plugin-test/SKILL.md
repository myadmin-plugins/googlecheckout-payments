---
name: phpunit-plugin-test
description: Adds a PHPUnit test method to tests/PluginTest.php for plugin hook registration, static properties, or settings verification. Use when user says 'add test', 'write test', 'test plugin', or 'test settings'. Follows phpunit.xml.dist config and vendor/bin/phpunit --bootstrap vendor/autoload.php tests/ invocation. Do NOT use for integration tests or tests requiring a live DB/HTTP.
---
# phpunit-plugin-test

## Critical

- All tests go in `tests/PluginTest.php` — **never** create a new test file.
- Class `PluginTest` is in namespace `Detain\MyAdminGooglecheckout\Tests`; keep it.
- Never call `getSettings()` or `getMenu()` directly — they call global helpers (`add_text_setting`, `$GLOBALS['tf']`) that are unavailable in unit tests. Use `ReflectionClass` to inspect them instead.
- For `getRequirements()` tests, inject an anonymous class as the event subject — do not mock with `$this->createMock()` because the subject uses duck-typed method calls.
- Run tests with: `vendor/bin/phpunit tests/ -v`

## Instructions

1. **Read `tests/PluginTest.php`** to understand current coverage before adding anything. Verify the method name you plan to add does not already exist.

2. **Choose the correct test pattern** based on what you're testing:
   - Static property → assert directly on `Plugin::$propertyName`
   - Hook registration → call `Plugin::getHooks()` and assert on the returned array
   - Method signature → use `$this->reflection->getMethod('methodName')` (already set up in `setUp()`)
   - `getRequirements()` behavior → inject anonymous loader class via `new GenericEvent($loader)`

3. **Write the test method** following this exact structure:
   ```php
   /**
    * Test that <what you are asserting>.
    *
    * @return void
    */
   public function test<PascalCaseDescription>(): void
   {
       // arrange / act / assert
   }
   ```
   - Return type must be `: void`
   - DocBlock must start with `Test that`
   - Method name must start with `test`

4. **For `getRequirements()` tests**, use this anonymous loader pattern (matches existing tests at lines 337–358):
   ```php
   $registered = [];
   $loader = new class($registered) {
       private $registered;
       public function __construct(array &$registered) {
           $this->registered = &$registered;
       }
       public function add_page_requirement(string $name, string $path): void {
           $this->registered[$name] = $path;
       }
   };
   $event = new GenericEvent($loader);
   Plugin::getRequirements($event);
   // assert on $registered
   ```

5. **For hook structure tests**, follow this pattern (matches lines 147–155):
   ```php
   $hooks = Plugin::getHooks();
   $this->assertArrayHasKey('system.settings', $hooks);
   $this->assertSame([Plugin::class, 'getSettings'], $hooks['system.settings']);
   ```

6. **For ReflectionClass tests**, use `$this->reflection` (already a `ReflectionClass` of `Plugin::class` from `setUp()`):
   ```php
   $method = $this->reflection->getMethod('getSettings');
   $this->assertTrue($method->isStatic());
   ```

7. **Verify** by running: `vendor/bin/phpunit tests/ -v`
   All existing tests must still pass. Look for the new test name in the output.

## Examples

**User says:** "Add a test that verifies `system.settings` hook points to `getSettings`"

**Actions taken:**
1. Read `tests/PluginTest.php` — confirm `testSystemSettingsHookPointsToGetSettings` doesn't already exist (it does at line 163, so this one is already covered — pick a different name).
2. Add the method inside `class PluginTest extends TestCase` before the closing `}`:
```php
/**
 * Test that the system.settings hook handler is a two-element callable array.
 *
 * @return void
 */
public function testSystemSettingsHookHandlerIsTwoElementArray(): void
{
    $hooks = Plugin::getHooks();
    $handler = $hooks['system.settings'];
    $this->assertIsArray($handler);
    $this->assertCount(2, $handler);
}
```
3. Run `vendor/bin/phpunit tests/ -v` — confirm new test appears and passes.

## Common Issues

- **`Error: Call to undefined function add_text_setting()`** — you called `Plugin::getSettings()` directly. Use `ReflectionClass` to inspect the method signature instead; never invoke `getSettings()` in unit tests.
- **`Error: Undefined index: tf in Plugin.php`** — you called `Plugin::getMenu()` directly. Inspect it via reflection only.
- **`Fatal error: Class 'Detain\MyAdminGooglecheckout\Tests\PluginTest' not found`** — autoloader missing. Always run from the project root so Composer autoloading is active.
- **`There was 1 error: Cannot redeclare test<Name>()`** — duplicate method name. Read `tests/PluginTest.php` first to check existing method names.
- **Anonymous class type error on `add_page_requirement`** — ensure the anonymous class parameter types match exactly: `string $name, string $path`.
