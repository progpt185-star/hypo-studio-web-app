<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Preprocessor;

class PreprocessorTest extends TestCase
{
    public function test_fit_transform_centers_and_scales()
    {
        $prep = new Preprocessor();

        $matrix = [
            [10, 100, 1000],
            [20, 110, 1200],
            [30, 90, 1100],
        ];

        $res = $prep->fitTransform($matrix);
        $scaled = $res['matrix'];
        $params = $res['params'];

        // each column of scaled should have mean approx 0 (within small epsilon)
        $cols = count($scaled[0]);
        $rows = count($scaled);
        for ($c = 0; $c < $cols; $c++) {
            $sum = 0.0;
            for ($r = 0; $r < $rows; $r++) {
                $sum += $scaled[$r][$c];
            }
            $mean = $sum / $rows;
            $this->assertEqualsWithDelta(0.0, $mean, 1e-9);
        }

        $this->assertArrayHasKey('means', $params);
        $this->assertArrayHasKey('stds', $params);
    }

    public function test_fit_transform_handles_empty_input()
    {
        $prep = new Preprocessor();
        $result = $prep->fitTransform([]);

        $this->assertEmpty($result['matrix']);
        $this->assertEmpty($result['params']['means']);
        $this->assertEmpty($result['params']['stds']);
    }

    public function test_transform_applies_scaling_correctly()
    {
        $prep = new Preprocessor();
        
        // Original matrix
        $matrix = [
            [10, 100],
            [20, 200],
        ];

        // First fit and transform
        $fit = $prep->fitTransform($matrix);
        
        // New data point
        $newMatrix = [[15, 150]];
        
        // Transform using saved params
        $transformed = $prep->transform($newMatrix, $fit['params']);
        
        // Should be centered between the two original points
        $this->assertCount(1, $transformed);
        $this->assertCount(2, $transformed[0]);
        
        // Check that values are as expected (should be ~0 since it's halfway)
        $this->assertEqualsWithDelta(0.0, $transformed[0][0], 0.1);
        $this->assertEqualsWithDelta(0.0, $transformed[0][1], 0.1);
    }

    public function test_transform_handles_zero_variance()
    {
        $prep = new Preprocessor();
        
        // Matrix with a constant column
        $matrix = [
            [10, 100],
            [10, 200],
            [10, 300],
        ];
        
        $result = $prep->fitTransform($matrix);
        
        // First column should be all zeros after scaling
        foreach ($result['matrix'] as $row) {
            $this->assertEquals(0.0, $row[0]);
        }
        
        // std should be 1.0 for constant column to avoid division by zero
        $this->assertEquals(1.0, $result['params']['stds'][0]);
    }
}
