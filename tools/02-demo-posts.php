<?php
/**
 * Three clearly-marked local/demo blog posts, so the Blog page and the
 * [eqc_latest_posts] shortcode have real content to prove the dynamic
 * WordPress Posts system end-to-end (TASK-WEBSITE.md Phase J: "Demo posts
 * must be clearly local/demo and not accidentally released as
 * client-authored content"). Idempotent — matched by slug.
 *
 * Run with: wp eval-file /tools/02-demo-posts.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via WP-CLI: wp eval-file /tools/02-demo-posts.php\n" );
}

/**
 * Set a post's featured image by matching the source filename fragment (not
 * post_name — see eqc_media_id() in tools/elementor-helpers.php, which this
 * duplicates rather than requires, since this file is meant to run
 * standalone). Ordered by post_id DESC for the same reason as
 * eqc_media_id(): if a fragment matches more than one attachment (e.g. a
 * legacy file and its re-imported replacement), the most recently imported
 * match wins instead of an undefined LIMIT-1-with-no-ORDER-BY pick — this
 * exact bug silently mis-set all 5 demo post thumbnails during a 2026-09-07
 * WebP re-import, caught only during production QA.
 */
function eqc_set_demo_thumbnail( $post_id, $filename_fragment ) {
	global $wpdb;
	$thumb_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1", '%' . $wpdb->esc_like( $filename_fragment ) . '%' ) );
	if ( $thumb_id ) {
		set_post_thumbnail( $post_id, (int) $thumb_id );
	}
}

$category_id         = wp_create_category( 'Learning Tips' );
$tajweed_category_id = wp_create_category( 'Tajweed' );
$parents_category_id = wp_create_category( 'For Parents' );

$posts = array(
	array(
		'slug'      => 'demo-starting-your-childs-quran-journey',
		'title'     => '[DEMO] Starting Your Child\'s Quran Journey',
		'excerpt'   => 'A few practical, low-pressure ways to help a young beginner build a steady daily habit with the Quran.',
		'content'   => "<p><em>This is a local demo post used to prove the blog template while real articles are written.</em></p>\n<p>Starting young doesn't have to mean starting fast. A short, consistent daily session — even five to ten minutes — builds more lasting familiarity with the Arabic letters and sounds than an occasional long class.</p>\n<h2>A simple weekly rhythm</h2>\n<p>Keep the same time each day where practical, keep the session short, and end while your child still wants more rather than pushing until they're tired of it.</p>\n<h2>What progress actually looks like early on</h2>\n<p>Recognizing letters, joining sounds, and reading short words correctly are all real progress — long before a child is reading full verses independently.</p>",
		'thumbnail' => 'blog-islamic-education-1',
	),
	array(
		'slug'      => 'demo-what-is-tajweed-and-why-it-matters',
		'title'     => '[DEMO] What Is Tajweed, and Why Does It Matter?',
		'excerpt'   => 'A plain-language introduction to Tajweed for adults returning to the Quran after years away from regular recitation.',
		'content'   => "<p><em>This is a local demo post used to prove the blog template while real articles are written.</em></p>\n<p>Tajweed is the set of rules governing how each letter of the Quran is correctly pronounced when recited. For many adults who learned to read as children but never formally studied Tajweed, revisiting it can feel like learning to read again — in a good way.</p>\n<h2>Why it's worth relearning as an adult</h2>\n<p>Correct pronunciation changes both the sound and, in some cases, the meaning of a word. A teacher who listens and corrects in real time is the fastest way to unlearn small habits picked up over years.</p>",
		'thumbnail' => 'blog-islamic-education-2',
	),
	array(
		'slug'      => 'demo-choosing-between-live-online-classes',
		'title'     => '[DEMO] Choosing the Right Online Learning Schedule',
		'excerpt'   => 'How to think about class frequency and timing when balancing school, work, and family commitments.',
		'content'   => "<p><em>This is a local demo post used to prove the blog template while real articles are written.</em></p>\n<p>There's no single right number of classes per week — the right schedule is the one a student can actually keep consistently for months, not the most ambitious one on paper.</p>\n<h2>Questions worth asking before committing</h2>\n<p>What time of day is genuinely free, not just theoretically free? Is the student more focused right after school, or in the evening? Flexible scheduling exists precisely so the answer can be specific to one family.</p>",
		'thumbnail' => 'blog-islamic-education-3',
	),
	array(
		'slug'      => 'demo-common-tajweed-mistakes-new-reciters-make',
		'title'     => '[DEMO] 5 Common Tajweed Mistakes New Reciters Make',
		'excerpt'   => "Small, easy-to-miss habits that change how a letter sounds — and how a teacher usually corrects them.",
		'content'   => "<p><em>This is a local demo post used to prove the blog template while real articles are written.</em></p>\n<p>Most Tajweed mistakes aren't dramatic — they're small habits that feel natural until a teacher points them out. A few of the most common ones:</p>\n<ul>\n<li>Rushing through letters that need to be held (madd) for their full length.</li>\n<li>Softening letters that require a firm, full articulation (the qalqalah letters).</li>\n<li>Merging two letters together instead of pronouncing each from its correct point of articulation.</li>\n<li>Losing the nasal sound (ghunnah) where the rules call for it.</li>\n<li>Reading at a pace too fast to apply any rule consciously.</li>\n</ul>\n<h2>Why real-time correction matters</h2>\n<p>Reading rules in a book rarely fixes a habit already learned by ear. A live teacher who stops a student mid-word and demonstrates the correct sound is, in practice, the fastest way most people actually unlearn these patterns.</p>",
		'thumbnail' => 'about-quran-open-page',
		'category'  => $tajweed_category_id,
	),
	array(
		'slug'      => 'demo-keeping-kids-motivated-between-classes',
		'title'     => '[DEMO] How to Keep Kids Motivated Between Quran Classes',
		'excerpt'   => 'Simple, low-pressure habits parents can use on the days there is no class at all.',
		'content'   => "<p><em>This is a local demo post used to prove the blog template while real articles are written.</em></p>\n<p>The days between classes matter as much as the class itself. A child doesn't need a long practice session at home — a few consistent minutes of encouragement usually does more than an occasional long, tense one.</p>\n<h2>What tends to help</h2>\n<p>Reviewing just the previous class's short passage rather than assigning something new, letting the child read aloud to a parent even if the parent can't correct it, and praising effort and consistency rather than only correct recitation, all help a child stay willing to keep going.</p>\n<h2>What to avoid</h2>\n<p>Turning home practice into a test, comparing progress to another child, or treating a missed day as a failure rather than something to simply pick back up.</p>",
		'thumbnail' => 'about-child-reading-quran',
		'category'  => $parents_category_id,
	),
);

foreach ( $posts as $p ) {
	$existing = get_page_by_path( $p['slug'], OBJECT, 'post' );
	if ( $existing ) {
		if ( ! has_post_thumbnail( $existing->ID ) ) {
			eqc_set_demo_thumbnail( $existing->ID, $p['thumbnail'] );
			WP_CLI::log( "Backfilled thumbnail for existing demo post: {$p['slug']}" );
		} else {
			WP_CLI::log( "Skipping existing demo post: {$p['slug']}" );
		}
		continue;
	}
	$id = wp_insert_post(
		array(
			'post_title'   => $p['title'],
			'post_name'    => $p['slug'],
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_excerpt' => $p['excerpt'],
			'post_content' => $p['content'],
			'post_category'=> array( isset( $p['category'] ) ? $p['category'] : $category_id ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		continue;
	}
	eqc_set_demo_thumbnail( $id, $p['thumbnail'] );
	WP_CLI::log( "Created demo post: {$p['title']} -> #{$id}" );
}

WP_CLI::success( 'Demo blog posts ready.' );
