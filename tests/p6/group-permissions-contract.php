<?php
declare(strict_types=1);

// Isolated contract test using the installed, unmodified Grav API resolver.
// php tests/p6/group-permissions-contract.php /path/to/PermissionResolver.php
namespace Grav\Common\User\Interfaces {
    interface UserInterface {
        public function get(string $key, mixed $default = null): mixed;
    }
}
namespace Grav\Common {
    final class ConfigFixture {
        public function __construct(private array $data) {}
        public function get(string $key, mixed $default = null): mixed {
            $node = $this->data;
            foreach (explode('.', $key) as $part) {
                if (!is_array($node) || !array_key_exists($part, $node)) return $default;
                $node = $node[$part];
            }
            return $node;
        }
    }
    final class Grav {
        public static array $services = [];
        public static function instance(): array { return self::$services; }
    }
    final class Utils {
        public static function arrayFlattenDotNotation(array $data, string $prefix = ''): array {
            $flat = [];
            foreach ($data as $key => $value) {
                $path = $prefix . $key;
                if (is_array($value)) $flat += self::arrayFlattenDotNotation($value, $path . '.');
                else $flat[$path] = $value;
            }
            return $flat;
        }
        public static function isPositive(mixed $value): bool {
            return in_array($value, [true, 1, '1', 'true'], true);
        }
    }
    class Plugin {}
}
namespace Psr\Http\Message {
    interface ServerRequestInterface {
        public function getAttribute(string $name, mixed $default = null): mixed;
    }
}
namespace {
    use Grav\Common\ConfigFixture;
    use Grav\Common\Grav;
    use Grav\Common\User\Interfaces\UserInterface;
    use Goosialize\Google\Admin\ReportingAuthorization;
    use Goosialize\Google\Admin\AnalyticsDashboardController;
    use Goosialize\Google\Admin\SearchConsoleDashboardController;

    $root = dirname(__DIR__, 2);
    $resolverFile = $argv[1] ?? '';
    if (!is_file($resolverFile)) throw new \RuntimeException('Provide the installed Grav API PermissionResolver.php path.');
    require $resolverFile;
    require $root . '/classes/Admin/ReportingAuthorization.php';
    require $root . '/classes/Admin/AnalyticsDashboardController.php';
    require $root . '/classes/Admin/SearchConsoleDashboardController.php';
    require $root . '/goosialize-google.php';

    final class FixtureUser implements UserInterface {
        public function __construct(private array $data) {}
        public function get(string $key, mixed $default = null): mixed {
            return $this->data[$key] ?? $default;
        }
    }
    final class FixtureRequest implements \Psr\Http\Message\ServerRequestInterface {
        public function __construct(private array $data) {}
        public function getAttribute(string $name, mixed $default = null): mixed {
            return $this->data[$name] ?? $default;
        }
    }
    $reportAccess = ['access' => true, 'goosialize_google' => [
        'analytics' => ['read' => true], 'search_console' => ['read' => true],
    ]];
    Grav::$services['config'] = new ConfigFixture(['groups' => [
        'admin' => ['enabled' => true, 'access' => ['api' => ['access' => true, 'super' => true]]],
        'editor' => ['enabled' => true, 'access' => ['api' => $reportAccess]],
        'disabled' => ['enabled' => false, 'access' => ['api' => $reportAccess]],
        'pages' => ['enabled' => true, 'access' => ['api' => ['access' => true, 'pages' => ['write' => true]]]],
    ]]);
    $admin = new FixtureUser(['groups' => ['admin'], 'access' => []]);
    $editor = new FixtureUser(['groups' => ['editor'], 'access' => []]);
    $direct = new FixtureUser(['groups' => [], 'access' => ['api' => $reportAccess]]);
    $revoked = new FixtureUser(['groups' => ['editor'], 'access' => ['api' => ['goosialize_google' => ['analytics' => ['read' => false]]]]]);
    $disabled = new FixtureUser(['groups' => ['disabled'], 'access' => []]);
    $disabledUser = new FixtureUser(['state' => 'disabled', 'groups' => ['admin'], 'access' => []]);
    $pages = new FixtureUser(['groups' => ['pages'], 'access' => []]);
    $parent = new FixtureUser(['groups' => [], 'access' => ['api' => true]]);
    $demoted = new FixtureUser(['groups' => ['admin'], 'access' => ['api' => ['super' => false]]]);
    $noAccess = new FixtureUser(['groups' => ['editor'], 'access' => ['api' => ['access' => false]]]);
    $checks = 0;
    function check(string $name, bool $actual, bool $expected): void {
        global $checks;
        if ($actual !== $expected) throw new \RuntimeException('FAIL ' . $name);
        ++$checks;
        echo 'PASS ' . $name . PHP_EOL;
    }
    $analytics = 'api.goosialize_google.analytics.read';
    $search = 'api.goosialize_google.search_console.read';
    foreach ([$analytics, $search] as $permission) {
        check('editor inherits ' . $permission, ReportingAuthorization::hasPermission($editor, $permission), true);
        check('direct grant ' . $permission, ReportingAuthorization::hasPermission($direct, $permission), true);
        check('disabled group ' . $permission, ReportingAuthorization::hasPermission($disabled, $permission), false);
        check('pages-only ' . $permission, ReportingAuthorization::hasPermission($pages, $permission), false);
        check('missing user ' . $permission, ReportingAuthorization::hasPermission(null, $permission), false);
    }
    check('admin inherits exact super', ReportingAuthorization::hasPermission($admin, 'api.super'), true);
    check('editor is not super', ReportingAuthorization::hasPermission($editor, 'api.super'), false);
    check('disabled account', ReportingAuthorization::hasPermission($disabledUser, 'api.super'), false);
    check('parent is not super', ReportingAuthorization::hasPermission($parent, 'api.super'), false);
    check('explicit super denial', ReportingAuthorization::hasPermission($demoted, 'api.super'), false);
    check('explicit reporting denial', ReportingAuthorization::hasPermission($revoked, $analytics), false);
    check('editor cannot configure', ReportingAuthorization::hasPermission($editor, 'api.config.write'), false);
    check('editor cannot manage packages', ReportingAuthorization::hasPermission($editor, 'api.gpm.write'), false);
    check('editor cannot manage users', ReportingAuthorization::hasPermission($editor, 'api.users.write'), false);

    $pluginClass = new \ReflectionClass(\Grav\Plugin\GoosializeGooglePlugin::class);
    $plugin = $pluginClass->newInstanceWithoutConstructor();
    $dashboard = $pluginClass->getMethod('dashboardAllowed');
    foreach ([['admin', $admin, true], ['editor', $editor, true], ['direct', $direct, true], ['revoked', $revoked, false],
              ['disabled', $disabled, false], ['pages-only', $pages, false], ['no access', $noAccess, false], ['guest', null, false]] as [$name, $user, $expected]) {
        check('sidebar ' . $name, $dashboard->invoke($plugin, $user), $expected);
    }
    foreach ([[AnalyticsDashboardController::class, $analytics], [SearchConsoleDashboardController::class, $search]] as [$class, $permission]) {
        $reflection = new \ReflectionClass($class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $authorized = $reflection->getMethod('authorized');
        foreach ([['admin', $admin, [], true], ['editor', $editor, [], true], ['direct', $direct, [], true],
                  ['disabled group', $disabled, [], false], ['disabled account', $disabledUser, [], false],
                  ['pages only', $pages, [], false], ['no access', $noAccess, [], false],
                  ['editor allowed scope', $editor, [$permission], true],
                  ['editor wrong scope', $editor, ['api.pages.read'], false],
                  ['admin wrong scope', $admin, ['api.pages.read'], false],
                  ['admin allowed scope', $admin, [$permission], true]] as [$name, $user, $scopes, $expected]) {
            check($class . ' ' . $name, $authorized->invoke($controller, new FixtureRequest(['api_key_scopes' => $scopes]), $user), $expected);
        }
        check($class . ' explicit reporting denial', $authorized->invoke($controller, new FixtureRequest([]), $revoked), $permission === $search);
    }
    echo 'GROUP_PERMISSION_CONTRACT=PASS (' . $checks . ' assertions)' . PHP_EOL;
}
