<?php

use Ulid\Ulid;

class layer extends base
{
	public function __construct( $args )
	{
		parent::__construct( $args );

		switch( $this->command )
		{
			case 'c':
			case 'create':
			case 'n':
			case 'new':
				$this->create_layer( $this->args[0] );
				break;
		}
	}

	private function create_layer( $layer_name )
	{
		$layer_id = (string) Ulid::generate( TRUE );
		$layer_dir = $this->layers_dir . $layer_id;
		$files_dir = $layer_dir . '/files/';
		mkdir( $files_dir, recursive: true );

		$layer_metadata = [
			'id' => $layer_id,
			'name' => $layer_name,
			'recipes' => []
		];

		$this->print_config( $layer_dir . "/layer.metadata.json", $layer_metadata );

		$this->copy_recursive( $this->root_dir, $files_dir );

		$cake_metadata = $this->read_config( $this->cake_dir . 'metadata.json' );
		$cake_metadata['current_layer']['id'] = $layer_id;
		$cake_metadata['current_layer']['name'] = $layer_name;
		$cake_metadata['kitchen']['counts']['layers'] += 1;
		$this->print_config( $this->cake_dir . 'metadata.json', $cake_metadata );

		return [ $layer_id, $layer_name ];
	}
}