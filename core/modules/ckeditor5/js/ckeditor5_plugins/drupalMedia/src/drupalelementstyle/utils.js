/**
 * A simple helper function that returns the command group name.
 *
 * @example
 *    groupName = 'viewMode' -> commandGroupName = 'drupalViewMode'
 *
 * @param {string} groupName The name of the group (ex. 'align', 'viewMode').
 * @return {string} Command group name.
 */
export default function getCommandGroupNameFromGroup(groupName) {
  return 'drupal'.concat(groupName[0].toUpperCase() + groupName.substring(1));
}
