<?php

namespace App\Models\Util;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpToken extends Model
{
    use HasFactory;

    protected $table = 'otp_tokens';

    protected $guarded = [];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($otpToken) {
            OtpToken::where('source', $otpToken->source)->delete();
        });
    }

    public function scopeActiveToken($query, $email)
    {
        $query->whereEmail($email)->where('available_until', '>=', now());
    }


    public static function findDataFromToken($token,$index)
    {
        $tokens = explode('|', base64_decode($token));

        return $tokens[$index];
    }

    public static function createToken($email,$token,$role,$action)
    {
        return base64_encode("{$email}|{$token}|{$role}|{$action}");
    }
}
