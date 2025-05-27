<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class CategoryController extends ResourceController
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
        return $category ? $this->respond($category) : $this->failNotFound('Category not found');
    }

    public function create()
    {
        $data = $this->request->getJSON();
        if ($this->model->save($data)) {
            return $this->respondCreated(['message' => 'Category created successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Category updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Category deleted successfully']);
        }
        return $this->fail('Failed to delete category');
    }
}
