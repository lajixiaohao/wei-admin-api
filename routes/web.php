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
    // return $router->app->version();
    return response()->json(['code'=>1, 'msg'=>'Bad Request'], 400);
});

$router->group(['namespace'=>'V1', 'prefix'=>'api'], function () use ($router) {
	// 登录
	$router->post('login', 'LoginController@index');
	// 获取验证码
	$router->get('get-captcha', 'CaptchaController@get');

	//<^只需要验证token有效
	$router->group(['middleware'=>'token'], function () use ($router) {
		$router->get('initialize', 'InitializeController@index');
		$router->post('logout', 'LogoutController@index');
		$router->post('upload-image', 'UploadFileController@image');
		$router->post('upload-video', 'UploadFileController@video');
		$router->get('profile', 'ProfileController@get');
		$router->post('profile/modify-name', 'ProfileController@modifyName');
		$router->post('profile/modify-password', 'ProfileController@modifyPassword');
	});
	//^>
	//<^需要验证token+权限
	$router->group(['middleware'=>['token', 'auth']], function () use ($router) {
		//<^菜单管理
		$router->get('menu/list', 'MenuController@list');
		$router->post('menu/add', 'MenuController@add');
		$router->post('menu/edit', 'MenuController@edit');
		$router->post('menu/remove', 'MenuController@remove');
		$router->post('menu/add-register-route', 'MenuController@addRegisterRoute');
		$router->post('menu/edit-register-route', 'MenuController@editRegisterRoute');
		$router->post('menu/add-permission', 'MenuController@addPermission');
		$router->post('menu/edit-permission', 'MenuController@editPermission');
		$router->get('menu/get-two-level-menu', 'MenuController@getTwoLevelMenu');
		//^>
		//<^角色管理
		$router->get('role/list', 'RoleController@list');
		$router->post('role/add', 'RoleController@add');
		$router->post('role/edit', 'RoleController@edit');
		$router->post('role/remove', 'RoleController@remove');
		$router->get('role/tree', 'RoleController@tree');
		$router->post('role/permission-assign', 'RoleController@permissionAssign');
		//^>
		//<^管理员管理
		$router->get('admin/list', 'AdminController@list');
		$router->post('admin/add', 'AdminController@add');
		$router->post('admin/edit', 'AdminController@edit');
		$router->post('admin/remove', 'AdminController@remove');
		$router->get('admin/tree', 'AdminController@tree');
		$router->post('admin/modify-password', 'AdminController@modifyPassword');
		$router->post('admin/change-takeover', 'AdminController@changeTakeover');
		//^>
		//<^日志管理
		$router->get('log/operation', 'LogController@operation');
		$router->get('log/login', 'LogController@login');
		$router->get('log/operation/export', 'LogController@exportOperationLog');
		//^>
		//<^部门管理
		$router->get('dept/index', 'DeptController@index');
		// $router->get('department/tree', 'DepartmentController@tree');
		// $router->post('department/add', 'DepartmentController@add');
		// $router->post('department/edit', 'DepartmentController@edit');
		// $router->post('department/remove', 'DepartmentController@remove');
		//^>
		//<^岗位管理
		$router->get('post/list', 'PostController@list');
		$router->post('post/add', 'PostController@add');
		$router->post('post/edit', 'PostController@edit');
		$router->post('post/remove', 'PostController@remove');
		//^>
		//<加密应用
		$router->get('encryption/rsa', 'EncryptionController@rsa');
		//^>
	});
	//^>
});
