## Git Feature branching documentation

### Before you create your feature branch

Make sure you are on the `develop` branch then enter the command `git pull origin develop`

### Creating a feature branch

You have a ticket of PLAT-123 test feature branch

Enter the following command `git checkout -b feature/PLAT-123_test_feature_branch`

This will create a branch called `feature/PLAT-123_test_feature_branch` for you to do your work.

To enable poeple to contribute to work we recommend committing 'little and often'.

### Committing your feature branch

Enter the command `git add -A`

The commit message must have the branch name before your message as below:

`git commit -m "PLAT-123_test_feature_branch your message here"` 

Then push your feature branch

`git push origin feature/PLAT-123_test_feature_branch`

### Creating a pull request

Visit https://github.com/comicrelief/campaign and you should see `feature/PLAT-123_test_feature_branch (less than a minute ago)` with a button to `Compare & pull request`. Click this button to open a pull request, enter the details on the pull request in the next screen and click `save`.