<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class Yellow
{
    public static function phoneCountryCode()
    {
        return json_decode(file_get_contents(resource_path('assets/phone.json')), true);
    }

    public static function uploadFile($file, $location = 'image',$name = null)
    {
        if (config('app.env') === 'development') {
            return Storage::disk('local')->put("uploads/{$location}", $file);
        }
        $path = Storage::disk('s3')->putFileAs('', $file,$name ?: $file->getClientOriginalName());

        return Storage::cloud()->url($path);
    }

    public static function deleteFile($url)
    {
        if ($url) {
            if ((!str_contains($url, 'http://') || !str_contains($url, 'https://')) && Storage::exists($url)) {
                Storage::disk('local')->delete($url);
            } else {
                $url = str_replace(config('filesystems.disks.s3.url'), '', $url);
                if (Storage::disk('s3')->exists($url)) {
                        Storage::disk('s3')->delete($url);
                }
            }
        }
    }

    public static function phoneNumber($phone, $prefix = '62')
    {
        $start = substr($phone, 0, 1);
        $prefix = str_replace('+', '', $prefix);
        if ($start == '0') {
            return $prefix . ltrim($phone, '0');
        } else {
            return $prefix . $phone;
        }
    }

    public static function createUsername($name)
    {
        $name = substr(strtolower(str($name)->replace(' ', '_')), 0, 7);
        $rand = rand(111, 999);

        return $name . $rand;
    }

    public static function normalPhoneNumber($phone, $code)
    {
        return substr($phone, strlen($code));
    }

    public static function updateUserSession($replacement = [])
    {
        $sessionObject = [
            ...(array) user(),
            ...$replacement
        ];

        session()->put(config('services.session-user-prefix'), (object) $sessionObject);
    }
}
