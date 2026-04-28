<?php

namespace App\command;

class Cake
{
	protected \League\CLImate\CLImate $cli;

	public function __construct()
	{
		$this->cli = new \League\CLImate\CLImate;
	}

	protected function rm_dir( string $path ): void
	{
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $path, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
	
		foreach( $files as $file )
		{
			$file->isDir() ?
				rmdir( $file->getRealPath() )
				: unlink( $file->getRealPath() );
		}
	
		rmdir( $path );
	}
}