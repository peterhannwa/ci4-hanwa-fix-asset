<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class SupplierController extends ResourceController
{
    protected $modelName = 'App\Models\SupplierModel';
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $supplier = $this->model->find($id);
        if ($supplier === null) {
            return $this->failNotFound('Supplier not found');
        }
        return $this->respond($supplier);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if ($this->model->insert($data)) {
            return $this->respondCreated(['message' => 'Supplier created successfully', 'id' => $this->model->getInsertID()]);
        }
        return $this->fail($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        
        // Check if supplier exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Supplier not found');
        }
        
        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Supplier updated successfully']);
        }
        return $this->fail($this->model->errors());
    }

    public function delete($id = null)
    {
        // Check if supplier exists
        if ($this->model->find($id) === null) {
            return $this->failNotFound('Supplier not found');
        }
        
        // For supplier, we can delete even if in use by assets (SET NULL)
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Supplier deleted successfully']);
        }
        return $this->fail('Failed to delete supplier');
    }
}
