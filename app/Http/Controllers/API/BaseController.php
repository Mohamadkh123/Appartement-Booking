<?php
  
namespace App\Http\Controllers\API;
  
use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\JsonResponse;
  
class BaseController extends Controller
{
    public function sendResponse($result, string $message = '', int $status )
    {
        return response()->json([
            'message' => $message,
            'data'    => $result,
        ], $status);
    }

    public function sendError(string $message, array $errorMessages = [], int $status ): JsonResponse
    {
        $response = [
            'message' => $message,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $status);
    }

    public function sendPaginatedResponse($paginator, string $message = '', int $status ): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data'    => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ], $status);
    }
}