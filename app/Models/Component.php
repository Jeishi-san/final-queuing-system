<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model {
    protected $fillable = ['name'];

    public function tickets() {
        return $this->belongsToMany(Ticket::class, 'ticket_components');
    }
}

