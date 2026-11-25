# MyConsult website

Marketing site for MyConsult, a Malaysian accounting consultancy. Live on WordPress at
https://myconsult.bear-demo.com (staging/demo host). One page so far: Home.

## How the project is set up

There are two copies of the site, and the work flows one way:

1. **Static prototype** (`index.html`, `css/`, `js/`, `assets/`): the design source. Build and test changes here first.
2. **WordPress theme** (`wp/`): the same page split into ACF blocks so the client can edit it.
   `wp/build.sh` zips `wp/` together with the prototype's `css/`, `js/` and `assets/` into `dist/myconsult.zip`.

CSS and JS exist **only** in `css/` and `js/`; the theme has no copy of them. Section markup is the exception: it lives in two places,
`index.html` and the theme's `render.php` / `parts/*.php`, so a markup change must be made in both.

Content (text, images, links) does **not** live in the repo. It lives in the WordPress Home page's blocks and the
"Site settings" options page, and the client edits it there.

## Repo map

```
index.html                 prototype page; each <section> starts with a comment naming its ACF block and fields
css/style.css              all styles (desktop, vw-based, see Conventions)
js/main.js                 Lenis smooth scroll, GSAP text/section reveals, logo marquee, service-card animations
js/arcs.js + js/orb.js     hero: three.js particles + 3D orb (ES modules), scroll-pinned hero
assets/                    logo, orb model (.glb), footer orb, case image
wp/style.css               theme header only
wp/functions.php           enqueues, menus, block registration, options page, template helpers (mc_btn, mc_arrow, mc_menu, mc_anchor)
wp/header.php, footer.php  header and footer (content from Site settings + 4 menus)
wp/index.php               renders the_content (the page's blocks)
wp/blocks/<name>/          block.json + render.php, one per section
wp/parts/visual-*.php      the decorative dashboard-card pairs used by the services cards
wp/acf-json/               ACF field groups (loaded automatically by ACF from the theme)
wp/inc/seed.php            one-time content seed on first theme activation (already ran on the live site)
wp/build.sh                builds dist/myconsult.zip
dist/myconsult.zip         last built theme (build output)
```

## WordPress setup on the live site

- Theme: `myconsult` (this repo). Plugin for fields: **Secure Custom Fields** (the free ACF fork; it includes
  repeaters, options pages and ACF blocks). Also active: SVG Support, Custom CSS & JS, Insert PHP Code Snippet,
  Disable Comments, a security headers plugin, Imunify.
- No FTP or server access was used. Deploys are through wp-admin only (see Deploying).
- Home page = static front page, built entirely from the blocks below.

### Blocks and fields

| Block (`acf/...`) | Fields |
|---|---|
| `hero` | eyebrow, title, text, arc_left_label, arc_right_label (side cards are decorative, hard-coded) |
| `about-intro` | title (line breaks kept), text, button (link) |
| `logos` | label, logos (repeater: logo image, name, gap in design px) |
| `services` | chip, title, text, slides, labelled "Cards" (repeater: visual select, title, points repeater, button) |
| `case-studies` | chip, title, cases (repeater: image, title, text, solution, results, link) |
| `faq` | title (line breaks kept), items (repeater: question, answer, open toggle) |

- **Site settings** (options page): header_cta, cta_title, cta_address, cta_button, phone, email, contact_address,
  copyright (the year is added automatically).
- **Menus** (Appearance > Menus): Header, Footer: solutions, Footer: links, Footer: legal. All links are `#` for now.
- Services card visuals: `dashboard` (accuracy + revenue), `payroll` (payroll processed + employee satisfaction),
  `automation` (automation impact + time saved). Their numbers are decorative and hard-coded in `wp/parts/`.
- Logos: upload **white logos on transparent background, exported at 3x**. Each displays at a third of its pixel height.

## Conventions

- **Sizing:** the design is 1440 px wide. Every size is written as `design px / 1440 * 100vw` and commented with the
  design px value, e.g. `width: 13.611vw; /* 196 */`. Keep doing this.
- **Fonts:** Instrument Serif (headings, `.h1–.h3`), Inter Tight (body/UI, `.body`, `.h4`, `.button_label`),
  Space Mono (labels, chips, numbers). Loaded from Google Fonts.
- **Block names** use hyphens (`about-intro`). WordPress block names can't contain underscores.
- **ACF field keys** follow `field_<group>_<name>[_<sub>]` (e.g. `field_services_slides_points_text`). Never rename
  an existing key or field name; existing content is stored against them. Add new fields instead.
- **Editing fields:** edit the JSON in `wp/acf-json/` (bump `modified`), rebuild and deploy. Editing field groups in
  wp-admin writes to the server's copy, which the next theme upload overwrites. If someone changed fields in wp-admin,
  use ACF's sync/export first.
- **One block per section**, so the client can duplicate and reorder sections with Gutenberg's native tools.
- **Comments:** each `<section>` in `index.html` carries a comment listing its block and fields. Keep it accurate.

## Making a change

1. Change the prototype (`index.html`, `css/`, `js/`). Serve it locally (`python3 -m http.server` in the repo root) and check it.
2. If markup changed, mirror it in `wp/blocks/<name>/render.php` or `wp/parts/`. If fields changed, update `wp/acf-json/`.
3. Check in a browser at 1440x900, including scrolling, reloading mid-page and the animations. Playwright screenshots work well for this.
4. `wp/build.sh`, then deploy.
5. Update content in WordPress if the change needs new content.

## Deploying

1. Run `wp/build.sh`. It writes `dist/myconsult.zip` (about 3.8 MB).
2. In wp-admin: Appearance > Themes > Add New > Upload Theme, choose the zip, then **"Replace installed with uploaded"**.
3. Content is unaffected by theme uploads. `inc/seed.php` only runs on first activation and is guarded by the
   `myconsult_seeded` option, so it won't re-run. Don't delete that option, or the seed creates a second Home page.
4. Cache busting: CSS/JS `?ver=` comes from each file's modification time (`mc_ver()`), so no version bump is needed.
5. Check the live site after deploying.

Content can be edited by hand in the block editor, or scripted through the WP REST API from a logged-in session
(nonce `wpApiSettings.nonce` in wp-admin). ACF block data in `post_content` is stored flat, postmeta-style:
`name`, `_name` = field key, repeaters as `name` = row count plus `name_0_sub` / `_name_0_sub`.

## Animation rules (learned the hard way)

- **Don't use `once: true` on ScrollTriggers.** Each one deletes itself when it fires. Reloading mid-page fires many at
  once during ScrollTrigger's refresh loop, which crashed it and stopped `main.js` partway. The default
  `toggleActions` already plays once without reversing.
- **Per-letter CSS blur is GPU-expensive.** Every blurred element is its own GPU pass per frame, so dozens of letters
  blurring at once drops scrolling to about 30 fps on an M1. Measured fixes, currently **not applied**, held for a
  later performance pass:
  - blur per word instead of per letter
  - keep not-yet-revealed letters only `visibility: hidden` (no transform or blur) until their tween starts
  - revert SplitText once a reveal finishes
  - cap the hero canvas DPR at 1.5
  - precompile the particle shaders (`renderer.compile`) to avoid a roughly 90 ms stall on first scroll
- Services body text (`.services .body`) is deliberately **not** animated (client request).
- The hero pin is created in `js/arcs.js`, which loads after three.js from the CDN. See Known issues.

## Known issues and open items

- **No mobile/tablet layout.** Everything scales with viewport width from the 1440 design; there are no breakpoints yet.
- **Reloading mid-page can land at the wrong position**, occasionally leaving reveals below unplayed. Cause: the hero
  pin (which adds a screen of height) is created only after three.js downloads, after the browser has restored the
  scroll position. Suggested fix: create the pin in `main.js` straight away and have `arcs.js` reuse it.
- **Scroll smoothness:** see the animation rules above. The work is measured but not applied.
- **Admin bar:** when logged in, the WP admin bar covers the fixed header. Visitors aren't affected.
- **Placeholder content:**
  - all menu and button links are `#`
  - services card 3 ("e-Invoice and MyInvois") bullets 2–4 were written from earlier copy, because the mock repeated
    card 1's bullets; confirm with the client
  - the hero side cards and services dashboard numbers are illustrative
- **Header logo** is the theme asset `assets/myconsult-logo.svg`. Custom-logo support isn't wired up.
- `orb.js` loads `lil-gui` for a debug panel (open the page with `?lab`); it's imported on every hero page load.
- `dist/myconsult.zip` is committed. Either rebuild it before each deploy or gitignore it; don't hand-edit it.

## Access

No credentials are stored in the repo. WordPress admin access comes from the site owner.
Design files: Figma "[WEB] MyConsult" (page "Rework" holds the current homepage frames). Use your own Figma access token, and
never commit one.
