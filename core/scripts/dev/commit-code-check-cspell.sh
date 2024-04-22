#!/bin/bash
#
# This script performs spelling code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - Spell checking.

# Ensure JavaScript development dependencies are installed.
yarn --version
yarn >/dev/null

cd "$TOP_LEVEL/core"

STATUS=0
# Check all files for spelling in one go for better performance.
if [[ $CSPELL_DICTIONARY_FILE_CHANGED == "1" ]] ; then
  printf "\nRunning spellcheck on *all* files.\n"
  yarn run -s spellcheck:core --no-must-find-files --no-progress
else
  # Check all files for spelling in one go for better performance. We pipe the
  # list files in so we obey the globs set on the spellcheck:core command in
  # core/package.json.
  printf "\nRunning spellcheck on changed files.\n"
  echo "${ABS_FILES}" | tr ' ' '\n' | yarn run -s spellcheck:core --no-progress --no-must-find-files --file-list stdin
fi

if [ "$?" -ne "0" ]; then
  STATUS=1
  printf "\nCSpell: ${red}failed${reset}\n"
else
  printf "\nCSpell: ${green}passed${reset}\n"
fi

# Add a separator line to make the output easier to read.
printf "\n"
printf -- '-%.0s' {1..100}
printf "\n"

cd "$TOP_LEVEL"

if [[ "$STATUS" == "1" ]] && [[ "$CI" == "1" ]]; then
  printf "${red}Drupal code quality checks failed.${reset}\n"
  printf "To reproduce this output locally:\n"
  printf "* Apply the change as a patch\n"
  printf "* Run this command locally: sh ./core/scripts/dev/commit-code-check.sh\n"
  printf "OR:\n"
  printf "* From the merge request branch\n"
  printf "* Run this command locally: sh ./core/scripts/dev/commit-code-check.sh --branch %s\n" "$DRUPAL_VERSION"
fi

return "$STATUS"
