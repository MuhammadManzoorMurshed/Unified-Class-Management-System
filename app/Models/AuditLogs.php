<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogs extends Model
{
    protected $fillable = [
        'action',        // যেমন 'class.join', 'class.removeMember'
        'entity_type',   // যেমন 'Class', 'Enrollment'
        'entity_id',     // ক্লাস বা এনরোলমেন্ট ID
        'meta',          // অতিরিক্ত ডেটা (যেমন 'by' => user_id)
        'user_id',       // ইউজারের ID
        'ip_address',    // ইউজারের আইপি অ্যাড্রেস
    ];
}