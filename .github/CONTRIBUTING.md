# Contributing to ImboClient

If you want to contribute to ImboClient please follow these guidelines.

## Requirements for local development

You should ideally use PHP 8.3 since that is the lowest supported version. Features added to PHP 8.4 and later MUST NOT be used as long as we want to support 8.3.

The GitHub workflow will run tests / QA on PHP 8.3, 8.4 and 8.5.

Refer to [composer.json](../composer.json) for more requirements.

## Coding standards

ImboClient follows the [Imbo coding standard](https://github.com/imbo/imbo-coding-standard), and runs [php-cs-fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) as a step in the CI workflow, failing the workflow if there are discrepancies. You can also run the check locally:

    composer run cs

You can also have php-cs-fixer automatically fix the issues:

    composer run cs:fix

## Running tests and static analysis

[PHPUnit](https://phpunit.de) is used for unit tests. Run the test suite using a composer script:

    composer run test

[PHPStan](https://phpstan.org) is used for static code analysis. Run the test suite using a composer script:

    composer run sa

You can also run all of the above in a single script:

    composer run ci

## Documentation

ImboClient uses [Sphinx](https://www.sphinx-doc.org) for end-user documentation. The documentation files resides in the `docs` directory. To generate the current documentation after checking out your fork, run the following command:

    composer run docs

If the command fails you are most likely missing packages not installable by Composer. Install missing packages and re-run the command to generate docs.

## Reporting issues

Use the [issue tracker on GitHub](https://github.com/imbo/imboclient-php/issues) when reporting an issue.

## Submitting a pull request

If you want to implement a new feature, fork this project and create a feature branch called `feature/my-awesome-feature`, and send a pull request. The feature needs to be fully documented and tested before it will be merged.

If the pull request is a bug fix, remember to file an issue in the issue tracker first, then create a branch called `issue/<issue number>`. One or more test cases to verify the bug is required. When creating specific test cases for issues, please add a `@see` tag to the docblock or the added test case. For instance:

```php
/**
 * @see https://github.com/imbo/imboclient-php/issues/<issue number>
 */
public function testSomething(): void
{
    // ...
}
```

## Conventional commits

Use [conventional commits](https://www.conventionalcommits.org) for all commits. When a pull request is merged it will be squashed. There is a commit-msg Git hook script that you can use to validate your commits locally. Enable the script by running the following command:

    ln -s ../../scripts/conventional-commit-msg.sh .git/hooks/commit-msg
