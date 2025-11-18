<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $fillable=['user_id','document_id','rating','approved'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function document(){
        return $this->belongsTo(Document::class);
    }
}
