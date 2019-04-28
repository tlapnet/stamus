<?php declare(strict_types = 1);

namespace Tlapnet\Stamus;

use Tlapnet\Stamus\State\State;
use Tlapnet\Stamus\Transition\Transition;

class StateMachineBuilder
{

	/** @var State[] */
	protected $states = [];

	/** @var Transition[] */
	protected $transitions = [];

	public function addState(State $state): void
	{
		$this->states[] = $state;
	}

	public function addTransition(Transition $transition): void
	{
		$this->transitions[] = $transition;
	}

	public function build(): StateMachine
	{
		return new StateMachine($this->states, $this->transitions);
	}

}
