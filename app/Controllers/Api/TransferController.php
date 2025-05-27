<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class TransferController extends ResourceController
{
    protected $modelName = 'App\Models\AssetTransferModel';
    protected $format    = 'json';

    public function getByAsset($assetId)
    {
        $transfers = $this->model->where('asset_id', $assetId)->findAll();
        return $this->respond($transfers);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Create transfer record
            $this->model->save($data);
            
            // Update asset location
            $assetModel = model('AssetModel');
            $assetModel->update($data->asset_id, ['location' => $data->to_location]);
            
            $db->transCommit();
            return $this->respondCreated(['message' => 'Transfer recorded successfully']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }
}
