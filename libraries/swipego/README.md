# Swipe PHP Library for WordPress

## Installing the library

#### Step 1. Add the Repository as a Remote

```
git remote add -f subtree-swipego git@bitbucket.org:yiedpozi/swipego.git
```

#### Step 2. Add the Repo as a Subtree
```
git subtree add --prefix libraries/swipego subtree-swipego master --squash
```

#### Step 3. Update the Subtree
```
git fetch subtree-swipego master
git subtree pull --prefix libraries/swipego subtree-swipego master --squash
```