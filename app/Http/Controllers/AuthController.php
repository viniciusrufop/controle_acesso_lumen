<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response; /* https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/HttpFoundation/Response.php */

use Tymon\JWTAuth\JWTAuth;
use App\Models\User;
use App\Models\DataUser;
use App\Models\Cache;
use Exception;

class AuthController extends Controller
{
    // private $service;
    protected $jwt;
    private $userModel;

    public function __construct(JWTAuth $jwt,User $user,DataUser $dataUser)
    {
        $this->jwt = $jwt;
        $this->userModel = $user;
        $this->dataUserModel = $dataUser;
    }

    public function login(Request $req)
    {
        try {
            $validator = Validator::make( $req->all(),[ 'email' => 'required', 'password'  => 'required | min:6' ] );
            if ($validator->fails()) { throw new Exception($validator->errors()); }

            if (!$token = $this->jwt->attempt($req->only('email', 'password')) ) {
                return response()->json(['result' => 'Acesso negado.', 'success' => false], Response::HTTP_UNAUTHORIZED);
            }

            $user = $user = $this->userModel
                    ->where('email', $req['email'])
                    ->with([
                        'dataUser'  => function($query) { $query->select('id', 'user_id', 'nome', 'sobrenome'); }, 
                        'admin'     => function($query) { $query->select('user_id', 'token'); },
                    ])
                    ->get()->first();


            $options = json_encode([ 'sessionKey' => $token ]);

            $cache = $user->cache()->get()->first();
            if ($cache) {
                $user->cache()->update(['options' => $options]);
            } else {
                $user->cache()->create(['options' => $options]);
            }

            $result = [
                'token'         => $token,
                'auth'          => $user['admin'] ? $user['admin']['token'] : false,
                'userName'      => $user['dataUser'] ? $user['dataUser']['nome'] . " " . $user['dataUser']['sobrenome'] : null, 
                'userEmail'     => $req['email'],
                'dataUserId'    => $user['dataUser']['id'],
            ];

            return response()->json(['result' => $result, 'success' => true],Response::HTTP_OK);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch(Exception $e){
            return response()->json(['result' => $e->getMessage(), 'success' => false],Response::HTTP_BAD_REQUEST);
        }
    }

    public function logged(Request $request)
   {
        try{
            $sessionKey = str_ireplace("bearer ", "", $request->header('Authorization'));

            $result = $this->getDataUserBySession($sessionKey);

            if (!$result['success']) { throw new Exception($result['message']); }

            return response()->json(["result" => $result, "success" => true], 200);
        } catch (Exception $e) {
            return response()->json(['result' => $e->getMessage(), 'success' => false], Response::HTTP_BAD_REQUEST);
        }
   }

   private function getDataUserBySession($sessionKey)
   {
        $caches = Cache::get();

        foreach ($caches as $cache) {

            $collection = collect(json_decode($cache['options'], true));

            if ($collection['sessionKey'] === $sessionKey) {
                $user = $cache->user()
                ->with([
                    'dataUser'  => function($query) { $query->select('id', 'user_id', 'nome', 'sobrenome'); }, 
                    'admin'     => function($query) { $query->select('user_id', 'token'); },
                ])->get()->first();
                break;
            }

        }

        if (!isset($user)) { return ["message" => 'Usuário não encontrado.', "success" => false]; }

        return [
            'token'         => $collection['sessionKey'],
            'auth'          => $user['admin'] ? $user['admin']['token'] : false,
            'userName'      => $user['dataUser'] ? $user['dataUser']['nome'] . " " . $user['dataUser']['sobrenome'] : null, 
            'userEmail'     => $user->email,
            'dataUserId'    => $user['dataUser']['id'],
            'success'      => true
        ];
   }
}
