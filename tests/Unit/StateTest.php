<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Stamus\Unit;

use PHPUnit\Framework\TestCase;
use Tlapnet\Stamus\Exception\Logical\InvalidArgumentException;
use Tlapnet\Stamus\State\State;

class StateTest extends TestCase
{

	public function testCreate(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new State('');
	}

}
