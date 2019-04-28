<?php declare(strict_types = 1);

namespace Tlapnet\Stamus\Exception\Runtime;

use Tlapnet\Stamus\Exception\RuntimeException;
use Tlapnet\Stamus\Transition\Transition;

class UniqueTransitionException extends RuntimeException
{

	/** @var Transition[] */
	protected $transitions;

	/**
	 * @param Transition[] $transitions
	 */
	public function __construct(string $message, array $transitions)
	{
		parent::__construct($message);
		$this->transitions = $transitions;
	}

	/**
	 * @return Transition[]
	 */
	public function getTransitions(): array
	{
		return $this->transitions;
	}

}
