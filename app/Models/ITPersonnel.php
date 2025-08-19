<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ITPersonnel extends Model {
    protected $table = 'it_personnel';
    protected $fillable = ['name','email','password'];
    protected $hidden = ['password'];

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }
}
