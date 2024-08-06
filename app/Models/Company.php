<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';
    protected $guarded = ['id'];

    
    # Base Relationship
    public function relationship()
    {
        return ['employee'];
    }

    public function employee()
    {
        return $this->hasMany(Employee::class,'company_id','id');
    }
}
