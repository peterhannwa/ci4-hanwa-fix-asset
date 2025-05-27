<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetTransferModel extends Model
{
    protected $table = 'asset_transfers';
    protected $primaryKey = 'transfer_id';
    protected $allowedFields = ['asset_id', 'transfer_date', 'from_location', 'to_location', 'transferred_by', 'reason'];
    protected $useTimestamps = false;
}
