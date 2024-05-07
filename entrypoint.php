<?php

declare(strict_types=1);

use Symplify\MonorepoSplit\ConfigFactory;
use Symplify\MonorepoSplit\Exception\ConfigurationException;
use Symplify\MonorepoSplit\ScriptHelper;

require_once __DIR__ . '/src/autoload.php';

// Resolve configuration
ScriptHelper::note('Resolving configuration...');

$configFactory = new ConfigFactory();
try {
    $config = $configFactory->create(getenv());
} catch (ConfigurationException $configurationException) {
    ScriptHelper::error($configurationException->getMessage());
    exit(0);
}

$baseDir = getcwd();
$cloneDirectory = sys_get_temp_dir() . '/monorepo_split/clone_directory';
$buildDirectory = sys_get_temp_dir() . '/monorepo_split/build_directory';
$hostRepositoryOrganizationName = $config->getGitRepository();

// Set up git credentials
ScriptHelper::setupGitCredentials($config);

// Clone repository
$clonedRepository = 'https://' . $hostRepositoryOrganizationName;
ScriptHelper::note(sprintf('Cloning "%s" repository to "%s" directory', $clonedRepository, $cloneDirectory));

$commandLine = 'git clone -- https://' . $config->getAccessToken() . '@' . $hostRepositoryOrganizationName . ' ' . $cloneDirectory;
ScriptHelper::execWithNote($commandLine);

chdir($cloneDirectory);

// Git fetch and checkout branch
ScriptHelper::execWithOutputPrint('git fetch');
ScriptHelper::note(sprintf('Trying to checkout %s branch', $config->getBranch()));

// If the given branch doesn't exist it returns empty string
$branchSwitchedSuccessfully = exec(sprintf('git checkout %s', $config->getBranch())) !== '';

// If the branch doesn't exist we create it and push to origin, otherwise we just checkout to the given branch
if (! $branchSwitchedSuccessfully) {
    ScriptHelper::note(sprintf('Creating branch "%s" as it doesn\'t exist', $config->getBranch()));

    ScriptHelper::execWithOutputPrint(sprintf('git checkout -b %s', $config->getBranch()));
    ScriptHelper::execWithOutputPrint(sprintf('git push --quiet origin %s', $config->getBranch()));
}

chdir($baseDir);

// Cleaning destination repository of old files
ScriptHelper::note('Cleaning destination repository of old files');

// We're only interested in the .git directory, move it to $TARGET_DIR and use it from now on
mkdir($buildDirectory . '/.git', 0777, true);

$copyGitDirectoryCommandLine = sprintf('cp -r %s %s', $cloneDirectory . '/.git', $buildDirectory);
exec($copyGitDirectoryCommandLine, $outputLines, $exitCode);

if ($exitCode === 1) {
    die('Command failed');
}

// Cleanup old unused data to avoid pushing them
exec('rm -rf ' . $cloneDirectory);

// Copy the package directory including all hidden files to the clone dir
// Make sure the source dir ends with `/.` so that all contents are copied (including .github etc)
ScriptHelper::note(sprintf('Copying contents to git repo of "%s" branch', $config->getCommitHash()));
exec(sprintf('cp -ra %s %s', $config->getPackageDirectory() . '/.', $buildDirectory));

ScriptHelper::note('Files that will be pushed');
ScriptHelper::execWithOutputPrint('ls -la ' . $buildDirectory);

// WARNING! this function happen before we change directory
// If we do this in split repository, the original hash is missing there and it will fail
$commitMessage = ScriptHelper::createCommitMessage($config->getCommitHash());

$formerWorkingDirectory = getcwd();
chdir($buildDirectory);

ScriptHelper::note(sprintf('Changing directory from "%s" to "%s"', $formerWorkingDirectory, $buildDirectory));

// Avoids doing the git commit failing if there are no changes to be committed, see https://stackoverflow.com/a/8123841/1348344
ScriptHelper::execWithOutputPrint('git status');

// "status --porcelain" retrieves all modified files, no matter if they are newly created or not,
// when "diff-index --quiet HEAD" only checks files that were already present in the project.
// $changedFiles is an array that contains the list of modified files, and is empty if there are no changes.
exec('git status --porcelain', $changedFiles);

if ($changedFiles) {
    ScriptHelper::note('Adding git commit');

    ScriptHelper::execWithOutputPrint('git add .');

    ScriptHelper::note(sprintf('Pushing git commit with "%s" message to "%s"', $commitMessage, $config->getBranch()));

    exec("git commit --message '{$commitMessage}'");
    exec('git push --quiet origin ' . $config->getBranch());

    // Retrieve the original branch name
    //ScriptHelper::note('Retrieve the original branch name');

    //ScriptHelper::execWithOutputPrint(sprintf('git branch --contains %s', $config->getCommitHash()));
} else {
    ScriptHelper::note('No files to change');

    // Nothing to commit, delete the dev branch created previously
    if ($config->getBranch() !== 'master' && $config->getBranch() !== 'main') {
        ScriptHelper::note(sprintf('Deleting branch "%s"', $config->getBranch()));

        ScriptHelper::execWithOutputPrint('git status');
        ScriptHelper::execWithOutputPrint(sprintf('git checkout master && git push origin --delete %s', $config->getBranch()));
    }
}

// Push tag if present
if ($config->getTag()) {
    $message = sprintf('Publishing "%s"', $config->getTag());

    ScriptHelper::note($message);
    ScriptHelper::execWithNote(sprintf('git tag %s -m "%s"', $config->getTag(), $message));
    ScriptHelper::execWithNote('git push --quiet origin ' . $config->getTag());
}

// Restore original directory to avoid nesting WTFs
chdir($formerWorkingDirectory);
ScriptHelper::note(sprintf('Changing directory from "%s" to "%s"', $buildDirectory, $formerWorkingDirectory));
