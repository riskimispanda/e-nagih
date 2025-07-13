<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
  use HasFactory, Notifiable;
  
  /**
  * The attributes that are mass assignable.
  *
  * @var array<int, string>
  */
  protected $fillable = [
    'name',
    'email',
    'password',
    'roles_id'
  ];
  
  /**
  * The attributes that should be hidden for serialization.
  *
  * @var array<int, string>
  */
  protected $hidden = [
    'password',
    'remember_token',
  ];
  
  /**
  * Get the attributes that should be cast.
  *
  * @return array<string, string>
  */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }
  public function roles()
  {
    return $this->belongsTo(Roles::class, 'roles_id');
  }
  public function logActivities()
  {
    return $this->hasMany(LogActivity::class);
  }

  public function customer()
  {
    return $this->hasMany(Customer::class, 'agen_id');
  }

}
