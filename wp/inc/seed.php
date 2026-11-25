<?php
// One-time seed on theme activation: menus, Site settings, and a Home page built from the prototype copy.
// Guarded by the myconsult_seeded option; delete that option to re-run (it adds a second Home page).

// ACF block data is stored flat, like postmeta: name => value, _name => field key, repeaters as name_0_sub.
function mc_seed_data($fields, $values, $prefix = '') {
  $out = [];
  foreach ($fields as $f) {
    if (!array_key_exists($f['name'], $values)) continue;
    $v = $values[$f['name']]; $n = $prefix . $f['name'];
    $out[$n] = $f['type'] === 'repeater' ? count($v) : $v;
    $out["_$n"] = $f['key'];
    if ($f['type'] === 'repeater') foreach ($v as $i => $row) $out += mc_seed_data($f['sub_fields'], $row, "{$n}_{$i}_");
  }
  return $out;
}

function mc_seed_block($slug, $values) {
  $data = mc_seed_data(acf_get_fields('group_' . str_replace('-', '_', $slug)), $values);
  return serialize_block(['blockName' => "acf/$slug", 'attrs' => ['name' => "acf/$slug", 'data' => $data, 'mode' => 'preview'], 'innerBlocks' => [], 'innerHTML' => '', 'innerContent' => []]);
}

function mc_seed_image($file) {
  require_once ABSPATH . 'wp-admin/includes/image.php';
  $up = wp_upload_dir(); $dest = wp_unique_filename($up['path'], basename($file));
  copy($file, "$up[path]/$dest");
  $id = wp_insert_attachment(['post_mime_type' => wp_check_filetype($dest)['type'], 'post_title' => pathinfo($dest, PATHINFO_FILENAME), 'post_status' => 'inherit'], "$up[path]/$dest");
  wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, "$up[path]/$dest"));
  return $id;
}

$link = fn($title, $url = '#') => ['title' => $title, 'url' => $url, 'target' => ''];

// ---------- menus ----------
$locations = [];
foreach ([
  'primary'          => ['Header', ['About', 'Solutions', 'Blog', 'Case Studies']],
  'footer-solutions' => ['Footer: solutions', ['Business Process Automation', 'Accounting & Financial Solutions', 'Payroll & HR Solutions']],
  'footer'           => ['Footer: links', ['About', 'Blog', 'Case Studies']],
  'legal'            => ['Footer: legal', ['Privacy Policy', 'Terms & Conditions']],
] as $loc => [$name, $items]) {
  $menu = wp_get_nav_menu_object($name);
  $id = $menu ? $menu->term_id : wp_create_nav_menu($name);
  if (!$menu) foreach ($items as $t) wp_update_nav_menu_item($id, 0, ['menu-item-title' => $t, 'menu-item-url' => '#', 'menu-item-status' => 'publish']);
  $locations[$loc] = $id;
}
set_theme_mod('nav_menu_locations', $locations);

// ---------- site settings ----------
$address = 'D-12-3A, Menara Mitraland, Kota Damansara, 47810 Petaling Jaya, Selangor';
foreach ([
  'header_cta' => $link('Speak to us'), 'cta_title' => "Book an\nAppointment", 'cta_address' => $address, 'cta_button' => $link('Speak to us'),
  'phone' => '03-7624 1906', 'email' => 'info@myconsult.com.my', 'contact_address' => $address, 'copyright' => 'My Consult Sdn Bhd.',
] as $k => $v) update_field("field_options_$k", $v, 'option');

// ---------- home page ----------
$placeholder = 'Placeholder: answer to come.';
$faq = [
  ['Should I choose AutoCount or SQL Accounting?', 'It depends on how you sell. Retail and F&B businesses selling direct to consumers usually go to AutoCount, for its multi-outlet handling and integrated POS. Smaller operations that need straightforward accounting usually go to SQL Accounting.'],
  ['How much does AutoCount or SQL Accounting cost in Malaysia?', 'A single-company SQL Accounting package starts from RM1,499, which includes the software and half a day of basic training. AutoCount starts from RM2,499. These are starter packages. A full implementation — database setup, proper training and form customisation — starts from RM2,000 on top of the software.', true],
  ['How long does an accounting software implementation take?'],
  ['What makes an implementation take longer than expected?'],
  ['What does an accounting software implementation actually involve?'],
  ['What is included in outsourced accounting?'],
  ['Do you handle payroll as well as accounting?'],
  ['What does e-Invoice setup involve?'],
  ['Can you handle e-Invoice submission for us?'],
  ['Is the financial health report included?'],
];

$content = implode("\n\n", [
  mc_seed_block('hero', [
    'eyebrow' => 'Accounting Consultancy',
    'title' => 'Accounting Powered by Intelligence.',
    'text' => 'From accounting and payroll to automation and advisory, we help businesses simplify financial operations, improve reporting, and make better-informed decisions.',
    'arc_left_label' => 'Accounting', 'arc_right_label' => 'A.I.',
  ]),
  mc_seed_block('about-intro', [
    'title' => "We help businesses simplify\nfinancial operations, improve reporting, and\nmake better-informed decisions.",
    'text' => 'Since 2003, Myconsult has supported Malaysian SMEs with accounting software implementation, support, outsourced accounting and payroll. With over 1,000 implementations completed, we bring both technical and accounting experience to every business we work with. Our experience across multiple accounting systems allows us to recommend solutions based on what fits your business best and not simply what we sell.',
    'button' => $link('About Myconsult'),
  ]),
  mc_seed_block('logos', [
    'label' => 'Trusted by more than 100+ companies',
    'logos' => [
      ['logo' => '', 'name' => 'mac city', 'gap' => 92], ['logo' => '', 'name' => 'The Japan Club of Kuala Lumpur', 'gap' => 92],
      ['logo' => '', 'name' => 'Reapfield', 'gap' => 86], ['logo' => '', 'name' => 'machines', 'gap' => 68],
    ],
  ]),
  mc_seed_block('services', [
    'chip' => 'Services',
    'title' => 'What Myconsult does for Malaysian SMEs',
    'text' => 'Two divisions, run by one team: accounting software implemented properly, and accounting and payroll handled monthly on your behalf.',
    'slides' => [
      [
        'title' => 'AutoCount & SQL Accounting — chosen, implemented, supported',
        'text' => 'We are an authorised dealer for both AutoCount and SQL Accounting, so the recommendation comes before the sale. Our accounting team also runs client books in Xero and QuickBooks daily, which means we can tell you how each system behaves when someone actually has to close a month in it.',
        'points' => array_map(fn($t) => ['text' => $t], [
          'Retail and F&B businesses selling direct to consumers usually go to AutoCount, for its multi-outlet handling and integrated POS.',
          'Smaller operations that need straightforward accounting usually go to SQL Accounting.',
          'Businesses that would rather not invest in hardware go to a cloud option — AutoCount Cloud, QuickBooks, or Xero.',
          'Software from RM1,499 (SQL Accounting, single company, with half-day basic training) or RM2,499 (AutoCount). Full implementation from RM2,000.',
        ]),
        'button' => $link('Compare AutoCount and SQL Accounting'),
        'case_label' => 'Case study',
        'case_text' => 'A Selangor private college was recognising term fees as income on collection, distorting every period. We introduced deferred revenue and now provide monthly management accounts — reporting is consistent, tax is predictable, and budgeting works. With us since 2021.',
      ],
      ['title' => 'Accounting & payroll — handled monthly, on your behalf', 'text' => 'Placeholder: copy for the accounting and payroll division to come.', 'points' => [], 'button' => '', 'case_label' => 'Case study', 'case_text' => ''],
    ],
  ]),
  mc_seed_block('case-studies', [
    'chip' => 'Case studies',
    'title' => 'Transforming Finance Operations.',
    'cases' => [[
      'image' => mc_seed_image(get_theme_file_path('assets/case-study-1.jpg')),
      'title' => 'Six-outlet retail group, Peninsular Malaysia',
      'text' => 'Every outlet sent in an Excel sheet, which the accounts team keyed manually into ABSS. Data entry alone took two to three weeks, and management reports took another two weeks after that — numbers reached management more than a month after period close. Stock levels by outlet were not visible at all.',
      'solution' => 'We recommended AutoCount for its multi-outlet POS capabilities and migrated them from ABSS',
      'results' => 'Sales now sync daily, manual rekeying is eliminated, and management accounts are ready by mid-month.',
      'link' => $link('View case study'),
    ]],
  ]),
  mc_seed_block('faq', [
    'title' => "Frequently Asked\nQuestions.",
    'items' => array_map(fn($q) => ['question' => $q[0], 'answer' => $q[1] ?? $placeholder, 'open' => !empty($q[2])], $faq),
  ]),
]);

$home = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_content' => wp_slash($content)]);
update_option('show_on_front', 'page');
update_option('page_on_front', $home);
update_option('myconsult_seeded', 1);
