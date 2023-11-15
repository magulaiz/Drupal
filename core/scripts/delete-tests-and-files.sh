#!/bin/bash
# This script deletes unnecessary directories and files.
#
# It is meant to be run before packaging the Drupal application for deplpoyment
# to production environment to minimize its digital footprint (i.e. disk usage).
#
# @see https://unix.stackexchange.com/a/89937

# cSpell:disable

# Delete Drupal test directories.
find . -type d -name tests -exec rm -rf {} +
# Delete README files.
find . -type f -name 'README*' -delete
find . -type f -name 'CHANGELOG*' -delete
# Delete patch records.
find . -type f -name 'PATCHES.txt' -delete
# Delete markdown files except licenses.
find . -type f -name "*.md" -not -name "LICENSE*" -delete
# Delete txt files except humans and licenses.
find . -type f -name "*.txt" -not -name "salt*" -not -name "humans*" -not -name "LICENSE*" -not -name "COPYRIGHT*" -delete
# Delete testing and demo profiles.
find ./profiles -type d -name 'testing*' -exec rm -rf {} +
find ./profiles -type d -name 'nightwatch_testing' -exec rm -rf {} +
find ./profiles -type d -name 'demo_umami' -exec rm -rf {} +
