#!/bin/bash
#
# This script performs code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - Spell checking.
# - File modes.
# - No changes to core/node_modules directory.
# - PHPCS checks PHP and YAML files.
# - PHPStan checks PHP files.
# - ESLint checks JavaScript and YAML files.
# - Stylelint checks CSS files.
# - Checks .pcss.css and .css files are equivalent.

CACHED=0
BRANCH=""

while test $# -gt 0; do
  case "$1" in
    -h|--help)
      echo "Drupal code quality checks"
      echo " "
      echo "options:"
      echo "-h, --help                show brief help"
      echo "--branch BRANCH           creates list of files to check by comparing against a branch"
      echo "--cached                  checks staged files"
      echo " "
      echo "Example usage: sh ./core/scripts/dev/commit-code-check.sh --branch 9.2.x"
      exit 0
      ;;
    --branch)
      BRANCH="$2"
      if [[ "$BRANCH" == "" ]]; then
        printf "The --branch option requires a value. For example: --branch 9.2.x\n"
        exit;
      fi
      shift 2
      ;;
    --cached)
      CACHED=1
      shift
      ;;
    *)
      break
      ;;
  esac
done

# Set up variables to make colored output simple. Color output is disabled on
# DrupalCI because it is breaks reporting.
# @todo https://www.drupal.org/project/drupalci_testbot/issues/3181869
if [[ "$DRUPALCI" == "1" ]]; then
  red=""
  green=""
  reset=""
  DRUPAL_VERSION=$(php -r "include 'vendor/autoload.php'; print preg_replace('#\.[0-9]+-dev#', '.x', \Drupal::VERSION);")
  GIT="sudo -u www-data git"
else
  red=$(tput setaf 1 && tput bold)
  green=$(tput setaf 2)
  reset=$(tput sgr0)
  GIT="git"
fi

# Gets list of files to check.
if [[ "$BRANCH" != "" ]]; then
  FILES=$($GIT diff --name-only $BRANCH HEAD);
elif [[ "$CACHED" == "0" ]]; then
  # For DrupalCI patch testing or when running without --cached or --branch,
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

if [[ "$FILES" == "" ]] && [[ "$DRUPALCI" == "1" ]]; then
  # If the FILES is empty we might be testing a merge request on DrupalCI. We
  # need to diff against the Drupal branch or tag related to the Drupal version.
  printf "Creating list of files to check by comparing branch to %s\n" "$DRUPAL_VERSION"
  # On DrupalCI there's a merge commit so we can compare to HEAD~1.
  FILES=$($GIT diff --name-only HEAD~1 HEAD);
fi

TOP_LEVEL=$($GIT rev-parse --show-toplevel)

# This variable will be set to one when the file core/phpcs.xml.dist is changed.
PHPCS_XML_DIST_FILE_CHANGED=0

# This variable will be set to one when the files core/.phpstan-baseline.php or
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
    PHPCS_XML_DIST_FILE_CHANGED=1;
  fi;

  if [[ $FILE == "core/.phpstan-baseline.php" || $FILE == "core/phpstan.neon.dist" ]]; then
    PHPSTAN_DIST_FILE_CHANGED=1;
  fi;

  if [[ $FILE == "core/.eslintrc.json" || $FILE == "core/.eslintrc.passing.json" || $FILE == "core/.eslintrc.jquery.json" ]]; then
    ESLINT_CONFIG_PASSING_FILE_CHANGED=1;
  fi;

  if [[ $FILE == "core/.stylelintignore" || $FILE == "core/.stylelintrc.json" ]]; then
    STYLELINT_CONFIG_FILE_CHANGED=1;
  fi;

  # If JavaScript packages change, then rerun all JavaScript style checks.
  if [[ $FILE == "core/package.json" || $FILE == "core/yarn.lock" ]]; then
    ESLINT_CONFIG_PASSING_FILE_CHANGED=1;
    STYLELINT_CONFIG_FILE_CHANGED=1;
    JAVASCRIPT_PACKAGES_CHANGED=1;
  fi;

  if [[ -f "$TOP_LEVEL/$FILE" ]] && [[ $FILE =~ \.js$ ]] && [[ $FILE =~ ^core/modules/ckeditor5/js/build || $FILE =~ ^core/modules/ckeditor5/js/ckeditor5_plugins ]]; then
    CKEDITOR5_PLUGINS_CHANGED=1;
  fi;

  if [[ $FILE == "core/misc/cspell/dictionary.txt" || $FILE == "core/misc/cspell/drupal-dictionary.txt" ]]; then
    CSPELL_DICTIONARY_FILE_CHANGED=1;
  fi
done

# Exit early if there are no files.
if [[ "$ABS_FILES" == "" ]]; then
  printf "There are no files to check. If you have staged a commit use the --cached option.\n"
  exit;
fi;

# This script assumes that composer install and yarn install have already been
# run and all dependencies are updated.
DEPENDENCIES_NEED_INSTALLING=0
# Ensure PHP development dependencies are installed.
# @todo https://github.com/composer/composer/issues/4497 Improve this to
#  determine if dependencies in the lock file match the installed versions.
#  Using composer install --dry-run is not valid because it would depend on
#  user-facing strings in Composer.
if ! [[ -f 'vendor/bin/phpcs' ]]; then
  printf "Drupal's PHP development dependencies are not installed. Run 'composer install' from the root directory.\n"
  DEPENDENCIES_NEED_INSTALLING=1;
fi

# Ensure JavaScript development dependencies are installed.
yarn --version
yarn >/dev/null

# Setup variables.
source core/scripts/dev/commit-code-check-setup.sh

# Exit early if there are no files.
if [[ "$ABS_FILES" == "" ]]; then
  printf "There are no files to check. If you have staged a commit use the --cached option.\n"
  exit
fi

FINAL_STATUS=0
# Check spelling.
if source core/scripts/dev/commit-code-check-cspell.sh; then
  FINAL_STATUS=1
fi

# Run PHPStan.
if source core/scripts/dev/commit-code-check-phpstan.sh; then
  FINAL_STATUS=1
fi

# Run PHPCS.
if ! source core/scripts/dev/commit-code-check-phpcs.sh; then
  FINAL_STATUS=1
fi

# Run eslint.
if source core/scripts/dev/commit-code-check-eslint.sh; then
  FINAL_STATUS=1
fi

# Run stylelint.
if source core/scripts/dev/commit-code-check-stylelint.sh; then
  FINAL_STATUS=1
fi

# Compile check.
if source core/scripts/dev/commit-code-check-compile.sh; then
  FINAL_STATUS=1
fi

# File check.
if source core/scripts/dev/commit-code-check-file.sh; then
  FINAL_STATUS=1
fi

exit $FINAL_STATUS
