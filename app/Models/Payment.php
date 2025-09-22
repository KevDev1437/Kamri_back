<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id','order_id','provider','intent_id','status','amount','currency','last_error','meta'
    ];

    protected $casts = ['last_error' => 'array', 'meta' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
    public function order() { return $this->belongsTo(Order::class); }

    public function mark(string $status, array $extra = []): self {
        $data = array_merge(['status' => $status], $extra);
        $this->fill($data)->save();
        return $this;
    }
}
