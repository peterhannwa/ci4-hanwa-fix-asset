<?php

namespace App\Libraries;

class DepreciationCalculator
{
    /**
     * Calculate depreciation schedule for an asset
     * 
     * @param float $cost Acquisition cost
     * @param float $salvage Salvage value
     * @param int $life Useful life in years
     * @param int $methodId Depreciation method ID
     * @param string $startDate Acquisition date in Y-m-d format
     * @return array Depreciation schedule entries
     */
    public function calculate($cost, $salvage, $life, $methodId, $startDate)
    {
        switch ($methodId) {
            case 1: // Straight-line
                return $this->calculateStraightLine($cost, $salvage, $life, $startDate);
            case 2: // Declining balance
                return $this->calculateDecliningBalance($cost, $salvage, $life, $startDate);
            case 3: // Sum-of-the-years' digits
                return $this->calculateSumOfYearsDigits($cost, $salvage, $life, $startDate);
            case 4: // Units of production
                // Default to straight-line for simplicity
                return $this->calculateStraightLine($cost, $salvage, $life, $startDate);
            default:
                return $this->calculateStraightLine($cost, $salvage, $life, $startDate);
        }
    }
    
    /**
     * Calculate next depreciation entry after the latest entry
     * 
     * @param array $asset Asset data
     * @param array $latestEntry Latest depreciation entry
     * @return array New depreciation entry
     */
    public function calculateNextDepreciation($asset, $latestEntry)
    {
        // Determine next date (typically 1 year after the last entry)
        $nextDate = date('Y-m-d', strtotime('+1 year', strtotime($latestEntry['depreciation_date'])));
        
        // Get remaining years
        $acquisitionDate = new \DateTime($asset['acquisition_date']);
        $nextEntryDate = new \DateTime($nextDate);
        $yearsPassed = $acquisitionDate->diff($nextEntryDate)->y;
        $remainingYears = $asset['useful_life_years'] - $yearsPassed;
        
        // If fully depreciated, no more entries
        if ($remainingYears <= 0 || $latestEntry['book_value_after_depreciation'] <= $asset['salvage_value']) {
            throw new \Exception('Asset is fully depreciated');
        }
        
        $methodId = $asset['depreciation_method_id'];
        $bookValue = $latestEntry['book_value_after_depreciation'];
        $salvage = $asset['salvage_value'];
        $accumulatedDepreciation = $latestEntry['accumulated_depreciation'];
        
        switch ($methodId) {
            case 1: // Straight-line
                $amount = ($asset['acquisition_cost'] - $salvage) / $asset['useful_life_years'];
                break;
            case 2: // Declining balance
                $rate = 2/($asset['useful_life_years']);
                $amount = $bookValue * $rate;
                // Adjust to not go below salvage value
                if (($bookValue - $amount) < $salvage) {
                    $amount = $bookValue - $salvage;
                }
                break;
            case 3: // Sum-of-the-years' digits
                $sumOfYears = ($asset['useful_life_years'] * ($asset['useful_life_years'] + 1)) / 2;
                $amount = ($asset['acquisition_cost'] - $salvage) * ($remainingYears / $sumOfYears);
                break;
            default:
                $amount = ($asset['acquisition_cost'] - $salvage) / $asset['useful_life_years'];
        }
        
        $newAccumulatedDepreciation = $accumulatedDepreciation + $amount;
        $newBookValue = $bookValue - $amount;
        
        // Ensure we don't depreciate below salvage value
        if ($newBookValue < $salvage) {
            $newBookValue = $salvage;
            $amount = $bookValue - $salvage;
            $newAccumulatedDepreciation = $asset['acquisition_cost'] - $salvage;
        }
        
        return [
            'asset_id' => $asset['asset_id'],
            'depreciation_date' => $nextDate,
            'depreciation_amount' => $amount,
            'accumulated_depreciation' => $newAccumulatedDepreciation,
            'book_value_after_depreciation' => $newBookValue
        ];
    }
    
    private function calculateStraightLine($cost, $salvage, $life, $startDate)
    {
        $schedule = [];
        $annualDepreciation = ($cost - $salvage) / $life;
        $bookValue = $cost;
        $accumulatedDepreciation = 0;
        
        for ($year = 1; $year <= $life; $year++) {
            $depreciationDate = date('Y-m-d', strtotime("+$year year", strtotime($startDate)));
            $accumulatedDepreciation += $annualDepreciation;
            $bookValue -= $annualDepreciation;
            
            // Round to 2 decimal places
            $bookValue = round($bookValue, 2);
            $accumulatedDepreciation = round($accumulatedDepreciation, 2);
            
            $schedule[] = [
                'depreciation_date' => $depreciationDate,
                'depreciation_amount' => $annualDepreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'book_value_after_depreciation' => $bookValue
            ];
        }
        
        return $schedule;
    }
    
    private function calculateDecliningBalance($cost, $salvage, $life, $startDate)
    {
        $schedule = [];
        $rate = 2/($life); // Double declining rate
        $bookValue = $cost;
        $accumulatedDepreciation = 0;
        
        for ($year = 1; $year <= $life; $year++) {
            $depreciationDate = date('Y-m-d', strtotime("+$year year", strtotime($startDate)));
            $depreciation = $bookValue * $rate;
            
            // Switch to straight-line for the remaining years if it results in higher depreciation
            $remainingLife = $life - $year + 1;
            $straightLineAmt = ($bookValue - $salvage) / $remainingLife;
            if ($straightLineAmt > $depreciation) {
                $depreciation = $straightLineAmt;
            }
            
            // Ensure we don't go below salvage value
            if (($bookValue - $depreciation) < $salvage) {
                $depreciation = $bookValue - $salvage;
                $bookValue = $salvage;
                $accumulatedDepreciation = $cost - $salvage;
            } else {
                $bookValue -= $depreciation;
                $accumulatedDepreciation += $depreciation;
            }
            
            // Round to 2 decimal places
            $depreciation = round($depreciation, 2);
            $bookValue = round($bookValue, 2);
            $accumulatedDepreciation = round($accumulatedDepreciation, 2);
            
            $schedule[] = [
                'depreciation_date' => $depreciationDate,
                'depreciation_amount' => $depreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'book_value_after_depreciation' => $bookValue
            ];
            
            // If we've hit salvage value, stop calculating
            if ($bookValue <= $salvage) {
                break;
            }
        }
        
        return $schedule;
    }
    
    private function calculateSumOfYearsDigits($cost, $salvage, $life, $startDate)
    {
        $schedule = [];
        $sumOfYears = ($life * ($life + 1)) / 2;
        $depreciableAmount = $cost - $salvage;
        $bookValue = $cost;
        $accumulatedDepreciation = 0;
        
        for ($year = 1; $year <= $life; $year++) {
            $depreciationDate = date('Y-m-d', strtotime("+$year year", strtotime($startDate)));
            $depreciation = $depreciableAmount * (($life - $year + 1) / $sumOfYears);
            
            $bookValue -= $depreciation;
            $accumulatedDepreciation += $depreciation;
            
            // Round to 2 decimal places
            $depreciation = round($depreciation, 2);
            $bookValue = round($bookValue, 2);
            $accumulatedDepreciation = round($accumulatedDepreciation, 2);
            
            $schedule[] = [
                'depreciation_date' => $depreciationDate,
                'depreciation_amount' => $depreciation,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'book_value_after_depreciation' => $bookValue
            ];
        }
        
        return $schedule;
    }
}
