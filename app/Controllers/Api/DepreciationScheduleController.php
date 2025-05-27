<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class DepreciationScheduleController extends ResourceController
{
    protected $modelName = 'App\Models\DepreciationScheduleModel';
    protected $format    = 'json';

    public function index()
    {
        $assetId = $this->request->getGet('asset_id');
        
        if ($assetId) {
            return $this->getByAsset($assetId);
        }
        
        return $this->respond($this->model->findAll());
    }
    
    public function getByAsset($assetId)
    {
        // Verify asset exists
        $assetModel = model('AssetModel');
        if (!$assetModel->find($assetId)) {
            return $this->failNotFound('Asset not found');
        }
        
        $schedules = $this->model->where('asset_id', $assetId)
            ->orderBy('depreciation_date', 'ASC')
            ->findAll();
        
        return $this->respond($schedules);
    }

    public function show($id = null)
    {
        $schedule = $this->model->find($id);
        if ($schedule === null) {
            return $this->failNotFound('Depreciation schedule not found');
        }
        return $this->respond($schedule);
    }

    // Depreciation schedules are typically auto-generated, not manually created
    // But we'll provide the endpoint for completeness
    public function create()
    {
        $data = $this->request->getJSON(true);
        
        // Check if asset exists
        $assetModel = model('AssetModel');
        if (!$assetModel->find($data['asset_id'])) {
            return $this->fail('Invalid asset ID');
        }
        
        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'message' => 'Depreciation schedule entry created successfully',
                'id' => $this->model->getInsertID()
            ]);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Check if schedule exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Depreciation schedule not found');
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Depreciation schedule updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if schedule exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Depreciation schedule not found');
        }
        
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Depreciation schedule deleted successfully']);
        }
        return $this->fail('Failed to delete depreciation schedule');
    }
}
