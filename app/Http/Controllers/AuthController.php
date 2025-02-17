<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Consult;
use Illuminate\Http\Request;
use App\Traits\ConsultableTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginPostRequest;
use App\Http\Requests\CreateUserPostRequest;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ConsultableTrait;

    public function createUser(CreateUserPostRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'User created',
        ], Response::HTTP_CREATED);
}

    public function login(LoginPostRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token
            ], Response::HTTP_OK);
        }

        return response()->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    }

    public function logout(Request $request)
    {
        /* Save query history */
        $this->saveConsult();
        auth('sanctum')->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successfully']);
    }

    public function showHistory(Request $request)
    {
        /* Save query history */
        $this->saveConsult();

        $page = $request->input('page', 1);
        $userId = auth('sanctum')->user()->id;

        $visits = Consult::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);

        return response()->json([
            'visits' => $visits->items(),
            'pagination' => [
                'current_page' => $visits->currentPage(),
                'per_page' => $visits->perPage(),
                'total' => $visits->total(),
                'last_page' => $visits->lastPage(),
            ],
        ], Response::HTTP_OK);
    }
}
