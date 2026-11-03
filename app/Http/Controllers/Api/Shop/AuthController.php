<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Shop\SaveUserRequest;
use Illuminate\Http\Request;
use App\Service\Api\Shop\AuthService;

class AuthController extends Controller
{
    private $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signIn(SaveUserRequest $request)
    {
        return $this->authService->signIn($request->validated());
    }
}
