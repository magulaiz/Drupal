#!/bin/bash
#
# This script creates the sub-tree split when an extension is removed from core.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#

# Set default values.
# The commit hash when most extensions were moved to core/extension_type.
MODULE_COMMIT_HASH=06fb770bd340e5a18555e0da55a4dd6ba22f76ba
THEME_COMMIT_HASH=06fb770bd340e5a18555e0da55a4dd6ba22f76ba
# Working directory.
WORKING_DIR=/tmp/split

#
# Helpful messages.
#
echo -e "\nCreate a sub-tree split for extension removal."
echo -e "This script assumes the extension was moved to core/modules or core/themes in issue #22336 in Oct 2011,"
echo -e "If not you can find the commit using  git log -- modules/MODULE_NAME and save the commit hash."
echo -e "You will be prompted to change this commit, if needed.".

# Get user input for the extension name and type.
echo -e "\nEnter the extension name:"
read EXTENSION

TYPE=modules
COMMIT_HASH=$MODULE_COMMIT_HASH
if [ -d "./core/themes/$EXTENSION" ]
then
  TYPE=themes
  COMMIT_HASH=$THEME_COMMIT_HASH
  if [ -d "./core/modules/$EXTENSION" ]
  then
    echo -e "Is this a module (Y/n)?:"
    read ANSWER
    if [ -z "$ANSWER" ]
    then
      TYPE=modules
      COMMIT_HASH=$MODULE_COMMIT_HASH
    fi
  fi
fi

# Ask for a user-specified hash.
echo -e "Enter a commit hash, if different than the default:"
read HASH
if [ -n "$HASH" ]
then
  COMMIT_HASH=$HASH
fi

# Start the sub-tree split process.
echo "Executing a sub-tree split for $TYPE/$EXTENSION."

# 1. Create a working directory and clone Drupal.
mkdir "$WORKING_DIR"
cd "$WORKING_DIR" || exit
if ! git clone https://git.drupalcode.org/project/drupal.git
then
  printf "\ngit clone failed."
  exit 1
fi

cd drupal || exit

# 2. Split core/extension_type/extension.
if ! git subtree split -P core/"$TYPE"/"$EXTENSION" -b "$EXTENSION"
then
  printf "\First subtree split failed."
  exit 1
fi

# 3. Split core/extension_type/extension and merge it with the history for
#    core/core/extension_type/extension.
#    - Find the commit where the extension was moved to
#      core/extension_type/extension.
#    - If there is no output, SKIP TO STEP 4. Otherwise, note the SHA of the
#      most recent commit.
git tag core-move "$COMMIT_HASH"
git checkout core-move
if ! git subtree split -P core/"$TYPE"/"$EXTENSION" -b "$EXTENSION"-pre-core
then
  printf "\Second sub-tree split failed"
  exit 1
fi
git checkout "$EXTENSION"

# Rebase the post-core-move branch onto the pre-core move. Note
# that we don't want the actual commit that did the move: it has
# no effect in our split since the module is top-level in both the
# new branches.
if ! git rebase --onto "$EXTENSION"-pre-core core-move
then
  printf "\Rebase failed"
  exit 1
fi

# 4.Create the new repository from the sub-tree split.
git checkout "$EXTENSION"
# Create the repository in a new directory.
cd ..
mkdir "$EXTENSION"
cd "$EXTENSION" || exit
git init
git pull ../drupal "$EXTENSION"
git remote add origin https://git.drupalcode.org/project/"$EXTENSION".git

echo -e "The split is at $WORKING_DIR/$EXTENSION."


