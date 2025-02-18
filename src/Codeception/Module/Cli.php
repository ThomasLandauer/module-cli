<?php

declare(strict_types=1);

namespace Codeception\Module;

use Codeception\Module;
use Codeception\PHPUnit\TestCase;
use Codeception\TestInterface;
use PHPUnit\Framework\Assert;

/**
 * Wrapper for basic shell commands and shell output
 */
class Cli extends Module
{
    public string $output = '';

    public int $result;

    public function _before(TestInterface $test): void
    {
        $this->output = '';
    }

    /**
     * Executes a shell command.
     * Fails if exit code is > 0. You can disable this by passing `false` as second argument
     *
     * ```php
     * <?php
     * $I->runShellCommand('phpunit');
     *
     * // do not fail test when command fails
     * $I->runShellCommand('phpunit', false);
     * ```
     */
    public function runShellCommand(string $command, bool $failNonZero = true): void
    {
        $data = [];
        /**
         * \Symfony\Component\Console\Application::configureIO sets SHELL_VERBOSITY environment variable
         * which may affect execution of shell command
         */
        if (\function_exists('putenv')) {
            @putenv('SHELL_VERBOSITY');
        }
        exec("{$command}", $data, $resultCode);
        $this->result = $resultCode;
        $this->output = implode("\n", $data);
        if ($this->output === null) {
            Assert::fail("{$command} can't be executed");
        }

        if ($resultCode !== 0 && $failNonZero) {
            Assert::fail("Result code was {$resultCode}.\n\n" . $this->output);
        }

        $this->debug(preg_replace('#s/\e\[\d+(?>(;\d+)*)m//g#', '', $this->output));
    }

    /**
     * Checks that output from last executed command contains text
     */
    public function seeInShellOutput(string $text): void
    {
        TestCase::assertStringContainsString($text, $this->output);
    }

    /**
     * Checks that output from latest command doesn't contain text
     */
    public function dontSeeInShellOutput(string $text): void
    {
        $this->debug($this->output);
        TestCase::assertStringNotContainsString($text, $this->output);
    }

    public function seeShellOutputMatches(string $regex): void
    {
        TestCase::assertMatchesRegularExpression($regex, $this->output);
    }

    /**
     * Returns the shell output of the latest command
     */
    public function grabShellOutput(): string
    {
        return $this->output;
    }

    /**
     * Checks the exit code of the latest command. To verify a result code >0, you need to pass `false` as second argument to `runShellCommand()`
     *
     * ```php
     * <?php
     * $I->seeExitCodeIs(0);
     * ```
     */
    public function seeExitCodeIs(int $code): void
    {
        $this->assertEquals($this->result, $code, "result code is {$code}");
    }

    /**
     * Checks the exit code of the latest command.
     *
     * ```php
     * <?php
     * $I->seeExitCodeIsNot(0);
     * ```
     */
    public function seeExitCodeIsNot(int $code): void
    {
        $this->assertNotEquals($this->result, $code, "result code is {$code}");
    }

    /**
     * @deprecated Use `seeExitCodeIs()` instead
     */
    public function seeResultCodeIs(int $code): void
    {
        $this->seeExitCodeIs($code);
    }

    /**
     * @deprecated Use `seeExitCodeIsNot()` instead
     */
    public function seeResultCodeIsNot(int $code): void
    {
        $this->seeExitCodeIsNot($code);
    }
}
