<?php

namespace App\Libraries;

class DepreciationCalculator
{
    public function calculate($cost, $salvage, $life, $methodId)
    {
        switch ($methodId) {
            case 1: // Straight-line
                return $this->calculateStraightLine($cost, $salvage, $life);
            case 2: // Declining balance
                return $this->calculateDecliningBalance($cost, $salvage, $life);
            default:
                return $this->calculateStraightLine($cost, $salvage, $life);
        }
    }

    protected function calculateStraightLine($cost, $salvage, $life)
    {
        $annualDepreciation = ($cost - $salvage) / $life;
        $schedule = [];
        $bookValue = $cost;

        for ($year = 1; $year <= $life; $year++) {
            $bookValue -= $annualDepreciation;
            $schedule[] = [
                'depreciation_date' => date('Y-m-d', strtotime("+$year years")),
                'depreciation_amount' => $annualDepreciation,
                'accumulated_depreciation' => $annualDepreciation * $year,
                'book_value_after_depreciation' => $bookValue
            ];
        }

        return $schedule;
    }

    protected function calculateDecliningBalance($cost, $salvage, $life)
    {
        $rate = 2/($life); // Double declining rate
        $schedule = [];
        $bookValue = $cost;
        $accumulatedDepreciation = 0;

        for ($year = 1; $year <= $life; $year++) {
            $depreciation = $bookValue * $rate;
            $bookValue -= $depreciation;
            $accumulatedDepreciation += $depreciation;

            $schedule[] = [
                'depreciation_date' => date('Y-m-d', strtotime("+$year years")),
                'depreciation_amount' => $depreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'book_value_after_depreciation' => $bookValue
            ];
        }

        return $schedule;
    }
}
