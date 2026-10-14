<?php
declare(strict_types=1);

use CcCheckerBot\Tests\Support\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/Support/TestCase.php';
require __DIR__ . '/Support/FakeTelegramClient.php';
$files = glob(__DIR__ . '/Unit/*Test.php') ?: [];
sort($files);
foreach ($files as $file) require_once $file;

$classes = array_values(array_filter(get_declared_classes(), static fn (string $class): bool => is_subclass_of($class, TestCase::class)));
sort($classes);
$tests = [];
foreach ($classes as $class) {
    foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (str_starts_with($method->getName(), 'test') && $method->getDeclaringClass()->getName() === $class) {
            $tests[] = [$class, $method->getName()];
        }
    }
}
TestCase::resetAssertions();
$failures = [];
foreach ($tests as [$class, $method]) {
    $failure = TestCase::execute($class, $method);
    if ($failure === null) {
        fwrite(STDOUT, '.');
    } else {
        fwrite(STDOUT, 'F');
        $failures[] = [$class . '::' . $method, $failure];
    }
}
fwrite(STDOUT, "\n\n");
foreach ($failures as [$name, $failure]) {
    fwrite(STDERR, "FAIL: {$name}\n" . $failure::class . ': ' . $failure->getMessage() . "\n" . $failure->getTraceAsString() . "\n\n");
}
fwrite(STDOUT, sprintf("%d tests, %d assertions, %d failures\n", count($tests), TestCase::assertionCount(), count($failures)));
exit($failures === [] ? 0 : 1);
