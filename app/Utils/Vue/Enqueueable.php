<?php

namespace TaskLedger\App\Utils\Vue;

trait Enqueueable
{
	public function enqueue($callback)
	{
		$callback();

		return $this;
	}
}
