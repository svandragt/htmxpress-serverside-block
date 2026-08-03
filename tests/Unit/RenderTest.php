<?php

declare(strict_types=1);

namespace Yak\HtmxServerBlock\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
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

    public function testRenderCallbackReturnsTemplateOutput(): void
    {
        Functions\expect('load_template')
            ->once()
            ->andReturnUsing(function () {
                echo 'template output';
            });

        $this->assertSame('template output', \htmx_server_block_render_callback());
    }

    public function testRegisterTemplatePathsAppendsTemplatesDirectory(): void
    {
        $paths = \htmx_server_block_register_template_paths(['existing']);

        $this->assertSame('existing', $paths[0]);
        $this->assertStringEndsWith('/templates', $paths[1]);
    }
}
