<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    public function testPasswordHashVerification(): void
    {
        $rawPassword = 'Password@123';
        $hash = '$2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq';

        $this->assertTrue(
            password_verify($rawPassword, $hash),
            "Bcrypt demo hash must verify accurately against Password@123."
        );
    }

    public function testOutputEscapingXssPrevention(): void
    {
        if (!function_exists('e')) {
            require_once dirname(__DIR__, 2) . '/app/Helpers/response.php';
        }

        $malicious = '<script>alert("XSS")</script>';
        $escaped = e($malicious);

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }
}
