<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class DisposalController extends ResourceController
{
    protected $modelName = 'App\Models\AssetDisposalModel';
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
        
        $disposals = $this->model->where('asset_id', $assetId)
            ->orderBy('disposal_date', 'DESC')
            ->findAll();
        
        return $this->respond($disposals);
    }

    public function show($id = null)
    {
        $disposal = $this->model->find($id);
        if ($disposal === null) {
            return $this->failNotFound('Disposal record not found');
        }
        return $this->respond($disposal);
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
        
        // Check if asset is already disposed
        if ($asset['status'] === 'Disposed') {
            return $this->fail('Asset is already disposed');
        }
        
        // Begin transaction
        $db = db_connect();
        $db->transBegin();
        
        try {
            // Insert disposal record
            if (!$this->model->insert($data)) {
                throw new \Exception(implode(' ', $this->model->errors()));
            }
            
            // Update asset status
            if (!$assetModel->update($data['asset_id'], ['status' => 'Disposed'])) {
                throw new \Exception('Failed to update asset status');
            }
            
            $db->transCommit();
            
            return $this->respondCreated([
                'message' => 'Asset disposal recorded successfully',
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
        
        // Check if disposal record exists
        $disposal = $this->model->find($id);
        if ($disposal === null) {
            return $this->failNotFound('Disposal record not found');
        }
        
        // Prevent changing asset_id
        if (isset($data['asset_id']) && $data['asset_id'] != $disposal['asset_id']) {
            return $this->fail('Cannot change the associated asset');
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Disposal record updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if record exists
        $disposal = $this->model->find($id);
        if ($disposal === null) {
            return $this->failNotFound('Disposal record not found');
        }
        
        // Begin transaction - need to revert asset status
        $db = db_connect();
        $db->transBegin();
        
        try {
            // Delete disposal record
            if (!$this->model->delete($id)) {
                throw new \Exception('Failed to delete disposal record');
            }
            
            // Check if there are other disposal records for this asset
            $otherDisposals = $this->model->where('asset_id', $disposal['asset_id'])
                ->countAllResults();
            
            // If no other disposal records, revert asset status to Active
            if ($otherDisposals === 0) {
                $assetModel = model('AssetModel');
                if (!$assetModel->update($disposal['asset_id'], ['status' => 'Active'])) {
                    throw new \Exception('Failed to update asset status');
                }
            }
            
            $db->transCommit();
            return $this->respondDeleted(['message' => 'Disposal record deleted successfully']);
            
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }
}
