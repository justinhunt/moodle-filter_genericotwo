# Generico Two Filter

Generico Two (G2) is a Moodle filter that allows site administrators to define custom templates consisting of HTML, JavaScript, and CSS. These templates can be embedded into Moodle content using simple text tags. It is designed to be a more modern and flexible successor to the original Generico filter.

## Requirements

*   Moodle 4.1 or later (compatible with standard Moodle releases).
*   PHP 8.0 or later.

## Usage

1.  **Define Templates:** Go to `Site Administration > Plugins > Filters > Generico Two`.
2.  **Add a Template:** Click "Add template".
    *   **Template Key:** A unique identifier for your template (e.g., `youtube`).
    *   **Content:** The HTML structure of your template. Use mustache-style variables like `{{VIDEO_ID}}`.
    *   **CSS Styles:** Check the `Custom CSS` or `Import CSS URL` fields to add styling.
    *   **JS Content:** Add JavaScript to control the template's behavior.
    *   **Variable Defaults:** Define default values for your variables, e.g., `width=500,height=300`.
3.  **Embed in Content:** Use the G2 tag in any Moodle text area (forum post, page, label, etc.):
    `{G2:type=templatekey,param1=value1,param2=value2}`

    Example: `{G2:type=youtube,videoid=AbCdEfGh}`

## Variables and Properties

G2 supports dynamic variable substitution:

*   **Template Parameters:** Passed in the tag, e.g., `videoid` in `{G2:type=youtube,videoid=123}`.
*   **User Properties:** `{{USER:firstname}}`, `{{USER:email}}`, `{{USER:picurl}}`, etc.
*   **Course Properties:** `{{COURSE:fullname}}`, `{{COURSE:shortname}}`, `{{COURSE:id}}`.
*   **URL Parameters:** `{{URLPARAM:id}}` (fetches `id` from the page URL).
*   **Datasets:** Fetch data from the database using SQL queries defined in the template settings.

## Installation

1.  Download the `filter_genericotwo` plugin.
2.  Upload the folder `genericotwo` to your Moodle installation's `filter/` directory.
3.  Visit `Site Administration > Notifications` to trigger the installation.
4.  Enable the filter in `Site Administration > Plugins > Filters > Manage filters`.

## License

GNU GPL v3 or later.
