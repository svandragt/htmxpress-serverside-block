<?php

declare(strict_types=1);

namespace Yak\HtmxServerBlock\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testRendersPostsWhenFound(): void
    {
        \WP_Query::$postCount = 2;

        Functions\when('the_title')->justEcho('A title');
        Functions\when('the_excerpt')->justEcho('An excerpt');
        Functions\expect('wp_reset_postdata')->once();

        ob_start();
        require __DIR__ . '/../../templates/random_posts.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("id='random-posts'", $output);
        $this->assertStringContainsString('hx-post="/htmx/random_posts"', $output);
    }

    public function testRendersNothingWhenNoPostsFound(): void
    {
        \WP_Query::$postCount = 0;

        Functions\expect('wp_reset_postdata')->never();

        ob_start();
        require __DIR__ . '/../../templates/random_posts.php';
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }
}
