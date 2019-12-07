<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'registration']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
//            return response()->json(['error' => 'Unauthorized'], 401);
            return response()->json([
                'user' =>(object)[],
                'code' => 1,
                'message' => 'Неверное телефон или пароль'
            ], 401);
        }
        $body= $request->user();
        $date = $body->created_at;
        unset($body->email_verified_at,
            $body->updated_at,
            $body->created_at
        );
        $body->reg_date = strtotime($date);
        $body->access_token=$token;
        $body->token_type='Bearer';
        $body->expires_at = auth()->factory()->getTTL() * 60;

        return response()->json(
            [
                'user'=>$body,
                'code' => 0,
                'message' => 'Success'
            ]);

//        return $this->respondWithToken($token);
    }

    /**
     * User registration
     */
    public function registration(Request $request)
    {
//        $name = request('name');
//        $email = request('email');
//        $password = request('password');
        $mail = User::where('email',$request->email)->first();
        if (isset($mail)) return response()->json([
            'code' => 1,
            'message' => 'Такой телефон уже зарегистрирован'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'code' => 0,
            'message' => 'Аккаунт успешно создан.'
        ], 201);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'code' => 0,
            'message' => 'Успешно вышли'
        ]);
//        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}