<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class MaintenanceController extends ResourceController
{
    protected $modelName = 'App\Models\MaintenanceHistoryModel';
    protected $format    = 'json';

    public function getByAsset($assetId)
    {
        $maintenance = $this->model->where('asset_id', $assetId)->findAll();
        return $this->respond($maintenance);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        if ($this->model->save($data)) {
            return $this->respondCreated(['message' => 'Maintenance record created']);
        }
        return $this->fail($this->model->errors());
    }
}
