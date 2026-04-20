<?php
namespace Piplup\ImageX\Tests\Unit\Generators;

use PHPUnit\Framework\TestCase;
use Piplup\ImageX\Generators\ResponsiveGenerator;

class ResponsiveGeneratorTest extends TestCase
{
    public function testExplicitWidthsAreFilteredAndIncludeOriginal()
    {
        $g = new ResponsiveGenerator();
        $res = $g->computeWidths(1200, ['widths' => [320, 640, 1600]]);
        $this->assertSame([320, 640, 1200], $res);
    }

    public function testBreakpointsAreUsedAndIncludeOriginal()
    {
        $g = new ResponsiveGenerator();
        $res = $g->computeWidths(800, ['breakpoints' => [320, 480, 768, 1024]]);
        $this->assertSame([320, 480, 768, 800], $res);
    }

    public function testAutoGenerationProducesProgressiveWidths()
    {
        $g = new ResponsiveGenerator();
        $res = $g->computeWidths(1300);
        $this->assertGreaterThanOrEqual(2, count($res));
        $this->assertSame(320, $res[0]);
        $this->assertSame(1300, end($res));
        // Ensure strictly increasing
        for ($i = 0; $i < count($res) - 1; $i++) {
            $this->assertTrue($res[$i] < $res[$i + 1]);
        }
    }

    public function testDPRMultipliersIncludeHigherResVariants()
    {
        $g = new ResponsiveGenerator();
        $res = $g->computeWidths(1000, ['dprs' => [1, 2]]);
        $this->assertSame(1000, end($res));
        $this->assertTrue(in_array(960, $res), 'Expected 960 (480x2) to be present for 2x DPR');
    }
}
