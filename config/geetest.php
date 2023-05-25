<?php
return [

	/*
	|--------------------------------------------------------------------------
	| Config Language
	|--------------------------------------------------------------------------
	|
	| Here you can config your yunpian api key from yunpian provided.
	|
	| Options: ['zh-cn', 'zh-tw', 'en', 'ja', 'ko']
	|
	*/
	'lang' => 'en',

	/*
	|--------------------------------------------------------------------------
	| Config Geetest Id
	|--------------------------------------------------------------------------
	|
	| Here you can config your yunpian api key from yunpian provided.
	|
	*/
	'id' => env('GEETEST_ID'),

	/*
	|--------------------------------------------------------------------------
	| Config Geetest Key
	|--------------------------------------------------------------------------
	|
	| Here you can config your yunpian api key from yunpian provided.
	|
	*/
	'key' => env('GEETEST_KEY'),

	/*
	|--------------------------------------------------------------------------
	| Config Geetest URL
	|--------------------------------------------------------------------------
	|
	| Here you can config your geetest url for ajax validation.
	|
	*/
	'url' => '/geetest',

	/*
	|--------------------------------------------------------------------------
	| Config Geetest Protocol
	|--------------------------------------------------------------------------
	|
	| Here you can config your geetest url for ajax validation.
	|
	| Options: http or https
	|
	*/
	'protocol' => env('GEETEST_PROTOCOL', 'http'),

	/*
	|--------------------------------------------------------------------------
	| Config Geetest Product
	|--------------------------------------------------------------------------
	|
	| Here you can config your geetest url for ajax validation.
	|
	| Options: float, popup, custom, bind
	|
	*/
	'product' => 'float',

	/*
	|--------------------------------------------------------------------------
	| Config Client Fail Alert Text
	|--------------------------------------------------------------------------
	|
	| Here you can config the alert text when it failed in client.
	|
	*/
	'client_fail_alert' => 'Fail validation',

	/*
	|--------------------------------------------------------------------------
	| Config Server Fail Alert
	|--------------------------------------------------------------------------
	|
	| Here you can config the alert text when it failed in server (two factor validation).
	|
	*/
	'server_fail_alert' => 'Fail validation',


];
