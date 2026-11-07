<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;

Trait ApiValidation
{
    public $validationErrorStatus = 422;
    public $uncompletedErrorStatus = 423;
    public $unauthorizedErrorStatus = 403;
    public $notFoundErrorStatus = 404;
    public $invalidErrorStatus = 400;
    public $notAcceptableStatus = 406;
    public $unknownStatus = 419;

    public function validationErrors($error)
    {
        return ['message' => 'The given data was invalid.', 'error' => $error];
    }

    public function withErrors($error)
    {
        return ['status' => 'failed', 'message' => $error];
    }

    public function withSuccess($msg)
    {
        return ['status' => 'success', 'message' => $msg];
    }


    protected function isApiRequest(): bool
    {
        $req = request();
        return $req->is('api/*') || $req->expectsJson() || $req->ajax();
    }

    protected function handleValidationError(ValidationException $e, ?string $route = null)
    {
        $errors = collect($e->errors())->collapse();

        if ($this->isApiRequest()) {
            return response()->json($this->withErrors($errors), 422);
        }

        $redirect = $route ? redirect($route) : back();
        return $redirect->withErrors($e->errors())->withInput();
    }

    protected function handleException(\Throwable $e, ?string $route = null)
    {
        if ($this->isApiRequest()) {
            return response()->json($this->withErrors($e->getMessage()));
        }

        $redirect = $route ? redirect($route) : back();
        return $redirect->with("error", $e->getMessage());
    }

    protected function successResponse(string $message, ?string $redirectRoute = null)
    {
        if ($this->isApiRequest()) {
            return response()->json($this->withSuccess($message));
        }

        return $redirectRoute
            ? redirect($redirectRoute)->with('success', $message)
            : back()->with('success', $message);
    }


    //$to_route = route('agent.cash_in.preview', $transaction->trx_id);
    //return $this->successResponse('Cash-in initiated', $to_route);


}
