<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    protected $table = 'assets';
    protected $primaryKey = 'asset_id';
    protected $allowedFields = [
        'unique_tag', 'asset_name', 'description', 'category_id',
        'acquisition_date', 'acquisition_cost', 'supplier_id',
        'warranty_expiration_date', 'current_value', 'useful_life_years',
        'salvage_value', 'depreciation_method_id', 'location', 'status',
        'serial_number', 'manufacturer', 'purchase_order_number'
    ];
    protected $useTimestamps = false;
}
