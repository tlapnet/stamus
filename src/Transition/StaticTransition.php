<?php declare(strict_types = 1);

namespace Tlapnet\Stamus\Transition;

use Tlapnet\Stamus\State\State;

class StaticTransition extends Transition
{

	/** @var bool */
	protected $evaluator;

	public function __construct(State $from, State $to, bool $evaluator = true)
	{
		parent::__construct($from, $to);
		$this->evaluator = $evaluator;
	}

	public function evaluate(State $from, State $to): bool
	{
		return $this->evaluator;
	}

}
