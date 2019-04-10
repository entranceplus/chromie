<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = ['user_id','url','link_content'];

    public function user(){
        return $this->belongsTo('App\User');
    }

}
