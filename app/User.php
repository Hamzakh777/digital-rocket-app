<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends \TCG\Voyager\Models\User
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Enabling soft delete for users
     * 
     */
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * Get the appointments assigned for the sales agents
     */
    public function appointments() {
        return $this->hasMany('App\Appointment', 'sales_agent_id', 'id');
    }

    /**
     * Get the appointments created by call agents
     */
    public function callAgentsAppointments()
    {
        return $this->hasMany('App\Appointment', 'call_agent_id', 'id');
    }

    /**
     * Get the call center for the user
     */
    public function callCenter()
    {
        return $this->belongsTo('App\CallCenter', 'call_center_id', 'id');
    }
}
