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
 * - $announcement['timestamp']: Time of the announcement.
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
        <div class="announcements-featured" <?php if ($announcement['new']) : ?> id="new-feed-featured"
       <?php endif; ?>>
          <div class="announcement_title">
            <h4>
              <?php print $announcement['title']; ?>
            </h4>
            <?php if($announcement['new']) : ?><span class="new-feed"><?php print t('New'); ?></span>
                <div class="unread_status"></div>
            <?php endif ?>
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
      <div class="announcements-standard" <?php if ($announcement['new']): ?> id="new-feed-standard"
     <?php endif?> >
        <div class="announcement_title">
            <a href="<?php print $announcement['link']; ?>"><?php print $announcement['title'] ?></a>
          <?php if($announcement['new']) : ?><span class="new-feed"><?php print t('New'); ?></span>
              <div class="unread_status"></div>
          <?php endif ?>
            <div class="announce_date"><?php print $announcement['date_published']; ?></div>
        </div>
      </div>
    <?php endforeach; ?>
        <p class="announcements--view-all">
        <a href="<?php print $feed_link; ?>">View all announcements</a>
    </p>
  </div>
<?php else: ?>
<div class="no_alerts"><p> <?php print t('No announcements available') ?></p></div>
<?php endif; ?>
