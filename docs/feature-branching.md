## Git Feature branching documentation

### Before you create your feature branch

Make sure you are on the `develop` branch then enter the command `git pull origin develop`

### Creating a feature branch

You have an issue created in the issue queue.

Enter the following command `git checkout -b feature/123_test_feature_branch`

This will create a branch called `feature/123_test_feature_branch` for you to do your work, for issue #123

To enable everyone to contribute to your work we recommend committing 'little and often'.

### Committing your feature branch

Enter the command `git add -A`

The commit message must have the branch name before your message as below:

`git commit -m "#123 your commit message here"` 

Then push your feature branch

`git push origin feature/123_test_feature_branch`

### Creating a pull request

Visit https://github.com/comicrelief/campaign and you should see `feature/123_test_feature_branch (less than a minute ago)` with a button to `Compare & pull request`. Click this button to open a pull request, enter the details on the pull request in the next screen and click `save`.
