<?php

use Ulid\Ulid;

require_once( 'layer.scope.php' );

class kitchen extends base
{
	public function __construct( array $args )
	{
		parent::__construct( $args );

		switch( $this->command )
		{
			case 'open':
			case 'o':
				$this->cake_name = $args[1];
				$this->open_kitchen();
				break;
			case 'closed':
			case 'c':
				break;
			case 'pantry':
			case 'p':
				break;
			default:
				say( "Invalid command: " . $this->command );
		}
	}

	private function open_kitchen()
	{
		say( 'Cake name: ', $this->cake_name );

		$this->can_open_kitchen_location();

		// Check for existing cake but we'll do this after we set up the first one
		
		$this->init_cake();
	}

	private function init_cake()
	{
		if( !file_exists( $this->cake_dir ) )
		{
			mkdir( $this->cake_dir, recursive: TRUE );
		}
		else
		{
			if( !$this->opts['f'] && !$this->opts['force'] )
			{
				say( 'Could not create cake dir', $this->cake_dir );
				exit;
			}

			$this->rmdir_recursive( $this->cake_dir );
			mkdir( $this->cake_dir );
		}

		say( 'Created cake dir: ', $this->cake_dir );

		foreach([ 'layers', 'bakes', 'recipes' ] as $cdir )
		{
			if( !file_exists( $this->cake_dir . $cdir ) )
			{
				say( "Making ", $cdir );
				mkdir( $this->cake_dir . $cdir );
			}
		}

		$kitchen_id = (string) Ulid::generate( TRUE );

		$cake_metadata = [
			'cake' => [
				'version' => '0.1',
				'name' => 'Baker'
			],
			'kitchen' => [
				'id' => $kitchen_id,
				'name' => $this->cake_name,
				'counts' => [
					'layers' => 0,
					'recipes' => 0,
					'bakes' => 0
				]
			],
			'hash_key' => (string) Ulid::generate( TRUE )
		];

		$this->print_config( $this->cake_dir . "metadata.json", $cake_metadata );

		$layer_name = $this->cake_name . '-base';

		new layer([ 'create', $layer_name ]);

		say( 'Kitchen open' );
	}

	private function can_open_kitchen_location()
	{
		if( !is_dir( $this->root_dir ) )
		{
			say( 'Directory does not exist: ', $this->root_dir );
			exit;
		}

		return TRUE;
	}
}