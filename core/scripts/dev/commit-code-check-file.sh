#!/bin/bash
#
# This script performs file checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - File modes.
# - No changes to core/node_modules directory.

if [[ "$FILES" != "" ]]; then
  printf "\nRunning file checks on changed files.\n"
fi

STATUS=0
for FILE in $FILES; do
  # Print a line to separate spellcheck output from per file output.
  printf "Checking %s\n" "$FILE"

  # Ensure the file still exists (i.e. is not being deleted).
  if [ -a $FILE ]; then
    if [ ${FILE: -3} != ".sh" ]; then
      if [ -x $FILE ]; then
        printf "${red}check failed:${reset} file $FILE should not be executable\n"
        STATUS=1
      fi
    fi
  fi

  # Don't commit changes to vendor.
  if [[ "$FILE" =~ ^vendor/ ]]; then
    printf "${red}check failed:${reset} file in vendor directory being committed ($FILE)\n"
    STATUS=1
  fi

  # Don't commit changes to core/node_modules.
  if [[ "$FILE" =~ ^core/node_modules/ ]]; then
    printf "${red}check failed:${reset} file in core/node_modules directory being committed ($FILE)\n"
    STATUS=1
  fi
done

if [ "$STATUS" -ne "0" ]; then
  printf "\nFile checks: ${red}failed${reset}\n"
else
  printf "\nFile checks: ${green}passed${reset}\n"
fi

# Add a separator line to make the output easier to read.
printf "\n"
printf -- '-%.0s' {1..100}
printf "\n"

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
