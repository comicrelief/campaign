Hi there, and thanks in advance for wanting to contribute to Comic Relief's campaign profile! Here's how we work, please do follow these conventions when opening an issue or submitting a pull request.

### Preparing your development environment

See [Installation guide](docs/install.md) to get your local environment setup. In short, you'll need `Drush`, `Composer` and your typical `MySQL/PHP/Apache` stack or Docker.

Once set up, you should run `composer campaign:build` to get a fully set up environment (read our [testing](docs/testing.md) docs).

### Rules of the Road

Make sure you read through our [Rules of the road](docs/rules_of_the_road.md) before making any contribution.

### Submitting Issues

We do welcome issues, but remember that an issue without a PR will not necessarily be looked at.

Issues should always have enough information for anyone to recreate the problem and should ideally contain a link to an environment in which the problem can be seen. 

### Submitting Pull Requests

When submitting a Pull Request, always follow our [feature branching model](docs/feature-branching.md) and create your Pull Request on GitHub following our template.

We use GitHub labels to indicate the status of the PR and we use GitHub milestones to organize PRs for upcoming releases. Currently, both labels and milestones are for internal Comic Relief organisation only and we will consider adopting a different labeling depending on demand.

### Features wishlist

We're looking at extending our codebase the include the following features (in no particular order) and are welcoming Pull Requests:

* Use of Webpack instead of Grunt
* Remove Drush make support in favor of Composer
* More behat tests
* A Yeoman scaffolder to generate the codebase for new sites using the Campaign profile
