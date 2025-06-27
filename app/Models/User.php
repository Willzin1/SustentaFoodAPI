<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The list of admin emails.
     *
     * @var list<string>
     */
    private static $adminEmails = [
        'admin@example.com',
        'william.mendonca34@gmail.com',
        'rianaoliver1910@gmail.com',
        'stephanychryscv@gmail.com',
        'sofiaoliveirasantos2007@gmail.com',
        'eusouwelinton@gmail.com'
    ];

    /**
     * Check if the given email belongs to an admin.
     *
     * @param string $email
     * @return bool
     */
    public static function isAdmin(string $email): bool
    {
        return in_array($email, self::$adminEmails);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }


    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }
}
