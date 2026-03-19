<?php

namespace Detain\MyAdminGooglecheckout\Tests;

use Detain\MyAdminGooglecheckout\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class PluginTest
 *
 * Unit tests for the Google Checkout payment plugin.
 *
 * @package Detain\MyAdminGooglecheckout\Tests
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * Set up test fixtures.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    /**
     * Test that the Plugin class can be instantiated.
     *
     * @return void
     */
    public function testPluginCanBeInstantiated(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Test that the Plugin class exists in the expected namespace.
     *
     * @return void
     */
    public function testPluginClassExistsInCorrectNamespace(): void
    {
        $this->assertTrue(class_exists('Detain\\MyAdminGooglecheckout\\Plugin'));
    }

    /**
     * Test that the static $name property is set correctly.
     *
     * @return void
     */
    public function testNamePropertyIsCorrect(): void
    {
        $this->assertSame('Googlecheckout Plugin', Plugin::$name);
    }

    /**
     * Test that the static $description property is a non-empty string.
     *
     * @return void
     */
    public function testDescriptionPropertyIsNonEmpty(): void
    {
        $this->assertIsString(Plugin::$description);
        $this->assertNotEmpty(Plugin::$description);
    }

    /**
     * Test that the static $help property exists and is a string.
     *
     * @return void
     */
    public function testHelpPropertyIsString(): void
    {
        $this->assertIsString(Plugin::$help);
    }

    /**
     * Test that the static $type property is 'plugin'.
     *
     * @return void
     */
    public function testTypePropertyIsPlugin(): void
    {
        $this->assertSame('plugin', Plugin::$type);
    }

    /**
     * Test that getHooks returns an array.
     *
     * @return void
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Test that getHooks contains the system.settings hook.
     *
     * @return void
     */
    public function testGetHooksContainsSystemSettingsHook(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('system.settings', $hooks);
    }

    /**
     * Test that getHooks contains the function.requirements hook.
     *
     * @return void
     */
    public function testGetHooksContainsFunctionRequirementsHook(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('function.requirements', $hooks);
    }

    /**
     * Test that getHooks does not include the commented-out ui.menu hook.
     *
     * @return void
     */
    public function testGetHooksDoesNotContainUiMenuHook(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayNotHasKey('ui.menu', $hooks);
    }

    /**
     * Test that each hook value is a callable array with the class name and method.
     *
     * @return void
     */
    public function testGetHooksValuesAreCallableArrays(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $event => $handler) {
            $this->assertIsArray($handler, "Handler for '{$event}' should be an array");
            $this->assertCount(2, $handler, "Handler for '{$event}' should have exactly 2 elements");
            $this->assertSame(Plugin::class, $handler[0], "Handler class for '{$event}' should be Plugin");
            $this->assertIsString($handler[1], "Handler method for '{$event}' should be a string");
        }
    }

    /**
     * Test that the system.settings hook points to the getSettings method.
     *
     * @return void
     */
    public function testSystemSettingsHookPointsToGetSettings(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getSettings'], $hooks['system.settings']);
    }

    /**
     * Test that the function.requirements hook points to the getRequirements method.
     *
     * @return void
     */
    public function testFunctionRequirementsHookPointsToGetRequirements(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getRequirements'], $hooks['function.requirements']);
    }

    /**
     * Test that all hook handler methods exist on the Plugin class.
     *
     * @return void
     */
    public function testAllHookHandlerMethodsExist(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $event => $handler) {
            $this->assertTrue(
                method_exists($handler[0], $handler[1]),
                "Method {$handler[1]} should exist on class {$handler[0]} for event '{$event}'"
            );
        }
    }

    /**
     * Test that getSettings method is static.
     *
     * @return void
     */
    public function testGetSettingsMethodIsStatic(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test that getRequirements method is static.
     *
     * @return void
     */
    public function testGetRequirementsMethodIsStatic(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test that getMenu method is static.
     *
     * @return void
     */
    public function testGetMenuMethodIsStatic(): void
    {
        $method = $this->reflection->getMethod('getMenu');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test that getHooks method is static.
     *
     * @return void
     */
    public function testGetHooksMethodIsStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test that the constructor is public.
     *
     * @return void
     */
    public function testConstructorIsPublic(): void
    {
        $method = $this->reflection->getConstructor();
        $this->assertNotNull($method);
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test that the constructor takes no required parameters.
     *
     * @return void
     */
    public function testConstructorHasNoRequiredParameters(): void
    {
        $method = $this->reflection->getConstructor();
        $this->assertSame(0, $method->getNumberOfRequiredParameters());
    }

    /**
     * Test that getSettings accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetSettingsAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that getRequirements accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetRequirementsAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that getMenu accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetMenuAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getMenu');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that all four static properties exist on the class.
     *
     * @return void
     */
    public function testAllStaticPropertiesExist(): void
    {
        $expectedProperties = ['name', 'description', 'help', 'type'];
        foreach ($expectedProperties as $property) {
            $this->assertTrue(
                $this->reflection->hasProperty($property),
                "Property \${$property} should exist on Plugin class"
            );
            $prop = $this->reflection->getProperty($property);
            $this->assertTrue($prop->isStatic(), "Property \${$property} should be static");
            $this->assertTrue($prop->isPublic(), "Property \${$property} should be public");
        }
    }

    /**
     * Test that getRequirements registers the expected page requirements via the loader.
     *
     * @return void
     */
    public function testGetRequirementsRegistersPageRequirements(): void
    {
        $registered = [];
        $loader = new class($registered) {
            /** @var array */
            private $registered;

            public function __construct(array &$registered)
            {
                $this->registered = &$registered;
            }

            public function add_page_requirement(string $name, string $path): void
            {
                $this->registered[$name] = $path;
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        $this->assertArrayHasKey('view_google_transaction', $registered);
        $this->assertArrayHasKey('view_google_order', $registered);
        $this->assertArrayHasKey('pay_balance_google', $registered);
        $this->assertCount(3, $registered);
    }

    /**
     * Test that getRequirements registers paths containing the vendor package path.
     *
     * @return void
     */
    public function testGetRequirementsPathsContainPackageVendorPath(): void
    {
        $registered = [];
        $loader = new class($registered) {
            /** @var array */
            private $registered;

            public function __construct(array &$registered)
            {
                $this->registered = &$registered;
            }

            public function add_page_requirement(string $name, string $path): void
            {
                $this->registered[$name] = $path;
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        foreach ($registered as $name => $path) {
            $this->assertStringContainsString(
                'myadmin-googlecheckout-payments/src/',
                $path,
                "Path for '{$name}' should reference the package src directory"
            );
        }
    }

    /**
     * Test that getRequirements registers the correct file paths.
     *
     * @return void
     */
    public function testGetRequirementsRegistersCorrectPaths(): void
    {
        $registered = [];
        $loader = new class($registered) {
            /** @var array */
            private $registered;

            public function __construct(array &$registered)
            {
                $this->registered = &$registered;
            }

            public function add_page_requirement(string $name, string $path): void
            {
                $this->registered[$name] = $path;
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        $this->assertStringEndsWith('view_google_transaction.php', $registered['view_google_transaction']);
        $this->assertStringEndsWith('view_google_order.php', $registered['view_google_order']);
        $this->assertStringEndsWith('pay_balance_google.php', $registered['pay_balance_google']);
    }

    /**
     * Test that the Plugin class is not abstract.
     *
     * @return void
     */
    public function testPluginClassIsNotAbstract(): void
    {
        $this->assertFalse($this->reflection->isAbstract());
    }

    /**
     * Test that the Plugin class is not final.
     *
     * @return void
     */
    public function testPluginClassIsNotFinal(): void
    {
        $this->assertFalse($this->reflection->isFinal());
    }

    /**
     * Test that getHooks returns consistent results on multiple calls.
     *
     * @return void
     */
    public function testGetHooksIsIdempotent(): void
    {
        $first = Plugin::getHooks();
        $second = Plugin::getHooks();
        $this->assertSame($first, $second);
    }

    /**
     * Test that all declared methods have the expected return types or parameters.
     *
     * @return void
     */
    public function testPluginMethodCount(): void
    {
        $methods = $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $methodNames = array_map(function (ReflectionMethod $m) {
            return $m->getName();
        }, $methods);

        $this->assertContains('__construct', $methodNames);
        $this->assertContains('getHooks', $methodNames);
        $this->assertContains('getMenu', $methodNames);
        $this->assertContains('getRequirements', $methodNames);
        $this->assertContains('getSettings', $methodNames);
    }

    /**
     * Test that the description property mentions payment processing.
     *
     * @return void
     */
    public function testDescriptionMentionsPaymentProcessing(): void
    {
        $this->assertStringContainsString('Payment', Plugin::$description);
    }

    /**
     * Test that source files referenced by getRequirements exist on disk.
     *
     * @return void
     */
    public function testSourceFilesExist(): void
    {
        $srcDir = dirname((new ReflectionClass(Plugin::class))->getFileName());
        $expectedFiles = [
            'view_google_transaction.php',
            'view_google_order.php',
            'pay_balance_google.php',
        ];
        foreach ($expectedFiles as $file) {
            $this->assertFileExists(
                $srcDir . DIRECTORY_SEPARATOR . $file,
                "Source file {$file} should exist in src directory"
            );
        }
    }

    /**
     * Test that Plugin.php source file exists.
     *
     * @return void
     */
    public function testPluginFileExists(): void
    {
        $file = (new ReflectionClass(Plugin::class))->getFileName();
        $this->assertFileExists($file);
    }

    /**
     * Test that multiple Plugin instances are independent.
     *
     * @return void
     */
    public function testMultipleInstancesAreIndependent(): void
    {
        $a = new Plugin();
        $b = new Plugin();
        $this->assertNotSame($a, $b);
        $this->assertEquals($a, $b);
    }
}
