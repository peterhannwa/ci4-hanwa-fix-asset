<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Exception;
use App\Libraries\DepreciationCalculator;

class AssetController extends ResourceController
{
    protected $modelName = 'App\Models\AssetModel';
    protected $format    = 'json';

    // GET /api/assets
    public function index()
    {
        try {
            $assets = $this->model->select('assets.*, 
                asset_categories.category_name,
                suppliers.supplier_name,
                depreciation_methods.method_name')
                ->join('asset_categories', 'asset_categories.category_id = assets.category_id')
                ->join('suppliers', 'suppliers.supplier_id = assets.supplier_id', 'left')
                ->join('depreciation_methods', 'depreciation_methods.method_id = assets.depreciation_method_id')
                ->findAll();
            
            if (empty($assets)) {
                return $this->respond([
                    'status' => 200,
                    'data' => [],
                    'message' => 'No assets found'
                ]);
            }
            return $this->respond([
                'status' => 200,
                'data' => $assets,
                'message' => 'Assets retrieved successfully'
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // GET /api/assets/{id}
    public function show($id = null)
    {
        $asset = $this->model->find($id);
        if ($asset === null) {
            return $this->failNotFound('Asset not found');
        }

        // Get related data
        $asset['depreciation_schedule'] = model('DepreciationScheduleModel')
            ->where('asset_id', $id)
            ->findAll();
        $asset['maintenance_history'] = model('MaintenanceHistoryModel')
            ->where('asset_id', $id)
            ->findAll();

        return $this->respond($asset);
    }

    // POST /api/assets
    public function create()
    {
        $data = $this->request->getJSON();
        
        // Calculate initial depreciation
        $calculator = new DepreciationCalculator();
        $data->current_value = $data->acquisition_cost;
        
        if ($this->model->save($data)) {
            $assetId = $this->model->getInsertID();
            
            // Create initial depreciation schedule
            $this->calculateDepreciation($assetId);
            
            return $this->respondCreated(['message' => 'Asset created successfully']);
        }
        
        return $this->fail($this->model->errors());
    }

    // PUT /api/assets/{id}
    public function update($id = null)
    {
        $data = $this->request->getJSON();
        
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Asset not found');
        }

        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Asset updated successfully']);
        }

        return $this->fail($this->model->errors());
    }

    // DELETE /api/assets/{id}
    public function delete($id = null)
    {
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Asset not found');
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Asset deleted successfully']);
        }

        return $this->fail('Failed to delete asset');
    }

    protected function calculateDepreciation($assetId)
    {
        $asset = $this->model->find($assetId);
        $calculator = new DepreciationCalculator();
        $schedule = $calculator->calculate(
            $asset['acquisition_cost'],
            $asset['salvage_value'],
            $asset['useful_life_years'],
            $asset['depreciation_method_id']
        );

        // Save depreciation schedule
        $scheduleModel = model('DepreciationScheduleModel');
        foreach ($schedule as $entry) {
            $entry['asset_id'] = $assetId;
            $scheduleModel->insert($entry);
        }
    }
}
