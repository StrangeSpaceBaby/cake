<?php

namespace App;

error_reporting( E_ALL & ~E_WARNING & ~E_DEPRECATED );

require_once "./vendor/autoload.php";

$cli = new \League\CLImate\CLImate;

// Remove on release, only for debugging
function p( $print )
{
	print_r( $print );
	print "\n";
}

spl_autoload_register(
	function( $class )
	{
		$class = str_replace( '\\', '/', $class );
		$class = str_replace( 'App/', '', $class );

		$loc = realpath( str_replace( 'App/', '', './' . $class . ".command.php" ) );
		if( !file_exists( $loc ) )
		{
			$cli->red( $class . ' command file not found' );
			exit;
		}

		require_once( $loc );
	}
);

// Procedural code for executing the overall cake cli by parsing and passing arguments to the appropriate command class
$params = array_slice( $argv, 1 ); // Removes script name

if( !$params )
{
	$cli->red( 'No arguments given' );
	exit;
}

list( $scope, $method ) = explode( ':', array_shift( $params ) );
if( !$scope || !$method )
{
	$cli->red( 'No ' . (!$scope ? 'scope' : 'command') . ' given' );
	exit;
}

$args = [];
$opts =
[
	'dry-run' => FALSE,
	'force-cmd' => FALSE
];

// For managing options on the cli
foreach( $params as $param )
{
	switch( $param )
	{
		// All flags first
		case '--dry-run':
		case '--dry':
		case '-dr':
			$opts['dry-run'] = TRUE;
			break;
		case '--force':
		case '-f':
			$opts['force-cmd'] = TRUE;
			break;
		case FALSE !== stripos( $param, '=' ):
			list( $param, $value ) = explode( '=', $param );
		default:
			if( !$value )
			{
				$value = $param;
			}
			$args[$param] = $value;
	}
}

define( 'CAKE_SCOPE', $scope );
define( 'CAKE_METHOD', $method );
define( 'CAKE_ARGS', $args );
define( 'CAKE_OPTS', $opts );
define( 'CAKE_FORCE', $opts['force-cmd'] );
define( 'CAKE_DRY_RUN', $opts['dry-run'] );

define( 'CAKE_DIR', dirname( dirname( __FILE__ ) ) . "/" );
define( 'CAKE_CLI_DIR', CAKE_DIR . "cli/" );
define( 'CAKE_COMMAND_DIR', CAKE_CLI_DIR . 'command' . "/" );

$cli_command_name = "App\command\\" . ucfirst( CAKE_SCOPE );

new $cli_command_name()->$method();
