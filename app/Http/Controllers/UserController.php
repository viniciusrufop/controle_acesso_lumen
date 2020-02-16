<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response; /* https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/HttpFoundation/Response.php */
use Illuminate\Support\Facades\Hash;

use Tymon\JWTAuth\JWTAuth;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;

class UserController extends Controller
{

    // private $service;
    protected $jwt;
    private $userModel;

    public function __construct(JWTAuth $jwt,User $user)
    {
        $this->jwt = $jwt;
        $this->userModel = $user;
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
}
