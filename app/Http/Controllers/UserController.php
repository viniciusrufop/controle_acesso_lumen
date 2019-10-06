<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response; /* https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/HttpFoundation/Response.php */
use Illuminate\Support\Facades\Hash;

// use App\Services\UserService;
use Tymon\JWTAuth\JWTAuth;
use App\Models\User;
use App\Models\DataUser;
use Exception;

class UserController extends Controller
{

    // private $service;
    protected $jwt;
    private $userModel;
    private $dataUserModel;

    public function __construct(/* UserService $service, */JWTAuth $jwt,User $user,DataUser $dataUser)
    {
        // $this->service = $service;
        $this->jwt = $jwt;
        $this->userModel = $user;
        $this->dataUserModel = $dataUser;
    }

    public function login(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->all(),[
                    'email'     => 'required',
                    'password'  => 'required'
                    ]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                if (! $token = $this->jwt->attempt($req->only('email', 'password')) ) {
                    return response()->json(['user_not_found'], Response::HTTP_UNAUTHORIZED);
                } else {
                    $email = $req->email;
                    $user = $this->userModel->where('email',$email)->get()->first();
                    $dataUser = $user->dataUser()->get()->first();
                    // $admin = ($user->admin()->get()->first()) ? true : false;
                    $admin = $user->admin()->get()->first();
                    $tokenAdminValue = ($admin) ? $admin->token : false;

                    if($dataUser){
                        $userName = $dataUser->nome . " " . $dataUser->sobrenome;
                        $dataUserId = $dataUser->id;
                        $result = [
                            'token'         => $token,
                            'userName'      => $userName,
                            'userEmail'     => $email,
                            'auth'          => $tokenAdminValue, 
                            'dataUserId'    => $dataUserId,
                        ];    
                        return response()->json(['error' => false,'result' => $result],Response::HTTP_OK);
                    }else {
                        $result = [
                            'token'     => $token,
                            'userEmail'  => $email,
                            'auth'      => $tokenAdminValue,
                            'dataUserId'    => null,
                        ];    
                        return response()->json(['error' => false,'result' => $result],Response::HTTP_OK);
                    }
                }
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function auth()
    {
        return response()->json('ok', Response::HTTP_OK);
    }

    public function getAdmin(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->params,[
                    'email' => 'required',
                    'authToken' =>  'required | max:60 | min:60',
                    ]
            );
            if($validator->fails()){ return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST); }

            $email_value = $req->params['email'];
            $authToken_value = $req->params['authToken'];

            $user = $this->userModel->where('email',$email_value)->get()->first();

            if (!$user) { return response()->json(['user_not_found'], Response::HTTP_UNAUTHORIZED); }

            $admin = $user->admin()->get()->first();
            $authToken = Hash::check($email_value, $authToken_value);

            if(!$admin || !$authToken) { return response()->json(['error' => true,'message' => 'nao_autorizado'],Response::HTTP_UNAUTHORIZED); }
            
            return response()->json(['result' => 'autorizado', 'success' => true,],Response::HTTP_OK);
        } catch(Exception $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /* public function generateTokeTeste(Request $req)
    {
        // dd($req->only('email','password'));

        $token = $this->jwt->attempt($req->only('email', 'password'));
        dd($token);

    } */
}
