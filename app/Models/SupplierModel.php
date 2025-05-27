<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';
    protected $allowedFields = ['supplier_name', 'contact_person', 'phone_number', 'email', 'address'];
    protected $useTimestamps = false;
}
