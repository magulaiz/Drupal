#!/bin/bash
# cspell:ignore CORRECTCSS HASPOSTCSS
#
# This script performs code quality checks.
#
# @internal
#   This script is not covered by Drupal core's backwards compatibility promise.
#   It exists only for core development purposes.
#
# The script makes the following checks:
# - Checks .pcss.css and .css files are equivalent.

STATUS=0;
# When a Drupal-specific CKEditor 5 plugin changed ensure that it is compiled
# properly. Only check on GitLabCI, since we're concerned about the build being
# run with the expected package versions and making sure the result of the build
# is in sync and conform to expectations.
if [[ "$CI" == "1" ]] && [[ $CKEDITOR5_PLUGINS_CHANGED == "1" ]]; then
  cd "$TOP_LEVEL/core"
  yarn run -s check:ckeditor5
  if [ "$?" -ne "0" ]; then
    # If there are failures set the status to a number other than 0.
    STATUS=1
    printf "\nDrupal-specific CKEditor 5 plugins: ${red}failed${reset}\n"
  else
    printf "\nDrupal-specific CKEditor 5 plugins: ${green}passed${reset}\n"
  fi
  cd $TOP_LEVEL
  # Add a separator line to make the output easier to read.
  printf "\n"
  printf -- '-%.0s' {1..100}
  printf "\n"
fi

# When JavaScript packages change, then rerun all JavaScript style checks.
if [[ "$JAVASCRIPT_PACKAGES_CHANGED" == "1" ]]; then
  cd "$TOP_LEVEL/core"
  yarn run build:css --check
  CORRECTCSS=$?
  if [ "$CORRECTCSS" -ne "0" ]; then
    STATUS=1
    printf "\n${red}ERROR: The compiled CSS from the PCSS files"
    printf "\n       does not match the current CSS files. Some added"
    printf "\n       or updated JavaScript package made changes."
    printf "\n       Recompile the CSS with: yarn run build:css${reset}\n\n"
  fi
  # Add a separator line to make the output easier to read.
  printf "\n"
  printf -- '-%.0s' {1..100}
  printf "\n"
fi

if [[ "$FILES" != "" ]]; then
  printf "\nRunning compile checks on changed files.\n"
fi

for FILE in $FILES; do
  ############################################################################
  ### CSS FILES
  ############################################################################
  if [[ -f "$TOP_LEVEL/$FILE" ]] && [[ $FILE =~ \.css$ ]]; then
    # Work out the root name of the CSS so we can ensure that the PostCSS
    # version has been compiled correctly.
    if [[ $FILE =~ \.pcss\.css$ ]]; then
      BASENAME=${FILE%.pcss.css}
      COMPILE_CHECK=1
    else
      BASENAME=${FILE%.css}
      # We only need to compile check if the .pcss.css file is not also
      # changing. This is because the compile check will occur for the
      # .pcss.css file. This might occur if the compiled stylesheets have
      # changed.
      contains_element "$BASENAME.pcss.css" "${FILES[@]}"
      HASPOSTCSS=$?
      if [ "$HASPOSTCSS" -ne "0" ]; then
        COMPILE_CHECK=1
      else
        COMPILE_CHECK=0
      fi
    fi
    # PostCSS
    if [[ "$COMPILE_CHECK" == "1" ]] && [[ -f "$TOP_LEVEL/$BASENAME.pcss.css" ]]; then
      cd "$TOP_LEVEL/core"
      yarn run build:css --check --file "$TOP_LEVEL/$BASENAME.pcss.css"
      CORRECTCSS=$?
      if [ "$CORRECTCSS" -ne "0" ]; then
        # If the CSS does not match the PCSS, set the status to a number other
        # than 0.
        STATUS=1
        printf "\n${red}ERROR: The compiled CSS from"
        printf "\n       ${BASENAME}.pcss.css"
        printf "\n       does not match its CSS file. Recompile the CSS with:"
        printf "\n       yarn run build:css${reset}\n\n"
      else
        printf "%s ${green}passed${reset}\n" "$FILE"
      fi
      cd "$TOP_LEVEL"
    fi
  fi
done

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
