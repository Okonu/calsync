<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found.'
                ], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (app()->environment('production') && $this->isHttpException($e)) {
                $statusCode = $e->getStatusCode();

                if ($statusCode != 404) {
                    return response()->view('errors.generic', ['exception' => $e], $statusCode);
                }
            }
        });
    }
}
