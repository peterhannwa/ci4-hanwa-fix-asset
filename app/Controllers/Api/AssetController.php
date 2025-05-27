<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AssetModel;

class AssetController extends ResourceController
{
    protected $modelName = 'App\Models\AssetModel';
    protected $format    = 'json';

    // GET /api/assets
    public function index()
    {
        $assets = $this->model->findAll();
        return $this->respond($assets);
    }

    // GET /api/assets/{id}
    public function show($id = null)
    {
        $asset = $this->model->find($id);
        if ($asset === null) {
            return $this->failNotFound('Asset not found');
        }
        return $this->respond($asset);
    }

    // POST /api/assets
    public function create()
    {
        $data = $this->request->getJSON();
        
        if ($this->model->save($data)) {
            return $this->respondCreated(['message' => 'Asset created successfully']);
        }
        
        return $this->fail($this->model->errors());
    }

    // PUT /api/assets/{id}
    public function update($id = null)
    {
        $data = $this->request->getJSON();
        
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Asset not found');
        }

        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Asset updated successfully']);
        }

        return $this->fail($this->model->errors());
    }

    // DELETE /api/assets/{id}
    public function delete($id = null)
    {
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Asset not found');
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Asset deleted successfully']);
        }

        return $this->fail('Failed to delete asset');
    }
}
