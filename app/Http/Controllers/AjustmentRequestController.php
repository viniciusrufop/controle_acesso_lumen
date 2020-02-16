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
use Exception;

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
            if($validator->fails()){ throw new Exception($validator->errors()); }
            
            $dataForm = $req->params;
            $dataUser = $this->dataUserModel->find($dataForm['data_user_id']);

            if (!$dataUser) { throw new Exception('Usuário não encontrado'); }
            
            $dataForm['atendido'] = false;
            $dataForm['data'] = date('Y-m-d',strtotime($dataForm['data']));
            $dataForm['hora'] = date('H:i:s',strtotime($dataForm['hora']));
            $dataForm['aceito'] = false;
            $this->ajustmentModel->create($dataForm);    

            return response()->json(['result' => 'Solicitação de ajuste realizada.', 'success' => true],Response::HTTP_OK);
        } catch(Exception $e){
            return response()->json(['result' => $e->getMessage(), 'success' => false],Response::HTTP_BAD_REQUEST);
        } catch(QueryException $e){
            return response()->json(['result' => $e, 'success' => false],Response::HTTP_BAD_REQUEST);
        }
    }

    public function getAdjustmentRequest(Request $req)
    {
        try {
            $validator = Validator::make($req->params,['email'  =>  'required']);
            if($validator->fails()){ throw new Exception($validator->errors()); }

            $dataForm = $req->params;
            $user = $this->userModel->where('email',$dataForm['email'])->get()->first();

            if (!$user) { throw new Exception('Usuário não encontrado'); }
            
            $admin = $user->admin()->get()->first();

            if($admin){
                $results = $this->ajustmentModel->where('atendido',0)->with('dataUser')->get();

                foreach ($results as $result) {
                    $result['nome'] = $result['dataUser']['nome'] . " " . $result['dataUser']['sobrenome'];
                    unset(
                        $result['data_user_id'],
                        $result['atendido'],
                        $result['created_at'],
                        $result['updated_at'],
                        $result['dataUser']
                    );    
                }

                return response()->json(['result' => $results, 'success' => true],Response::HTTP_OK);
            }

            $dataUser = $user->dataUser()->get()->first();
            $nome = $dataUser->nome . " " . $dataUser->sobrenome;
            $results = $dataUser->adjustmentRequest()->where('atendido',0)->get();
            
            foreach ($results as $result) {
                $result['nome'] = $nome;
                unset(
                    $result['data_user_id'],
                    $result['atendido'],
                    $result['created_at'],
                    $result['updated_at']
                );    
            }
            return response()->json(['result' => $results, 'success' => true],Response::HTTP_OK);
        } catch(Exception $e){
            return response()->json(['result' => $e->getMessage(), 'success' => false],Response::HTTP_BAD_REQUEST);
        } catch(QueryException $e){
            return response()->json(['result' => $e, 'success' => false],Response::HTTP_BAD_REQUEST);
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
            if($validator->fails()){ throw new Exception($validator->errors()); }
            
            $id = $req->params['id'];
            $accept = $req->params['accept'];
            $admin = $this->isAdmin($req->params);

            if (!$admin) { throw new Exception('Usuário não autorizado.'); }
        
            if(!$accept){
                $this->ajustmentModel->where('id',$id)->update(['atendido' =>1,'aceito' => 0]);
                return response()->json(['result' => 'Solicitação rejeitada', 'success' => true],Response::HTTP_OK);
            } else {
                $ajustment = $this->ajustmentModel->find($id);

                if ($ajustment['atendido']) { throw new Exception('Solicitação já foi atendida antes.'); }

                $data_user_id = $ajustment->data_user_id;
                $data = $ajustment->data;
                $hora = $ajustment->hora;

                $dataForm = [
                    'data_user_id' => $data_user_id,
                    'tag_value' => 'login',
                    'data'  => $data,
                    'hora'  => $hora,
                ];

                $this->historyModel->create($dataForm);

                $this->ajustmentModel->where('id',$id)->update(['atendido' =>1,'aceito' => 1]);

                return response()->json(['result' => 'Solicitação aceita', 'success' => true], Response::HTTP_OK);
            }
        } catch(Exception $e){
            return response()->json(['result' => $e->getMessage(), 'success' => false],Response::HTTP_BAD_REQUEST);
        } catch(QueryException $e){
            return response()->json(['result' => $e, 'success' => false],Response::HTTP_BAD_REQUEST);
        }
    }

    public function getAdjustmentHistoryRequest(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->params,['email'  =>  'required']
            );

            if($validator->fails()){ throw new Exception($validator->errors()); }
        
            $dataForm = $req->params;
            $user = $this->userModel->where('email',$dataForm['email'])->get()->first();

            if (!$user) { throw new Exception('Usuário não encontrado.'); }
            
            $admin = $user->admin()->get()->first();

            if($admin){
                $results = $this->ajustmentModel->where('atendido',1)->with('dataUser')->get();

                foreach ($results as $result) {
                    $result['nome'] = $result['dataUser']['nome'] . " " . $result['dataUser']['sobrenome'];
                    $result['data'] = $result['data'] ."T03:00:00.000000Z";
                    $result['diaDoPedido'] = substr($result['created_at'],0,10) . "T03:00:00.000000Z";
                    unset(
                        $result['created_at'],
                        $result['data_user_id'],
                        $result['atendido'],
                        $result['updated_at'],
                        $result['dataUser']
                    );    
                }

                return response()->json(['result' => $results, 'success' => true],Response::HTTP_OK);

            } else {
                $dataUser = $user->dataUser()->get()->first();
                $nome = $dataUser->nome . " " . $dataUser->sobrenome;
                $results = $dataUser->adjustmentRequest()->where('atendido',1)->get();
                
                foreach ($results as $key => $result) {
                    $result['nome'] = $nome;
                    $result['data'] = $result['data'] ."T03:00:00.000000Z";
                    $result['diaDoPedido'] = substr($result['created_at'],0,10) . "T03:00:00.000000Z";
                    unset(
                        $result['created_at'],
                        $result['data_user_id'],
                        $result['atendido'],
                        $result['updated_at']
                    );    
                }

                return response()->json(['result' => $results, 'success' => true],Response::HTTP_OK);
            }
            
        } catch(Exception $e){
            return response()->json(['result' => $e->getMessage(), 'success' => false],Response::HTTP_BAD_REQUEST);
        } catch(QueryException $e){
            return response()->json(['result' => $e, 'success' => false],Response::HTTP_BAD_REQUEST);
        }
    }

    private function isAdmin($data)
    {
        $email_value = $data['email'];
        $authToken_value = $data['authToken'];

        $user = $this->userModel->where('email',$email_value)->get()->first();

        if (!$user) { return false; }
        
        $admin = $user->admin()->get()->first();
        $authToken = Hash::check($email_value, $authToken_value);

        if($admin && $authToken){ return true; } 
        
        return false;
    }
}
