<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Stamus\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tlapnet\Stamus\Exception\Logical\DuplicateStateException;
use Tlapnet\Stamus\Exception\Logical\InvalidArgumentException;
use Tlapnet\Stamus\Exception\Runtime\UniqueTransitionException;
use Tlapnet\Stamus\State\State;
use Tlapnet\Stamus\StateMachine;
use Tlapnet\Stamus\Transition\CallableTransition;
use Tlapnet\Stamus\Transition\StaticTransition;

class StateMachineTest extends TestCase
{

	public function testNoStatesGiven(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$sm = new StateMachine([], []);
	}

	public function testNoTransitionsGiven(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$sm = new StateMachine([new State('S1')], []);
	}

	public function testDuplicateStatesGiven(): void
	{
		$this->expectException(DuplicateStateException::class);

		$s1 = new State('S1');
		$s2 = new State('S2');
		$s3 = new State('S1');
		$sm = new StateMachine(
			[$s1, $s2, $s3],
			[new StaticTransition($s1, $s3)]
		);
	}

	public function testUniqueTransition(): void
	{
		$this->expectException(UniqueTransitionException::class);

		$s1 = new State('S1');
		$s2 = new State('S2');
		$s3 = new State('S3');
		$sm = new StateMachine(
			[$s1, $s2, $s3],
			[new StaticTransition($s1, $s2), new StaticTransition($s1, $s3)]
		);

		$sm->setCurrentState('S1');
		$sm->canChange('S3');
	}

	public function testStateMachine1(): void
	{
		$state1 = new State('START');
		$state2 = new State('S2');
		$state3 = new State('S3');
		$state4 = new State('END');

		$t1 = new StaticTransition($state1, $state2);
		$t2 = new StaticTransition($state2, $state3);
		$t3 = new StaticTransition($state3, $state4);

		$sm = new StateMachine([
			$state1,
			$state2,
			$state3,
			$state4,
		], [
			$t1,
			$t2,
			$t3,
		]);

		$this->assertNull($sm->getCurrentState());

		// Change initialize state
		$sm->setCurrentState('START');
		$this->assertSame($state1, $sm->getCurrentState());

		$this->assertTrue($sm->canChange('S2'));
		$sm->change('S2');
		$this->assertSame($state2, $sm->getCurrentState());

		$this->assertTrue($sm->canChange('S3'));
		$sm->change('S3');
		$this->assertSame($state3, $sm->getCurrentState());

		$this->assertTrue($sm->canChange('END'));
		$sm->change('END');
		$this->assertSame($state4, $sm->getCurrentState());
	}

	public function testStateMachine2(): void
	{
		$state1 = new State('START');
		$state2 = new State('END');

		$t1 = new StaticTransition($state1, $state2, false);

		$sm = new StateMachine([
			$state1,
			$state2,
		], [
			$t1,
		]);

		// Change initialize state
		$sm->setCurrentState('START');
		$this->assertSame($state1, $sm->getCurrentState());

		$this->assertFalse($sm->canChange('END'));
	}

	public function testStateMachine3(): void
	{
		$state1 = new State('START');
		$state2 = new State('END');

		$t1 = new StaticTransition($state1, $state2, false);
		$t2 = new StaticTransition($state1, $state2, true);

		$sm = new StateMachine([
			$state1,
			$state2,
		], [
			$t1,
			$t2,
		]);

		// Change initialize state
		$sm->setCurrentState('START');
		$this->assertSame($state1, $sm->getCurrentState());

		$this->assertTrue($sm->canChange('END'));
	}

	public function testStateMachine4(): void
	{
		$state1 = new State('START');
		$state2 = new State('END');

		$t1 = new StaticTransition($state1, $state2, false);
		$t2 = new StaticTransition($state1, $state2, false);

		$sm = new StateMachine([
			$state1,
			$state2,
		], [
			$t1,
			$t2,
		]);

		// Change initialize state
		$sm->setCurrentState('START');
		$this->assertSame($state1, $sm->getCurrentState());

		$this->assertFalse($sm->canChange('END'));
	}

	public function testStateMachine5(): void
	{
		$process = new stdClass();
		$process->foo = false;

		$state1 = new State('START');
		$state2 = new State('S1');
		$state3 = new State('S2', $process);
		$state4 = new State('END');

		$t1 = new StaticTransition($state1, $state2);
		$t2 = new StaticTransition($state2, $state3);
		$t3 = new CallableTransition($state3, $state4, function (State $from, State $to): bool {
			return $from->getContext()->foo;
		});
		$t4 = new CallableTransition($state3, $state2, function (State $from, State $to): bool {
			return !$from->getContext()->foo;
		});

		$sm = new StateMachine([
			$state1,
			$state2,
			$state3,
			$state4,
		], [
			$t1,
			$t2,
			$t3,
			$t4,
		]);

		$sm->setCurrentState('START');
		$this->assertTrue($sm->canChange('S1'));
		$sm->change('S1');
		$this->assertTrue($sm->canChange('S2'));
		$sm->change('S2');
		$this->assertFalse($sm->canChange('END'));
		$this->assertTrue($sm->canChange('S1'));
		$sm->change('S1');
		$process->foo = true;
		$this->assertTrue($sm->canChange('S2'));
		$sm->change('S2');
		$this->assertTrue($sm->canChange('END'));
		$sm->change('END');
	}

}
