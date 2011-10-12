<?php
// $Id$

 /* techcommons - page.tpl.php (Drupal 6) - 7/13/11 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language; ?>" lang="<?php print $language->language; ?>" dir="<?php print $language->dir; ?>">

<head>
  <title><?php print $head_title; ?></title>
  <?php print $head; ?>
  <?php print $styles; ?>
  <?php print $scripts; ?>
</head>
<body class="<?php print $body_classes; ?>">

  <div id="page">
    <div id="topwrapper">
      <a name="navigation-top" id="navigation-top"></a>
        <?php if ($primary_links || $secondary_links): ?>
          <div id="skip-to-main"><a href="#maincontent"><?php print t('Skip to main content'); ?></a>  <a href="#navigation"><?php print t('Skip to Navigation'); ?></a></div>
        <?php endif; ?>

      <?php if ($secondary_links): ?>
        <div id="secondary" class="clear-block">
          <?php print theme('links', $secondary_links); ?>
        </div> <!-- /#secondary -->
      <?php endif; ?>

    </div>
    
    <div id="page-inner">

    <div id="header"><div id="header-inner" class="clear-block">

      <?php if ($logo || $site_name || $site_slogan): ?>
        <div id="logo-title">

          <?php if ($logo): ?>
            <div id="logo"><a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
              rel="home"><img src="<?php print $logo; ?>" alt="<?php print t('Tech Commons'); ?>"
              id="logo-image" /></a>
            </div>
            <div id="header-print">
              <?php print $header_print; ?>
            </div> <!-- /#header-print -->

          <?php endif; ?>

          <?php if ($site_name): ?>
              <h1 id="site-name">
                <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home">
                  <?php print $techcommons_title; ?>
                </a>
              </h1>
          <?php endif; ?>

          <?php if ($site_slogan): ?>
            <div id="site-slogan"><?php print $site_slogan; ?></div>
          <?php endif; ?>

        </div> <!-- /#logo-title -->
      <?php endif; ?>

      <?php if ($header): ?>
        <div id="header-blocks" class="region region-header">
          <?php print $header; ?>
        </div> <!-- /#header-blocks -->
      <?php endif; ?>

      <?php if ($search_box): ?>
        <div id="search-box">
          <?php print $search_box; ?>
        </div> <!-- /#search-box -->
      <?php endif; ?>
      
    </div></div> <!-- /#header-inner, /#header -->

    <div id="banner-top">
      <!-- style in css -->
    </div>

    <div id="banner">
      <?php print $banner;?>
    </div><!-- /banner -->

    <div id="banner-bottom">
      <!-- style in css -->
    </div>

    <div id="crumbbar">
      <div id="breadcrumb">
        <?php if ($breadcrumb): ?>
            <?php print $breadcrumb; ?>
        <?php endif; ?>
      </div>
    </div>  
    
    <div id="main"><div id="main-inner" class="clear-block<?php if ($primary_links) { print ' with-navbar'; } ?>">

      <div id="content"><div id="content-inner">

        <?php if ($mission): ?>
          <div id="mission"><?php print $mission; ?></div>
        <?php endif; ?>

        <?php if ($content_top): ?>
          <div id="content-top" class="region region-content_top">
            <?php print $content_top; ?>
          </div> <!-- /#content-top -->
        <?php endif; ?>

        <?php if ($title || $tabs || $help || $messages): ?>
          <div id="content-header">
            <?php if ($title): ?>
              <h1 class="title"><?php print $title; ?></h1>
            <?php endif; ?>
            <?php print $messages; ?>
            <?php if ($tabs): ?>
              <div class="tabs"><?php print $tabs; ?></div>
            <?php endif; ?>
            <?php print $help; ?>
          </div> <!-- /#content-header -->
        <?php endif; ?>

        <div id="content-area">
		  <a name="maincontent" id="maincontent"></a>
          <?php print $content; ?>
        </div>

        <?php if ($feed_icons): ?>
          <div class="feed-icons"><?php print $feed_icons; ?></div>
        <?php endif; ?>

        <?php if ($content_bottom): ?>
          <div id="content-bottom" class="region region-content_bottom">
            <?php print $content_bottom; ?>
          </div> <!-- /#content-bottom -->
        <?php endif; ?>

      </div></div> <!-- /#content-inner, /#content -->

      <?php if ($primary_links): ?>
        <div id="navbar"><div id="navbar-inner" class="clear-block region region-navbar">

          <a name="navigation" id="navigation"></a>

          <?php if ($primary_links): ?>
            <div id="primary" class="clear-block">
              <?php print theme('links', $primary_links); ?>
            </div> <!-- /#primary -->
          <?php endif; ?>

        </div></div> <!-- /#navbar-inner, /#navbar -->
      <?php endif; ?>

      <?php if ($left): ?>
        <div id="sidebar-left"><div id="sidebar-left-inner" class="region region-left">
          <?php print $left; ?>

          <div id="identitybox">
            <?php print $brandidentity; ?>
          </div>

        </div></div> <!-- /#sidebar-left-inner, /#sidebar-left -->
      <?php endif; ?>

      <?php if ($right): ?>
        <div id="sidebar-right"><div id="sidebar-right-inner" class="region region-right">
          <?php print $right; ?>
        </div></div> <!-- /#sidebar-right-inner, /#sidebar-right -->
      <?php endif; ?>

    </div></div> <!-- /#main-inner, /#main -->

    <?php if ($footer || $footer_message): ?>
      <div id="footer">
	    <div id="footer-inner" class="region region-footer">
          <?php print $footer; ?>
        </div> <!-- /#footer-inner -->
		<?php if ($footer_message): ?>
          <div id="footer-message"><?php print $footer_message; ?></div>
        <?php endif; ?>	  
	  </div> <!-- /#footer -->
    <?php endif; ?>

  </div></div> <!-- /#page-inner, /#page -->

  <?php if ($closure_region): ?>
    <div id="closure-blocks" class="region region-closure"><?php print $closure_region; ?></div>
  <?php endif; ?>

  <?php print $closure; ?>

</body>
</html>
