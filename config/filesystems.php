<?php
/**
* 文件系统配置
*/

return [
	'default' => env('FILESYSTEM_DRIVER', 'local'),
	'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => 'D:\webserve\nginx\html\resources'
        ]
	]
];