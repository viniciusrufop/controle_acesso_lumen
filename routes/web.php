<?php

/** AuthController */
$router->post('login','AuthController@login');

$router->group(['prefix'=>'api'],function () use ($router){
   $router->post('auth-by-tag',"TagController@authByTag");
   $router->post('insert-new-tag',"TagController@insertNewTag");
   $router->post('desable-tag',"TagController@desableTag");
   $router->post('get-date',"TagController@getDate");
   $router->post('server-on',"TagController@serverOn");
   $router->post('auth-by-login',"TagController@authByLogin");
});

$router->group(['prefix'=>'api','middleware' => 'auth'],function () use ($router){

   /** AuthController */
   $router->get('logged','AuthController@logged');
   
   /** UserController */
   $router->post('get-admin','UserController@getAdmin');
   $router->post('changePassword','UserController@changePassword');

   /** DataUserController */
   $router->post('upgrade-admin',"DataUserController@upgradeAdmin");
   $router->put('downgrade-admin',"DataUserController@downgradeAdmin");
   $router->get('get-token-admin',"DataUserController@getTokenAdmin");
   $router->post('get-cep','DataUserController@getCep');
   $router->get('get-empty-tags','DataUserController@getEmptyTags');
   $router->post('create-user','DataUserController@createUser');
   $router->post('get-email-user','DataUserController@getEmailUser');
   $router->post('get-login-user','DataUserController@getLoginUser');
   $router->get('get-all-users','DataUserController@getAllUsers');
   $router->delete('delete-user','DataUserController@deleteUser');
   $router->post('get-data-user','DataUserController@getDataUser');
   $router->post('get-data-user-by-email','DataUserController@getDataUserByEmail');
   $router->put('update-user','DataUserController@updateUser');
   $router->post('get-history','DataUserController@getHistory');
   $router->post('get-relatorio','DataUserController@getRelatorio');
   $router->get('get-all-data-user','DataUserController@getAllDatauser');
   
   /** AjustmentRequestController */
   $router->post('adjustment-request','AjustmentRequestController@adjustmentRequest');
   $router->post('get-adjustment-request','AjustmentRequestController@getAdjustmentRequest');
   $router->post('get-adjustment-history-request','AjustmentRequestController@getAdjustmentHistoryRequest');
   $router->post('accept-adjustment-request','AjustmentRequestController@acceptAdjustmentRequest');

   /** TagController */
   $router->post('get-tags',"TagController@getTags");
   $router->post('delete-tag',"TagController@deleteTag");
   $router->post('desvincular-tag',"TagController@desvincularTag");
   $router->post('vincular-tag',"TagController@vincularTag");

   /** ImportExcelController */
   $router->post('importExcelUser',"ImportExcelController@importExcelUser");

});

