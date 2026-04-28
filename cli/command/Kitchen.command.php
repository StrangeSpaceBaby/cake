<?php

namespace App\command;

class Kitchen extends Cake
{
	public function __construct()
	{
		parent::__construct();
	}

	public function init()
	{
		$path = array_key_first( CAKE_ARGS );
		if( !$path )
		{
			$this->cli->red( 'no path given to init' );
			exit;
		}

		$kitchen_path = rtrim( $path, '/' ) . '/.kitchen';
	
		if( is_dir( $kitchen_path ) && !CAKE_FORCE )
		{
			$this->cli->red( 'Kitchen already exists at ' . $kitchen_path );
			exit;
		}

		$this->reset_kitchen( $kitchen_path );
	}

	public function reset_kitchen( $kitchen_path )
	{
		if( file_exists( $kitchen_path ) )
		{
			$this->rm_dir( $kitchen_path );
		}

		mkdir( $kitchen_path . '/layers', 0755, TRUE );
		mkdir( $kitchen_path . '/recipes', 0755, TRUE );
		mkdir( $kitchen_path . '/bakes', 0755, TRUE );
	
		file_put_contents( $kitchen_path . '/kitchen.state.json', '{}' );
	
		$this->cli->green( 'Kitchen initialized at ' . $kitchen_path );
	}

	public function uninit()
	{
		$path = array_key_first( CAKE_ARGS ) . '/.kitchen';

		if( !$path )
		{
			$this->cli->red( 'No path given' );
			exit;
		}

		if( !file_exists( $path ) )
		{
			$this->cli->red( 'No kitchen found at path' );
			exit;
		}

		$this->rm_dir( $path );

		$this->cli->green( 'Kitchen removed' );
	}
}