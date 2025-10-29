<?php
namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

trait JsonResponse
{
    public function success($data = [], $message = "success")
    {
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function message($message,$code = Response::HTTP_OK)
    {
        return response()->json([
            'status' => $code,
            'message' => $message,
        ],$code);
    }

    public function unauthorized($message, $err = "Unauthorized")
    {
        return response()->json([
            'status' => Response::HTTP_UNAUTHORIZED,
            'error' => $err,
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function badRequest($message, $err = "Bad Request")
    {
        return response()->json([
            'status' => Response::HTTP_BAD_REQUEST,
            'error' => $err,
            'message' => $message,
        ], Response::HTTP_BAD_REQUEST);
    }

    public function forbidden($message, $err = "Forbidden")
    {
        return response()->json([
            'status' => Response::HTTP_FORBIDDEN,
            'error' => $err,
            'message' => $message,
        ], Response::HTTP_FORBIDDEN);
    }
    public function validates($rules, $message = [], $attributes = [])
    {
        $validator = Validator::make(request()->all(), $rules, $message, $attributes);
        if ($validator->fails()) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
            ->send();
        }
    }

    public function validateException($message = [])
    {
        return response()->json([
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => collect($message)->first(),
            'error' => $message
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
