<?php

namespace App\Models;

use CodeIgniter\Model;

class DepreciationMethodModel extends Model
{
    protected $table = 'depreciation_methods';
    protected $primaryKey = 'method_id';
    protected $allowedFields = ['method_name', 'description'];
    protected $useTimestamps = false;
}
