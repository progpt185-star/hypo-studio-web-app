<?php

namespace App\Services;

class Preprocessor
{
    // Fit z-score parameters on matrix (array of rows with numeric columns) and transform
    // Returns ['matrix' => transformedMatrix, 'params' => ['means'=>[], 'stds'=>[]]]
    public function fitTransform(array $matrix): array
    {
        if (empty($matrix)) {
            return ['matrix' => [], 'params' => ['means' => [], 'stds' => []]];
        }

        $cols = count($matrix[0]);
        $rows = count($matrix);

        $means = array_fill(0, $cols, 0.0);
        $stds = array_fill(0, $cols, 0.0);

        // compute means
        for ($c = 0; $c < $cols; $c++) {
            $sum = 0;
            for ($r = 0; $r < $rows; $r++) {
                $sum += $matrix[$r][$c];
            }
            $means[$c] = $sum / $rows;
        }

        // compute stds
        for ($c = 0; $c < $cols; $c++) {
            $var = 0.0;
            for ($r = 0; $r < $rows; $r++) {
                $var += pow($matrix[$r][$c] - $means[$c], 2);
            }
            $var = $var / $rows;
            $stds[$c] = sqrt($var);
            if ($stds[$c] == 0) {
                $stds[$c] = 1.0; // avoid division by zero -> leave column at zero after centering
            }
        }

        // transform
        $out = [];
        for ($r = 0; $r < $rows; $r++) {
            $row = [];
            for ($c = 0; $c < $cols; $c++) {
                $row[] = ($matrix[$r][$c] - $means[$c]) / $stds[$c];
            }
            $out[] = $row;
        }

        return ['matrix' => $out, 'params' => ['means' => $means, 'stds' => $stds]];
    }

    // Apply previously fitted params
    public function transform(array $matrix, array $params): array
    {
        if (empty($matrix)) {
            return [];
        }
        $means = $params['means'] ?? [];
        $stds = $params['stds'] ?? [];
        $cols = count($matrix[0]);
        $rows = count($matrix);
        $out = [];
        for ($r = 0; $r < $rows; $r++) {
            $row = [];
            for ($c = 0; $c < $cols; $c++) {
                $m = $means[$c] ?? 0.0;
                $s = $stds[$c] ?? 1.0;
                if ($s == 0) $s = 1.0;
                $row[] = ($matrix[$r][$c] - $m) / $s;
            }
            $out[] = $row;
        }
        return $out;
    }
}
