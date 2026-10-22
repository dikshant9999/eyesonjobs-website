<?php
/**
 * Starter content for every page. After setup, edit any of it in
 * Pages → (page) → Edit. Nothing here is used again unless setup is re-run.
 *
 * @package EyesOnJobs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Closing call-to-action band, reused across pages.
 *
 * @param string $title Title.
 * @param string $text  Text.
 */
function eoj_cta_band( $title = '', $text = '' ) {
	$title = $title ? $title : 'Ready to build your team, or take the next step in your career?';
	$text  = $text ? $text : 'Talk to an Eyes on Jobs consultant today. We respond to every enquiry within one working day.';
	return eoj_b_section(
		eoj_b_group(
			array(
				eoj_b_h( $title, 2, 'eoj-title' ),
				eoj_b_p( $text, 'eoj-lead' ),
				eoj_b_btns(
					array(
						array( 'Hire talent', '/employers/' ),
						array( 'Find a job', '/jobs/', 'outline' ),
					)
				),
			),
			'eoj-cta'
		),
		'eoj-section--cta'
	);
}

/**
 * All starter pages. Keys are page slugs; order matters for parents.
 *
 * @return array
 */
function eoj_page_definitions() {
	$pages = array();

	/* ---------------------------------------------------------------- Home */
	$pages['home'] = array(
		'title'   => 'Home',
		'content' => implode(
			'',
			array(
				eoj_b_group(
					eoj_b_group(
						eoj_b_cols(
							array(
								array(
									array(
										eoj_b_p( 'Recruitment partner · Pan India', 'eoj-eyebrow eoj-pill' ),
										eoj_b_h( 'Hire the right talent. <strong>Land the right job.</strong>', 1, 'eoj-hero__title' ),
										eoj_b_p( 'Eyes on Jobs connects ambitious professionals with India’s fastest-growing companies. From first jobs to the boardroom, we find, verify and deliver people who make a real difference.', 'eoj-lead' ),
										eoj_b_sc( '[eoj_job_search]' ),
										eoj_b_p( 'Now hiring in [eoj_rotator words="IT &amp; Software|Sales|Finance|Marketing|HR|Fashion &amp; Design|Operations"]', 'eoj-hero__note' ),
									),
									'eoj-hero__copy',
								),
								array( eoj_b_sc( '[eoj_art name="hero"]' ), 'eoj-hero__art' ),
							),
							'eoj-hero__cols'
						),
						'eoj-wrap'
					),
					'eoj-hero',
					'section'
				),

				eoj_b_section(
					eoj_b_sc( '[eoj_stats items="11+|Industries we recruit for;3|Hiring levels, entry to leadership;1|Working day to respond to every enquiry;100%|Confidential executive search"]' ),
					'eoj-section--stats'
				),

				eoj_b_section(
					array(
						eoj_b_head( 'How we help', 'One partner for both sides of hiring', 'Whether you are building a team or building a career, you get a dedicated consultant who understands your goals and moves fast.' ),
						eoj_b_cols(
							array(
								array(
									eoj_b_group(
										array(
											eoj_b_sc( '[eoj_icon name="building"]' ),
											eoj_b_h( 'For employers', 3 ),
											eoj_b_p( 'Share your requirement once. We shortlist pre-screened, verified candidates who match the role, the budget and your culture.' ),
											eoj_b_list( array( 'Permanent, contract and bulk hiring', 'Executive and leadership search', 'Background verification before you hire' ), 'eoj-checklist' ),
											eoj_b_btns( array( array( 'Post a requirement', '/employers/' ) ) ),
										),
										'eoj-card eoj-card--navy eoj-card--big'
									),
									'',
								),
								array(
									eoj_b_group(
										array(
											eoj_b_sc( '[eoj_icon name="user"]' ),
											eoj_b_h( 'For job seekers', 3 ),
											eoj_b_p( 'Get discovered by employers who are hiring right now, with honest guidance on roles, salaries and interviews along the way.' ),
											eoj_b_list( array( 'Openings across India in every major industry', 'One profile, many opportunities', 'Interview and resume support when you need it' ), 'eoj-checklist' ),
											eoj_b_btns(
												array(
													array( 'Browse jobs', '/jobs/' ),
													array( 'Submit resume', '/submit-resume/', 'outline' ),
												)
											),
										),
										'eoj-card eoj-card--big'
									),
									'',
								),
							),
							'eoj-split'
						),
					)
				),

				eoj_b_section(
					array(
						eoj_b_head( 'Our process', 'Hiring made simple in three steps' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'file', 'Share your requirement', 'Tell us about the role, the skills and the kind of person who will thrive in your team. It takes five minutes.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'search', 'We find and screen', 'Our recruiters search our talent network, assess every candidate and verify their background before you see a profile.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'handshake', 'You hire the best', 'Interview a focused shortlist, make your offer, and we support both sides right up to the joining date.', array(), 'eoj-card eoj-step' ),
							),
							'eoj-grid-3 eoj-steps'
						),
					),
					'eoj-section--tint'
				),

				eoj_b_section(
					array(
						eoj_b_head( 'Services', 'Everything you need to hire well', 'Specialist recruitment services designed around how Indian businesses actually hire.' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'award', 'Executive Search', 'Retained, confidential search for board, CXO and senior leadership roles.', array( 'Learn more', '/executive-search/' ) ),
								eoj_b_card( 'shield', 'Background Verification', 'Pre-employment checks that protect your business from costly hiring mistakes.', array( 'Learn more', '/background-verification/' ) ),
								eoj_b_card( 'users', 'Senior &amp; Mid-level Hiring', 'Managers and specialists with the niche skills your growth depends on.', array( 'Learn more', '/services/' ) ),
								eoj_b_card( 'cap', 'Campus Recruitment', 'Bright graduates from leading colleges, assessed and ready to learn.', array( 'Learn more', '/services/' ) ),
								eoj_b_card( 'trend', 'Employee Development', 'Programmes that help your people grow, perform and stay.', array( 'Learn more', '/services/' ) ),
								eoj_b_card( 'sparkle', 'Job Preparation Package <span class="eoj-tag eoj-tag--hot">New</span>', 'Resume, interview and salary coaching for job seekers. Now at 50% off.', array( 'See the package', '/job-preparation-package/' ), 'eoj-card eoj-card--highlight' ),
							)
						),
					)
				),

				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'New launch · Limited-period offer', 'eoj-eyebrow eoj-pill eoj-pill--light' ),
									eoj_b_h( 'Walk into your next interview fully prepared', 2, 'eoj-title' ),
									eoj_b_p( 'Our Complete Job Preparation Package gives you an ATS-friendly resume, one-to-one interview coaching, a live mock interview and expert salary negotiation guidance, all for one simple fee.', 'eoj-lead' ),
									eoj_b_list( array( 'ATS-friendly resume', 'HR screening prep', 'Mock interview', 'Personal feedback', 'Salary guidance', 'Joining support' ), 'eoj-chiplist eoj-chiplist--light' ),
									eoj_b_btns(
										array(
											array( 'Register now', '/job-preparation-package/register/' ),
											array( 'What’s included', '/job-preparation-package/', 'outline' ),
										)
									),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_jpp_price]' ), 'eoj-promo__price' ),
						),
						'eoj-promo__cols'
					),
					'eoj-section--promo'
				),

				eoj_b_section(
					array(
						eoj_b_head( 'Why Eyes on Jobs', 'Built to save you time at every step' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'search', 'Easy search', 'Find relevant roles or candidates in seconds with smart filters.', array(), 'eoj-feature' ),
								eoj_b_card( 'briefcase', 'Many options', 'Openings across 11+ industries and every career level.', array(), 'eoj-feature' ),
								eoj_b_card( 'globe', 'One job, many locations', 'Hire or get hired anywhere in India from a single listing.', array(), 'eoj-feature' ),
								eoj_b_card( 'clock', 'Save time', 'Pre-screened shortlists mean fewer interviews and faster offers.', array(), 'eoj-feature' ),
								eoj_b_card( 'heart', 'We care', 'A real consultant who answers your calls and follows through.', array(), 'eoj-feature' ),
								eoj_b_card( 'lock', 'Confidential', 'Your data and your search stay private, always.', array(), 'eoj-feature' ),
							),
							'eoj-grid-3 eoj-features'
						),
					),
					'eoj-section--tint'
				),

				eoj_b_section(
					array(
						eoj_b_head( 'Industries', 'Sector expertise that shortens your search' ),
						eoj_b_list( array( 'Fashion &amp; Lifestyle', 'IT &amp; Software', 'Manufacturing', 'Sales &amp; Marketing', 'Consumer &amp; Services', 'Infrastructure', 'Logistics &amp; Supply Chain', 'Banking &amp; Financial Services', 'Hospitality', 'Retail', 'Media &amp; Advertising' ), 'eoj-chiplist eoj-chiplist--center' ),
					)
				),

				eoj_b_section(
					array(
						eoj_b_head( 'Latest openings', 'Jobs you can apply for today' ),
						eoj_b_sc( '[eoj_latest_jobs count="4"]' ),
						eoj_b_btns( array( array( 'View all jobs', '/jobs/', 'outline' ) ), 'eoj-center' ),
					),
					'eoj-section--tint'
				),

				eoj_b_section(
					array(
						eoj_b_p( 'Trusted by growing companies across India', 'eoj-eyebrow eoj-center' ),
						eoj_b_list( array( 'Otipy', 'Vertoz Advertising', 'iEnergizer', 'Ozone Secutech', 'Sarita Handa', 'Punihani International', 'Indospirit', 'Fashion Accessories' ), 'eoj-marquee' ),
					),
					'eoj-section--logos'
				),

				eoj_b_section(
					array(
						eoj_b_head( 'Testimonials', 'What candidates and clients say' ),
						eoj_b_group(
							array(
								eoj_b_quote( 'The team gave me personal attention at every step and timed the whole recruitment process perfectly. I could not have asked for better support.', 'Mohit <span>Back-end Developer</span>', 'eoj-testimonial' ),
								eoj_b_quote( 'A strong platform for sourcing talent. The website is easy to use and the team is genuinely helpful.', 'Pardeep <span>Graphic Designer</span>', 'eoj-testimonial' ),
								eoj_b_quote( 'A comprehensive database with a wide variety of profiles. Shortlisting the right people was quick and easy.', 'Aman <span>Finance professional</span>', 'eoj-testimonial' ),
								eoj_b_quote( 'Outstanding, user-friendly service that responds quickly to every requirement we share.', 'Hiring manager <span>Client company</span>', 'eoj-testimonial' ),
								eoj_b_quote( 'Searching resumes was simple, and the quality of profiles was excellent. I recommend Eyes on Jobs to anyone hiring experts.', 'HR lead <span>Client company</span>', 'eoj-testimonial' ),
							),
							'eoj-testimonials'
						),
					)
				),

				eoj_cta_band(),
			)
		),
	);

	/* ------------------------------------------------------------- About */
	$pages['about-us'] = array(
		'title'   => 'About Us',
		'excerpt' => 'A people-first recruitment firm connecting India’s best talent with the companies that need it most.',
		'content' => implode(
			'',
			array(
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'Who we are', 'eoj-eyebrow' ),
									eoj_b_h( 'Human resource experts who put people first', 2, 'eoj-title' ),
									eoj_b_p( 'Eyes on Jobs is a Gurugram-based human resource services firm that brings employers and professionals together. Our goal is simple: deliver the highest level of satisfaction at every stage of recruitment, for the company that hires and for the person who joins.' ),
									eoj_b_p( 'Over the years we have built a trusted network of hiring companies and a deep pool of talent across domains. Every candidate starts with a one-to-one conversation, so we understand their strengths and ambitions before suggesting a role. Every client gets a hiring approach tailored to their sector, because recruiting for a fashion house is very different from hiring for a software team.' ),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_art name="team"]' ), '' ),
						),
						'eoj-media-cols'
					)
				),
				eoj_b_section(
					eoj_b_grid(
						array(
							eoj_b_card( 'target', 'Our mission', 'To make hiring faster, fairer and more reliable for Indian businesses, and to help every professional we meet find work they are proud of.' ),
							eoj_b_card( 'globe', 'Our vision', 'To be India’s most trusted recruitment partner, known for quality, integrity and lasting relationships.' ),
							eoj_b_card( 'heart', 'Our values', 'Honesty in every conversation, respect for every candidate, confidentiality for every client and a relentless focus on results.' ),
						)
					),
					'eoj-section--tint'
				),
				eoj_b_section(
					array(
						eoj_b_head( 'How we work', 'A proven approach to every hire' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'chat', 'Understand', 'We learn your business, the role and the culture, or your career goals, before we start searching.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'search', 'Search', 'We tap our talent network, referrals and market research to find the right people, including those not actively looking.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'shield', 'Assess', 'Candidates are interviewed, evaluated and verified so you only meet serious, qualified professionals.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'handshake', 'Deliver', 'We coordinate interviews, offers and joining, and stay in touch long after day one.', array(), 'eoj-card eoj-step' ),
							),
							'eoj-grid-4 eoj-steps'
						),
					)
				),
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'Every level', 'eoj-eyebrow' ),
									eoj_b_h( 'From first jobs to the boardroom', 2, 'eoj-title' ),
									eoj_b_p( 'We recruit at entry level, middle management and top management, so a growing company can work with one partner as it scales.' ),
								),
								'',
							),
							array( eoj_b_list( array( 'Fashion &amp; Lifestyle', 'IT &amp; Software', 'Manufacturing', 'Marketing', 'Consumer &amp; Services', 'Infrastructure', 'Logistics', 'Financial Services', 'Hospitality', 'Retail', 'Media' ), 'eoj-chiplist' ), '' ),
						)
					),
					'eoj-section--tint'
				),
				eoj_cta_band(),
			)
		),
	);

	/* ----------------------------------------------- Social responsibility */
	$pages['social-responsibility'] = array(
		'title'   => 'Social Responsibility',
		'parent'  => '',
		'excerpt' => 'Doing good is part of how we do business, not a separate activity.',
		'content' => implode(
			'',
			array(
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'Our commitment', 'eoj-eyebrow' ),
									eoj_b_h( 'Responsibility in action, not just in words', 2, 'eoj-title' ),
									eoj_b_p( 'At Eyes on Jobs we believe a recruitment company has real influence over people’s lives and livelihoods. We use that position to support the wider community and to operate as an ethical, responsible organisation in everything we do.' ),
									eoj_b_p( 'Our social responsibility programme covers our obligations from every angle, to candidates, to clients, to our own team and to society. We approach it with the same energy and pride we bring to our recruitment work.' ),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_art name="csr"]' ), '' ),
						),
						'eoj-media-cols'
					)
				),
				eoj_b_section(
					array(
						eoj_b_head( 'Where we focus', 'Four commitments we live by' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'shield', 'Ethical recruitment', 'Transparent processes, honest job descriptions and respect for every applicant, whether or not they are selected.' ),
								eoj_b_card( 'users', 'Fair and inclusive hiring', 'We encourage clients to hire on merit and welcome candidates of every background, gender and ability.' ),
								eoj_b_card( 'cap', 'Skills and guidance', 'Career advice for students and first-time job seekers, so talent from every town has a fair start.' ),
								eoj_b_card( 'heart', 'Community support', 'Our team volunteers time and resources for local causes in and around Gurugram.' ),
							),
							'eoj-grid-4'
						),
					),
					'eoj-section--tint'
				),
				eoj_cta_band( 'Want to partner with us on a community initiative?', 'We are always happy to collaborate with organisations that share our values.' ),
			)
		),
	);

	/* ------------------------------------------------------------- Services */
	$pages['services'] = array(
		'title'   => 'Services',
		'excerpt' => 'Specialist recruitment services, from campus to boardroom, delivered with speed and care.',
		'content' => implode(
			'',
			array(
				eoj_b_section(
					array(
						eoj_b_head( 'What we do', 'India’s professionally managed, fast-growing recruitment partner', 'Companies come to Eyes on Jobs to fill the roles that matter most: positions that need advanced skills, leadership experience or niche expertise. Our head-hunting team searches across organisations on your behalf to find the people who will move your business forward.' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'award', 'Executive Search', 'Retained, confidential search for board-level, CXO and senior management roles, backed by market mapping and rigorous assessment.', array( 'Explore executive search', '/executive-search/' ) ),
								eoj_b_card( 'shield', 'Background Verification', 'Thorough pre-employment checks so every new hire is exactly who they say they are.', array( 'Explore verification', '/background-verification/' ) ),
								eoj_b_card( 'users', 'Senior &amp; Mid-level Hiring', 'Experienced managers and specialists for roles where skills are scarce and the right hire makes a measurable difference.', array( 'Share a requirement', '/employers/' ) ),
								eoj_b_card( 'cap', 'Campus Recruitment', 'End-to-end campus drives: college outreach, aptitude screening, interviews and offer management for graduate intakes.', array( 'Plan a campus drive', '/employers/' ) ),
								eoj_b_card( 'trend', 'Employee Development', 'Training and development programmes that build capability, raise performance and improve retention.', array( 'Talk to us', '/contact-us/' ) ),
								eoj_b_card( 'sparkle', 'Job Preparation Package <span class="eoj-tag eoj-tag--hot">New</span>', 'For job seekers: an ATS-friendly resume, interview coaching, a mock interview and salary guidance. Now [eoj_jpp_inline] incl. all taxes.', array( 'View the package', '/job-preparation-package/' ), 'eoj-card eoj-card--highlight' ),
							)
						),
					)
				),
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'New for job seekers', 'eoj-eyebrow eoj-pill eoj-pill--light' ),
									eoj_b_h( 'Complete Job Preparation Package', 2, 'eoj-title' ),
									eoj_b_p( 'Everything you need to stand out: a professionally written ATS-friendly resume, HR screening and role-specific interview preparation, a live mock interview with personal feedback, and expert guidance on salary negotiation and joining formalities.', 'eoj-lead' ),
									eoj_b_btns(
										array(
											array( 'Register now', '/job-preparation-package/register/' ),
											array( 'See full details', '/job-preparation-package/', 'outline' ),
										)
									),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_jpp_price]' ), 'eoj-promo__price' ),
						),
						'eoj-promo__cols'
					),
					'eoj-section--promo'
				),
				eoj_b_section(
					array(
						eoj_b_head( 'Why clients choose us', 'Reliability you can plan around' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'clock', 'Fast turnaround', 'Shortlists in days, not weeks, without cutting corners.', array(), 'eoj-feature' ),
								eoj_b_card( 'target', 'Right-fit candidates', 'We assess skills, attitude and culture fit, not just keywords.', array(), 'eoj-feature' ),
								eoj_b_card( 'rupee', 'Cost-effective', 'Transparent fees and fewer mis-hires mean a lower cost per hire.', array(), 'eoj-feature' ),
								eoj_b_card( 'lock', 'Confidential', 'Sensitive searches handled discreetly from start to finish.', array(), 'eoj-feature' ),
							),
							'eoj-grid-4 eoj-features'
						),
					),
					'eoj-section--tint'
				),
				eoj_cta_band(),
			)
		),
	);

	/* ----------------------------------------------------- Executive search */
	$pages['executive-search'] = array(
		'title'   => 'Executive Search',
		'excerpt' => 'Confidential, retained search for board-level and senior leadership roles.',
		'content' => implode(
			'',
			array(
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'Leadership hiring', 'eoj-eyebrow' ),
									eoj_b_h( 'The leaders who will shape your next decade', 2, 'eoj-title' ),
									eoj_b_p( 'Our executive search practice focuses on retained board-level and senior management hiring. We begin by understanding your strategy, the leadership capabilities it demands and the culture a new executive must strengthen.' ),
									eoj_b_p( 'Senior search specialists then combine their professional networks, market research and our own talent intelligence to approach proven leaders, including those who are not actively looking. Every mandate is handled with complete confidentiality, quick turnaround and a clear focus on value.' ),
									eoj_b_btns( array( array( 'Discuss a mandate', '/contact-us/' ) ) ),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_art name="executive"]' ), '' ),
						),
						'eoj-media-cols'
					)
				),
				eoj_b_section(
					array(
						eoj_b_head( 'What you can expect', 'A search built around your business' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'target', 'Strategic briefing', 'Deep discovery of your goals, the role’s success measures and the leadership profile you need.' ),
								eoj_b_card( 'globe', 'Market mapping', 'A clear view of who the best people are, where they work and what it takes to attract them.' ),
								eoj_b_card( 'chart', 'Rigorous assessment', 'Structured interviews, competency evaluation and thorough referencing on every finalist.' ),
								eoj_b_card( 'lock', 'Total confidentiality', 'Discreet outreach that protects your brand, your team and the candidates we approach.' ),
								eoj_b_card( 'clock', 'Speed with quality', 'A focused shortlist delivered quickly, with regular progress updates throughout.' ),
								eoj_b_card( 'handshake', 'Offer and onboarding', 'Support with negotiation, resignation and the critical first months in the role.' ),
							)
						),
					),
					'eoj-section--tint'
				),
				eoj_b_section(
					array(
						eoj_b_head( 'Roles we fill', 'Senior mandates across industries' ),
						eoj_b_list( array( 'Board members &amp; independent directors', 'CEO, COO, CFO, CTO, CHRO, CMO', 'Vice presidents &amp; business heads', 'Country and regional managers', 'General managers &amp; plant heads', 'Specialist and niche leadership roles' ), 'eoj-chiplist eoj-chiplist--center' ),
					)
				),
				eoj_cta_band( 'Looking for a leader who can make a difference?', 'Tell us about the role in confidence. A senior consultant will call you back within one working day.' ),
			)
		),
	);

	/* ------------------------------------------------ Background verification */
	$pages['background-verification'] = array(
		'title'   => 'Background Verification',
		'excerpt' => 'Hire with confidence. Pre-employment checks that protect your business, your team and your reputation.',
		'content' => implode(
			'',
			array(
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'Pre-employment screening', 'eoj-eyebrow' ),
									eoj_b_h( 'Because one wrong hire costs more than you think', 2, 'eoj-title' ),
									eoj_b_p( 'A single mis-hire can cost months of salary, lost productivity and damage to team morale. That is why background verification is built into how Eyes on Jobs recruits, and why we offer it as a standalone service to employers.' ),
									eoj_b_p( 'Our simple, structured process confirms that candidates are who they say they are, so you can hire quality talent quickly and with complete peace of mind.' ),
									eoj_b_btns( array( array( 'Request verification', '/contact-us/' ) ) ),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_art name="verify"]' ), '' ),
						),
						'eoj-media-cols'
					)
				),
				eoj_b_section(
					array(
						eoj_b_head( 'What we check', 'Comprehensive checks, clear reports', 'Choose the checks that suit each role. Typical verifications include:' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'briefcase', 'Employment history', 'Previous employers, designations, tenure and reason for leaving.' ),
								eoj_b_card( 'cap', 'Education', 'Degrees, certificates and institutions confirmed at source.' ),
								eoj_b_card( 'pin', 'Address', 'Current and permanent address verification.' ),
								eoj_b_card( 'user', 'Identity', 'Validation of identity documents provided by the candidate.' ),
								eoj_b_card( 'chat', 'References', 'Structured conversations with former managers and colleagues.' ),
								eoj_b_card( 'shield', 'Records check', 'Checks permitted under applicable law, with candidate consent.' ),
							)
						),
					),
					'eoj-section--tint'
				),
				eoj_b_section(
					array(
						eoj_b_head( 'How it works', 'Verification in four easy steps' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'file', 'Share details', 'Send candidate details and choose the checks you need.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'lock', 'Consent', 'We collect the candidate’s written consent, as required.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'search', 'Verify', 'Our team verifies each item directly with the source.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'check', 'Report', 'You receive a clear, easy-to-read verification report.', array(), 'eoj-card eoj-step' ),
							),
							'eoj-grid-4 eoj-steps'
						),
					)
				),
				eoj_cta_band( 'Make your next hire a safe one', 'Talk to us about one-off checks or a verification plan for all your new joiners.' ),
			)
		),
	);

	/* ------------------------------------------- Job preparation package */
	$pages['job-preparation-package'] = array(
		'title'   => 'Complete Job Preparation Package',
		'excerpt' => 'Strengthen your profile and prepare with confidence for your next career opportunity.',
		'content' => implode(
			'',
			array(
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'New · 50% off for a limited period', 'eoj-eyebrow eoj-pill' ),
									eoj_b_h( 'Get interview-ready with expert, one-to-one support', 2, 'eoj-title' ),
									eoj_b_p( 'Looking to strengthen your professional profile and prepare confidently for your next opportunity? Eyes on Jobs introduces the <strong>Complete Job Preparation Package</strong>, designed to help professionals build a stronger resume, prepare for interviews and navigate the recruitment process far more effectively.' ),
									eoj_b_p( 'Created by recruiters who screen and place candidates every day, the package shows you exactly what employers look for, and how to present yourself at your best.' ),
									eoj_b_p( 'Package fee: [eoj_jpp_inline] <span class="eoj-muted">inclusive of all taxes</span>', 'eoj-pricepara' ),
									eoj_b_btns(
										array(
											array( 'Register now', '/job-preparation-package/register/' ),
											array( 'What’s included', '#included', 'outline' ),
										)
									),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_art name="prep"]' ), '' ),
						),
						'eoj-media-cols'
					)
				),
				'<!-- wp:group {"tagName":"section","className":"eoj-section eoj-section--tint"} --><section id="included" class="wp-block-group eoj-section eoj-section--tint">' . eoj_b_group(
					array(
						eoj_b_head( 'What’s included', 'Seven steps to a stronger job search' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'file', 'ATS-friendly resume', 'A professionally designed resume tailored to your skills, experience and target roles, built to pass applicant tracking systems.' ),
								eoj_b_card( 'phone', 'HR screening preparation', 'Guidance on common telephonic screening questions and how to present your profile clearly and confidently.' ),
								eoj_b_card( 'target', 'Role-specific interview prep', 'Preparation based on your target role, industry and professional experience.' ),
								eoj_b_card( 'mic', 'Mock interview session', 'A realistic practice interview with a recruiter to sharpen your answers and build confidence.' ),
								eoj_b_card( 'chat', 'Personalised feedback', 'Clear suggestions to improve your answers, communication and overall presentation.' ),
								eoj_b_card( 'rupee', 'Salary discussion guidance', 'Help with expected CTC, salary negotiation, notice period and relocation questions.' ),
								eoj_b_card( 'handshake', 'Joining-stage guidance', 'Practical advice on handling offer discussions and completing joining formalities smoothly.' ),
							),
							'eoj-grid-4 eoj-grid--center'
						),
					),
					'eoj-wrap'
				) . '</section><!-- /wp:group -->',
				eoj_b_section(
					eoj_b_cols(
						array(
							array(
								array(
									eoj_b_p( 'Simple pricing', 'eoj-eyebrow eoj-pill eoj-pill--light' ),
									eoj_b_h( 'One fee. Everything you need.', 2, 'eoj-title' ),
									eoj_b_p( 'Our goal is to help you become better prepared, more confident and more effective throughout your job search. Register online in two minutes and pay securely by UPI, debit or credit card, net banking or wallet.', 'eoj-lead' ),
									eoj_b_list( array( 'Instant confirmation by email', 'A consultant contacts you within one working day', 'Sessions online or by phone, at a time that suits you' ), 'eoj-checklist eoj-checklist--light' ),
								),
								'',
							),
							array( eoj_b_sc( '[eoj_jpp_price]' ), 'eoj-promo__price' ),
						),
						'eoj-promo__cols'
					),
					'eoj-section--promo'
				),
				eoj_b_section(
					array(
						eoj_b_head( 'How it works', 'From registration to your new role' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'user', 'Register and pay', 'Fill in a short form and complete your secure payment online.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'file', 'Resume consultation', 'We review your profile and goals, then build your ATS-friendly resume.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'mic', 'Coaching and mock interview', 'Screening, role-specific prep and a full mock interview with feedback.', array(), 'eoj-card eoj-step' ),
								eoj_b_card( 'trend', 'Offer-stage support', 'Guidance on salary talks, notice period and joining formalities.', array(), 'eoj-card eoj-step' ),
							),
							'eoj-grid-4 eoj-steps'
						),
					)
				),
				eoj_b_section(
					array(
						eoj_b_head( 'FAQ', 'Questions, answered' ),
						eoj_b_group(
							array(
								eoj_b_faq( 'Who is this package for?', 'Freshers and experienced professionals who want a stronger resume and better interview performance, whatever their industry.' ),
								eoj_b_faq( 'How are the sessions delivered?', 'Sessions are held online or over the phone at a time agreed with you. In-person sessions at our Gurugram office can be arranged on request.' ),
								eoj_b_faq( 'How long does it take?', 'Most candidates complete the package within one to two weeks, depending on their availability.' ),
								eoj_b_faq( 'Which payment methods can I use?', 'UPI (Google Pay, PhonePe, Paytm and others), debit and credit cards, net banking and popular wallets, all through a secure payment gateway.' ),
								eoj_b_faq( 'Does the package guarantee a job?', 'No. The package focuses on career preparation, resume development and interview guidance. We do not provide or guarantee job placement, interview calls, job selection, an offer letter or employment. Final hiring decisions rest with the respective employers.' ),
							),
							'eoj-faqs'
						),
					),
					'eoj-section--tint'
				),
				eoj_b_section(
					eoj_b_group(
						array(
							eoj_b_p( '<strong>Important note:</strong> Our services focus on career preparation, resume development and interview guidance. We do not provide or guarantee job placement, interview calls, job selection, an offer letter or employment. Final hiring decisions rest with the respective employers.' ),
						),
						'eoj-note'
					)
				),
				eoj_cta_band( 'Ready to prepare for your next opportunity?', 'Register today and get the complete package for 50% off, inclusive of all taxes.' ),
			)
		),
	);

	$pages['register'] = array(
		'title'   => 'Register for the Job Preparation Package',
		'parent'  => 'job-preparation-package',
		'excerpt' => 'Complete the form below and pay securely online. Your consultant will contact you within one working day.',
		'content' => eoj_b_section(
			eoj_b_cols(
				array(
					array( eoj_b_group( eoj_b_sc( '[eoj_jpp_form]' ), 'eoj-card eoj-formcard' ), 'eoj-col-form' ),
					array(
						array(
							eoj_b_sc( '[eoj_jpp_price button="View package details" url="/job-preparation-package/"]' ),
							eoj_b_group( eoj_b_p( '<strong>Need help?</strong> Call or WhatsApp us and we will register you over the phone.' ), 'eoj-note eoj-note--small' ),
						),
						'eoj-col-side',
					),
				),
				'eoj-formcols'
			)
		),
	);

	/* ------------------------------------------------------- Submit resume */
	$pages['submit-resume'] = array(
		'title'   => 'Submit Your Resume',
		'excerpt' => 'Share your profile once. Our recruiters will match you with suitable openings, including roles that are never advertised.',
		'content' => eoj_b_section(
			eoj_b_cols(
				array(
					array( eoj_b_group( eoj_b_sc( '[eoj_resume_form]' ), 'eoj-card eoj-formcard' ), 'eoj-col-form' ),
					array(
						array(
							eoj_b_card( 'lock', 'Your data is safe', 'We only share your profile with an employer after speaking to you first.' ),
							eoj_b_card( 'sparkle', 'Want a stronger resume?', 'Our Job Preparation Package includes an ATS-friendly resume, now at 50% off.', array( 'See the package', '/job-preparation-package/' ), 'eoj-card eoj-card--highlight' ),
						),
						'eoj-col-side',
					),
				),
				'eoj-formcols'
			)
		),
	);

	/* ----------------------------------------------------------- Employers */
	$pages['employers'] = array(
		'title'   => 'Hire Talent',
		'excerpt' => 'Tell us who you need. We will send a shortlist of pre-screened, verified candidates.',
		'content' => implode(
			'',
			array(
				eoj_b_section(
					eoj_b_cols(
						array(
							array( eoj_b_group( array( eoj_b_h( 'Share your hiring requirement', 3 ), eoj_b_sc( '[eoj_requirement_form]' ) ), 'eoj-card eoj-formcard' ), 'eoj-col-form' ),
							array(
								array(
									eoj_b_card( 'clock', 'Fast shortlists', 'Pre-screened profiles in days, not weeks.' ),
									eoj_b_card( 'shield', 'Verified candidates', 'Background checks available before you make an offer.' ),
									eoj_b_card( 'users', 'Every level', 'Entry level, middle management and leadership roles.' ),
								),
								'eoj-col-side',
							),
						),
						'eoj-formcols'
					)
				),
				eoj_b_section(
					array(
						eoj_b_head( 'Services for employers', 'Choose the support you need' ),
						eoj_b_grid(
							array(
								eoj_b_card( 'award', 'Executive Search', 'Confidential leadership hiring.', array( 'Learn more', '/executive-search/' ) ),
								eoj_b_card( 'shield', 'Background Verification', 'Hire with confidence.', array( 'Learn more', '/background-verification/' ) ),
								eoj_b_card( 'cap', 'Campus Recruitment', 'Graduate hiring, end to end.', array( 'Learn more', '/services/' ) ),
							)
						),
					),
					'eoj-section--tint'
				),
			)
		),
	);

	/* ------------------------------------------------------------- Contact */
	$pages['contact-us'] = array(
		'title'   => 'Contact Us',
		'excerpt' => 'We would love to hear from you. Call, email, visit or send us a message and we will reply within one working day.',
		'content' => implode(
			'',
			array(
				eoj_b_section( eoj_b_sc( '[eoj_contact_cards]' ) ),
				eoj_b_section(
					eoj_b_cols(
						array(
							array( eoj_b_group( array( eoj_b_h( 'Send us a message', 3 ), eoj_b_sc( '[eoj_contact_form]' ) ), 'eoj-card eoj-formcard' ), '' ),
							array( eoj_b_sc( '[eoj_map]' ), '' ),
						),
						'eoj-formcols eoj-formcols--even'
					),
					'eoj-section--tint'
				),
			)
		),
	);

	/* ------------------------------------------------------------- Account */
	$pages['account'] = array(
		'title'   => 'My Account',
		'excerpt' => 'Log in or create a free candidate or employer account.',
		'content' => eoj_b_section( eoj_b_sc( '[eoj_account]' ) ),
	);

	/* ------------------------------------------------------------ Policies */
	$company = 'Eyes on Jobs';
	$email   = 'career@eyesonjobs.com';
	$legal   = 'eoj-prose';

	$pages['privacy-policy'] = array(
		'title'   => 'Privacy Policy',
		'excerpt' => 'How we collect, use and protect your personal information.',
		'content' => eoj_b_section(
			eoj_b_group(
				array(
					eoj_b_p( 'This policy explains how ' . $company . ' (“we”, “us”) collects, uses and protects the personal information you share with us through this website.' ),
					eoj_b_h( 'Information we collect', 3 ),
					eoj_b_p( 'Details you submit through our forms and accounts, such as your name, email address, phone number, city, work experience, salary expectations and resume. When you pay online, payments are processed by our payment partner (Razorpay); we do not store your card, UPI or bank details.' ),
					eoj_b_h( 'How we use it', 3 ),
					eoj_b_p( 'To respond to your enquiry, deliver the services you request, match you with suitable job opportunities, share your profile with prospective employers after speaking with you, send service updates and meet legal obligations.' ),
					eoj_b_h( 'Sharing', 3 ),
					eoj_b_p( 'We never sell your data. We share it only with employers you are being considered by, service providers who help us run this website and process payments, or when required by law.' ),
					eoj_b_h( 'Security and retention', 3 ),
					eoj_b_p( 'We use reasonable technical and organisational measures to protect your information, including encrypted connections and spam protection by Google reCAPTCHA. We keep your data only as long as needed for the purposes above.' ),
					eoj_b_h( 'Your rights', 3 ),
					eoj_b_p( 'You can ask us to access, correct or delete your information at any time by writing to <a href="mailto:' . $email . '">' . $email . '</a>.' ),
				),
				$legal
			)
		),
	);

	$pages['terms-and-conditions'] = array(
		'title'   => 'Terms and Conditions',
		'excerpt' => 'The terms that apply when you use this website and our services.',
		'content' => eoj_b_section(
			eoj_b_group(
				array(
					eoj_b_p( 'By using this website or purchasing any service from ' . $company . ', you agree to the following terms.' ),
					eoj_b_h( 'Our services', 3 ),
					eoj_b_p( 'We provide recruitment, executive search, background verification and career preparation services. Paid career services such as the Complete Job Preparation Package focus on career preparation, resume development and interview guidance. We do not provide or guarantee job placement, interview calls, job selection, an offer letter or employment. Final hiring decisions rest with the respective employers.' ),
					eoj_b_h( 'Your responsibilities', 3 ),
					eoj_b_p( 'You agree to provide accurate information, keep your account details confidential and use the website lawfully. We may suspend accounts that misuse the platform.' ),
					eoj_b_h( 'Fees and payment', 3 ),
					eoj_b_p( 'Prices are shown in Indian Rupees and include all applicable taxes unless stated otherwise. Payments are processed securely by Razorpay.' ),
					eoj_b_h( 'Liability', 3 ),
					eoj_b_p( 'We take care to keep information on this website accurate, but job listings are provided by employers and may change without notice. Our total liability for any paid service is limited to the fee paid for that service.' ),
					eoj_b_h( 'Governing law', 3 ),
					eoj_b_p( 'These terms are governed by the laws of India, and courts in Gurugram, Haryana have jurisdiction. Questions? Email <a href="mailto:' . $email . '">' . $email . '</a>.' ),
				),
				$legal
			)
		),
	);

	$pages['refund-policy'] = array(
		'title'   => 'Refund and Cancellation Policy',
		'excerpt' => 'Our policy on cancellations and refunds for paid services.',
		'content' => eoj_b_section(
			eoj_b_group(
				array(
					eoj_b_h( 'Cancellations', 3 ),
					eoj_b_p( 'You may cancel your Job Preparation Package within 48 hours of payment, provided no session has been held and no resume work has started, by emailing <a href="mailto:' . $email . '">' . $email . '</a> with your payment ID.' ),
					eoj_b_h( 'Refunds', 3 ),
					eoj_b_p( 'Approved refunds are processed to the original payment method within 7 working days. Once a session has been held or resume work has begun, the fee is non-refundable.' ),
					eoj_b_h( 'Service delivery', 3 ),
					eoj_b_p( 'All services are delivered online, by phone or at our Gurugram office. No physical goods are shipped. Your consultant will contact you within one working day of successful payment.' ),
					eoj_b_h( 'Failed or duplicate payments', 3 ),
					eoj_b_p( 'If money was deducted but your payment failed, or you were charged twice, the amount is usually reversed automatically by your bank within 5 to 7 working days. If not, contact us and we will help.' ),
				),
				$legal
			)
		),
	);

	return $pages;
}
