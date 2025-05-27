<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetCategoryModel extends Model
{
    protected $table = 'asset_categories';
    protected $primaryKey = 'category_id';
    protected $allowedFields = ['category_name', 'description'];
    protected $useTimestamps = false;
}
