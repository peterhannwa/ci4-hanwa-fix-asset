<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class TransferController extends ResourceController
{
    protected $modelName = 'App\Models\AssetTransferModel';
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
        
        $transfers = $this->model->where('asset_id', $assetId)
            ->orderBy('transfer_date', 'DESC')
            ->findAll();
        
        return $this->respond($transfers);
    }

    public function show($id = null)
    {
        $transfer = $this->model->find($id);
        if ($transfer === null) {
            return $this->failNotFound('Transfer record not found');
        }
        return $this->respond($transfer);
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
        
        // Check if asset is available for transfer
        if ($asset['status'] !== 'Active') {
            return $this->fail('Only active assets can be transferred');
        }
        
        // Set from_location if not provided
        if (!isset($data['from_location']) || empty($data['from_location'])) {
            $data['from_location'] = $asset['location'];
        }
        
        // Begin transaction
        $db = db_connect();
        $db->transBegin();
        
        try {
            // Insert transfer record
            if (!$this->model->insert($data)) {
                throw new \Exception(implode(' ', $this->model->errors()));
            }
            
            // Update asset location and status
            $updateData = [
                'location' => $data['to_location'],
                'status' => 'Transferred'
            ];
            
            if (!$assetModel->update($data['asset_id'], $updateData)) {
                throw new \Exception('Failed to update asset location');
            }
            
            $db->transCommit();
            
            return $this->respondCreated([
                'message' => 'Asset transfer recorded successfully',
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
        
        // Check if transfer record exists
        $transfer = $this->model->find($id);
        if ($transfer === null) {
            return $this->failNotFound('Transfer record not found');
        }
        
        // Prevent changing asset_id
        if (isset($data['asset_id']) && $data['asset_id'] != $transfer['asset_id']) {
            return $this->fail('Cannot change the associated asset');
        }
        
        if ($this->model->update($id, $data)) {
            // If to_location changed, update asset location
            if (isset($data['to_location']) && $data['to_location'] != $transfer['to_location']) {
                $assetModel = model('AssetModel');
                $assetModel->update($transfer['asset_id'], [
                    'location' => $data['to_location']
                ]);
            }
            
            return $this->respond(['message' => 'Transfer record updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if record exists
        $transfer = $this->model->find($id);
        if ($transfer === null) {
            return $this->failNotFound('Transfer record not found');
        }
        
        // This could impact asset history, but we'll allow it
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Transfer record deleted successfully']);
        }
        return $this->fail('Failed to delete transfer record');
    }
    
    // Complete transfer by setting status back to Active
    public function complete($id = null)
    {
        // Check if transfer record exists
        $transfer = $this->model->find($id);
        if ($transfer === null) {
            return $this->failNotFound('Transfer record not found');
        }
        
        // Update asset status
        $assetModel = model('AssetModel');
        $asset = $assetModel->find($transfer['asset_id']);
        
        if ($asset && $asset['status'] === 'Transferred') {
            if ($assetModel->update($transfer['asset_id'], ['status' => 'Active'])) {
                return $this->respond(['message' => 'Transfer completed successfully']);
            } else {
                return $this->fail('Failed to update asset status');
            }
        }
        
        return $this->fail('Asset is not in transferred state');
    }
}
