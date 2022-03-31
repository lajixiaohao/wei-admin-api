<?php
/**
* 文件系统配置
* 2021.7.30
*/

return [
	'default' => env('FILESYSTEM_DRIVER', 'local'),
	'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => 'D:\webserve\nginx\html\resources'
        ],
        //配置SFTP上传图片
        'image' => [
            'driver' => 'sftp',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'port' => 22,
            'root' => '/www/resources',
            'timeout' => 30,
            //目录权限
            'directoryPerm' => 0755
        ],
        //配置SFTP上传视频
        'video' => [
            'driver' => 'sftp',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'port' => 22,
            'root' => '/www/resources',
            'timeout' => 30,
            //目录权限
            'directoryPerm' => 0755
        ]
	]
];