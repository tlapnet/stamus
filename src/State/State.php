<?php declare(strict_types = 1);

namespace Tlapnet\Stamus\State;

use Tlapnet\Stamus\Exception\Logical\InvalidArgumentException;

class State
{

	/** @var string */
	protected $id;

	/** @var mixed|null */
	protected $context;

	/**
	 * @param mixed|null $context
	 */
	public function __construct(string $id, $context = null)
	{
		if ($id === '') {
			throw new InvalidArgumentException('State id cannot be empty string');
		}

		$this->id = $id;
		$this->context = $context;
	}

	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return mixed|null
	 */
	public function getContext()
	{
		return $this->context;
	}

}
