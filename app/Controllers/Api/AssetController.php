<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Exception;

class AssetController extends ResourceController
{
    protected $modelName = 'App\Models\AssetModel';
    protected $format    = 'json';

    // GET /api/assets
    public function index()
    {
        try {
            $assets = $this->model->findAll();
            if (empty($assets)) {
                return $this->respond([
                    'status' => 200,
                    'data' => [],
                    'message' => 'No assets found'
                ]);
            }
            return $this->respond([
                'status' => 200,
                'data' => $assets,
                'message' => 'Assets retrieved successfully'
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
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
