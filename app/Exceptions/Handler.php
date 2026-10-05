<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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

        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an API exception into a clear, formatted JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleApiException($request, Throwable $e)
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $errors = function_exists('error_processor') ? error_processor($e->validator) : [];
            $firstError = $e->validator->errors()->first() ?: 'Validation failed';
            return response()->json([
                'response_code' => 'validation_400',
                'message' => $firstError,
                'content' => null,
                'errors' => $errors
            ], 400);
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json([
                'response_code' => 'auth_401',
                'message' => translate('Unauthenticated or invalid token.'),
                'content' => null,
                'errors' => [
                    ['error_code' => 'auth', 'message' => translate('Please provide a valid Bearer token.')]
                ]
            ], 401);
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'response_code' => 'route_not_found_404',
                'message' => 'API endpoint not found: ' . $request->method() . ' ' . $request->path(),
                'content' => null,
                'errors' => [
                    ['error_code' => 'route', 'message' => 'The requested endpoint does not exist. Please check the URL and HTTP method.']
                ]
            ], 404);
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->json([
                'response_code' => 'method_not_allowed_405',
                'message' => $e->getMessage() ?: 'HTTP method not allowed for this route.',
                'content' => null,
                'errors' => [
                    ['error_code' => 'method', 'message' => $e->getMessage()]
                ]
            ], 405);
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'response_code' => 'model_not_found_404',
                'message' => translate('Requested resource record could not be found.'),
                'content' => null,
                'errors' => [
                    ['error_code' => 'not_found', 'message' => translate('The requested data could not be found in the database.')]
                ]
            ], 404);
        }

        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }

        return response()->json([
            'response_code' => 'server_error_' . $statusCode,
            'message' => $e->getMessage() ?: 'An error occurred while processing the request.',
            'content' => null,
            'errors' => [
                ['error_code' => 'error', 'message' => $e->getMessage() ?: 'Internal server error.']
            ]
        ], $statusCode);
    }
}
