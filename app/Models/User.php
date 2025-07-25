<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'username',
        'email',
        'no_hp',
        'alamat',
        'password',
        'level',
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

   public function anak()
    {
        return $this->hasManyThrough(
            Anak::class,
            OrangTua::class,
            'users_id',     // foreign key di tabel orang_tua yang mengarah ke user
            'orang_tua_id', // foreign key di tabel anak yang mengarah ke orang_tua
            'id',           // primary key user
            'id'            // primary key orang_tua
        );
    }


    public function orangTua()
    {
        return $this->hasOne(OrangTua::class, 'users_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'orang_tua_id');
    }


}
