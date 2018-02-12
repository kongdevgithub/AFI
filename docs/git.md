# Git Instructions

## Dev Server

These instructions are for a development environment.

Add your public key to your github settings.

To clone:

```
git clone git@bitbucket.org:afibranding/console.git
```

To pull:

```
git pull
```

To push:

```
git push
```

## Live Server

These instructions are intended to be used on the live server that has been setup with the deployment key.

Add the servers root users public key to the github project's deployment keys (https://bitbucket.org/afibranding/console/admin/deploy-keys/).

To clone:

```
git clone git@bitbucket.org:afibranding/console.git
```

To setup:

```
git remote add origin git@bitbucket.org:afibranding/console.git
git remote add https https://bitbucket.org/afibranding/console.git
git config branch.master.remote origin
git config branch.master.merge refs/heads/master
```

If origin has already been setup, simply remove it, then repeat the steps above:

```
git remote rm origin
git remote rm https
```

To see the remotes:

```
git remote -v
```

To pull:

```
git pull
```

To push:

```
git push https master
```
