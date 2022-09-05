<?php

namespace Drupal\KernelTests\Core\Theme;

use Drupal\KernelTests\KernelTestBase;

/**
 * Confirms that theme assets copied from Classy have not been changed.
 *
 * If a copied Classy asset is changed, it should no longer be in a /classy
 * subdirectory. The files there should be exact copies from Classy. Once it has
 * changed, it is custom to the theme and should be moved to a different
 * location.
 *
 * @group Theme
 */
class ConfirmClassyCopiesTest extends KernelTestBase {

  /**
   * Tests Classy's assets have not been altered.
   */
  public function testClassyHashes() {
    $theme_path = $this->container->get('extension.list.theme')->getPath('classy');
    foreach (['images', 'css', 'js', 'templates'] as $type => $sub_folder) {
      $asset_path = "$theme_path/$sub_folder";
      $directory = new \RecursiveDirectoryIterator($asset_path, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);
      $iterator = new \RecursiveIteratorIterator($directory);
      $this->assertGreaterThan(0, iterator_count($iterator));
      foreach ($iterator as $fileinfo) {
        $filename = $fileinfo->getFilename();
        $this->assertSame(
          $this->getClassyHash($sub_folder, $filename),
          hash_file('xxh64', $fileinfo->getPathname()),
          "$filename has expected hash"
        );
      }
    }
  }

  /**
   * Confirms that files copied from Classy have not been altered.
   *
   * The /classy subdirectory in a theme's css, js and images directories is for
   * unaltered copies of files from Classy. If a file in that subdirectory has
   * changed, then it is custom to that theme and should be moved to a different
   * directory. Additional information can be found in the README.txt of each of
   * those /classy subdirectories.
   *
   * @param string $theme
   *   The theme being tested.
   * @param string $path_replace
   *   A string to replace paths found in CSS so relative URLs don't cause the
   *   hash to differ.
   * @param string[] $filenames
   *   Provides list of every asset copied from Classy.
   *
   * @dataProvider providerTestClassyCopies
   */
  public function testClassyCopies($theme, $path_replace, array $filenames) {
    $theme_path = $this->container->get('extension.list.theme')->getPath($theme);

    foreach (['images', 'css', 'js', 'templates'] as $sub_folder) {
      $asset_path = "$theme_path/$sub_folder/classy";
      // If a theme has completely customized all files of a type there is
      // potentially no Classy subdirectory for that type. Tests can be skipped
      // for that type.
      if (!file_exists($asset_path)) {
        $this->assertEmpty($filenames[$sub_folder]);
        continue;
      }

      // Create iterators to collect all files in a asset directory.
      $directory = new \RecursiveDirectoryIterator($asset_path, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);
      $iterator = new \RecursiveIteratorIterator($directory);
      $filecount = 0;
      foreach ($iterator as $fileinfo) {
        $filename = $fileinfo->getFilename();
        if ($filename === 'README.txt') {
          continue;
        }

        $filecount++;

        // Replace paths in the contents so the hash will match Classy's hashes.
        $contents = file_get_contents($fileinfo->getPathname());
        $contents = str_replace('(' . $path_replace, '(../../../../', $contents);
        $contents = str_replace('(../../../images/classy/icons', '(../../images/icons', $contents);
        preg_match_all("/attach_library\('.+\/classy\.(.+)'/", $contents, $classy_attach_library_matches);
        if (!empty($classy_attach_library_matches[0])) {
          $library_module = $classy_attach_library_matches[1][0];
          $contents = str_replace("'$theme/classy.$library_module'", "'classy/$library_module'", $contents);
        }

        $this->assertContains($filename, $filenames[$sub_folder], "$sub_folder file: $filename not present.");
        $this->assertSame(
          $this->getClassyHash($sub_folder, $filename),
          hash('xxh64', $contents),
          "$filename is in the theme's /classy subdirectory, but the file contents no longer match the original file from Classy. This should be moved to a new directory and libraries should be updated. The file can be removed from the data provider."
        );
      }
      $this->assertCount($filecount, $filenames[$sub_folder], "Different count for $sub_folder files in the /classy subdirectory. If a file was added to /classy, it shouldn't have been. If it was intentionally removed, it should also be removed from this test's data provider.");
    }
  }

  /**
   * Provides lists of filenames for a theme's asset files copied from Classy.
   *
   * @return array
   *   Theme name, how to replace a path to core assets and asset file names.
   */
  public function providerTestClassyCopies() {
    return [
      'umami' => [
        'theme-name' => 'umami',
        'path-replace' => '../../../../../../../',
        'filenames' => [
          'css' => [
            'action-links.css',
            'book-navigation.css',
            'breadcrumb.css',
            'button.css',
            'collapse-processed.css',
            'container-inline.css',
            'details.css',
            'dialog.css',
            'dropbutton.css',
            'exposed-filters.css',
            'field.css',
            'file.css',
            'form.css',
            'forum.css',
            'icons.css',
            'inline-form.css',
            'item-list.css',
            'link.css',
            'links.css',
            'media-embed-error.css',
            'media-library.css',
            'menu.css',
            'more-link.css',
            'node.css',
            'pager.css',
            'progress.css',
            'search-results.css',
            'tabledrag.css',
            'tableselect.css',
            'tablesort.css',
            'tabs.css',
            'textarea.css',
            'ui-dialog.css',
          ],
          'js' => [
            'media_embed_ckeditor.theme.es6.js',
            'media_embed_ckeditor.theme.js',
          ],
          'images' => [
            'application-octet-stream.png',
            'application-pdf.png',
            'application-x-executable.png',
            'audio-x-generic.png',
            'forum-icons.png',
            'image-x-generic.png',
            'package-x-generic.png',
            'text-html.png',
            'text-plain.png',
            'text-x-generic.png',
            'text-x-script.png',
            'video-x-generic.png',
            'x-office-document.png',
            'x-office-presentation.png',
            'x-office-spreadsheet.png',
          ],
          'templates' => [
            'node-edit-form.html.twig',
            'image-widget.html.twig',
            'node-add-list.html.twig',
            'filter-guidelines.html.twig',
            'filter-tips.html.twig',
            'file-managed-file.html.twig',
            'text-format-wrapper.html.twig',
            'filter-caption.html.twig',
            'help-section.html.twig',
            'progress-bar.html.twig',
            'form-element-label.html.twig',
            'datetime-wrapper.html.twig',
            'fieldset.html.twig',
            'datetime-form.html.twig',
            'textarea.html.twig',
            'details.html.twig',
            'form-element.html.twig',
            'radios.html.twig',
            'item-list.html.twig',
            'item-list--search-results.html.twig',
            'table.html.twig',
            'forum-list.html.twig',
            'forum-icon.html.twig',
            'forums.html.twig',
            'maintenance-page.html.twig',
            'book-export-html.html.twig',
            'html.html.twig',
            'region.html.twig',
            'book-all-books-block.html.twig',
            'book-tree.html.twig',
            'book-navigation.html.twig',
            'toolbar.html.twig',
            'comment.html.twig',
            'taxonomy-term.html.twig',
            'media-embed-error.html.twig',
            'book-node-export-html.html.twig',
            'links--node.html.twig',
            'page-title.html.twig',
            'search-result.html.twig',
            'media.html.twig',
            'mark.html.twig',
            'forum-submitted.html.twig',
            'username.html.twig',
            'user.html.twig',
            'time.html.twig',
            'image.html.twig',
            'field--text.html.twig',
            'field--text-long.html.twig',
            'file-audio.html.twig',
            'field--comment.html.twig',
            'link-formatter-link-separate.html.twig',
            'field.html.twig',
            'field--text-with-summary.html.twig',
            'field--node--uid.html.twig',
            'field--node--title.html.twig',
            'field--node--created.html.twig',
            'file-video.html.twig',
            'links--media-library-menu.html.twig',
            'media--media-library.html.twig',
            'views-view-unformatted--media-library.html.twig',
            'container--media-library-content.html.twig',
            'media-library-item--small.html.twig',
            'container--media-library-widget-selection.html.twig',
            'media-library-wrapper.html.twig',
            'media-library-item.html.twig',
            'views-mini-pager.html.twig',
            'views-exposed-form.html.twig',
            'views-view-grouping.html.twig',
            'views-view-summary.html.twig',
            'views-view-table.html.twig',
            'views-view-row-rss.html.twig',
            'views-view-summary-unformatted.html.twig',
            'views-view.html.twig',
            'block.html.twig',
            'block--local-actions-block.html.twig',
            'block--system-menu-block.html.twig',
            'block--local-tasks-block.html.twig',
          ],
        ],
      ],
      'claro' => [
        'theme-name' => 'claro',
        'path-replace' => '../../../../../',
        'filenames' => [
          'css' => [
            'book-navigation.css',
            'container-inline.css',
            'exposed-filters.css',
            'field.css',
            'file.css',
            'forum.css',
            'icons.css',
            'indented.css',
            'inline-form.css',
            'item-list.css',
            'link.css',
            'links.css',
            'media-embed-error.css',
            'menu.css',
            'more-link.css',
            'node.css',
            'search-results.css',
            'tablesort.css',
            'textarea.css',
            'ui-dialog.css',
          ],
          'js' => [
            'media_embed_ckeditor.theme.es6.js',
            'media_embed_ckeditor.theme.js',
          ],
          'images' => [
            'application-octet-stream.png',
            'application-pdf.png',
            'application-x-executable.png',
            'audio-x-generic.png',
            'forum-icons.png',
            'image-x-generic.png',
            'package-x-generic.png',
            'text-html.png',
            'text-plain.png',
            'text-x-generic.png',
            'text-x-script.png',
            'video-x-generic.png',
            'x-office-document.png',
            'x-office-presentation.png',
            'x-office-spreadsheet.png',
          ],
          'templates' => [
            'filter-caption.html.twig',
            'help-section.html.twig',
            'progress-bar.html.twig',
            'item-list.html.twig',
            'item-list--search-results.html.twig',
            'table.html.twig',
            'forum-list.html.twig',
            'forum-icon.html.twig',
            'forums.html.twig',
            'book-export-html.html.twig',
            'html.html.twig',
            'region.html.twig',
            'menu.html.twig',
            'book-all-books-block.html.twig',
            'book-tree.html.twig',
            'book-navigation.html.twig',
            'toolbar.html.twig',
            'comment.html.twig',
            'node.html.twig',
            'taxonomy-term.html.twig',
            'media-embed-error.html.twig',
            'book-node-export-html.html.twig',
            'links--node.html.twig',
            'page-title.html.twig',
            'search-result.html.twig',
            'media.html.twig',
            'mark.html.twig',
            'forum-submitted.html.twig',
            'username.html.twig',
            'user.html.twig',
            'time.html.twig',
            'image.html.twig',
            'field--text.html.twig',
            'field--text-long.html.twig',
            'file-audio.html.twig',
            'field--comment.html.twig',
            'link-formatter-link-separate.html.twig',
            'field.html.twig',
            'field--text-with-summary.html.twig',
            'field--node--uid.html.twig',
            'field--node--title.html.twig',
            'field--node--created.html.twig',
            'file-video.html.twig',
            'links--media-library-menu.html.twig',
            'container--media-library-content.html.twig',
            'media-library-item--small.html.twig',
            'container--media-library-widget-selection.html.twig',
            'media-library-wrapper.html.twig',
            'media-library-item.html.twig',
            'views-view-grouping.html.twig',
            'views-view-summary.html.twig',
            'views-view-table.html.twig',
            'views-view-row-rss.html.twig',
            'views-view-summary-unformatted.html.twig',
            'views-view.html.twig',
            'block--system-branding-block.html.twig',
            'block--search-form-block.html.twig',
            'block.html.twig',
            'block--system-menu-block.html.twig',
          ],
        ],
      ],
    ];
  }

  /**
   * Gets the hash of a Classy asset.
   *
   * @param string $type
   *   The asset type.
   * @param string $file
   *   The asset filename.
   *
   * @return string
   *   A hash for the file.
   */
  protected function getClassyHash($type, $file) {
    static $hashes = [
      'css' => [
        'action-links.css' => 'c99feeafc84e3736',
        'book-navigation.css' => '2d26cf3ad7cb49c5',
        'breadcrumb.css' => '770c3391989b374e',
        'button.css' => '275d9d7ecf68c867',
        'collapse-processed.css' => '4d3397ac009bb500',
        'container-inline.css' => '78f0f7b6e37e4243',
        'details.css' => 'af1b6237b38422ca',
        'dialog.css' => '83887cc9ef516280',
        'dropbutton.css' => 'e1237942ef8a8015',
        'exposed-filters.css' => 'f4370a278f1b70d6',
        'field.css' => '7b6663ca9ce1eb02',
        'file.css' => '0596f3e49e613b12',
        'form.css' => '91d696c096376810',
        'forum.css' => 'c68239351b141e3e',
        'icons.css' => '4ea235154b96c87a',
        'image-widget.css' => 'd1054a4c094bab98',
        'indented.css' => '9140a369b450dc50',
        'inline-form.css' => 'd6c48973e400b3e6',
        'item-list.css' => '79c8d372152832d6',
        'link.css' => 'be367f94c203de4d',
        'links.css' => '2e9f12262b422ce6',
        'media-embed-error.css' => '6b23dec6770ff90e',
        'media-library.css' => '3745beeb06e58ec4',
        'menu.css' => 'd6d3be0ec3daa330',
        'messages.css' => '1effc5daa3d09c95',
        'more-link.css' => 'd4ab0bebdb79428b',
        'node.css' => '6a4f433fc8791218',
        'pager.css' => '1242a13c3387f854',
        'progress.css' => 'e6396ec72329b37f',
        'search-results.css' => '52ef15d63adaf534',
        'tabledrag.css' => '81631e8702a1f80a',
        'tableselect.css' => '2515f8d5170a7fb6',
        'tablesort.css' => '02bdcbf9777457b5',
        'tabs.css' => '14ceaa3972640ba3',
        'textarea.css' => '7471980731df4c45',
        'ui-dialog.css' => '0ae4fb9aec3bf2b4',
        'user.css' => 'fadb04fa5b65869f',
      ],
      'js' => [
        'media_embed_ckeditor.theme.es6.js' => '55614c634a3049ec',
        'media_embed_ckeditor.theme.js' => 'fc165b0445e82e22',
      ],
      'images' => [
        'application-octet-stream.png' => 'e92638408bda6cb3',
        'application-pdf.png' => '8f419d7b08c86457',
        'application-x-executable.png' => 'e92638408bda6cb3',
        'audio-x-generic.png' => '7d7a07a4d35ea1b7',
        'forum-icons.png' => '38d747d509dafd8e',
        'image-x-generic.png' => '1296024be56e5bfb',
        'package-x-generic.png' => '7631c9b9b41ed943',
        'text-html.png' => '2f2369d88a099d4e',
        'text-plain.png' => 'da4cb1d92a5b3913',
        'text-x-generic.png' => 'da4cb1d92a5b3913',
        'text-x-script.png' => '03c4981a553a4a65',
        'video-x-generic.png' => '44e8f8185922e6dd',
        'x-office-document.png' => '6d51ab7ad12a955e',
        'x-office-presentation.png' => '3f3f8568f725b204',
        'x-office-spreadsheet.png' => '22e2e38e448bd919',
      ],
      'templates' => [
        'node-edit-form.html.twig' => '99bb6a6f983c1276',
        'file-widget-multiple.html.twig' => '312ae67a6b0019f4',
        'image-widget.html.twig' => '653ac79f14379663',
        'file-upload-help.html.twig' => '563898a375628497',
        'node-add-list.html.twig' => '578f1808e31f3612',
        'filter-guidelines.html.twig' => '87298c69558fa2de',
        'filter-tips.html.twig' => '5a972ede2c453a64',
        'file-managed-file.html.twig' => '5526052b3c8eb3fd',
        'text-format-wrapper.html.twig' => '8922c115f53adaa3',
        'filter-caption.html.twig' => '6b1862a029151220',
        'help-section.html.twig' => 'f5f7cfa1c8a5d02a',
        'progress-bar.html.twig' => 'b43bac82834d5281',
        'status-messages.html.twig' => 'b15f4693282caec2',
        'container.html.twig' => '913dde4d54544124',
        'input.html.twig' => 'd8ae792ff5a970eb',
        'form-element-label.html.twig' => 'c8a594505d396d34',
        'datetime-wrapper.html.twig' => 'e655cebe1ea2a069',
        'fieldset.html.twig' => '06ed77e57b15a046',
        'form.html.twig' => '859e2f3ec52c0e1c',
        'datetime-form.html.twig' => '6c57520db02f9ab6',
        'checkboxes.html.twig' => '588e6a6bccb8386a',
        'textarea.html.twig' => '0c2109bfb3ff0755',
        'field-multiple-value-form.html.twig' => 'd64ff3a270eece05',
        'dropbutton-wrapper.html.twig' => 'b8d2fcba5dc0c716',
        'details.html.twig' => '2bc411571dc55154',
        'select.html.twig' => 'd000ea07aad246cf',
        'form-element.html.twig' => '69d597b7d0d4c0e2',
        'confirm-form.html.twig' => 'aa938275a124374c',
        'radios.html.twig' => 'd7e7ebc3d98f6c12',
        'item-list.html.twig' => '91481081d57731d6',
        'aggregator-feed.html.twig' => 'f61669526e3f5bcb',
        'item-list--search-results.html.twig' => '744bb33a7c77ee0c',
        'table.html.twig' => '387c900a862be506',
        'forum-list.html.twig' => '73f26c71773ca155',
        'forum-icon.html.twig' => 'd17b0be2f0c26780',
        'forums.html.twig' => '881f8ab2a528ca02',
        'page.html.twig' => 'b260908fa8b26403',
        'maintenance-page.html.twig' => 'bc041ab3bc4a833b',
        'book-export-html.html.twig' => '98fa16bcbd392a32',
        'html.html.twig' => '6bbc70d5f5a85a0c',
        'region.html.twig' => 'dbebefeffac53106',
        'menu.html.twig' => '59ec2572e17beefd',
        'book-all-books-block.html.twig' => 'e0e1a0e397a8d862',
        'book-tree.html.twig' => 'ee3c512658309039',
        'links.html.twig' => '6ce0de5d44c11dfe',
        'vertical-tabs.html.twig' => '0ccf45f1af0f3f58',
        'pager.html.twig' => '7eb4ca70420934df',
        'menu-local-task.html.twig' => 'a7d3c0b60b71e576',
        'book-navigation.html.twig' => 'b8db00adedd7c5c7',
        'breadcrumb.html.twig' => 'eac3aac1087d9aad',
        'menu-local-action.html.twig' => '317f7411f68778e0',
        'toolbar.html.twig' => 'd2b059e6959b10a7',
        'menu-local-tasks.html.twig' => 'f132073d686b97fd',
        'comment.html.twig' => '5106db25ea8a4fd7',
        'node.html.twig' => '108cc1273b556c14',
        'taxonomy-term.html.twig' => '4e134aa548673121',
        'media-embed-error.html.twig' => 'd6e422863ce6f2e0',
        'book-node-export-html.html.twig' => 'de4450322eeca262',
        'links--node.html.twig' => '7a7b76d4ce915342',
        'page-title.html.twig' => 'af0eae9e8024b530',
        'search-result.html.twig' => 'dec4c422673273a2',
        'aggregator-item.html.twig' => '505f04d01c5f417d',
        'media.html.twig' => '62dfa5a0c8afc407',
        'mark.html.twig' => 'd0f12f21db3a7d7a',
        'forum-submitted.html.twig' => 'a4e322d43bc8d0b6',
        'username.html.twig' => 'c90b8b3d53ad1b01',
        'user.html.twig' => 'f1044fb2a5ba969d',
        'time.html.twig' => '0c0a7fe88748d3ac',
        'image.html.twig' => '8dd11677d51c99e2',
        'field--text.html.twig' => 'cd2c390143aa01f7',
        'field--text-long.html.twig' => 'c195249e6c368c37',
        'file-audio.html.twig' => '10efea1663934193',
        'field--comment.html.twig' => '2c73a9c44f80ff83',
        'link-formatter-link-separate.html.twig' => 'bbe66b35580fbcaa',
        'field.html.twig' => 'b9e9240888b701da',
        'file-link.html.twig' => '49db6e7bb690e176',
        'field--text-with-summary.html.twig' => 'c195249e6c368c37',
        'field--node--uid.html.twig' => '75c93bcfb6f5fbd7',
        'field--node--title.html.twig' => '2c8b7af15bf6fa92',
        'image-style.html.twig' => '7b873ff4a3e0c7f2',
        'field--node--created.html.twig' => '6ca382917733f40c',
        'image-formatter.html.twig' => '8311721372ef783b',
        'file-video.html.twig' => '3393995db9694033',
        'links--media-library-menu.html.twig' => 'dcfcbb1b27dfa954',
        'media--media-library.html.twig' => 'bb3c67518404ceaf',
        'views-view-unformatted--media-library.html.twig' => '27a893340aa6ba05',
        'container--media-library-content.html.twig' => 'cfa5af23a270e1c2',
        'media-library-item--small.html.twig' => '1a433ae82baf0388',
        'container--media-library-widget-selection.html.twig' => '1964c9afb70041e8',
        'media-library-wrapper.html.twig' => 'd7bd0a34f0e1f1f9',
        'media-library-item.html.twig' => 'd3cc50af3a73bdbc',
        'views-mini-pager.html.twig' => 'ea38225eac4ce1c5',
        'views-view-row-opml.html.twig' => 'c15528b038a7980b',
        'views-exposed-form.html.twig' => '3f181752fb49b894',
        'views-view-grouping.html.twig' => '045fbd5439b34ed0',
        'views-view-summary.html.twig' => '03756cdc42b72739',
        'views-view-table.html.twig' => 'e5eab97ac1780000',
        'views-view-list.html.twig' => 'e16748cf48700324',
        'views-view-unformatted.html.twig' => 'cdc18226017cdf60',
        'views-view-row-rss.html.twig' => '9c43d6ee26c0f06b',
        'views-view-mapping-test.html.twig' => '7d1ee1f988bb67dc',
        'views-view-opml.html.twig' => '34f245eb2c677f3c',
        'views-view-summary-unformatted.html.twig' => '2544ba596f000822',
        'views-view.html.twig' => '3d61537842f59c58',
        'views-view-grid.html.twig' => 'b016b1dd5ef541b6',
        'views-view-rss.html.twig' => 'e2c21b1a8fc54d65',
        'block--system-branding-block.html.twig' => '1bfcdc83805a37a8',
        'block--search-form-block.html.twig' => '026a2e8b559ce346',
        'block.html.twig' => 'ec217d017aaf021f',
        'block--local-actions-block.html.twig' => '33f2271ba7e013aa',
        'block--system-menu-block.html.twig' => '37e7cec18182dcfd',
        'block--local-tasks-block.html.twig' => '1e1fb0c7583cead4',
      ],
    ];
    $this->assertArrayHasKey($type, $hashes);
    $this->assertArrayHasKey($file, $hashes[$type]);
    return $hashes[$type][$file];
  }

}
