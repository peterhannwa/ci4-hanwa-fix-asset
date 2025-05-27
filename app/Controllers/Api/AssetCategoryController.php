<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class AssetCategoryController extends ResourceController
{
    protected $modelName = 'App\Models\AssetCategoryModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $category = $this->model->find($id);
        if ($category === null) {
            return $this->failNotFound('Asset category not found');
        }
        return $this->respond($category);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        
        // Validate category_name is unique
        $existingCategory = $this->model->where('category_name', $data['category_name'])->first();
        if ($existingCategory) {
            return $this->fail('Category name must be unique');
        }
        
        if ($this->model->insert($data)) {
            return $this->respondCreated([
                'message' => 'Asset category created successfully', 
                'id' => $this->model->getInsertID()
            ]);
        }
        
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Check if category exists
        $category = $this->model->find($id);
        if ($category === null) {
            return $this->failNotFound('Asset category not found');
        }
        
        // Validate category_name is unique (excluding current record)
        if (isset($data['category_name']) && $data['category_name'] !== $category['category_name']) {
            $existingCategory = $this->model->where('category_name', $data['category_name'])->first();
            if ($existingCategory) {
                return $this->fail('Category name must be unique');
            }
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Asset category updated successfully']);
        }
        
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if category exists
        $category = $this->model->find($id);
        if ($category === null) {
            return $this->failNotFound('Asset category not found');
        }
        
        // Check if category is in use (RESTRICT)
        $assetModel = model('AssetModel');
        $assetsUsingCategory = $assetModel->where('category_id', $id)->countAllResults();
        if ($assetsUsingCategory > 0) {
            return $this->fail('Cannot delete category because it is in use by assets');
        }
        
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Asset category deleted successfully']);
        }
        
        return $this->fail('Failed to delete asset category');
    }
}
