<?php

namespace Symplify\MonorepoSplit;

class ScriptHelper
{
    public static function note(string $message): void
    {
        echo PHP_EOL . PHP_EOL . "\033[0;33m[NOTE] " . $message . "\033[0m" . PHP_EOL . PHP_EOL;
    }

    public static function error(string $message): void
    {
        echo PHP_EOL . PHP_EOL . "\033[0;31m[ERROR] " . $message . "\033[0m" . PHP_EOL . PHP_EOL;
    }

    public static function execWithNote(string $commandLine): void
    {
        self::note('Running: ' . $commandLine);
        exec($commandLine);
    }

    public static function execWithOutputPrint(string $commandLine): void
    {
        exec($commandLine, $outputLines);
        echo implode(PHP_EOL, $outputLines);
    }

    public static function setupGitCredentials(Config $config): void
    {
        if ($config->getUserName()) {
            exec('git config --global user.name ' . $config->getUserName());
        }

        if ($config->getUserEmail()) {
            exec('git config --global user.email ' . $config->getUserEmail());
        }
    }

    public static function createCommitMessage(string $commitSha): string
    {
        exec("git show -s --format=%B {$commitSha}", $outputLines);
        return $outputLines[0] ?? '';
    }
}
