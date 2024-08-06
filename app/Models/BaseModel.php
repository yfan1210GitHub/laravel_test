<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function relationship()
    {
        return [];
    }

    # SCOPE
    public function loadRelationship()
    {
        return $this->loadMissing($this->relationship());
    }
}
