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
			case 'update':
			case 'u':
				$this->update_current_layer();
				break;
			case 'hash':
			case 'rehash':
				break;
		}
	}

	private function update_current_layer()
	{
		$cake_meta = $this->read_config( $this->cake_dir . '/metadata.json' );
		if( !$cake_meta['current_layer'] || !$cake_meta['current_layer']['id'] )
		{
			say( 'No current layer designated' );
			exit;
		}

		$layer_id = $cake_meta['current_layer']['id'];
		$layer_meta = $this->read_config( $this->layers_dir . "/{$layer_id}/layer.metadata.json" );
		if( $this->opts['f'] || $this->opts['force'] )
		{
			if( $layer_meta['recipes'] )
			{
				say( 'Layer still in recipes. Cannot mutate layer' );
				exit;
			}

			say( "Generating hashes" );
			$files_dir = $this->layers_dir . "/{$layer_id}/files/";
			$this->rmdir_recursive( $files_dir );
			mkdir( $files_dir, recursive: TRUE );

			$this->copy_recursive( $this->root_dir, $files_dir );
			$this->generate_file_hashes( $layer_id );
		}
		else
		{
			// Gotta compare
			say( 'compare to current layer' );
			$this->update_layer( $layer_id );
		}
	}

	private function update_layer( $layer_id )
	{
		say( 'update layer ', $layer_id );

		$hashes_file = $this->layers_dir . "{$layer_id}/hashes.json";
		$layer_dir = $this->layers_dir . "{$layer_id}/";
		$files_dir = $layer_dir . "files/";

		while( $file = $this->walk_recursive( $this->root_dir ) )
		{
			$relative_path = str_replace( $files_dir, '', $file->getRealPath() );
			$file_hash = hash_hmac_file( 'sha512', $file->getRealPath(), $cake_meta['hash_key'] );
			$hash_list = $this->read_config( $hashes_file );
		}
	}

	private function generate_file_hashes( $layer_id )
	{
		say( "Generating file hashes" );

		$files_dir = $this->layers_dir . $layer_id . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR;
		$hashes_file = $this->layers_dir . "{$layer_id}/hashes.json";

		$cake_meta = $this->read_config( $this->cake_dir . "metadata.json" );
		foreach( $this->walk_recursive( $files_dir ) as $file )
		{
			if( $file->isDir() )
			{
				continue;
			}

			$filename = str_replace( $files_dir, '', $file->getRealPath() );
			if( !file_exists( $hashes_file ) )
			{
				touch( $hashes_file );
			}

			$hashes = $this->read_config( $hashes_file );
			$hashes[$filename] = hash_hmac_file( 'sha512', $file->getRealPath(), $cake_meta['hash_key'] );
			$this->print_config( $hashes_file, $hashes );
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

		$this->generate_file_hashes( $layer_id );

		return [ $layer_id, $layer_name ];
	}
}