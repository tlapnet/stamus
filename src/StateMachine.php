<?php declare(strict_types = 1);

namespace Tlapnet\Stamus;

use Tlapnet\Stamus\Exception\Logical\DuplicateStateException;
use Tlapnet\Stamus\Exception\Logical\InvalidArgumentException;
use Tlapnet\Stamus\Exception\Logical\InvalidStateException;
use Tlapnet\Stamus\Exception\Runtime\CannotGoToNextStateException;
use Tlapnet\Stamus\Exception\Runtime\NoSuitableTransitionException;
use Tlapnet\Stamus\Exception\Runtime\UniqueTransitionException;
use Tlapnet\Stamus\State\State;
use Tlapnet\Stamus\Transition\Transition;

class StateMachine
{

	/** @var State[] */
	protected $states = [];

	/** @var Transition[] */
	protected $transitions = [];

	/** @var State|null */
	protected $currentState;

	/**
	 * @param State[] $states
	 * @param Transition[] $transitions
	 */
	public function __construct(array $states, array $transitions)
	{
		if ($states === []) {
			throw new InvalidArgumentException('Empty states given');
		}

		if ($transitions === []) {
			throw new InvalidArgumentException('Empty transitions given');
		}

		foreach ($states as $state) {
			if (array_key_exists($state->getId(), $this->states)) {
				throw new DuplicateStateException(sprintf('State (%s) alredy exists.', $state->getId()));
			}

			$this->states[$state->getId()] = $state;
		}

		$this->transitions = $transitions;
	}

	/**
	 * @return State[]
	 */
	public function getStates(): array
	{
		return $this->states;
	}

	/**
	 * @return Transition[]
	 */
	public function getTransitions(): array
	{
		return $this->transitions;
	}

	public function getCurrentState(): ?State
	{
		return $this->currentState;
	}

	public function setCurrentState(string $state): void
	{
		// Lookup for state by ID
		$found = $this->findState($state);

		// Validation
		if ($found === null) {
			throw new InvalidArgumentException(sprintf('State "%s" not found', $state));
		}

		$this->currentState = $found;
	}

	/**
	 * STATE API ***************************************************************
	 */

	/**
	 * Try to change SM to given state
	 *
	 * @throws UniqueTransitionException
	 */
	public function canChange(string $state): bool
	{
		// Validate current state
		if ($this->currentState === null) {
			throw new InvalidStateException('No current state selected');
		}

		try {
			$this->resolveTransition($this->currentState, $state);

			return true;
		} catch (NoSuitableTransitionException $e) {
			return false;
		}
	}

	/**
	 * Sets current state if possible
	 *
	 * @throws UniqueTransitionException
	 */
	public function change(string $state): void
	{
		if (!$this->canChange($state)) {
			throw new CannotGoToNextStateException(
				sprintf(
					'Cannot go from "%s" to "%s"',
					$this->currentState !== null ? $this->currentState->getId() : 'unknown',
					$state
				)
			);
		}

		$this->currentState = $this->findState($state);
	}

	/**
	 * HELPERS *****************************************************************
	 */

	/**
	 * Lookup for state by ID
	 */
	protected function findState(string $state): ?State
	{
		return $this->states[$state] ?? null;
	}

	/**
	 * Lookup for transitions by state
	 *
	 * @return Transition[]
	 */
	protected function findTransitionsByState(State $state): array
	{
		if (!isset($this->states[$state->getId()])) {
			throw new InvalidStateException(
				sprintf('Cannot search transitions for unknown state "%s"', $state->getId())
			);
		}

		$transitions = [];

		foreach ($this->transitions as $t) {
			if ($t->getFrom() === $state) {
				$transitions[] = $t;
			}
		}

		return $transitions;
	}

	/**
	 * Resolve transition between two states
	 *
	 * @throws NoSuitableTransitionException
	 * @throws UniqueTransitionException
	 */
	protected function resolveTransition(State $fromState, string $toState): bool
	{
		if (!isset($this->states[$toState])) {
			throw new InvalidStateException(
				sprintf('Unknown go-to state "%s"', $toState)
			);
		}

		// Lookup for transitions by states
		$transitions = $this->findTransitionsByState($fromState);

		if ($transitions === []) {
			throw new InvalidStateException(
				sprintf('No transitions defined for state "%s"', $fromState->getId())
			);
		}

		// Iterate over all transitions from $fromState
		// Only one possible is allowed
		$validTransitions = [];

		foreach ($transitions as $t) {
			if ($t->evaluate($fromState, $t->getTo()) === false) {
				continue;
			}

			$validTransitions[] = $t;
		}

		if (count($validTransitions) > 1) {
			throw new UniqueTransitionException(
				sprintf('State "%s" transitions to multiple states under same condition.', $fromState->getId()),
				$validTransitions
			);
		}

		// If there is transition to correct state
		if (count($validTransitions) === 1 && $validTransitions[0]->getTo()->getId() === $toState) {
			return true;
		}

		throw new NoSuitableTransitionException('Not suitable transition found');
	}

}
