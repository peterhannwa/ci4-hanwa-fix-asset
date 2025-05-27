<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class DepreciationMethodController extends ResourceController
{
    protected $modelName = 'App\Models\DepreciationMethodModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $method = $this->model->find($id);
        if ($method === null) {
            return $this->failNotFound('Depreciation method not found');
        }
        return $this->respond($method);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        // Validate method_name is unique
        $existingMethod = $this->model->where('method_name', $data['method_name'])->first();
        if ($existingMethod) {
            return $this->fail('Depreciation method name must be unique');
        }
        
        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Depreciation method created successfully', 'id' => $this->model->getInsertID()]);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Check if method exists
        $method = $this->model->find($id);
        if ($method === null) {
            return $this->failNotFound('Depreciation method not found');
        }
        
        // Validate method_name is unique (excluding current record)
        if (isset($data['method_name']) && $data['method_name'] !== $method['method_name']) {
            $existingMethod = $this->model->where('method_name', $data['method_name'])->first();
            if ($existingMethod) {
                return $this->fail('Depreciation method name must be unique');
            }
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Depreciation method updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if method exists
        $method = $this->model->find($id);
        if ($method === null) {
            return $this->failNotFound('Depreciation method not found');
        }
        
        // Check if method is in use (RESTRICT)
        $assetModel = model('AssetModel');
        $assetsUsingMethod = $assetModel->where('depreciation_method_id', $id)->countAllResults();
        if ($assetsUsingMethod > 0) {
            return $this->fail('Cannot delete depreciation method because it is in use by assets');
        }
        
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Depreciation method deleted successfully']);
        }
        return $this->fail('Failed to delete depreciation method');
    }
}
