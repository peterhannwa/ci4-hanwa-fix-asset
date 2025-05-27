<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ReportController extends ResourceController
{
    protected $format = 'json';

    public function assetRegister()
    {
        $assetModel = model('AssetModel');
        
        $assets = $assetModel->select('assets.*, 
            asset_categories.category_name,
            suppliers.supplier_name,
            depreciation_methods.method_name')
            ->join('asset_categories', 'asset_categories.category_id = assets.category_id')
            ->join('suppliers', 'suppliers.supplier_id = assets.supplier_id', 'left')
            ->join('depreciation_methods', 'depreciation_methods.method_id = assets.depreciation_method_id')
            ->orderBy('assets.asset_name', 'ASC')
            ->findAll();

        return $this->respond([
            'report_type' => 'Asset Register',
            'generated_date' => date('Y-m-d H:i:s'),
            'total_assets' => count($assets),
            'data' => $assets
        ]);
    }

    public function depreciation()
    {
        $scheduleModel = model('DepreciationScheduleModel');
        
        $depreciation = $scheduleModel->select('depreciation_schedules.*, 
            assets.asset_name, 
            assets.unique_tag,
            asset_categories.category_name')
            ->join('assets', 'assets.asset_id = depreciation_schedules.asset_id')
            ->join('asset_categories', 'asset_categories.category_id = assets.category_id')
            ->orderBy('depreciation_schedules.depreciation_date', 'DESC')
            ->findAll();

        $totalDepreciation = array_sum(array_column($depreciation, 'depreciation_amount'));
        $totalAccumulated = array_sum(array_column($depreciation, 'accumulated_depreciation'));

        return $this->respond([
            'report_type' => 'Depreciation Report',
            'generated_date' => date('Y-m-d H:i:s'),
            'total_depreciation_amount' => $totalDepreciation,
            'total_accumulated_depreciation' => $totalAccumulated,
            'data' => $depreciation
        ]);
    }

    public function valuation()
    {
        $assetModel = model('AssetModel');
        
        $assets = $assetModel->select('assets.asset_id, assets.asset_name, assets.unique_tag,
            assets.acquisition_cost, assets.current_value, assets.status,
            asset_categories.category_name')
            ->join('asset_categories', 'asset_categories.category_id = assets.category_id')
            ->where('assets.status !=', 'Disposed')
            ->orderBy('assets.current_value', 'DESC')
            ->findAll();

        $totalAcquisitionCost = array_sum(array_column($assets, 'acquisition_cost'));
        $totalCurrentValue = array_sum(array_column($assets, 'current_value'));
        $totalDepreciation = $totalAcquisitionCost - $totalCurrentValue;

        return $this->respond([
            'report_type' => 'Asset Valuation Report',
            'generated_date' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_acquisition_cost' => $totalAcquisitionCost,
                'total_current_value' => $totalCurrentValue,
                'total_depreciation' => $totalDepreciation,
                'active_assets_count' => count($assets)
            ],
            'data' => $assets
        ]);
    }

    public function maintenanceCosts()
    {
        $maintenanceModel = model('MaintenanceHistoryModel');
        
        $maintenance = $maintenanceModel->select('maintenance_history.*, 
            assets.asset_name, 
            assets.unique_tag,
            asset_categories.category_name')
            ->join('assets', 'assets.asset_id = maintenance_history.asset_id')
            ->join('asset_categories', 'asset_categories.category_id = assets.category_id')
            ->orderBy('maintenance_history.maintenance_date', 'DESC')
            ->findAll();

        $totalCost = array_sum(array_column($maintenance, 'cost'));

        return $this->respond([
            'report_type' => 'Maintenance Costs Report',
            'generated_date' => date('Y-m-d H:i:s'),
            'total_maintenance_cost' => $totalCost,
            'total_maintenance_records' => count($maintenance),
            'data' => $maintenance
        ]);
    }
}
