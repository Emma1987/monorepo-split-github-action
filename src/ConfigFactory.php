<?php

declare(strict_types=1);

namespace Symplify\MonorepoSplit;

use Symplify\MonorepoSplit\Exception\ConfigurationException;

final class ConfigFactory
{
    /**
     * @param array<string, mixed> $env
     */
    public function create(array $env): Config
    {
        return new Config(
            packageDirectory: $env['INPUT_PACKAGE_DIRECTORY'] ?? throw new ConfigurationException('Package directory is missing'),
            repositoryHost: $env['INPUT_REPOSITORY_HOST'] ?? throw new ConfigurationException('Repository host is missing'),
            repositoryOrganization: $env['INPUT_REPOSITORY_ORGANIZATION'] ?? throw new ConfigurationException('Repository organization is missing'),
            repositoryName: $env['INPUT_REPOSITORY_NAME'] ?? throw new ConfigurationException('Repository name is missing'),
            accessToken: $env['GITHUB_TOKEN'] ?? throw new ConfigurationException('Public access token is missing, add it via GITHUB_TOKEN'),
            commitHash: $env['GITHUB_SHA'],
            branch: $env['INPUT_BRANCH'],
            tag: $env['INPUT_TAG'] ?? null,
            userName: $env['INPUT_USER_NAME'] ?? null,
            userEmail: $env['INPUT_USER_EMAIL'] ?? null,
            ref: $env['GITHUB_REF'] ?? null
        );
    }
}
