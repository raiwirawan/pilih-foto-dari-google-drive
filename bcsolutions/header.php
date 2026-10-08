<?php
/**
 * Theme Header Template
 *
 * Outputs the <!DOCTYPE html>, <head>, and opening <body> tag.
 * Required by WordPress  without this file, the HTML document
 * structure is broken, causing scroll lock and Elementor failures.
 *
 * @package BCSolutions
  */?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
