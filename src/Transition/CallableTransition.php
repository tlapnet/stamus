<?php declare(strict_types = 1);

namespace Tlapnet\Stamus\Transition;

use Tlapnet\Stamus\State\State;

class CallableTransition extends Transition
{

	/** @var callable */
	protected $callback;

	public function __construct(State $from, State $to, callable $callback)
	{
		parent::__construct($from, $to);
		$this->callback = $callback;
	}

	public function evaluate(State $from, State $to): bool
	{
		return call_user_func($this->callback, $from, $to);
	}

}
