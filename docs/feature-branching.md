## Git Feature branching documentation

### Before you create your feature branch

Make sure you are on the `develop` branch then enter the command `git pull`

### Creating a feature branch

You have a ticket of PLAT-123 test feature branch

Enter the following command `git checkout -b feature/PLAT-123_test_feature_branch`

This will create a branch called `feature/PLAT-123_test_feature_branch` for you to do your work.

To enable poeple to contribute to work we recommend committing 'little and often'.

### Committing your feature branch

Enter the command `git add-A`

The commit message must have the branch name before your message as below:

`git commit -m "PLAT-123_test_feature_branch your message here"` 

Then push your feature branch

`git push origin feature/PLAT-123_test_feature_branch`