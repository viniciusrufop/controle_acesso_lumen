<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response; /* https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/HttpFoundation/Response.php */

use App\Services\UserService;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{

    private $service;
    protected $jwt;

    public function __construct(UserService $service,JWTAuth $jwt/*, User $model */)
    {
        $this->service = $service;
        $this->jwt = $jwt;
    }

    public function login(Request $req)
    {

        return response()->json('foi', Response::HTTP_OK);
        //dd($req->all());
        /* try {
            if (! $token = $this->jwt->attempt($req->only('email', 'password')) ) {
                return response()->json(['user_not_found'], Response::HTTP_UNAUTHORIZED);
        }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $token = [
            'token' => $token
        ];

        return response()->json($token, Response::HTTP_OK); */
    }

    public function auth(){
        return response()->json('ok', Response::HTTP_OK);
    }

    public function testeGet(){
        return response()->json('ok', Response::HTTP_OK);
    }
}
