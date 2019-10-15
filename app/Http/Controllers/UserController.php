<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response; /* https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/HttpFoundation/Response.php */
use Illuminate\Support\Facades\Hash;

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
                return response()->json(['Acesso negado.'], Response::HTTP_UNAUTHORIZED);
            }

            $user = $user = $this->userModel
                    ->where('email', $req['email'])
                    ->with([
                        'dataUser'  => function($query) { $query->select('id', 'user_id', 'nome', 'sobrenome'); }, 
                        'admin'     => function($query) { $query->select('user_id', 'token'); },
                    ])
                    ->get()->first();

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

    public function auth()
    {
        return response()->json('ok', Response::HTTP_OK);
    }

    public function getAdmin(Request $req)
    {
        try {
            $validator = Validator::make( $req->params,[ 'email' => 'required', 'authToken' =>  'required | max:60 | min:60', ]);
            if ($validator->fails()) { throw new Exception($validator->errors()); }

            $email_value = $req->params['email'];
            $authToken_value = $req->params['authToken'];

            $user = $this->userModel->where('email',$email_value)->get()->first();

            if (!$user) { throw new Exception('Usuário não autorizado'); }

            $admin = $user->admin()->get()->first();
            $authToken = Hash::check($email_value, $authToken_value);

            if(!$admin || !$authToken) { throw new Exception('Usuário não autorizado'); }
            
            return response()->json(['result' => 'autorizado', 'success' => true,],Response::HTTP_OK);
        } catch(Exception $e){
            return response()->json(['result' => $e->getMessage(), 'success' => false],Response::HTTP_BAD_REQUEST);
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changePassword(Request $req)
    {
        try{
            $validator = Validator::make(
                array_merge($req['params'], $req->header()), [ 'id' => 'required', 'email' => 'required', 'newPass' => 'required | min:6' ]
            );
            if ($validator->fails()) { throw new Exception($validator->errors()); }

            $user = $this->userModel->where('email', $req->header('email'))->get()->first();
            if (!$user) { throw new Exception('Usuário não encontrado.'); }

            $dataUserId = $user->dataUser()->get()->first()->id;

            if ($dataUserId !== intval($req['params']['id'])) { throw new Exception('Solicitação não autorizada.'); }

            $newPass = Hash::make($req['params']['newPass']);
            $user->update(['password' => $newPass]);

            return response()->json(['result' => 'Senha atualiazada com sucesso.', 'success' => true,],Response::HTTP_OK);
        } catch(Exception $e){
            return response()->json(['result' => $e->getMessage(), 'success' => false],Response::HTTP_BAD_REQUEST);
        } catch(QueryException $e){
            return response()->json(['result' => $e, 'success' => false],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /* public function generateTokeTeste(Request $req)
    {
        // dd($req->only('email','password'));

        $token = $this->jwt->attempt($req->only('email', 'password'));
        dd($token);

    } */
}
