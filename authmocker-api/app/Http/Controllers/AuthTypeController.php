<?php

namespace App\Http\Controllers;

use App\Enums\AuthType;
use Illuminate\Http\JsonResponse;

class AuthTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = array_map(fn(AuthType $type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'default_server_config' => $type->defaultServerConfig(),
            'default_credential' => $type->defaultCredential(),
        ], AuthType::cases());

        return response()->json(['data' => $types]);
    }
}
