<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\DataUser;
use App\Models\Tag;
use App\Models\Admin;
use App\Models\History;

class DataUserController extends Controller
{

    private $userModel;
    private $dataUserModel;
    private $tagModel;
    private $adminModel;
    private $historyModel;

    public function __construct(User $user,DataUser $dataUser, Tag $tag, Admin $admin,  History $history)
    {
        $this->userModel = $user;
        $this->dataUserModel = $dataUser;
        $this->tagModel = $tag;
        $this->adminModel = $admin;
        $this->historyModel = $history;
    }

    public function upgradeAdmin(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['email' => 'required',]
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $email_value = $req->email;
                $user = $this->userModel->where('email',$email_value)->get()->first();

                if(!empty($user)){
                    $admin = $user->admin()->get()->first();
                    if(empty($admin)){
                        $dataForm = [
                            'user_id'   => $user->id,
                            'token'     => Hash::make($email_value)
                        ];
                        $admin = $user->admin()->create($dataForm);
                        return response()->json(['error' => false,'message' => 'admin_cadastrado_com_sucesso'],Response::HTTP_OK);
                    } else {
                        return response()->json(['error' => false,'message' => 'usuario_ja_e_admin'],Response::HTTP_OK);
                    }
                } else {
                    return response()->json(['error' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downgradeAdmin(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['email' => 'required',]
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $email_value = $req->email;
                $user = $this->userModel->where('email',$email_value)->get()->first();

                if($user){
                    $admin = $this->adminModel->where('user_id',$user->id)->get()->first();
                    if($admin){
                        $admin->delete();
                        return response()->json(['error' => false,'message' => 'admin_removido'],Response::HTTP_OK);
                    } else {
                        return response()->json(['error' => 'admin_nao_encontrado'],Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json(['error' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTokenAdmin()
    {
        try{
            $tokens = $this->adminModel->get();

            if(!empty($tokens[0])){
                $tokenList = [];
                foreach ($tokens as $value){
                    array_push($tokenList,$value['token']);
                }
                return response()->json(['error' => false,'tokenList' => $tokenList],Response::HTTP_OK);
            } else {
                return response()->json(['error' => false,'message' => "nao_possui_tags_desvinculadas"],Response::HTTP_OK);
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function getCep(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['cep' => 'required | max:8 | min:8',]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                $cep = $req->cep;
                $url_feed = "http://viacep.com.br/ws/{$cep}/json";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_feed);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                return response()->json(['error' => false,'dados' => $result],Response::HTTP_OK);
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getEmptyTags(Request $req)
    {
        try{
            $tagList = $this->tagModel->where('data_user_id',null)->get();

            if(!empty($tagList[0])){
                $tagEmpty = [];
                foreach ($tagList as $value){
                    array_push($tagEmpty,$value['tag_value']);
                }
                return response()->json(['error' => false,'tagList' => $tagEmpty],Response::HTTP_OK);
            } else {
                return response()->json(['error' => false,'message' => "nao_possui_tags_desvinculadas"],Response::HTTP_OK);
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createUser(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->params,[
                    'nome'  =>  'required',
                    'email' =>  'required',
                    'login' =>  'required | max:4 | min:4',
                    'senha' =>  'required | max:4 | min:4',
                    'ativo' =>  'required | boolean'
                    ]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $email_value = $req->email;
                $login_value = $req->login;

                $email = $this->userModel->where('email',$email_value)->get()->first();
                $login = $this->dataUserModel->where('login',$login_value)->get()->first();

                if($email || $login){
                    $message = [
                        'message'   => 'email_ou_login_ja_cadastrados'
                    ];
                    return response()->json(['error' => true, $message],Response::HTTP_BAD_REQUEST);
                } else {

                    $dataForm = $req->params;
                    $dataForm['password'] = Hash::make($req->params['login'] . $req->params['senha']);
                    $user = $this->userModel->create($dataForm);
                    $dataUser = $user->dataUser()->create($req->params);

                    if($req->params['tag']){
                        $tags = $req->params['tag'];
                        $data_user_id = $dataUser->id;
                        foreach($tags as $tag_value){
                            $tag = $this->tagModel
                                    ->where('tag_value',$tag_value)
                                    ->update([
                                        'ativo' =>1,
                                        'data_user_id' => $data_user_id
                                        ]);
                        }
                        return response()->json(['error' => false,'message' => 'criado_com_sucesso'],Response::HTTP_CREATED);
                    } else {
                        return response()->json(['error' => false,'message' => 'criado_com_sucesso_sem_tags'],Response::HTTP_CREATED);
                    }
                }
            }

        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getEmailUser(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->params,['email' =>  'required']
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $email_value = $req->params['email'];
                $email = $this->userModel->where('email',$email_value)->get()->first();

                if($email){
                    return response()->json(['error' => true,'message' => 'email_existente'],Response::HTTP_BAD_REQUEST);
                } else {
                    return response()->json(['error' => false,'message' => 'email_nao_existente'],Response::HTTP_OK);
                }

            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLoginUser(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->params,['login' =>  'required | max:4 | min:4']
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $login_value = $req->params['login'];
                $login = $this->dataUserModel->where('login',$login_value)->get()->first();

                if($login){
                    return response()->json(['error' => true,'message' => 'login_existente'],Response::HTTP_BAD_REQUEST);
                } else {
                    return response()->json(['error' => false,'message' => 'login_nao_existente'],Response::HTTP_OK);
                }

            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllUsers()
    {
        try{
            $dataUsers = $this->dataUserModel->get();

            if(!empty($dataUsers[0])){
                $userList = [];
                foreach ($dataUsers as $dataUser){
                    // unset($user['id'],$user['login']);
                    $user = $dataUser->user()->get()->first();
                    $userData = [
                        'id'        => $dataUser['id'],
                        'nome'      => $dataUser['nome'],
                        'sobrenome' => $dataUser['sobrenome'],
                        'telefone'  => $dataUser['telefone'],
                        'email'     => $user['email']
                    ];
                    array_push($userList,$userData);
                }
                return response()->json(['error' => false,'userList' => $userList],Response::HTTP_OK);
            } else {
                return response()->json(['error' => false,'message' => "nao_possui_usuarios_cadastrados"],Response::HTTP_OK);
            }

        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteUser(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->all(),['id' =>  'required']
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $id_value = $req->id;
                $dataUser = $this->dataUserModel->find($id_value);

                if($dataUser){
                    $user = $dataUser->user()->delete();
                    return response()->json(['error' => false,'message' => 'usuario_deletado_com_sucesso'],Response::HTTP_OK);
                } else {
                    return response()->json(['error' => true,'message' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataUser(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->params,['id' =>  'required']
            );

            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                $id_value = $req->params['id'];
                $dataUser = $this->dataUserModel->find($id_value);

                if($dataUser){
                    $email = $dataUser->user()->get()->first()->email;
                    $tags = $dataUser->tags()->get();

                    if(!empty($tags[0])){
                        $tagList = [];
                        foreach ($tags as $tag) {
                            $values = [
                                'tag_value' => $tag['tag_value'],
                                'ativo' => $tag['ativo'],
                            ];
                            array_push($tagList,$values);
                        }
                        $dataUser['tags'] = $tagList;
                    } 
                    $dataUser['email'] = $email;
                    unset(
                        $dataUser['user_id'],
                        $dataUser['created_at'],
                        $dataUser['updated_at']
                    );
                    $result = [
                        'dataUser' => $dataUser,
                    ];
                    return response()->json(['error' => false,'result' => $result],Response::HTTP_OK);
                    
                } else {
                    return response()->json(['error' => true,'message' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateUser(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->params,[
                    'id'    =>  'required',
                    'nome'  =>  'required',
                    'email' =>  'required',
                    'login' =>  'required | max:4 | min:4',
                    'ativo' =>  'required | boolean'
                    ]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                $dataUser = $this->dataUserModel->find($req->params['id']);

                if($dataUser){
                    if(!empty($req->params['tag'][0])){
                        foreach ($req->params['tag'] as $tag_value) {
                            $tag = $this->tagModel->where('tag_value',$tag_value)->get()->first();
                            $ativo = ($tag['ativo']) ? 0 : 1;
                            $tag->update(['ativo' => $ativo]);
                        }
                    }
                    $dataForm = $req->params;
                    $result = $dataUser->update($dataForm);
                    return response()->json(['error' => false,'result' => $result],Response::HTTP_OK);
                } else {
                    return response()->json(['error' => true,'message' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getHistory(Request $req)
    {
        try{
            if(!empty($req->params['dataInicio']) && empty($req->params['dataFim']) && empty($req->params['users'])){

                $result = $this->getHistoryI($req);
                return response()->json(['option' => 1,'result' => $result],Response::HTTP_OK);

            } else if(!empty($req->params['dataInicio']) && !empty($req->params['dataFim']) && empty($req->params['users'])){

                $result = $this->getHistoryIF($req);
                return response()->json(['option' => 2,'result' => $result],Response::HTTP_OK);

            } else if(!empty($req->params['dataInicio']) && empty($req->params['dataFim']) && !empty($req->params['users'])){

                $result = $this->getHistoryIU($req);
                return response()->json(['option' => 3,'result' => $result],Response::HTTP_OK);

            } else if(empty($req->params['dataInicio']) && empty($req->params['dataFim']) && !empty($req->params['users'])){

                $result = $this->getHistoryU($req);
                return response()->json(['option' => 4,'result' => $result],Response::HTTP_OK);

            } else if(!empty($req->params['dataInicio']) && !empty($req->params['dataFim']) && !empty($req->params['users'])){

                $result = $this->getHistoryIFU($req);
                return response()->json(['option' => 5,'result' => $result],Response::HTTP_OK);

            } else {
                $resultList = $this->historyModel->whereNotNull('data_user_id')->get();
                $result = $this->trataHistory($resultList);
                return response()->json(['option' => 6,'result' => $result],Response::HTTP_OK);
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function trataHistory($array)
    {
        $result = [];
        foreach ($array as $res) {
            $nome = $res->dataUser()->get()->first()->nome;
            $data = [
                'data' => $res['data']."T03:00:00.000000Z",
                'hora' => $res['hora'],
                'nome' => $nome,
            ];
            array_push($result,$data);
        }
        return $result;
    }

    private function getHistoryIF(Request $req)
    {
        $dataInicio = $req->params['dataInicio']; 
        $dataInicio = date('Y-m-d',strtotime($dataInicio));
        $dataFim = $req->params['dataFim']; 
        $resultList = $this->historyModel->whereNotNull('data_user_id')->whereBetween('data', [$dataInicio, $dataFim])->get();
        return $this->trataHistory($resultList);
    }

    private function getHistoryI(Request $req)
    {
        $dataInicio = $req->params['dataInicio']; 
        $dataInicio = date('Y-m-d',strtotime($dataInicio));
        $resultList = $this->historyModel->whereNotNull('data_user_id')->whereDate('data','>=',$dataInicio)->get();
        return $this->trataHistory($resultList);
    }

    private function getHistoryIU(Request $req)
    {
        $dataInicio = $req->params['dataInicio']; 
        $dataInicio = date('Y-m-d',strtotime($dataInicio));
        $users = $req->params['users'];
        $resultList = [];
        foreach ($users as $userId) {
            $res = $this->historyModel->where('data_user_id',$userId)->whereDate('data','>=',$dataInicio)->get();
            if($res) array_push($resultList,$this->trataHistory($res));
        }
        return $resultList;
    }

    private function getHistoryU(Request $req)
    {
        $users = $req->params['users'];
        $resultList = [];
        foreach ($users as $userId) {
            $res = $this->historyModel->where('data_user_id',$userId)->get();
            if($res) array_push($resultList,$this->trataHistory($res));
        }
        return $resultList;
    }

    private function getHistoryIFU(Request $req)
    {
        $dataInicio = $req->params['dataInicio']; 
        $dataInicio = date('Y-m-d',strtotime($dataInicio));
        $dataFim = $req->params['dataFim'];
        $users = $req->params['users'];
        $resultList = [];
        foreach ($users as $userId) {
            $res = $this->historyModel->where('data_user_id',$userId)->whereBetween('data', [$dataInicio, $dataFim])->get();
            if($res) array_push($resultList,$this->trataHistory($res));
        }
        return $resultList;
    }

    public function getRelatorio(Request $req)
    {
        try{
            $validator = Validator::make(
                $req->params,[
                    'mes'    =>  'required',
                    'ano'    =>  'required',
                    'id'  =>  'required',
                ]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {

                $idUser = $req->params['id'];
                $mes = $req->params['mes'];
                $ano = $req->params['ano'];
                $ultimoDiaMes = date('t', mktime(0, 0, 0, $mes, 1, $ano ));

                $resultList = $this->historyModel
                                ->where('data_user_id',$idUser)
                                ->whereMonth('data', $mes)
                                ->whereYear('data',$ano)
                                ->get();   
                $resultTratado = $this->trataHistory($resultList);

                $resultLength = count($resultTratado);
                $nome = $this->dataUserModel->find($idUser)->nome;
                $mes = ($mes < 10) ? "0".$mes : $mes;
                $result = [];

                for ($i=1; $i <= $ultimoDiaMes ; $i++) { 
                    $arrayAux = [];
                    $findDay = false;
                    for ($j=0; $j < $resultLength ; $j++) { 
                        $dia = substr($resultTratado[$j]['data'],8,2);
                        if($i == $dia){
                            array_push($arrayAux,$resultTratado[$j]);
                            $findDay = true;
                        } 
                    }
                    if($findDay){
                        $result[$i] = $arrayAux;
                    } else {
                        $dia = ($i < 10) ? "0".$i : $i;
                        $arrayAux[0] = [
                            'data' => "$ano-$mes-$dia"."T03:00:00.000000Z",
                            'hora' => " " ,
                            'nome' => $nome
                        ];
                        $result[$i] = $arrayAux;
                    }
                }
                return response()->json(['option' => 6,'result' => $result],Response::HTTP_OK);
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}