<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Libraries\DepreciationCalculator;

class AssetController extends ResourceController
{
    protected $modelName = 'App\Models\AssetModel';
    protected $format    = 'json';

    public function index()
    {
        // Get query parameters for filtering
        $category = $this->request->getGet('category_id');
        $status = $this->request->getGet('status');
        $location = $this->request->getGet('location');
        
        $query = $this->model->select('assets.*, asset_categories.category_name, suppliers.supplier_name, depreciation_methods.method_name')
            ->join('asset_categories', 'asset_categories.category_id = assets.category_id')
            ->join('depreciation_methods', 'depreciation_methods.method_id = assets.depreciation_method_id')
            ->join('suppliers', 'suppliers.supplier_id = assets.supplier_id', 'left');
        
        // Apply filters if provided
        if ($category) {
            $query->where('assets.category_id', $category);
        }
        if ($status) {
            $query->where('assets.status', $status);
        }
        if ($location) {
            $query->like('assets.location', $location);
        }
        
        $assets = $query->findAll();
        return $this->respond($assets);
    }

    public function show($id = null)
    {
        // Get the asset with related data
        $asset = $this->model->select('assets.*, asset_categories.category_name, suppliers.supplier_name, depreciation_methods.method_name')
            ->join('asset_categories', 'asset_categories.category_id = assets.category_id')
            ->join('depreciation_methods', 'depreciation_methods.method_id = assets.depreciation_method_id')
            ->join('suppliers', 'suppliers.supplier_id = assets.supplier_id', 'left')
            ->find($id);
            
        if ($asset === null) {
            return $this->failNotFound('Asset not found');
        }
        
        // Get depreciation schedule
        $depreciationModel = model('DepreciationScheduleModel');
        $asset['depreciation_schedule'] = $depreciationModel->where('asset_id', $id)
            ->orderBy('depreciation_date', 'ASC')
            ->findAll();
        
        // Get maintenance history
        $maintenanceModel = model('MaintenanceHistoryModel');
        $asset['maintenance_history'] = $maintenanceModel->where('asset_id', $id)
            ->orderBy('maintenance_date', 'DESC')
            ->findAll();
        
        // Get transfer history
        $transferModel = model('AssetTransferModel');
        $asset['transfers'] = $transferModel->where('asset_id', $id)
            ->orderBy('transfer_date', 'DESC')
            ->findAll();
        
        // Get condition history
        $conditionModel = model('AssetConditionModel');
        $asset['conditions'] = $conditionModel->where('asset_id', $id)
            ->orderBy('assessment_date', 'DESC')
            ->findAll();
        
        // Get disposal information if disposed
        if ($asset['status'] === 'Disposed') {
            $disposalModel = model('AssetDisposalModel');
            $asset['disposal'] = $disposalModel->where('asset_id', $id)
                ->orderBy('disposal_date', 'DESC')
                ->first();
        }
        
        return $this->respond($asset);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        // Validate unique tag
        $existingAsset = $this->model->where('unique_tag', $data['unique_tag'])->first();
        if ($existingAsset) {
            return $this->fail('Asset tag must be unique');
        }
        
        // Check if category exists
        $categoryModel = model('AssetCategoryModel');
        if (!$categoryModel->find($data['category_id'])) {
            return $this->fail('Invalid category');
        }
        
        // Check if depreciation method exists
        $methodModel = model('DepreciationMethodModel');
        if (!$methodModel->find($data['depreciation_method_id'])) {
            return $this->fail('Invalid depreciation method');
        }
        
        // Check if supplier exists (if provided)
        if (!empty($data['supplier_id'])) {
            $supplierModel = model('SupplierModel');
            if (!$supplierModel->find($data['supplier_id'])) {
                return $this->fail('Invalid supplier');
            }
        }
        
        // Set initial current_value to acquisition_cost
        $data['current_value'] = $data['acquisition_cost'];
        
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'Active';
        }
        
        // Begin transaction
        $db = db_connect();
        $db->transBegin();
        
        try {
            // Insert asset
            if (!$this->model->insert($data)) {
                throw new \Exception(implode(' ', $this->model->errors()));
            }
            
            $assetId = $this->model->getInsertID();
            
            // Calculate initial depreciation schedule
            $calculator = new DepreciationCalculator();
            $schedule = $calculator->calculate(
                $data['acquisition_cost'],
                $data['salvage_value'],
                $data['useful_life_years'],
                $data['depreciation_method_id'],
                date('Y-m-d', strtotime($data['acquisition_date']))
            );
            
            // Save depreciation schedule
            $scheduleModel = model('DepreciationScheduleModel');
            foreach ($schedule as $entry) {
                $entry['asset_id'] = $assetId;
                if (!$scheduleModel->insert($entry)) {
                    throw new \Exception('Failed to create depreciation schedule');
                }
            }
            
            $db->transCommit();
            
            return $this->respondCreated([
                'message' => 'Asset created successfully',
                'id' => $assetId
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Check if asset exists
        $asset = $this->model->find($id);
        if ($asset === null) {
            return $this->failNotFound('Asset not found');
        }
        
        // Validate unique tag if changed
        if (isset($data['unique_tag']) && $data['unique_tag'] !== $asset['unique_tag']) {
            $existingAsset = $this->model->where('unique_tag', $data['unique_tag'])
                ->where('asset_id !=', $id)
                ->first();
            if ($existingAsset) {
                return $this->fail('Asset tag must be unique');
            }
        }
        
        // Prevent changing key depreciation parameters after creation
        // as they would invalidate existing depreciation schedules
        $protectedFields = [
            'acquisition_cost', 'acquisition_date', 'useful_life_years', 
            'salvage_value', 'depreciation_method_id'
        ];
        
        foreach ($protectedFields as $field) {
            if (isset($data[$field]) && $data[$field] != $asset[$field]) {
                return $this->fail("Cannot modify $field after asset creation. These changes would require recalculating depreciation.");
            }
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Asset updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if asset exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Asset not found');
        }
        
        // All related records will be deleted due to CASCADE
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Asset and all related records deleted successfully']);
        }
        
        return $this->fail('Failed to delete asset');
    }

    // Calculate and store depreciation for an asset
    public function depreciate($id = null)
    {
        // Check if asset exists
        $asset = $this->model->find($id);
        if ($asset === null) {
            return $this->failNotFound('Asset not found');
        }
        
        // Only active assets can be depreciated
        if ($asset['status'] !== 'Active') {
            return $this->fail('Cannot calculate depreciation for non-active assets');
        }
        
        // Get the calculator library
        $calculator = new DepreciationCalculator();
        
        // Begin transaction
        $db = db_connect();
        $db->transBegin();
        
        try {
            // Calculate next depreciation
            $scheduleModel = model('DepreciationScheduleModel');
            $latestEntry = $scheduleModel->where('asset_id', $id)
                ->orderBy('depreciation_date', 'DESC')
                ->first();
            
            if ($latestEntry) {
                $nextEntry = $calculator->calculateNextDepreciation(
                    $asset, 
                    $latestEntry
                );
                
                // Insert new depreciation entry
                if (!$scheduleModel->insert($nextEntry)) {
                    throw new \Exception('Failed to create depreciation entry');
                }
                
                // Update asset's current value
                if (!$this->model->update($id, [
                    'current_value' => $nextEntry['book_value_after_depreciation']
                ])) {
                    throw new \Exception('Failed to update asset current value');
                }
                
                $db->transCommit();
                return $this->respond([
                    'message' => 'Depreciation calculated successfully',
                    'entry' => $nextEntry
                ]);
            } else {
                throw new \Exception('No depreciation schedule found for this asset');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }
}
