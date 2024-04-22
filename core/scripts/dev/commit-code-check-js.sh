#!/bin/bash
#
# This script performs eslint code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - ESLint checks JavaScript and YAML files.

STATUS=0
# When the eslint config has been changed, then eslint must check all files.
if [[ $ESLINT_CONFIG_PASSING_FILE_CHANGED == "1" ]]; then
  printf "\nRunning lint:core-js-passing on *all* files.\n"
  cd "$TOP_LEVEL/core"
  if [[ "$CI" == "1" ]]; then
    yarn --cwd=./core run -s lint:core-js-passing --format gitlab
  else
    yarn run -s lint:core-js-passing "$TOP_LEVEL/core"
  fi
  STATUS=$?
  cd $TOP_LEVEL
fi

if [[ "$FILES" != "" ]]; then
  printf "\nRunning eslint on changed files.\n"
fi

for FILE in $FILES; do
  ############################################################################
  ### YAML FILES
  ############################################################################
  if [[ -f "$TOP_LEVEL/$FILE" ]] && [[ $FILE =~ \.yml$ ]]; then
    # Test files with ESLint.
    cd "$TOP_LEVEL/core"
    node ./node_modules/eslint/bin/eslint.js --quiet --resolve-plugins-relative-to . "$TOP_LEVEL/$FILE"
    YAMLLINT=$?
    if [ "$YAMLLINT" -ne "0" ]; then
      # If there are failures set the status to a number other than 0.
      STATUS=1
      printf "ESLint: $FILE ${red}failed${reset}\n"
    else
      printf "ESLint: $FILE ${green}passed${reset}\n"
    fi
    cd $TOP_LEVEL
  fi

  ############################################################################
  ### JAVASCRIPT FILES
  ############################################################################
  if [[ -f "$TOP_LEVEL/$FILE" ]] && [[ $FILE =~ \.js$ ]]; then
    cd "$TOP_LEVEL/core"
    # Check the coding standards.
    node ./node_modules/eslint/bin/eslint.js --quiet --config=.eslintrc.passing.json "$TOP_LEVEL/$FILE"
    JSLINT=$?
    if [ "$JSLINT" -ne "0" ]; then
      # No need to write any output the node command will do this for us.
      STATUS=1
      printf "ESLint: $FILE ${red}failed${reset}\n"
    else
      printf "ESLint: $FILE ${green}passed${reset}\n"
    fi
    cd $TOP_LEVEL
  fi
done

if [[ "$STATUS" -ne "0" ]]; then
  printf "\nESLint: ${red}failed${reset}\n"
else
  printf "\nESLint: ${green}passed${reset}\n"
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
