<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ConditionController extends ResourceController
{
    protected $modelName = 'App\Models\AssetConditionModel';
    protected $format    = 'json';

    public function getByAsset($assetId)
    {
        $conditions = $this->model->where('asset_id', $assetId)
            ->orderBy('assessment_date', 'DESC')
            ->findAll();
        return $this->respond($conditions);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        if ($this->model->save($data)) {
            return $this->respondCreated(['message' => 'Condition assessment recorded']);
        }
        return $this->fail($this->model->errors());
    }
}
