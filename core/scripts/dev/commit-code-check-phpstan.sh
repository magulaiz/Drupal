#!/bin/bash
#
# This script performs PHPStan code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - PHPStan checks PHP files.

cd "$TOP_LEVEL"
STATUS=0
# Run PHPStan on all files on GitLabCI or when phpstan files are changed.
# APCu is disabled to ensure that the composer classmap is not corrupted.
if [[ $PHPSTAN_DIST_FILE_CHANGED == "1" ]] || [[ "$CI" == "1" ]]; then
  printf "\nRunning PHPStan on *all* files.\n"
  if [[ "$CI" == "1" ]]; then
    # Rely on PHPStan caching to execute analysis multiple times without performance drawback.
    # Output a copy in junit.
    php vendor/bin/phpstan analyze --configuration=./core/phpstan.neon.dist --error-format=gitlab > phpstan-quality-report.json || EXIT_CODE=$?
    php vendor/bin/phpstan analyze --configuration=./core/phpstan.neon.dist --no-progress --error-format=junit > phpstan-junit.xml || true
    if [ -n "$EXIT_CODE" ]; then
      # Output a copy in plain text for human logs.
      php vendor/bin/phpstan analyze --configuration=./core/phpstan.neon.dist --no-progress || true
      # Generate a new baseline.
      echo "Generating an PHPStan baseline file (available as job artifact)."
      php vendor/bin/phpstan analyze --configuration=./core/phpstan.neon.dist --no-progress --generate-baseline=./core/phpstan-baseline.neon || true
    fi
  else
    php -d apc.enabled=0 -d apc.enable_cli=0 vendor/bin/phpstan analyze --no-progress --configuration="$TOP_LEVEL/core/phpstan.neon.dist"
  fi
else
  # Only run PHPStan on changed files locally.
  printf "\nRunning PHPStan on changed files.\n"
  php -d apc.enabled=0 -d apc.enable_cli=0 vendor/bin/phpstan analyze --no-progress --configuration="$TOP_LEVEL/core/phpstan-partial.neon" $ABS_FILES
fi

if [[ "$?" -ne "0" ]] || [[ "$EXIT_CODE" -ne "0" ]]; then
  STATUS=1
  printf "\nPHPStan: ${red}failed${reset}\n"
else
  printf "\nPHPStan: ${green}passed${reset}\n"
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
