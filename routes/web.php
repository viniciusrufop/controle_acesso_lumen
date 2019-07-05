<?php

$router->post('login','UserController@login');

$router->group(['prefix'=>'api','middleware' => 'auth'],function () use ($router){
   $router->get('auth',"UserController@auth");
});