<?php

$router->post('login','UserController@login');

$router->group(['prefix'=>'api'],function () use ($router){
   $router->post('auth-by-tag',"TagController@authByTag");
   $router->post('insert-new-tag',"TagController@insertNewTag");
   $router->post('desable-tag',"TagController@desableTag");
   $router->post('get-date',"TagController@getDate");
   $router->post('server-on',"TagController@serverOn");
   $router->post('auth-by-login',"TagController@authByLogin");
});

$router->group(['prefix'=>'api','middleware' => 'auth'],function () use ($router){
   $router->get('auth',"UserController@auth");
   $router->post('get-admin','UserController@getAdmin');

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
   
   $router->post('adjustment-request','AjustmentRequestController@adjustmentRequest');
   $router->post('get-adjustment-request','AjustmentRequestController@getAdjustmentRequest');
   $router->post('get-adjustment-history-request','AjustmentRequestController@getAdjustmentHistoryRequest');
   $router->post('accept-adjustment-request','AjustmentRequestController@acceptAdjustmentRequest');

   $router->post('get-tags',"TagController@getTags");
   $router->post('delete-tag',"TagController@deleteTag");
   $router->post('desvincular-tag',"TagController@desvincularTag");
   $router->post('vincular-tag',"TagController@vincularTag");
});

// ========= ROTAS TESTE =========


$router->get('teste','ExampleController@testeGet');
$router->get('teste-post','ExampleController@testePost');
$router->get('generate-token-teste','UserController@generateTokeTeste');

$router->get('get-data-user','ExampleController@getDataUser');
$router->get('get-user-by-data','ExampleController@getUserByData');
$router->get('insert-user','ExampleController@insertUser');
$router->get('get-tag-by-data-user','ExampleController@getTagByDataUser');
$router->get('get-data-user-by-tag','ExampleController@getDataUserByTag');
$router->get('insert-tag','ExampleController@insertTag');
$router->get('get-tags-without-user','ExampleController@getTagsWhithoutUse');
$router->get('get-tag-true','ExampleController@getTagTrue');
$router->get('change-tag-true','ExampleController@changeTagTrue');
$router->get('get-token-user','ExampleController@getTokenUser');
$router->get('generate-token','ExampleController@generateToken');
$router->get('upgrade-admin','ExampleController@upgradeAdmin');
$router->get('downgrade-admin','ExampleController@downgradeAdmin');
$router->get('teste-auth-token','ExampleController@testeAuthToken');
$router->get('insert-history','ExampleController@insertHistory');
$router->get('get-cep-exemplo','ExampleController@getCep');
