<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class MaintenanceController extends ResourceController
{
    protected $modelName = 'App\Models\MaintenanceHistoryModel';
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
        
        $records = $this->model->where('asset_id', $assetId)
            ->orderBy('maintenance_date', 'DESC')
            ->findAll();
        
        return $this->respond($records);
    }

    public function show($id = null)
    {
        $record = $this->model->find($id);
        if ($record === null) {
            return $this->failNotFound('Maintenance record not found');
        }
        return $this->respond($record);
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
        
        // Begin transaction
        $db = db_connect();
        $db->transBegin();
        
        try {
            // Insert maintenance record
            if (!$this->model->insert($data)) {
                throw new \Exception(implode(' ', $this->model->errors()));
            }
            
            // If asset status needs to be updated to "In Maintenance"
            if (isset($data['update_status']) && $data['update_status'] === true) {
                if (!$assetModel->update($data['asset_id'], ['status' => 'In Maintenance'])) {
                    throw new \Exception('Failed to update asset status');
                }
            }
            
            $db->transCommit();
            
            return $this->respondCreated([
                'message' => 'Maintenance record created successfully',
                'id' => $this->model->getInsertID()
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Check if record exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Maintenance record not found');
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Maintenance record updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if record exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Maintenance record not found');
        }
        
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Maintenance record deleted successfully']);
        }
        return $this->fail('Failed to delete maintenance record');
    }
    
    // Complete maintenance and change asset status back to Active
    public function complete($id = null)
    {
        // Check if record exists
        $record = $this->model->find($id);
        if ($record === null) {
            return $this->failNotFound('Maintenance record not found');
        }
        
        $assetId = $record['asset_id'];
        
        // Begin transaction
        $db = db_connect();
        $db->transBegin();
        
        try {
            // Update maintenance record with completion details
            $data = $this->request->getJSON(true);
            if (!$this->model->update($id, $data)) {
                throw new \Exception(implode(' ', $this->model->errors()));
            }
            
            // Update asset status back to Active
            $assetModel = model('AssetModel');
            $asset = $assetModel->find($assetId);
            
            if ($asset['status'] === 'In Maintenance') {
                if (!$assetModel->update($assetId, ['status' => 'Active'])) {
                    throw new \Exception('Failed to update asset status');
                }
            }
            
            $db->transCommit();
            return $this->respond(['message' => 'Maintenance completed successfully']);
            
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }
}
