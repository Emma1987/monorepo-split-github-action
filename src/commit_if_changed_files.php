<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// setup GitHub envs to variables
$envs = getenv();

$commitSha = $envs['GITHUB_SHA'];
$branch = $envs['BRANCH'];

function createCommitMessage(string $commitSha): string
{
    exec("git show -s --format=%B $commitSha", $output);
    return $output[0] ?? '';
}

$commitMessage = createCommitMessage($commitSha);


// avoids doing the git commit failing if there are no changes to be commit, see https://stackoverflow.com/a/8123841/1348344
exec('git diff-index --quiet HEAD', $output, $hasChangedFiles);

// 1 = changed files
// 0 = no changed files
if ($hasChangedFiles === 1) {
    echo 'Adding git commit' . PHP_EOL;
    exec('git add .');
    exec("git commit --message '$commitMessage'");

    echo "Pushing git commit with '$commitMessage' message" . PHP_EOL;
    exec('git push --quiet origin $branch');
} else {
    echo 'No files to change' . PHP_EOL;
}
