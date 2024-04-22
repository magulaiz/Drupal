#!/bin/bash
#
# This script performs stylelint code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - ESLint checks JavaScript and YAML files.
# - Stylelint checks CSS files.
# - Checks .pcss.css and .css files are equivalent.

# When the stylelint config has been changed, then stylelint must check all files.
CORRECT=0
if [[ $STYLELINT_CONFIG_FILE_CHANGED == "1" ]]; then
  printf "\nRunning stylelint on *all* files.\n"
  cd "$TOP_LEVEL/core"
  if [[ "$CI" == "1" ]]; then
    yarn run --cwd=./core lint:css --color --custom-formatter=node_modules/stylelint-formatter-gitlab
  else
    yarn run -s lint:css
  fi
  CORRECT=$?
  cd $TOP_LEVEL
fi

if [[ "$FILES" != "" ]]; then
  printf "\nRunning stylelint on changed files.\n"
fi

STATUS=0;
for FILE in $FILES; do
  if [[ -f "$TOP_LEVEL/$FILE" ]] && [[ $FILE =~ \.css$ ]] && [[ -f "core/node_modules/.bin/stylelint" ]]; then
    BASENAME=${FILE%.css}
    # We only need to use stylelint on the .pcss.css file. So if this CSS file
    # has a corresponding .pcss don't do stylelint.
    if [[ $FILE =~ \.pcss\.css$ ]] || [[ ! -f "$TOP_LEVEL/$BASENAME.pcss.css" ]]; then
      cd "$TOP_LEVEL/core"
      node_modules/.bin/stylelint --allow-empty-input "$TOP_LEVEL/$FILE"
      if [ "$?" -ne "0" ]; then
        STATUS=1
        printf "STYLELint: $FILE ${red}failed${reset}\n"
      else
        printf "STYLELint: $FILE ${green}passed${reset}\n"
      fi
      cd $TOP_LEVEL
    fi
  fi
done

if [ "$CORRECT" -ne "0" ] || [ "$STATUS" -ne "0" ]; then
  printf "\nStylelint: ${red}failed${reset}\n"
else
  printf "\nStyleLint: ${green}passed${reset}\n"
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
