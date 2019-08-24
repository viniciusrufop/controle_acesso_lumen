<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\DataUser;
use App\Models\User;
use App\Models\History;
use App\Models\AdjustmentRequest;

class AjustmentRequestController extends Controller
{
    private $userModel;
    private $dataUserModel;
    private $historyModel;
    private $ajustmentModel;

    public function __construct(User $user,DataUser $dataUser, History $history, AdjustmentRequest $ajustment)
    {
        $this->dataUserModel = $dataUser;
        $this->historyModel = $history;
        $this->ajustmentModel = $ajustment;
        $this->userModel = $user;
    }

    public function adjustmentRequest(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->params,[
                    'data_user_id'  =>  'required',
                    'data'          =>  'required',
                    'hora'          =>  'required',
                    'justificativa' =>  'required',
                    ]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                $dataForm = $req->params;
                $dataUser = $this->dataUserModel->find($dataForm['data_user_id']);

                if($dataUser){
                    $dataForm['atendido'] = false;
                    $dataForm['data'] = date('Y-m-d',strtotime($dataForm['data']));
                    $dataForm['hora'] = date('H:i:s',strtotime($dataForm['hora']));
                    $dataForm['aceito'] = false;
                    $result = $this->ajustmentModel->create($dataForm);    
                    return response()->json(['error' => false,'message'=>'solicitacao_de_ajuste_realizada'],Response::HTTP_OK);
                } else {
                    return response()->json(['error' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAdjustmentRequest(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->params,['email'  =>  'required']
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                $dataForm = $req->params;
                $user = $this->userModel->where('email',$dataForm['email'])->get()->first();

                if($user){
                    $admin = $user->admin()->get()->first();
                    if($admin){
                        $result = $this->ajustmentModel->where('atendido',0)->get();
                        foreach ($result as $key => $value) {
                            $dataUser = $value->dataUser()->get()->first();
                            $value['nome'] = $dataUser->nome . " " . $dataUser->sobrenome;
                            unset(
                                $value['data_user_id'],
                                $value['atendido'],
                                $value['created_at'],
                                $value['updated_at']
                            );    
                        }
                        return response()->json(['error' => false,'result'=>$result],Response::HTTP_OK);
                    } else {
                        $dataUser = $user->dataUser()->get()->first();
                        $nome = $dataUser->nome . " " . $dataUser->sobrenome;
                        $result = $dataUser->adjustmentRequest()->where('atendido',0)->get();
                        
                        foreach ($result as $key => $value) {
                            $value['nome'] = $nome;
                            unset(
                                $value['data_user_id'],
                                $value['atendido'],
                                $value['created_at'],
                                $value['updated_at']
                            );    
                        }
                        return response()->json(['error' => false,'result'=>$result],Response::HTTP_OK);
                    }
                } else {
                    return response()->json(['error' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function acceptAdjustmentRequest(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->params,[
                    'id'        =>  'required',
                    'email'     =>  'required',
                    'authToken' =>  'required | max:60 | min:60',
                    'accept'    =>  'required | boolean',
                    ]
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                $id = $req->params['id'];
                $accept = $req->params['accept'];
                $admin = $this->isAdmin($req->params);

                if($admin){
                    if(!$accept){
                        $result = $this->ajustmentModel->where('id',$id)->update(['atendido' =>1,'aceito' => 0]);
                        return response()->json(['error' => false,'message'=>'solicitacao_feita'],Response::HTTP_OK);
                    } else {
                        $ajustment = $this->ajustmentModel->find($id);
                        $data_user_id = $ajustment->data_user_id;
                        $data = $ajustment->data;
                        $hora = $ajustment->hora;
                        $dataForm = [
                            'data_user_id' => $data_user_id,
                            'tag_value' => 'login',
                            'data'  => $data,
                            'hora'  => $hora,
                        ];
                        $history = $this->historyModel->create($dataForm);
                        if($history){
                            $result = $this->ajustmentModel->where('id',$id)->update(['atendido' =>1,'aceito' => 1]);
                            return response()->json(['error' => false,'message'=>'solicitacao_feita'],Response::HTTP_OK);
                        }
                    }
                } else {
                    return response()->json(['error' => 'solicitacao_nao_autorizada'],Response::HTTP_BAD_REQUEST);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function isAdmin($data)
    {
        $email_value = $data['email'];
        $authToken_value = $data['authToken'];

        $user = $this->userModel->where('email',$email_value)->get()->first();

        if($user){
            $admin = $user->admin()->get()->first();
            $authToken = Hash::check($email_value, $authToken_value);
            if($admin && $authToken){
                return true;
            } 
            else return false;
        } else {
            return false;
        }
    }

    public function getAdjustmentHistoryRequest(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->params,['email'  =>  'required']
            );
            if($validator->fails()){
                return response()->json($validator->errors(),Response::HTTP_BAD_REQUEST);
            } else {
                $dataForm = $req->params;
                $user = $this->userModel->where('email',$dataForm['email'])->get()->first();

                if($user){
                    $admin = $user->admin()->get()->first();
                    if($admin){
                        $result = $this->ajustmentModel->where('atendido',1)->get();
                        foreach ($result as $key => $value) {
                            $dataUser = $value->dataUser()->get()->first();
                            $value['nome'] = $dataUser->nome . " " . $dataUser->sobrenome;
                            $value['data'] = $value['data'] ."T03:00:00.000000Z";
                            unset(
                                $value['data_user_id'],
                                $value['atendido'],
                                $value['updated_at']
                            );    
                        }
                        return response()->json(['error' => false,'result'=>$result],Response::HTTP_OK);
                    } else {
                        $dataUser = $user->dataUser()->get()->first();
                        $nome = $dataUser->nome . " " . $dataUser->sobrenome;
                        $result = $dataUser->adjustmentRequest()->where('atendido',1)->get();
                        
                        foreach ($result as $key => $value) {
                            $value['nome'] = $nome;
                            $value['data'] = $value['data'] ."T03:00:00.000000Z";
                            unset(
                                $value['data_user_id'],
                                $value['atendido'],
                                $value['updated_at']
                            );    
                        }
                        return response()->json(['error' => false,'result'=>$result],Response::HTTP_OK);
                    }
                } else {
                    return response()->json(['error' => 'usuario_nao_encontrado'],Response::HTTP_NOT_FOUND);
                }
            }
        } catch(QueryException $e){
            return response()->json(['error' => $e],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
