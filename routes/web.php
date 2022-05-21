<?php
/**
* 路由配置
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
		$router->post('upload/image', 'UploadFileController@image');
		$router->post('upload/video', 'UploadFileController@video');
		$router->post('upload/attachment', 'UploadFileController@attachment');
		$router->get('profile', 'ProfileController@get');
		$router->post('profile/modify-name', 'ProfileController@modifyName');
		$router->post('profile/modify-password', 'ProfileController@modifyPassword');
	});
	//^>
	//<^需要验证token+权限
	$router->group(['middleware'=>['token', 'auth']], function () use ($router) {
		//<^菜单管理
		$router->get('menu/tree', 'MenuController@tree');
		$router->post('menu/add', 'MenuController@add');
		$router->post('menu/edit', 'MenuController@edit');
		$router->post('menu/remove', 'MenuController@remove');
		$router->post('menu/permission/add', 'MenuController@addPermission');
		$router->post('menu/permission/edit', 'MenuController@editPermission');
		//^>
		//<^角色管理
		$router->get('role/list', 'RoleController@list');
		$router->post('role/add', 'RoleController@add');
		$router->post('role/edit', 'RoleController@edit');
		$router->post('role/remove', 'RoleController@remove');
		$router->get('role/tree', 'RoleController@tree');
		$router->post('role/permission-assign', 'RoleController@permissionAssign');
		//^>
		//<^部门管理
		$router->get('dept/tree', 'DeptController@tree');
		$router->post('dept/add', 'DeptController@add');
		$router->post('dept/edit', 'DeptController@edit');
		$router->post('dept/remove', 'DeptController@remove');
		//^>
		//<^岗位管理
		$router->get('post/list', 'PostController@list');
		$router->post('post/add', 'PostController@add');
		$router->post('post/edit', 'PostController@edit');
		$router->post('post/remove', 'PostController@remove');
		//^>
		//<^管理员管理
		$router->get('admin/list', 'AdminController@list');
		$router->post('admin/add', 'AdminController@add');
		$router->post('admin/edit', 'AdminController@edit');
		$router->post('admin/remove', 'AdminController@remove');
		$router->get('admin/tree', 'AdminController@tree');
		$router->post('admin/modify-password', 'AdminController@modifyPassword');
		$router->post('admin/modify-superior', 'AdminController@modifySuperior');
		$router->get('admin/export', 'AdminController@export');
		//^>
		//<^日志管理
		$router->get('log/operation', 'LogController@operation');
		$router->get('log/login', 'LogController@login');
		//^>
		//<应用示例
		$router->get('encryption/rsa', 'EncryptionController@rsa');
		//^>
	});
	//^>
});
