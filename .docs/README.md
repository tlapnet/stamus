# Tlapnet Stamus

Simple state machine library for verifying State transitions

## Content

- [Setup](.docs/README.md#setup)
- [State](.docs/README.md#state)
- [Transitions](.docs/README.md#transitions)
- [Example usage](.docs/README.md#example-usage)

## Setup

```bash
composer require tlapnet/stamus
```

## State

A state in state machine that can be verified whether can be transitioned to another state. State accepts unique ID and optional context

## Transitions

You can use 2 predefined transitions. Transition has one role to decide whether you can go from one state to another

* `StaticTransition` - which accepts FROM and TO states and boolean
* `CallableTransition` - which accepts FROM and TO states and callback. Callback is passed both states and must return boolean

## Example usage

```php
use Tlapnet\Stamus\State\State;
use Tlapnet\Stamus\StateMachineBuilder;
use Tlapnet\Stamus\Transition\StaticTransition;
use Tlapnet\Stamus\Transition\CallableTransition;

// Create states
$state1 = new State('START');
$state2 = new State('S2', ['isReady' => TRUE]);
$state3 = new State('END');

// Create transitions
$transition1 = new StaticTransition($state1, $state2);
$transition2 = new CallableTransition(
    $state2,
    $state3,
    function(State $s2, State $s3): bool {
        return $s2->getContext()['isReady'] === TRUE;
    });

// Build state machine
$smb = new StateMachineBuilder();
$smb->addState($state1);
$smb->addState($state2);
$smb->addState($state3);

$smb->addTransition($transition1);
$smb->addTransition($transition2);

$sm = $smb->build();

// Use machine
$sm->setCurrentState('START');
if ($sm->canChange('S2')) {
    $sm->change('S2');
}

if ($sm->getCurrentState()->getId() === 'S2') {
    $sm->change('END');
}

```
