<?php
/**
 * FAQ page (post #30) — TASK-WEBSITE.md Phase J "FAQ".
 * Run with: wp --user=1 eval-file /tools/pages/16-faq.php
 */
require_once '/tools/elementor-helpers.php';

$trial_url = home_url( '/free-trial/' );

$hero = eqc_page_hero(
	'FAQ',
	'Common questions, answered',
	"Everything families usually ask before starting — classes, beginners, children, scheduling and the free trial. Still have a question? Reach out and we'll help directly.",
	'quote'
);

$groups = array(
	'Classes & Teaching' => array(
		array( 'How are classes conducted?', 'Every class is a live, 1-to-1 video session with a qualified teacher — never a pre-recorded lesson.' ),
		array( 'How long is each class?', 'Standard classes are 30 minutes, matching the plans on our Pricing page.' ),
		array( 'Can I choose a male or female teacher?', 'Yes, families can request a male or female teacher based on their preference.' ),
	),
	'Beginners' => array(
		array( 'Can beginners start from zero?', 'Yes. Noorani Qaida starts from the Arabic alphabet itself, with no prior reading ability assumed.' ),
		array( 'What if I struggle with the alphabet as an adult?', 'That\'s exactly what Noorani Qaida is for, at any age. Teachers are patient with adult beginners.' ),
	),
	'Children' => array(
		array( 'What age can children start?', 'Children of school age can typically begin; a teacher can advise on readiness during the free trial.' ),
		array( 'Will my child have the same teacher each week?', 'We aim to keep the same teacher and time slot consistent wherever possible.' ),
	),
	'Courses' => array(
		array( 'Which course should I choose?', 'See the guidance on our Courses page, or tell us your level during the free trial and we will recommend one.' ),
		array( 'Can I combine Islamic Studies with a Quran course?', 'Yes, many students take Islamic Studies alongside a Quran course.' ),
	),
	'Scheduling & Free Trial' => array(
		array( 'How does the free trial work?', 'Tell us the student\'s age, level and availability, and we match a suitable teacher for one trial class before any commitment.' ),
		array( 'Are timings flexible?', 'Yes. Classes are scheduled around the times that work for your family, not a fixed institutional timetable.' ),
		array( 'Can I reschedule a class?', 'Contact your teacher or us directly in advance and we will help adjust the timing.' ),
	),
	'Devices & Access' => array(
		array( 'What device do I need?', 'A computer, tablet or smartphone with a camera, microphone and stable internet connection is enough.' ),
		array( 'Which video platform is used?', 'Classes run over standard video-call software; your teacher will confirm the exact link when your trial is booked.' ),
	),
);

$group_sections = array();
foreach ( $groups as $title => $items ) {
	$group_sections[] = eqc_faq_group( $title, $items );
}

$faq_content = eqc_section(
	'eqc-section eqc-section--surface',
	array(
		eqc_inner( 'eqc-container--narrow', $group_sections ),
	)
);

// A trailing dark "Didn't find your answer?" CTA used to close this page,
// directly above the global footer's own dark "Your First Class Is Free"
// panel (footer.php) — same color, adjacent, no content between them. Its
// "Contact Us" link stays reachable via the header nav and footer Quick
// Links, so removing the duplicate panel loses no path while fixing the
// double-dark-block heaviness. See 11-about.php for the full note.

eqc_save_elementor_page( eqc_page_id( 'faq' ), array( $hero, $faq_content ) );
