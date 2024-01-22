#!/bin/bash

git fetch -vn --depth=3 origin "+${CI_MERGE_REQUEST_DIFF_BASE_SHA}:${CI_MERGE_REQUEST_DIFF_BASE_SHA}"
git log -1 ${CI_MERGE_REQUEST_DIFF_BASE_SHA}

echo "ℹ️ Changes from ${CI_MERGE_REQUEST_DIFF_BASE_SHA}"
git diff ${CI_MERGE_REQUEST_DIFF_BASE_SHA} --name-only

echo "1️⃣ Reverting non test changes"
if [[ $(git diff ${CI_MERGE_REQUEST_DIFF_BASE_SHA} --diff-filter=DM --name-only|grep -Ev "*/tests/*"|grep -v .gitlab-ci|grep -v scripts/run-tests.sh) ]]; then
git diff ${CI_MERGE_REQUEST_DIFF_BASE_SHA} --diff-filter=DM --name-only|grep -Ev "*/tests/*"|grep -v .gitlab-ci|grep -v scripts/run-tests.sh|while read file;do
  echo "↩️ Reverting $file";
  git checkout ${CI_MERGE_REQUEST_DIFF_BASE_SHA} -- $file;
done
fi
if [[ $(git diff ${CI_MERGE_REQUEST_DIFF_BASE_SHA} --diff-filter=A --name-only|grep -Ev "*/tests/*"|grep -v .gitlab-ci|grep -v scripts/run-tests.sh) ]]; then
git diff ${CI_MERGE_REQUEST_DIFF_BASE_SHA} --diff-filter=A --name-only|grep -Ev "*/tests/*"|grep -v .gitlab-ci|grep -v scripts/run-tests.sh|while read file;do
  echo "🗑️️ Deleting $file";
  git rm $file;
done
fi

echo "2️⃣ Running test changes for this branch"
if [[ $(git diff ${CI_MERGE_REQUEST_DIFF_BASE_SHA} --name-only|grep -E "Test.php$") ]]; then
for test in `git diff ${CI_MERGE_REQUEST_DIFF_BASE_SHA} --name-only|grep -E "Test.php$"`; do
  sudo SIMPLETEST_BASE_URL="$SIMPLETEST_BASE_URL" SIMPLETEST_DB="$SIMPLETEST_DB" MINK_DRIVER_ARGS_WEBDRIVER="$MINK_DRIVER_ARGS_WEBDRIVER" -u www-data ./vendor/bin/phpunit -c core $test --log-junit=./sites/default/files/simpletest/phpunit-`echo $test|sed 's/\//_/g' `.xml;
done;
fi
