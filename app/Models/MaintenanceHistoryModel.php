<?php

namespace App\Models;

use CodeIgniter\Model;

class MaintenanceHistoryModel extends Model
{
    protected $table = 'maintenance_history';
    protected $primaryKey = 'maintenance_id';
    protected $allowedFields = ['asset_id', 'maintenance_date', 'description', 'cost', 'performed_by', 'next_due_date'];
    protected $useTimestamps = false;
}
