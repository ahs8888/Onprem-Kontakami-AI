<?php
namespace App\Helpers;

use App\Models\Account\Admin;
use App\Models\Account\User;
use Illuminate\Support\Facades\Hash;

class Kontakami
{
     public static function runInBackgroundArtisan($artisanCommand, $param = null)
     {
          logger($artisanCommand);
          if (!in_array($artisanCommand, ['app:run-recording-scoring', 'app:run-agent-scoring'])) {
               return;
          }
          // get base of php location
          $php = escapeshellcmd((PHP_OS_FAMILY === 'Windows') ? 'php' : config('services.PHP_LOCATION'));

          // base path project to run command
          $basePath = base_path();

          if ($param) {
               $artisanCommand = $artisanCommand . ' ' . $param;
          }
          $cmd = "$php artisan $artisanCommand";

          $fullcommand = null;
          if (PHP_OS_FAMILY === 'Windows') {
               // Windows
               // pclose(popen("start /B cmd /C \"cd /d $basePath && $cmd\"", "r"));
               $fullcommand = "start /B cmd /C \"cd /d $basePath && $cmd\"";
          } else {
               // Linux/macOS
               // pclose(popen("cd $basePath && $cmd > /dev/null 2>&1 &", "r"));
               $fullcommand = "cd $basePath && $cmd > /dev/null 2>&1 &";
          }
          exec($fullcommand);

     }
     public static function phoneCountryCode()
     {
          return json_decode(file_get_contents(resource_path('assets/phone.json')), true);
     }

     public static function normalPhoneNumber($phone, $code)
     {
          return substr($phone, strlen($code));
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

     public static function generateCode()
     {
          $code = Hash::make(str()->uuid());
          $cleanCode = preg_replace('/[.\/\\\\\]\{\}\)]/', '', $code);
          $exist = User::where('code', $code)->first();
          if ($exist) {
               return self::generateCode();
          }

          return $cleanCode;
     }

     public static function putSessionUser(User $user)
     {
          $sessionObject = [
               'id' => $user->id,
               'role' => $user->role,
               'name' => $user->name,
               'company' => $user->company,
               'email' => $user->email,
               'code' => $user->code,
               'phone_code' => $user->phone_code,
               'phone_number' => Kontakami::normalPhoneNumber($user->phone, $user->phone_code),
               'avatar' => asset($user->profile),
               'app' => 'client'
          ];

          session()->put(config('services.session-user-prefix'), (object) $sessionObject);
     }


     public static function putSessionAdmin(Admin $admin)
     {
          $sessionObject = [
               'id' => $admin->id,
               'role' => $admin->role,
               'name' => $admin->name,
               'email' => $admin->email,
               'avatar' => $admin->profile ? asset($admin->profile) : 'https://yelow-app-storage.s3.ap-southeast-1.amazonaws.com/cnako525c6GTGkUq1nefIJ38mXinpV5JovDMuuws.png',
               'app' => 'admin'
          ];

          session()->put(config('services.session-admin-prefix'), (object) $sessionObject);
     }

     public static function transcribeToConversation($transcribe)
     {
          return collect($transcribe)->map(function ($text) {
               $role = str_contains($text, 'Agent: ') ? 'agent' : 'customer';
               $text = str_replace($role == 'agent' ? 'Agent: ' : 'Customer: ', '', $text);
               return [
                    'role' => $role,
                    'text' => $text
               ];
          })->toArray();
     }
}