<?php

/**
 * @file
 * Template file for the theming example text form.
 *
 * Available custom variables:
 * - $count: A int containing the total number of announcements.
 * - $featured: An array of featured announcements.
 * - $standard: An array of non-featured announcements.
 *
 * Each $announcement in $featured and $standard contain:
 * - $announcement['title']: Title of the announcement.
 * - $announcement['teaser']: Short description of the announcement
 * - $announcement['link']: Link given by the announcement.
 * - $announcement['date_published']: Time of the announcement.
 *
 * @see announcements_feed_theme()
 * @ingroup themeable
 */
?>
<?php if ($count) : ?>
  <div class="announcements">

  <?php if ($featured) : ?>
    <div class="featured-announcements-wrapper">
      <?php foreach ($featured as $key => $announcement) : ?>
        <div class="announcements-featured">
          <div class="announcement_title">
            <h4>
              <?php print $announcement['title']; ?>
            </h4>
          </div>
          <div class="announcement_teaser">
            <?php print $announcement['teaser']; ?>
          </div>
          <div class="announcement_link">
            <?php if($announcement['link']) : ?>
                <a href="<?php print $announcement['link']; ?>"><span><?php print t('Learn More'); ?></span></a>
            <?php endif ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
  <?php endif ?>

    <?php foreach ($standard as $key => $announcement) : ?>
      <div class="announcements-standard">
        <div class="announcement_title">
            <a href="<?php print $announcement['link']; ?>"><?php print $announcement['title'] ?></a>
            <div class="announce_date"><?php print format_date(strtotime($announcement['date_published']), 'short'); ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if ($feed_link) : ?>
      <div class="announcements--view-all">
        <a href="<?php print $feed_link; ?>">View all announcements</a>
    </div>
    <?php endif ?>
  </div>
<?php else: ?>
<div class="no_alerts"><span><?php print t('No announcements available') ?></span></div>
<?php endif; ?>
