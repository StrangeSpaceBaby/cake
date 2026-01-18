<?php

use Ulid\Ulid;

class base
{
	protected string $command = '';
	protected array $args = [];
	protected array $opts = [];

	protected string $cake_name = '';
	protected string $cake_id = '';
	protected string $root_dir = '';
	protected string $cake_dir = '';
	protected string $bakes_dir = '';
	protected string $layers_dir = '';
	protected string $recipes_dir = '';
	
	public function __construct( $args )
	{
		$this->args = $args;
		$this->parse_args();
	}

	protected function parse_args(): void
	{
		$this->command = array_shift( $this->args );

		foreach( $this->args as $arg )
		{
			if ( str_contains( $arg, ':' ) )
			{
				list( $key, $value ) = explode( ':', $arg, 2 );
				$this->opts[$key] = $value;
			}
			else
			{
				$this->opts[$arg] = TRUE;
			}
		}

		$this->root_dir = ( $this->opts['d'] ?? $this->opts['dir'] ?? getcwd() ) . "/";
		$this->cake_dir = $this->root_dir . ".cake/";
		$this->cake_id = (string) Ulid::generate( TRUE );

		$this->bakes_dir = $this->cake_dir . "bakes/";
		$this->layers_dir = $this->cake_dir . "layers/";
		$this->recipes_dir = $this->cake_dir . "recipes/";
	}

	protected function print_config( $file, $data )
	{
		file_put_contents( $file, json_encode( $data, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_NUMERIC_CHECK ) );
	}

	protected function read_config( $file )
	{
		return json_decode( file_get_contents( $file ), flags: JSON_OBJECT_AS_ARRAY | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR );
	}

	protected function rmdir_recursive( string $dir ): bool
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $file )
		{
			if ( $file->isDir() )
			{
				rmdir( $file->getRealPath() );
			}
			else
			{
				unlink( $file->getRealPath() );
			}
		}

		return rmdir( $dir );
	}

	protected function copy_recursive( string $source, string $dest, array $ignore_dirs = [ '.cake' ] ): bool
	{
		if ( !is_dir( $source ) )
		{
			say( 'Source dir is not a dir: ', $source );
			exit;
		}

		if ( !is_dir( $dest ) )
		{
			say( 'Copy dir is not a dir: ', $dest );
			exit;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $source, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file )
		{
			$sub_path = $iterator->getSubPathname();

			$continue = FALSE;
			foreach( $ignore_dirs as $ignore )
			{
				if( str_starts_with( $sub_path, $ignore ) )
				{
					$continue = TRUE;
					break;
				}
			}

			if( $continue )
			{
				continue;
			}

			$target = $dest . '/' . $sub_path;

			if ( $file->isDir() )
			{
				if ( !file_exists( $target ) )
				{
					mkdir( $target, recursive: TRUE );
				}
			}
			else
			{
				copy( $file->getRealPath(), $target );
			}
		}

		return TRUE;
	}

	protected function walk_recursive( string $dir ): Generator
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file )
		{
			yield $file;
		}
	}
}
