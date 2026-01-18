<?php

if ( 'cli' !== php_sapi_name() )
{
	say( "Chef must be run from the command line" );
	exit( 1 );
}

if ( version_compare( PHP_VERSION, '8.4.0', '<' ) )
{
	say( "Chef requires PHP 8.4 or higher" );
	exit( 1 );
}

$scope = $argv[1] ?? '';
$method = $argv[2] ?? '';

if ( '' === $scope )
{
	say( "No command given" );
	exit( 1 );
}

$args = array_slice( $argv, 2 );

$scope_file = __DIR__ . "/scopes/{$scope}.scope.php";

if ( !file_exists( $scope_file ) )
{
	say( "Unknown command: {$scope}" );
	exit( 1 );
}

require_once( 'vendor/autoload.php' );
require_once( __DIR__ . "/scopes/base.scope.php" );
require_once( $scope_file );

new $scope( $args );

function say( $msg, $data = [] )
{
	print $msg;
	if( $data )
	{
		if( is_array( $data ) || is_object( $data ) )
		{
			print_r( $data );
		}
		else
		{
			print $data;
		}
	}
	print "\n";
}