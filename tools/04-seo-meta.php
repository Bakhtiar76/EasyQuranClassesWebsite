<?php
/**
 * Per-page SEO title/meta description via Rank Math's own postmeta keys
 * (`rank_math_title`, `rank_math_description`) — DESIGN.md §21 / TASK-WEBSITE.md
 * §21 require a title/meta plan per page; search intents follow §21's
 * suggested list. Idempotent — overwrites with the same content on re-run.
 *
 * Run with: wp eval-file /tools/04-seo-meta.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via WP-CLI: wp eval-file /tools/04-seo-meta.php\n" );
}

$pages = array(
	'home'                          => array(
		'title'       => 'Easy Quran Classes | Online Quran Classes with Personal Guidance',
		'description' => '1-to-1 live online Quran classes with qualified male and female teachers. Flexible scheduling, Tajweed, memorization and Islamic Studies. Book a free trial class.',
	),
	'about'                         => array(
		'title'       => 'About Us | Easy Quran Classes',
		'description' => "Easy Quran Classes offers qualified, personal 1-to-1 Quran teaching built around your schedule. Learn who we serve and how our teaching approach works.",
	),
	'courses'                       => array(
		'title'       => 'Online Quran Courses | Easy Quran Classes',
		'description' => 'Explore six online Quran courses: Noorani Qaida, Quran Reading with Tajweed, Tajweed Course, Quran Tafseer, Quran Memorization and Islamic Studies.',
	),
	'teachers'                      => array(
		'title'       => 'Online Quran Teachers | Easy Quran Classes',
		'description' => 'Meet our qualified, Tajweed-certified Quran teachers. Male and female tutors experienced with children and adults, matched to your learning needs.',
	),
	'pricing'                       => array(
		'title'       => 'Online Quran Classes Pricing | Easy Quran Classes',
		'description' => 'Simple monthly pricing for online Quran classes, from 2 to 5 days a week. Every plan includes 1-to-1 teaching, expert tutors and progress tracking.',
	),
	'contact'                       => array(
		'title'       => 'Contact Us | Easy Quran Classes',
		'description' => 'Questions about online Quran classes, pricing or scheduling? Contact Easy Quran Classes by email, WhatsApp or our contact form.',
	),
	'faq'                           => array(
		'title'       => 'FAQ | Easy Quran Classes',
		'description' => 'Answers to common questions about online Quran classes: beginners, children, scheduling, the free trial, devices and more.',
	),
	'blog'                          => array(
		'title'       => 'Blog | Easy Quran Classes',
		'description' => 'Articles and tips on learning the Quran, Tajweed, and building a consistent home learning routine for children and adults.',
	),
	'free-trial'                    => array(
		'title'       => 'Quran Trial Class | Free Trial | Easy Quran Classes',
		'description' => "Book a free online Quran trial class. Tell us the student's age, level and availability and we'll match a suitable teacher. No obligation.",
	),
	'online-quran-classes-for-kids' => array(
		'title'       => 'Online Quran Classes for Kids | Easy Quran Classes',
		'description' => 'Online Quran classes for kids with patient, experienced teachers. Short, focused 1-to-1 sessions built around a child\'s attention span.',
	),
);

foreach ( $pages as $slug => $meta ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		WP_CLI::warning( "Page not found: {$slug}" );
		continue;
	}
	update_post_meta( $page->ID, 'rank_math_title', $meta['title'] );
	update_post_meta( $page->ID, 'rank_math_description', $meta['description'] );
	WP_CLI::log( "Set SEO meta for {$slug} (#{$page->ID})" );
}

WP_CLI::success( 'SEO titles/descriptions set for all pages.' );
