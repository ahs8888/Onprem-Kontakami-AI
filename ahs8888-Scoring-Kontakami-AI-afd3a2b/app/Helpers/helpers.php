<?php

if (!function_exists('user')) {
     function user(): mixed
     {
          return session(config('services.session-user-prefix'));
     }
}


if (!function_exists('admin')) {
     function admin(): mixed
     {
          return session(config('services.session-admin-prefix'));
     }
}


if (!function_exists('is_request_in')) {
     function is_request_in($allowedUrl, $url)
     {
          foreach ($allowedUrl as $string) {
               if (str_contains($url, $string)) {
                    return true;
               }
          }
          return false;
     }
}

if (!function_exists('id_to_uuid')) {
     function id_to_uuid($id)
     {
          if (!$id) {
               return null;
          }
          $compressed = gzcompress($id);
          return base64_encode(substr(base64_encode($compressed), 0, 36));
     }
}


if (!function_exists('id_from_uuid')) {
     function id_from_uuid($uuid)
     {
          if (!$uuid) {
               return null;
          }
          $decoded = base64_decode($uuid);
          $decoded = base64_decode($decoded);
          return gzuncompress($decoded);
     }
}