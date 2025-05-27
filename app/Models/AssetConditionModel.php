<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetConditionModel extends Model
{
    protected $table = 'asset_conditions';
    protected $primaryKey = 'condition_id';
    protected $allowedFields = ['asset_id', 'assessment_date', 'condition_rating', 'notes', 'assessed_by'];
    protected $useTimestamps = false;
}
