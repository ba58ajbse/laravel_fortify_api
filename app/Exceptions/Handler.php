<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->header('Content-Type') === 'application/json' || $request->is('api/*')) {
                $message = $e->getMessage();
                $status_code = $this->getErrorCode($e);

                return response()->json(
                    ['message' => $message],
                    $status_code,
                );
            }
        });
    }

    private function getErrorCode(Throwable $e): int
    {
        $log_message = $e->getMessage();

        if ($e instanceof HttpException) {
            Log::error('Http Exception: ' . $log_message);
            return $e->getStatusCode();
        }

        switch ($e) {
            case $e instanceof AuthenticationException:
                Log::error('Authentication Exception: ' . $log_message);
                return Response::HTTP_UNAUTHORIZED;
            case $e instanceof QueryException:
                Log::error('Query Exception: ' . $log_message);
                return Response::HTTP_INTERNAL_SERVER_ERROR;
            case $e instanceof ValidationException:
                Log::error('Validation Exception: ' . $log_message);
                return Response::HTTP_BAD_REQUEST;
            case $e instanceof RouteNotFoundException:
                Log::error('Route Not Found Exception: ' . $log_message);
                return Response::HTTP_NOT_FOUND;
            default:
                Log::error('Internal Server Error: ' . $log_message);
                return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }
}
