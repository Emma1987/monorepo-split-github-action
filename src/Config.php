<?php

declare(strict_types=1);

namespace Symplify\MonorepoSplit;

final class Config
{
    public function __construct(
        private string $packageDirectory,
        private string $repositoryHost,
        private string $repositoryOrganization,
        private string $repositoryName,
        private string $accessToken,
        private string $commitHash,
        private string $branch,
        private ?string $tag = null,
        private ?string $userName = null,
        private ?string $userEmail = null,
        private ?string $originBranch = null
    ) {
    }

    public function getPackageDirectory(): string
    {
        return $this->packageDirectory;
    }

    public function getGitRepository(): string
    {
        return $this->repositoryHost . '/' . $this->repositoryOrganization . '/' . $this->repositoryName . '.git';
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getCommitHash(): string
    {
        return $this->commitHash;
    }

    public function getBranch(): ?string
    {
        return $this->branch;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function getOriginBranch(): ?string
    {
        return $this->originBranch;
    }
}
