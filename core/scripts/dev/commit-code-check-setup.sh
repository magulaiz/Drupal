#!/bin/bash
# cspell:ignore setaf stylelintrc stylelintignore
#
# This script performs setup steps for the code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.

# Set up variables to make colored output simple. Color output is disabled on
# GitLabCI because it is breaks reporting.
if [[ "$GITLABCI" == "1" ]]; then
  red="\e[31m"
  green="\e[32m"
  reset="\e[0m"
  DRUPAL_VERSION=$(php -r "include 'vendor/autoload.php'; print preg_replace('#\.[0-9]+-dev#', '.x', \Drupal::VERSION);")
  BRANCH=""
  CACHED=0
else
  red=$(tput setaf 1 && tput bold)
  green=$(tput setaf 2)
  reset=$(tput sgr0)
fi

GIT="git"

# Gets list of files to check.
if [[ "$BRANCH" != "" ]]; then
  FILES=$($GIT diff --name-only $BRANCH HEAD);
elif [[ "$CACHED" == "0" ]]; then
  # For GitLabCI patch testing or when running without --cached or --branch,
  # list of all changes in the working directory.
  FILES=$($GIT ls-files --other --modified --exclude-standard --exclude=vendor)
else
  # Check staged files only.
  if $GIT rev-parse --verify HEAD >/dev/null 2>&1
  then
    AGAINST=HEAD
  else
    # Initial commit: diff against an empty tree object
    AGAINST=4b825dc642cb6eb9a060e54bf8d69288fbee4904
  fi
  FILES=$($GIT diff --cached --name-only $AGAINST);
fi

if [[ "$FILES" == "" ]] && [[ "$GITLABCI" == "1" ]]; then
  # If the FILES is empty we might be testing a merge request on GitLabCI. We
  # need to diff against the Drupal branch or tag related to the Drupal version.
  printf "Creating list of files to check by comparing branch to %s\n" "$DRUPAL_VERSION"
  # On GitLabCI there's a merge commit so we can compare to HEAD~1.
  FILES=$($GIT diff --name-only HEAD~1 HEAD);
fi

TOP_LEVEL=$($GIT rev-parse --show-toplevel)

# This variable will be set to one when the file core/phpcs.xml.dist is changed.
PHPCS_XML_DIST_FILE_CHANGED=0

# This variable will be set to one when the files core/phpstan-baseline.neon or
# core/phpstan.neon.dist are changed.
PHPSTAN_DIST_FILE_CHANGED=0

# This variable will be set to one when one of the eslint config file is
# changed:
#  - core/.eslintrc.passing.json
#  - core/.eslintrc.json
#  - core/.eslintrc.jquery.json
ESLINT_CONFIG_PASSING_FILE_CHANGED=0

# This variable will be set to one when the stylelint config file is changed.
# changed:
#  - core/.stylelintignore
#  - core/.stylelintrc.json
STYLELINT_CONFIG_FILE_CHANGED=0

# This variable will be set to one when JavaScript packages files are changed.
# changed:
#  - core/package.json
#  - core/yarn.lock
JAVASCRIPT_PACKAGES_CHANGED=0

# This variable will be set when a Drupal-specific CKEditor 5 plugin has changed
# it is used to make sure the compiled JS is valid.
CKEDITOR5_PLUGINS_CHANGED=0

# This variable will be set to when the dictionary has changed.
CSPELL_DICTIONARY_FILE_CHANGED=0

# Build up a list of absolute file names.
ABS_FILES=
for FILE in $FILES; do
  if [ -f "$TOP_LEVEL/$FILE" ]; then
    ABS_FILES="$ABS_FILES $TOP_LEVEL/$FILE"
  fi

  if [[ $FILE == "core/phpcs.xml.dist" ]]; then
    PHPCS_XML_DIST_FILE_CHANGED=1
  fi

  if [[ $FILE == "core/phpstan-baseline.neon" || $FILE == "core/phpstan.neon.dist" ]]; then
    PHPSTAN_DIST_FILE_CHANGED=1
  fi

  if [[ $FILE == "core/.eslintrc.json" || $FILE == "core/.eslintrc.passing.json" || $FILE == "core/.eslintrc.jquery.json" ]]; then
    ESLINT_CONFIG_PASSING_FILE_CHANGED=1
  fi

  if [[ $FILE == "core/.stylelintignore" || $FILE == "core/.stylelintrc.json" ]]; then
    STYLELINT_CONFIG_FILE_CHANGED=1
  fi

  # If JavaScript packages change, then rerun all JavaScript style checks.
  if [[ $FILE == "core/package.json" || $FILE == "core/yarn.lock" ]]; then
    ESLINT_CONFIG_PASSING_FILE_CHANGED=1
    STYLELINT_CONFIG_FILE_CHANGED=1
    JAVASCRIPT_PACKAGES_CHANGED=1
  fi

  if [[ -f "$TOP_LEVEL/$FILE" ]] && [[ $FILE =~ \.js$ ]] && [[ $FILE =~ ^core/modules/ckeditor5/js/build || $FILE =~ ^core/modules/ckeditor5/js/ckeditor5_plugins ]]; then
    CKEDITOR5_PLUGINS_CHANGED=1
  fi

  if [[ $FILE == "core/misc/cspell/dictionary.txt" || $FILE == "core/misc/cspell/drupal-dictionary.txt" ]]; then
    CSPELL_DICTIONARY_FILE_CHANGED=1
  fi
done
