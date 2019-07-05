<?php

$router->post('login','UserController@login');
$router->get('teste','UserController@testeGet');

$router->group(['prefix'=>'api','middleware' => 'auth'],function () use ($router){
   $router->get('auth',"UserController@auth");
});