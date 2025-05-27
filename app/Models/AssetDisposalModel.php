<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetDisposalModel extends Model
{
    protected $table = 'asset_disposals';
    protected $primaryKey = 'disposal_id';
    protected $allowedFields = ['asset_id', 'disposal_date', 'disposal_method', 'sale_price', 'reason', 'disposed_by'];
    protected $useTimestamps = false;
}
