<?php

namespace App\Models;

use CodeIgniter\Model;

class DepreciationScheduleModel extends Model
{
    protected $table = 'depreciation_schedules';
    protected $primaryKey = 'schedule_id';
    protected $allowedFields = ['asset_id', 'depreciation_date', 'depreciation_amount', 'accumulated_depreciation', 'book_value_after_depreciation'];
    protected $useTimestamps = false;
}
