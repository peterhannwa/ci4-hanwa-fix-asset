<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class DisposalController extends ResourceController
{
    protected $modelName = 'App\Models\AssetDisposalModel';
    protected $format    = 'json';

    public function create()
    {
        $data = $this->request->getJSON();
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Create disposal record
            $this->model->save($data);
            
            // Update asset status
            $assetModel = model('AssetModel');
            $assetModel->update($data->asset_id, ['status' => 'Disposed']);
            
            $db->transCommit();
            return $this->respondCreated(['message' => 'Asset disposed successfully']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage());
        }
    }
}
