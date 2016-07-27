### Drafting Releases
 
Release are to happen every Monday, end of play.
- Head over to Github and draft a [new release](https://github.com/comicrelief/campaign/releases/new) 
- [Review JIRA](http://jira.comicrelief.com/browse/PLAT) and make sure ALL issues have been tagged with the new release, for example 1.2
- Add all pull requests and their associated JIRA tickets to the release notes, for example [1.2](https://github.com/comicrelief/campaign/releases/tag/1.2)
- Proceed to the Product release process documentation below to complete the weekly release.

### Deploying to Staging
Before a site instance deployment can happen, a Campaign profile release must be drafted.
- Create a release branch, for example `feature/1.2`
- Locate the `RND17.make.yml` file in `/profiles/rnd17/`, update the branch version to the Campaign profile tag you created previously and commit.
- Run `phing make-cr`, review the changes, add and commit the new files.
- Run `phing update-cr` to get the updated configuration, review the changes, add and commit the new files to the repo.
- Announce the start of the process on the #deployments and/or #rnd17 Slack channel. A staging deployment often needs to get a database dump and a files directory from production to be effective.
- Create a build artifact for your release branch using the following command:
`/build rnd17 [branch (e.g feature/1.2)]`
- Sync production database to staging using the following command: 
/sqlsync rnd17 production staging
- Sync production files to staging using the following command: 
`/filesync rnd17 production staging`
- Deploy the build artifact to the staging environment using the following command: 
`/deploy rnd17 staging [build artefact id]`
Note: The build artifact id will be output to the #craftlogs Slack channel once its built. 
- Login to the site and do general smoke tests
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
