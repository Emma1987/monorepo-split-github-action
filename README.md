# GitHub Action for Monorepo Split

Do you have [a monorepo](https://tomasvotruba.com/cluster/monorepo-from-zero-to-hero/) project on GitHub and need split packages to many repositories? Add this GitHub Action to your workflow and let it split your packages on every commit and tag.

### How does the Split Result Look Like?

This repository splits tests into [symplify/monorepo-split-github-action-test](https://github.com/symplify/monorepo-split-github-action-test) repository.

Not on every commit, but only if contents of `/tests/packages/some-package` directory changes.
Try it yourself - send PR with change in [that directory](/tests/packages/some-package).

<br>

## Config

Split is basically git push or local directory to remote git repository. This remote repository can be located on GitHub or Gitlab. To be able to do that, it needs `GITHUB_TOKEN` or `GITLAB_TOKEN` with write repository access:

```yaml
env:
    GITHUB_TOKEN: ${{ secrets.ACCESS_TOKEN }}
```

Make sure to add this access token in "Secrets" of package settings at https://github.com/<organization>/<package>/settings/environments

<br>

## Define your GitHub Workflow

```yaml
name: 'Bundles Split'

on:
    push:
        branches:
            - main
        tags:
            - '*'

env:
    GITHUB_TOKEN: ${{ secrets.ACCESS_TOKEN }}

jobs:
    packages_split:
        runs-on: ubuntu-latest

        strategy:
            fail-fast: false
            matrix:
                # define package to repository map
                package:
                    - { local_path: 'bundles/admin-ui-bundle', split_repository: 'admin-ui-bundle' }

        steps:
            -   uses: actions/checkout@v2

            # no tag
            -
                if: "!startsWith(github.ref, 'refs/tags/')"
                uses: "Eckinox/monorepo-split-github-action@1.0.0"
                with:
                    package_directory: '${{ matrix.package.local_path }}'

                    branch: 'master'

                    repository_organization: 'Eckinox'
                    repository_name: '${{ matrix.package.split_repository }}'

                    # ↓ the user signed under the split commit
                    user_name: 'Eckinox'
                    user_email: 'dev@eckinox.ca'

            # with tag
            -
                if: "startsWith(github.ref, 'refs/tags/')"
                uses: "Eckinox/monorepo-split-github-action@1.0.0"
                with:
                    tag: ${GITHUB_REF#refs/tags/}

                    package_directory: '${{ matrix.package.local_path }}'

                    branch: 'master'

                    repository_organization: 'Eckinox'
                    repository_name: '${{ matrix.package.split_repository }}'

                    user_name: 'Eckinox'
                    user_email: 'dev@eckinox.ca'
```
