<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

#------------------------------------------------------------------------------------
# API
#------------------------------------------------------------------------------------
$router->group(['prefix' => 'api'], function () use ($router) {
    #------------------------------------------------------------------------------------
    # Auth
    #------------------------------------------------------------------------------------
    $router->group(['as' => 'auth', 'prefix' => 'auth'], function () use ($router) {
        $router->group(['middleware' => ['guest']], function () use ($router) {
            $router->post('login', ['uses' => 'AuthController@login', 'as' => 'login']);
            $router->post('register', ['uses' => 'AuthController@register', 'as' => 'register']);
        });

        $router->group(['middleware' => ['auth']], function () use ($router) {
            $router->get('me', ['uses' => 'AuthController@me', 'as' => 'me']);

            $router->post('logout', ['uses' => 'AuthController@logout', 'as' => 'logout']);
            $router->post('refresh', ['uses' => 'AuthController@refresh', 'as' => 'refresh']);
        });
    });
});
