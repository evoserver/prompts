<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\steps;
use function Laravel\Prompts\text;

it('can run multiple steps', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::ENTER
    ]);

    $responses = steps()
        ->add(fn() => text('What is your name'))
        ->add(fn() => select('What is your language', ['PHP', 'JS']))
        ->add(fn() => confirm('Are you sure?'))
        ->run();

    expect($responses)->toBe([
        'Luke',
        'PHP',
        true
    ]);

});

it('can revert steps', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::CTRL_U, Key::CTRL_U,
        'J', 'e', 's', 's', Key::ENTER,
        Key::DOWN, Key::ENTER,
        Key::ENTER
    ]);

    $responses = steps()
        ->add(fn() => text('What is your name'))
        ->add(fn() => select('What is your language', ['PHP', 'JS']))
        ->add(fn() => confirm('Are you sure?'))
        ->run();

    expect($responses)->toBe([
        'Jess',
        'JS',
        true
    ]);

});

it('can prevent a step from being reverted', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::CTRL_U,
        Key::ENTER
    ]);

    steps()
        ->add(fn() => text('What is your name'))
        ->add(fn() => select('What is your language', ['PHP', 'JS']), revert: false)
        ->add(fn() => confirm('Are you sure?'))
        ->run();

    Prompt::assertOutputContains('This cannot be reverted');

});

it('can run custom logic when reverting a step', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::CTRL_U,
        Key::ENTER,
        Key::ENTER
    ]);

    steps()
        ->add(fn() => text('What is your name'))
        ->add(
            fn() => select('What is your language', ['PHP', 'JS']),
            fn() => info('Hello Luke!')
        )
        ->add(fn() => confirm('Are you sure?'))
        ->run();

    Prompt::assertOutputContains('Hello Luke!');

});

it('passes all available responses to each step', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::ENTER
    ]);

    steps()
        ->add(fn() => text('What is your name'))
        ->add(fn() => select('What is your language', ['PHP', 'JS']))
        ->add(fn($responses
        ) => confirm("Are you sure your name is {$responses[0]} and your language is {$responses[1]}?"))
        ->run();

    Prompt::assertOutputContains('Are you sure your name is Luke and your language is PHP?');

});

it('passes all available responses to each step revert closure', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::CTRL_U, Key::ENTER,
        Key::ENTER
    ]);

    steps()
        ->add(fn() => text('What is your name'))
        ->add(
            fn() => select('What is your language', ['PHP', 'JS']),
            revert: fn($responses) => info("You selected {$responses['language']}"),
            key: 'language'
        )
        ->add(fn() => confirm('Are you sure?'))
        ->run();

    Prompt::assertOutputContains('You selected PHP');

});

it('can key a response by a given string', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::ENTER
    ]);

    steps()
        ->add(fn() => text('What is your name'), key: 'name')
        ->add(fn() => select('What is your language', ['PHP', 'JS']), key: 'language')
        ->add(fn($responses
        ) => confirm("Are you sure your name is {$responses['name']} and your language is {$responses['language']}?"))
        ->run();

    Prompt::assertOutputContains('Are you sure your name is Luke and your language is PHP?');

});

it('does not allow reverting normal prompts', function () {

    Prompt::fake([
        'L', 'u', 'k', 'e', Key::ENTER,
        Key::ENTER,
        Key::CTRL_U,
        Key::ENTER
    ]);

    steps()
        ->add(fn() => text('What is your name'))
        ->add(fn() => select('What is your language', ['PHP', 'JS']))
        ->run();

    $confirm = confirm('Are you sure?');

    expect($confirm)->toBeTrue();

});
