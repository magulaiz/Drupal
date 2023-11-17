#!/bin/bash
# This script deletes test directories.
#
# It is meant to be run before packaging the Drupal application for deployment
# to production environment to minimize its digital footprint (i.e. disk usage).
#
# @see https://unix.stackexchange.com/a/89937

# cSpell:disable

# Delete Drupal test directories.
find . -type d -name tests -exec rm -rf {} +

# Delete testing and demo profiles.
find ./profiles -type d -name 'testing*' -exec rm -rf {} +
find ./profiles -type d -name 'nightwatch_testing' -exec rm -rf {} +
find ./profiles -type d -name 'demo_umami' -exec rm -rf {} +
