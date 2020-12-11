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

$router->get('/verify-email', [
    'as' => 'verify_email',
    'uses' => 'VerifyEmailController@verifyEmail'
]);

#------------------------------------------------------------------------------------
# API
#------------------------------------------------------------------------------------
$router->group(['prefix' => 'api'], function () use ($router) {
    #------------------------------------------------------------------------------------
    # Single Routes
    #------------------------------------------------------------------------------------
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('send-verify-email', [
            'as' => 'send_verify_email',
            'uses' => 'VerifyEmailController@sendVerifyEmail',
        ]);

        $router->post('broadcasting/auth', ['uses' => 'BroadcastController@authenticate']);
    });

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

    #------------------------------------------------------------------------------------
    # Post
    #------------------------------------------------------------------------------------
    $router->group(['as' => 'post', 'prefix' => 'posts'], function () use ($router) {
        $router->group(['middleware' => ['auth']], function () use ($router) {
            $router->post('/', 'PostController@store');

            $router->patch('/{id}', 'PostController@update');
            $router->patch('/{id}/solved', 'PostController@markSolved');

            $router->delete('/{id}', 'PostController@destroy');
        });

        $router->get('/', 'PostController@index');
        $router->get('/tags', 'PostController@getAvailableTags');
        $router->get('/{id}', 'PostController@show');
        $router->get('/{id}/comments', 'PostController@getComments');

        $router->patch('/{id}/views', 'PostController@incrementViews');
    });

    #------------------------------------------------------------------------------------
    # Comment
    #------------------------------------------------------------------------------------
    $router->group(['as' => 'comment', 'prefix' => 'comments'], function () use ($router) {
        $router->group(['middleware' => ['auth']], function () use ($router) {
            $router->post('/', 'CommentController@store');

            $router->patch('/{id}', 'CommentController@update');
            $router->patch('/{id}/solution', 'CommentController@markSolution');

            $router->delete('/{id}', 'CommentController@destroy');
        });
    });

    #------------------------------------------------------------------------------------
    # Profile
    #------------------------------------------------------------------------------------
    $router->group(['as' => 'profile', 'prefix' => 'profile'], function () use ($router) {
        $router->group(['middleware' => ['auth']], function () use ($router) {
            $router->patch('/', 'ProfileController@updateProfile');
            $router->patch('/password', 'ProfileController@updatePassword');
        });

        $router->get('/{username}', 'ProfileController@getUser');
        $router->get('/{username}/posts', 'ProfileController@getUserPosts');
    });
});
