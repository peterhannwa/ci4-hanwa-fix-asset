<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ConditionController extends ResourceController
{
    protected $modelName = 'App\Models\AssetConditionModel';
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
        
        $conditions = $this->model->where('asset_id', $assetId)
            ->orderBy('assessment_date', 'DESC')
            ->findAll();
        
        return $this->respond($conditions);
    }

    public function show($id = null)
    {
        $condition = $this->model->find($id);
        if ($condition === null) {
            return $this->failNotFound('Condition assessment not found');
        }
        return $this->respond($condition);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        // Check if asset exists
        $assetModel = model('AssetModel');
        $asset = $assetModel->find($data['asset_id']);
        if (!$asset) {
            return $this->fail('Invalid asset ID');
        }
        
        // Don't allow condition assessments for disposed assets
        if ($asset['status'] === 'Disposed') {
            return $this->fail('Cannot assess disposed assets');
        }
        
        if ($this->model->insert($data)) {
            // If condition is End-of-Life, suggest maintenance
            if (isset($data['condition_rating']) && $data['condition_rating'] === 'End-of-Life') {
                $message = 'Condition assessment recorded successfully. The asset is marked as End-of-Life. Consider planning for replacement.';
            } else {
                $message = 'Condition assessment recorded successfully';
            }
            
            return $this->respondCreated([
                'message' => $message,
                'id' => $this->model->getInsertID()
            ]);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Check if record exists
        $condition = $this->model->find($id);
        if ($condition === null) {
            return $this->failNotFound('Condition assessment not found');
        }
        
        // Prevent changing asset_id
        if (isset($data['asset_id']) && $data['asset_id'] != $condition['asset_id']) {
            return $this->fail('Cannot change the associated asset');
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Condition assessment updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if record exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Condition assessment not found');
        }
        
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Condition assessment deleted successfully']);
        }
        return $this->fail('Failed to delete condition assessment');
    }
}
