<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceComplaint extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'resource_id',
        'motive_id',
    ];

    protected $with = ['user', 'resource', 'motive'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function motive()
    {
        return $this->belongsTo(Motive::class);
    }
}
