#!/bin/bash
#
# This script performs phpcs code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - PHPCS checks PHP and YAML files.

# @todo. If an input argument is supplied then enable CI.
if [ $# -eq 1 ]; then
  GITLABCI=1
fi

if [[ "$GITLABCI" == "1" ]]; then
  source core/scripts/dev/commit-code-check-setup.sh
fi

# Run PHPCS on all files on GitLabCI or when phpcs files are changed.
PHPCS=0
STATUS=0
if [[ $PHPCS_XML_DIST_FILE_CHANGED == "1" ]] || [[ "$GITLABCI" == "1" ]]; then
  # Test all files with phpcs rules.
  printf "\nRunning  PHP_CodeSniffer on *all* files.\n"
  if [[ "$GITLABCI" == "1" ]]; then
    composer phpcs -- --report-full --report-summary --report-\\Micheh\\PhpCodeSniffer\\Report\\Gitlab=phpcs-quality-report.json
  else
    vendor/bin/phpcs -ps --parallel=$(nproc) --standard="$TOP_LEVEL/core/phpcs.xml.dist"
  fi
  PHPCS=$?
else
  if [[ "$FILES" != "" ]]; then
    printf "\nRunning PHP_CodeSniffer on changed files.\n"
  fi

  for FILE in $FILES; do
    ############################################################################
    ### PHP AND YAML FILES
    ############################################################################
    if [[ -f "$TOP_LEVEL/$FILE" ]] && [[ $FILE =~ \.(inc|install|module|php|profile|test|theme|yml)$ ]] && [[ $PHPCS_XML_DIST_FILE_CHANGED == "0" ]] && [[ "$GITLABCI" == "0" ]]; then
      # Test files with phpcs rules.
      vendor/bin/phpcs "$TOP_LEVEL/$FILE" --standard="$TOP_LEVEL/core/phpcs.xml.dist"
      PHPCS=$?
      if [ "$PHPCS" -ne "0" ]; then
        # If there are failures set the status to a number other than 0.
        STATUS=1
        printf "$FILE ${red}failed${reset}\n"
      else
        printf "$FILE ${green}passed${reset}\n"
      fi
    fi
  done
fi

if [ "$PHPCS" -ne "0" ] || [ "$STATUS" -ne "0" ]; then
  printf "\nPHPCS: ${red}failed${reset}\n"
else
  printf "\nPHPCS: ${green}passed${reset}\n"
fi

# Add a separator line to make the output easier to read.
printf "\n"
printf -- '-%.0s' {1..100}
printf "\n"

if [[ "$STATUS" == "1" ]] && [[ "$GITLABCI" == "1" ]]; then
  printf "${red}Drupal code quality checks failed.${reset}\n"
  printf "To reproduce this output locally:\n"
  printf "* Apply the change as a patch\n"
  printf "* Run this command locally: sh ./core/scripts/dev/commit-code-check.sh\n"
  printf "OR:\n"
  printf "* From the merge request branch\n"
  printf "* Run this command locally: sh ./core/scripts/dev/commit-code-check.sh --branch %s\n" "$DRUPAL_VERSION"
fi

return "$STATUS"
