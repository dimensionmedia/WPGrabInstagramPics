<?php

// This file is meant to be called by a cron job to automatically grab the posts

// TODO: Add options here, or at least pull them from the plugin settings, to control # of posts to pull, etc.

include '../../../wp-load.php';
include '../../../wp-admin/includes/file.php';
include '../../../wp-admin/includes/image.php';

$instagramgrab = WPGrabInstagramPics::get_instance();
$instagramgrab->wpgip_do_grab_instagram_posts();

?>