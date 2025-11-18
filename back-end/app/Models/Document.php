<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $fillable=['extension','decision'];


    public function user(){
        return $this->belongsTo(User::class);
    }
    public function feedbacks(){
        return $this->hasMany(Feedback::class);
    }
}
