<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\DataUser;
use App\Models\Tag;
use App\Models\Admin;
use App\Models\History;

class TagController extends Controller
{
    private $userModel;
    private $dataUserModel;
    private $tagModel;
    private $historyModel;

    public function __construct(DataUser $dataUser, Tag $tag, Admin $admin, History $history)
    {
        $this->dataUserModel = $dataUser;
        $this->tagModel = $tag;
        $this->adminModel = $admin;
        $this->historyModel = $history;
    }

    private function myAuthToken(string $token_value)
    {
        $admin = $this->adminModel->where('token',$token_value)->get()->first();
        return ($admin) ? true : false;
    }

    public function authByTag(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),[
                    'tag_value' => 'required | max:8 | min:8',
                    'token'     => 'required | max:60 | min:60',
                    ]
             );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                if(!$this->myAuthToken($req->token)){
                    return response()->json(['error' => 'solicitacao_nao_autorizada'], Response::HTTP_UNAUTHORIZED);
                }

                $tag_value = $req->tag_value;
                $tag = $this->tagModel
                            ->where('tag_value',$tag_value)
                            ->where('ativo',1)
                            ->whereNotNull('data_user_id')
                            ->get()->first();

                if(empty($tag)){
                    $this->insertHistories(null,$tag_value);
                    return response()->json(['error' => 'user_UNAUTHORIZED_1'], Response::HTTP_UNAUTHORIZED);
                }
                
                $id_user = $tag->dataUser()->get()->first()->id;
                $user = $tag->dataUser()->where('ativo',1)->get();

                if(empty($user[0])){
                    $this->insertHistories($id_user,$tag_value);
                    return response()->json(['error' => 'user_UNAUTHORIZED_2'], Response::HTTP_UNAUTHORIZED);
                } else{
                    $this->insertHistories($id_user,$tag_value);
                    return response()->json(['error' => false,'message' => 'autenticado_com_sucesso'],Response::HTTP_OK);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function insertNewTag(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),[
                    'tag_value' => 'required | max:8 | min:8',
                    'ativo'     => 'required | boolean',
                    'token'     => 'required | max:60 | min:60',
                    ]
             );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                if(!$this->myAuthToken($req->token)){
                    return response()->json(['error' => 'solicitacao_nao_autorizada'], Response::HTTP_UNAUTHORIZED);
                }

                $tag_value = $req->tag_value;
                $tag = $this->tagModel->where('tag_value',$tag_value)->get()->first();

                if($tag){
                    $tag = $this->tagModel
                        ->where('tag_value',$tag_value)
                        ->update(['ativo' =>1]);
                    return response()->json(['error' => false,'messgae'=>'tag_ja_cadastrada_foi_habilitada'],Response::HTTP_OK);
                } else {
                    $tag = $this->tagModel->create($req->all());
                    return response()->json(['error' => false,'message' => 'tag_cadastrada_com_sucesso'],Response::HTTP_OK);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function desableTag(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),[
                    'tag_value' => 'required | max:8 | min:8',
                    'token'     => 'required | max:60 | min:60',
                    ]
             );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                if(!$this->myAuthToken($req->token)){
                    return response()->json(['error' => 'solicitacao_nao_autorizada'], Response::HTTP_UNAUTHORIZED);
                }

                $tag_value = $req->tag_value;
                $tag = $this->tagModel->where('tag_value',$tag_value)->update(['ativo' => 0]);

                if($tag){
                    return response()->json(['error' => false,'message' => 'desativado_com_sucesso'],Response::HTTP_OK);
                } else {
                    return response()->json(['error' => 'tag_nao_encontrada'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDate(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['token'     => 'required | max:60 | min:60',]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                if(!$this->myAuthToken($req->token)){
                    return response()->json(['error' => 'solicitacao_nao_autorizada'], Response::HTTP_UNAUTHORIZED);
                }

                $dataHora = [
                    "dataHora" => date('d-m-Y H:i:s')
                ];

                return response()->json(['error' => false,'message' => $dataHora],Response::HTTP_OK);
            }

        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function serverOn(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['token'     => 'required | max:60 | min:60',]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                if(!$this->myAuthToken($req->token)){
                    return response()->json(['error' => 'solicitacao_nao_autorizada'], Response::HTTP_UNAUTHORIZED);
                }

                return response()->json(['error' => false,'message' => 'server_online'],Response::HTTP_OK);
            }

        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function authByLogin(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),[
                    'login_value' => 'required | max:4 | min:4',
                    'senha_value' => 'required | max:4 | min:4',
                    'token' => 'required | max:60 | min:60',
                    ]
             );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                if(!$this->myAuthToken($req->token)){
                    return response()->json(['error' => 'solicitacao_nao_autorizada'], Response::HTTP_UNAUTHORIZED);
                }

                $login_value = $req->login_value;
                $senha_value = $req->senha_value;

                $login = $this->dataUserModel
                            ->where('login',$login_value)
                            ->where('senha',$senha_value)
                            ->where('ativo',1)
                            ->get()->first();

                if(empty($login)){
                    $this->insertHistories(null,$login_value);
                    return response()->json(['error' => 'user_UNAUTHORIZED_1'], Response::HTTP_UNAUTHORIZED);
                } else{
                    $this->insertHistories($login->user_id,null);
                    return response()->json(['error' => false,'message' => 'autenticado_com_sucesso'],Response::HTTP_OK);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function insertHistories($data_user_id = null,$tag_value = null)
    {
        $dataForm = [
            'data_user_id' => $data_user_id,
            'tag_value' => $tag_value,
            'data'  => date('Y-m-d'),
            'hora'  => date('H:i:s')
        ];

        $history = $this->historyModel->create($dataForm);
    }
}