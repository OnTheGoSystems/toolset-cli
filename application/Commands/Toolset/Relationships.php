<?php

namespace OTGS\Toolset\CLI\Commands\Toolset;

use OTGS\Toolset\CLI\Commands\ToolsetCommand;

class Relationships extends ToolsetCommand {

	public function test() {
		$this->wp_cli()->success( 'Test successful!' );
	}

}
