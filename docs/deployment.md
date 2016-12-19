### Release Timescales
1. __Friday ~5pm__: Release/Pull Request cut off.
2. __Monday all day__: Last chance for code to be included by developers/bug fixes only. Build release notes.
3. __Tuesday AM__: First release build, and deployment to staging. 
4. __Wednesday AM__: Deploy to production. Tagging of branches, updating of Release notes. 

### Planning and communicating a release

- First of all, start by [reviewing the milestone marking the new release](https://github.com/comicrelief/campaign/milestone/14?closed=1) and make sure you understand the PRs that will be part of your release + make a mental note of any manual steps to be aware of. Help reviewing any open PRs that are tagged as "ready to merge".
- After you prepared a release and deployed to staging, you need to make sure that JIRA issues are correctly tagged. You can [use this JIRA view to do so](http://jira.comicrelief.com/secure/RapidBoard.jspa?rapidView=125&selectedIssue=PLAT-562&quickFilter=517)
- Announce the release log in the relevant channels. #QA for staging releases, #RND17 for production releases. @zach to provide the exact wording for this.

### Building a Release

- Git clone both the campaign and rnd17 repositories, and checkout the develop branch. Configure each local copy as per instructions in the docs folder.
- Create a new branch on each repository called release_X.X and push to github, e.g. for version 1.9 execute the following in each repository:
  ```
  git branch release_1.9
  git checkout release_1.9
  git push --set-upstream origin release_1.9
  git push
  ```
- In the RND17 repository, edit the file 'profiles/rnd17/rnd17.make.yml' and change the __branch:__ section to __branch:__ release_X.X.
- Still in the RND17 repository, execute `phing make-cr` and wait for it to finish.
- Review all the changes with either `git status` or `git diff`.
- Add any files to git that are new, and then commit and push the changes. 
- Execute `phing update-cr` to get the updated configuration, review the changes, add, commit, and push the new files to the repo.
- Execute `phing deploy:dryrun` to simulate a deployment. 
- In Slack, execute `/build rnd17 [branch (e.g feature_X.X)]` in the '#craft-logs' channel. Within a few minutes you should see something similar to:
    "A build artefact for rnd17 has been created"
- At this point its important to open a pull request from your release branch into the master branch, this way hotfixes that are made during the QA/ staging period, are merged back into the master branch (the merege is done once the release is signed off, just prior to tagging `master`)
- We'll also need to create another pull reququest from master into develop, this again ensures any work done for the realese is makes it way back into our primary development branch.
- Done! You now have a release called release_X.X that should be ready to go to any environment.

### Deploying a Release

There are numerous environments that a release can be deployed to, QA(1-3), Staging, and Production. To deploy a release you
need to execute the following command in the '#craft-logs' Slack channel: `/deploy [product] [environment] [release_X.X]`, where [product] is rnd17, and [environment] is one of qa1, qa2, qa3, staging, or production.
When deploying to staging or a QA environment it is good practice to both sync the database and files.
To do this on staging, execute the following 2 Slack commands. `/sqlsync rnd17 staging production` and `/filesync rnd17 staging production`. 
To do this on QA(X), execute the following 2 Slack commands `/sqlsync rnd17 QA(x) staging` and `/filesync rnd17 QA(x) staging`.

### Drafting Releases
 
Release are to happen every Monday, end of play.
- Head over to Github and draft a [new release](https://github.com/comicrelief/campaign/releases/new) 
- [Review JIRA](http://jira.comicrelief.com/browse/PLAT) and make sure ALL issues have been tagged with the new release, for example 1.2
- Add all pull requests and their associated JIRA tickets to the release notes, for example [1.2](https://github.com/comicrelief/campaign/releases/tag/1.2)
- Proceed to the Product release process documentation below to complete the weekly release.

### Deploying to Staging

Before a site instance deployment can happen, a Campaign profile release must be drafted.
- Create a release branch, for example `release_1.2` (do not use slashes for this branch as platform.sh does not like this)
- (in the RND17 repository) Locate the `RND17.make.yml` file in `/profiles/rnd17/`, update the branch version to the Campaign profile tag you created previously and commit.
- Run `phing make-cr`, review the changes, add and commit the new files.
- Run `phing update-cr` to get the updated configuration, review the changes, add and commit the new files to the repo.
- Announce the start of the process on the #deployments and/or #rnd17 Slack channel. A staging deployment often needs to get a database dump and a files directory from production to be effective.
- Create a build artifact for your release branch using the following command:
`/build rnd17 [branch (e.g feature/1.2)]`
- Sync production database to staging using the following command: 
/sqlsync rnd17 production staging
- Enable `stage_file_proxy` to have files available on staging. Alternatively, sync production files to staging using the following command.
`/filesync rnd17 production staging`
- Deploy the build artifact to the staging environment using the following command: 
`/deploy rnd17 staging [build artefact id]`
Note: The build artifact id will be output to the #craftlogs Slack channel once its built. 
- Login to the site and do [general smoke tests](http://softwaretestingfundamentals.com/smoke-testing/). Grab another dev that worked on most tickets in this release to help you out with the smoke tests. This process should not take more than 10-15 minutes if no major problems are found.
- Announce to the #deployments Slack channel so the release can be QA'd, using @QA will notify all QA's.

### Deploying to Production
Assuming the release branch that was deployed to staging in the previous steps has passed QA and all feed back has been prioritised and actioned, we are ready to move to production.
- Create the new release tag via [Github](https://github.com/comicrelief/rnd17/releases/new) making sure to note down the [Campaign profile](https://github.com/comicrelief/campaign/releases) version along with any important information related to this release, for example [1.2](https://github.com/comicrelief/rnd17/releases/tag/1.2)
- Create a build artifact for your tag using the following command:
`/build rnd17 [tag (e.g 1.2)]`
- Wait for the build to complete and deploy the build artifact to the production environment using the following command: 
`/deploy rnd17 production [build artefact id]`
Note: The build artifact id will be output to the #craftlogs Slack channel once its built. 
- Once the deployment is completed and the success message appears in #deployments, login to the site and do general smoke tests
- Announce to the #deployments Slack channel so the release can be sanity checked by @QA's
- Make sure the Jira release date has been filled in, both on the [RND](https://jira.comicrelief.com/browse/RND) project and [Platform](https://jira.comicrelief.com/browse/PLAT)
