=== Sobi Forms ===
Contributors: alesas
Tags: contact form, form builder, custom form, file upload, lead generation
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.5.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight form builder with inbox, shortcode, Gutenberg block, and vanilla AJAX.

== Description ==

Sobi Forms is a lightweight contact form plugin built for speed and simplicity. Create multiple forms, embed them anywhere with a shortcode or Gutenberg block, and keep your front-end lean.

Learn more on the official site: <a href="https://sobiforms.com">sobiforms.com</a> — features, FAQ, and the <a href="https://sobiforms.com/roadmap/">public roadmap</a>.

**Performance-first front-end**

* Vanilla JavaScript on the front-end
* ~3.7 KB CSS + JS combined (gzipped transfer; ~13.3 KB unminified source on form pages)
* Assets enqueue only when a form is rendered on the page - zero impact on other pages
* Script loaded in the footer with `defer` strategy (WordPress 6.3+)
* No global front-end CSS frameworks

**Form builder (admin only)**

* Redesigned editor for more comfort — form canvas on the left, settings sidebar on the right; save without a full page reload
* Lucide icons in the admin builder and submissions inbox (modern, consistent UI)
* Document-first drag-and-drop editor (React via WordPress `wp-element`, loaded only on the form edit screen)
* Field types: text, email, link (URL), textarea, phone, number, select, multiple choice (radio tiles), multi-option checkbox, **file upload**; layout blocks: title (section heading), paragraph (instructions)
* URL prefill: optional per-field URL parameter to pre-populate scalar fields from query strings (client-side; cache-friendly)
* Hidden fields: compact sidebar table to pass invisible data (URL params and/or static defaults); invisible on the front; always submitted; visible in inbox
* File upload: one file per field; admin picks allowed types by category (Application, Image, Text) or individual extensions; private storage under `uploads/sobiforms/`; download from the Submissions inbox (admin only). **Save to database** is required when a form includes a file field
* Field settings: number min/max; text min/max characters; show/hide label; textarea resize and max length; file max size (default 5 MB, capped by server)
* Radio and multi-checkbox options edited inline in the builder (add option, remove on hover); dropdown options in the field menu
* Multiple recipient emails per form
* After submit: inline success message or redirect to a published page
* Availability: pause a form, auto-close at a date/time, or after a set number of submissions, with a visitor message when closed

**Embedding**

* Shortcode: `[sobiforms id="3"]` or `[sobiforms slug="contact"]` (ID or slug required; slug is fixed after creation)
* Gutenberg block: **Sobi Forms Contact** with form picker
* Works with any page builder that supports shortcodes or blocks

**Submissions**

* Email notifications via `wp_mail()` (HTML)
* Optional visitor confirmation email — simple thank-you receipt to the submitter (per form, Notifications sidebar)
* Per-form delivery — email notifications and/or database storage (new forms default to inbox storage)
* Inbox with read/unread, starred, spam queue, admin notes, search and filters; split list + detail layout with resizable columns; **All / Unread / Starred** view tabs and per-form filter in the list header
* Submission source page — frozen page title and pathname at submit time in the detail sidebar (no query string; use hidden fields for UTM/campaign params)
* Dashboard widget on the WordPress admin home — recent unread submissions at a glance
* Optional Akismet spam filtering (when the Akismet plugin is active)
* Honeypot, nonce verification, rate limiting (5 submissions/hour per hashed IP)

**Security**

* Nonce on every submission
* Honeypot field
* Server-side field validation against a strict JSON schema
* Capability checks and nonces on all admin actions
* Optional Akismet integration — spam submissions quarantined when the Akismet plugin is active
* File uploads — server-side MIME validation; upload directory hardened on Apache (direct HTTP access denied); admin-only download with path verification

== Installation ==

1. Upload the `sobi-forms` folder to `/wp-content/plugins/` or install `sobi-forms.zip` from **Plugins -> Add New -> Upload**.
2. Activate **Sobi Forms** through the **Plugins** menu.
3. Go to **Sobi Forms -> Forms** and create your first form.
4. Click **Embed** in the form editor to copy the shortcode, or insert the **Sobi Forms Contact** block in the block editor.
5. Paste the shortcode into any page (Gutenberg, Elementor, Divi, widget, etc.).

== Frequently Asked Questions ==

= Does Sobi Forms slow down my site? =

On pages **without** a form, Sobi Forms adds **no** front-end CSS or JavaScript.

On pages **with** a form, only a small vanilla JS file and a minimal stylesheet are loaded (~3.7 KB gzipped combined; ~13.3 KB unminified) - no React or heavy libraries on the public site.

= Where is the form builder JavaScript loaded? =

The admin builder (~97 KB minified script, ~27 KB gzipped transfer, plus WordPress-bundled React via `wp-element`) loads **only** on **Sobi Forms -> Forms -> Edit**. It never runs on the front-end.

= Can I use Sobi Forms with Elementor, Divi, or other page builders? =

Yes. Use `[sobiforms id="3"]` or `[sobiforms slug="your-form"]`, or the Gutenberg block. Assets load when the form HTML is rendered.

= Is database storage required? =

No. Choose email notifications, **Save to database**, or both in the form editor’s settings sidebar. New forms default to inbox storage; turn off **Save to database** if you only want email.

= Does Sobi Forms include reCAPTCHA? =

No. Sobi Forms uses a honeypot, WordPress nonces, and rate limiting. reCAPTCHA is intentionally out of scope to keep the plugin lightweight.

= Does Sobi Forms work with Akismet? =

Yes. When the Akismet plugin is installed and configured, enable **Use Akismet to filter form spam** under **Sobi Forms → Resources → Usage** (on by default). Spam submissions are quarantined in the Submissions inbox, notification emails are skipped, and visitors still see a normal success message.

= Can I pause a form or close it after a deadline? =

Yes. In the settings sidebar under **Availability**, pause submissions immediately or set a **Schedule close date** (your site timezone). Visitors see your unavailable message instead of the form. Cached pages may still show an open form until the cache refreshes.

= Can I disable the plugin stylesheet and use my theme styles? =

Yes. Use the `sobiforms_enqueue_front_assets` filter to disable CSS while keeping AJAX submission.

= Where can I suggest features or see what is planned? =

Visit <a href="https://sobiforms.com">sobiforms.com</a> and the <a href="https://sobiforms.com/roadmap/">roadmap</a> to follow upcoming releases and submit ideas. For bugs and support, use the WordPress.org support forum (linked under **Sobi Forms → Resources → Feedback**).

== Privacy Policy ==

Sobi Forms processes data submitted through your forms. Per form you choose how submissions are delivered:

* **Email notifications** - when enabled and recipient addresses are set, field values are sent via `wp_mail()`.
* **Visitor confirmation** - when **Visitor email confirmation** is enabled, the submitter may receive a simple thank-you at the address from the selected email field (no submitted field values in that email).
* **Database storage** - when **Save to database** is enabled (default for new forms in the builder), submissions are saved in custom tables on your site (`wp_sobiforms_submissions`, `wp_sobiforms_forms`). Each form has its own retention setting (auto-delete after N days). You can use inbox-only delivery with no email.
* **Hashed IP** - when storage is enabled, a one-way SHA-256 hash of the visitor IP is stored with each submission for abuse prevention. Raw IP addresses are not stored.
* **Rate limiting** - a transient keyed by hashed IP limits submissions to 5 per hour. Transients expire automatically.
* **Admin notes** - internal notes on submissions are stored in your database and never shown on the front-end or included in emails.
* **No tracking** - Sobi Forms does not connect to third-party analytics or advertising when processing form submissions.
* **Optional Akismet** - if you enable Akismet spam filtering and the Akismet plugin is active, submission content may be sent to Akismet’s service for spam checks.
* **No data sent to the plugin author** - form submissions stay on your server and mail server. The **Feedback** settings tab links to the WordPress.org support forum and <a href="https://sobiforms.com/roadmap/">sobiforms.com/roadmap</a> only if you choose to open them.

Site owners are responsible for their privacy policy and lawful basis for collecting visitor data.

== Licenses for Third-Party Resources ==

This plugin bundled resources covered by their own respective licenses:
* Lucide Icons - https://lucide.dev
  License: ISC (https://lucide.dev/license)
  Copyright (c) Lucide Contributors

== Screenshots ==

1. Form builder - drag-and-drop canvas with settings sidebar and Embed shortcode.
2. Submissions inbox - split list and detail panels, All/Unread/Starred tabs, filters, read/unread and starred markers.
3. Gutenberg block - pick a form from the dropdown in the editor.
4. Front-end form - minimal markup, AJAX feedback after submit.

== Changelog ==

= 1.5.1 =
* **Security** — File upload: removed archive and web-active formats from the allowed picker (zip, rar, gzip, svg, html, xml).

= 1.5.0 =
* **Form builder** — **Title** and **Paragraph** layout blocks for section headings and instructions (not collected on submit).
* **URL parameter prefill** — optional per-field query-string prefill for text, email, phone, link, number, and long text (client-side; cache-friendly).
* **Hidden fields** — compact sidebar table to pass invisible data (URL params and/or static defaults); invisible on the front; always submitted; visible in inbox.
* **Availability** — close form automatically after a set number of submissions.
* **File upload** — one file per field; pick allowed types by category (Application, Image, Text) or individual extensions (default 5 MB cap); secure server validation; files stored privately under `uploads/sobiforms/`; download from the Submissions inbox (admin only). Save to database is required when a form includes a file field.
* **Inbox** — see which page a submission came from (frozen page title and pathname in the detail sidebar). Useful when one form is embedded on multiple pages. Query strings (UTM, etc.) are not captured — use hidden fields. Older submissions show “Not recorded”.
* **Performance & reliability** — form loading and submission are more robust on cached sites, with a smoother experience for visitors.
* **Admin speed** — builder editing and submissions inbox are noticeably faster on larger forms and busier sites.

= 1.4.3 =
* **Form builder** — settings sidebar sections collapse and expand correctly again (1.4.2 kept them visually open in the WordPress admin).

= 1.4.2 =
* **Form builder** — fix settings (save to inbox, emails, pause, after-submit, etc.) being reset when saving with a collapsed sidebar section.
* **Form builder** — new fields are optional by default (not required).

= 1.4.1 =
* **Submissions inbox** — full-page layout (no card frame); **Spam** view tab after Starred; form filter as a dropdown button with filter icon; search field styling polish; more comfortable list row spacing.
* **Submissions inbox** — starred marker uses filled yellow star.
* Form builder — settings sidebar toggles fully clickable.

= 1.4.0 =
* **Dashboard widget** — unread submissions on the WordPress admin home screen.
* **Visitor confirmation email** — optional per-form thank-you to submitters (Notifications sidebar).
* **Submissions inbox** — split layout (list + detail), resizable columns, pagination, bulk actions, Gmail-style toolbar; **All / Unread / Starred** view tabs; form filter dropdown in the list header; spam via more menu; community links in the empty detail panel.
* Form emails use the **site name** as sender instead of "WordPress".

= 1.3.0 =
* Form builder — redesigned edit screen for more comfort: canvas plus settings sidebar, clearer toggles, and save without a full page reload.
* New **Link** field type — collect website or profile URLs with http/https validation.

= 1.2.0 =
* Form availability — pause or auto-close by date/time (site timezone).
* Configurable unavailable message when the form is not accepting submissions.
* Akismet spam filtering (global setting, spam queue in Submissions).
* Submissions inbox — Spam filter, mark as spam / not spam.
* Submissions — auto mark as read on open; Mark all as read.
* Form improvements — field min/max (number, text); multiple choice tiles; multi-option checkboxes with inline option editing in the builder.

= 1.1.0 =
* Custom submit button text per form (editor preview + front-end).
* Field settings: show/hide label, textarea resize toggle, max character limit.
* Per-form database storage and retention (moved from global settings).
* Submissions inbox always available in admin.
* Form builder UX polish — context menus, field contrast, layout fixes.
* Settings: removed global Privacy & Storage tab; Feedback links to WordPress.org forum and public roadmap.

= 1.0.0 =
* Initial release.
* Multi-form builder with shortcode `[sobiforms slug="…"]` / `[sobiforms id="…"]` and Gutenberg block `sobiforms/contact`.
* Field types: text, email, textarea, phone, number, select, radio, checkbox.
* Multiple recipient emails, post-submit message or redirect.
* Optional DB storage, submissions inbox with notes and filters.
* Prefix `sobiforms_` throughout (WordPress.org coding standards).
* Conditional front-end assets - load only when a form is rendered.

== Upgrade Notice ==

= 1.5.1 =
Security hardening for file uploads: archive and web-active formats removed from the allowed picker. Re-save a form if its file field had only removed types selected.

= 1.5.0 =
Builder upgrade: layout blocks, URL prefill, hidden fields, submission cap, and file upload. Existing forms are unchanged until you enable these in the builder.

= 1.4.3 =
Fixes the form builder settings sidebar so sections can be collapsed again. No data changes.

= 1.4.2 =
Important builder fix: form settings no longer reset when sidebar sections are collapsed on save. New fields default to optional. Re-check forms you edited on 1.4.0–1.4.1 if save to inbox or notification settings looked wrong.

= 1.4.1 =
Inbox polish: full-page layout, Spam tab in the header, form filter dropdown, and starred icon fill. No settings or data changes.

= 1.4.0 =
Split inbox with All/Unread/Starred tabs, dashboard unread widget, visitor confirmation emails, Lucide admin icons, and shortcode now requires id or slug. Existing embeds with id or slug are unchanged.

= 1.3.0 =
A more comfortable form editor, new Link (URL) field, and smoother save. Existing forms and submissions are unchanged.

= 1.2.0 =
Pause or auto-close forms, Akismet spam queue, inbox polish, choice-tile multiple choice, multi-option checkboxes, and field min/max settings. Existing forms stay open until you enable availability settings.

= 1.1.0 =
Per-form storage settings, submit button customization, and builder improvements. Global save/retention options migrate to each form on upgrade.

= 1.0.0 =
Initial public release of Sobi Forms.
