<?php declare(strict_types = 1);

namespace Tlapnet\Stamus\Transition;

use Tlapnet\Stamus\State\State;

abstract class Transition
{

	/** @var State */
	protected $from;

	/** @var State */
	protected $to;

	public function __construct(State $from, State $to)
	{
		$this->from = $from;
		$this->to = $to;
	}

	public function getFrom(): State
	{
		return $this->from;
	}

	public function getTo(): State
	{
		return $this->to;
	}

	abstract public function evaluate(State $from, State $to): bool;

}
