<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Router;

class RouterTest extends TestCase
{
    public function testRouteRegistration(): void
    {
        Router::get('/test-sample-route', function() {
            return 'sample';
        });

        $routes = Router::getRoutes();
        $found = false;

        foreach ($routes as $route) {
            if ($route['uri'] === '/test-sample-route' && $route['method'] === 'GET') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Router must properly register GET routes.");
    }

    public function testRouteGroupPrefixing(): void
    {
        Router::group(['prefix' => 'api/v1'], function() {
            Router::get('/status', function() {
                return 'ok';
            });
        });

        $routes = Router::getRoutes();
        $found = false;

        foreach ($routes as $route) {
            if ($route['uri'] === '/api/v1/status') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Router group must correctly prefix child routes.");
    }
}
