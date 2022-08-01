<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
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
    }



    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {

        //formate les retours d'erreurs

        switch (class_basename($exception)){

            case 'TokenMismatchException':
                if ($request->expectsJson()){
                    return response()->json([
                        'error' => 66,
                        'errors' => ['forms' => 'Your request was denied. Please try again or reload your page']
                    ], 403);
                }
                break;

            case 'ThrottleRequestsException':
                return response()->json([
                    'errors' => ['forms' => 'You have been rate limited, please try again shortly']], 429);
                break;
            case 'MethodNotAllowedHttpException':
                if ($request->expectsJson()){
                    return response()->json(['errors' => ['forms' => 'Method Not Allowed']],405);
                }
                break;
            case 'NotFoundHttpException':
                if ($request->expectsJson()){
                    return response()->json([
                        'message' => $exception->getMessage(),
                        'error' => $exception->getTraceAsString(),
                        'line' => $exception->getLine()
                    ], 404);
                }
                break;
            case 'MaintenanceModeException':
                if ($request->expectsJson()){
                    return response()->json(['errors' => ['forms' => 'The site is currently down for maintenance, please check back with us soon']],503);
                }
                break;

            case 'AuthenticationException':
            case 'ValidationException':
                if($request->expectsJson()){
                    if($exception instanceof ValidationException){
                        return response()->json([
                            'message' => $exception->getMessage(),
                            'error' => $exception->validator->errors()
                        ], 422);
                    }
                }
                break;

        }

        /*
         *   if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                        'message' => $exception->getMessage(),
                        'error' => 'Resouce not found'
                    ], 404);
            }
         */

        if (app()->isProduction()){
            if ($request->expectsJson()){
                return response()->json('Server Error',500);
            }
            //return response()->view('errors.500', [], 500);
        }

        return parent::render($request, $exception);
    }

}
