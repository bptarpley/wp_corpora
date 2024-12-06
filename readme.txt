=== Corpora ===
Contributors: bptarpley
Tags: Corpora
Requires at least: 4.7
Tested up to: 6.4
Requires PHP: 7
Stable tag: trunk
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

A plugin for searching, browsing, and viewing data from the Corpora Dataset Studio.

== Description ==
This plugin allows Wordpress admins to configure their site to be able to display a tabular interface for searching and browsing data from their Corpora corpus. Clicking on results in that table will also open a \"detail\" page for a given piece of content with default ways of rendering a given field in HTML.

== Installation ==
Once you\'ve installed and activated the Corpora plugin, a \"Corpora\" section should appear on the sidebar of the admin area. Click there.

### Corpora Host
The first thing you\'ll need to do is provide the hostname for an instance of Corpora (i.e. https://mycorpora.org) in the Corpora Host field. Doing so should populate the \"Corpus\" dropdown beneath it with all the open-access corpora available on that host.

### Corpus
Select your corpus from that \"Corpus\" dropdown menu, which should then populate the \"Detail Configuration\" section with information and settings about your corpus\' content types. At this point, it\'s recommended to click the \"Save Changes\" button at the bottom of the page, as the next step might require you to leave the Corpora settings page.

### Detail Page
The \"Detail Page\" is the Wordpress page you\'d like to designate for displaying individual pieces of content from your corpus. This must be configured before the various links in your search results will work. Additionally, **you must also setup Permalinks properly**. To do this, go to the \"Settings\" area of the admin sidebar, and under \"Settings\" choose \"Permalinks.\" On the \"Permalink Settings\" page that opens, select the \"Post name\" radio button under \"Common Settings\" and click \"Save Changes\" at the bottom.

Once Permalinks have been setup properly, create a page in Wordpress (using the \"Pages\" area of the admin sidebar) and give it a title (the title \"Corpus\" or \"Content\" might make most sense). Save that page. When you return to the Corpora area of the admin sidebar, you should be able to select that new page from the \"Detail Page\" dropdown box.

### Detail Configuration
The detail configuration area allows you to specify how you want your individual pieces of content to be displayed on your site. A row for each content type is provided, and for each content type you can specify the template, what fields to display, and whether to show associated content.

**Template** specifically refers to any templates configured in the Content Type Manager of Corpora. In order for a template to appear as an option, that template must also be of the \"text/html\" MIME type. When a template other than \"Default\" is selected, this plugin will ignore any settings for that content type regarding field visibility, as that will be handled by the template.

**Field Display** allows you to hide certain fields when displaying content on your detail page. This is useful for fields that may not be of interest for users of a public facing site.

**Associated Content** determines whether, below the representation of an individual piece of content on the detail page, you want display search tables for every other content type in your corpus that might refer to the content in question.

### Searches
The \"Searches\" area allows you to define tabular searches that can be placed anywhere on a Wordpress page using shortcodes. To define one, click \"Create a Search.\" A modal pop-up will appear, allowing you to configure the following:

**Search Name** is a label for the search that can be optionally displayed as a header above your search table. The name is also used for generating the shortcode needed to place the search on a page. The checkbox beneath it labelled \"Display search name?\" specifies whether the name you provide appears as a header above your search table.

The **Content Type** dropdown allows you to select a content type defined in your corpus as the primary source of content to search and display.

**Default Page Size** allows you to specify how many results appear in the table beneath your search.

**Field Options** allows you to specify which fields will show up as searchable/sortable columns that are rendered in the tabular interface below the search bar.

Once you\'ve configured your search, click the \"Save\" button on the modal to have it appear in the table listing your defined searches. **NOTE**: your search won\'t actually be saved and available for use until you also click the \"Save Changes\" button at the bottom of the page!

To cause a search to appear on a given Wordpress page, you\'ll need to add that search\'s shortcode. Note in the tabular listing of defined searches, that first column provides the shortcode needed to make that search appear. Simply copy it, and then edit the page in question. If you\'re using the default block editor for your Wordpress pages, click the \"+\" symbol to add a new block, and choose the \"Shortcode\" block. Paste the shortcode you copied from the Corpora settings page and then save the changes to your page. When visiting your page, you should now see that search appear.

### Colors
Wordpress themes do not name color variables consistently, so it\'s difficult to automatically tie into the color scheme of a theme when displaying searches and content. As such, the \"Colors\" area allows you to define four colors that will be used in various ways to style content coming from Corpora.

### Custom Styling
A given Wordpress theme may style tables and form controls in such a way as to create an undesired appearance for content coming from Corpora. The easiest way to address this is to install a custom CSS plugin, such as the \"Simple Custom CSS and JS\" plugin. This will allow you to create styling rules that will override both the theme\'s _and_ the Corpora plugin\'s styling choices.

A full styling guide is beyond the scope of this document, but here are a couple of quick examples:

**Turn table headers blue:**

    .corpora-search-table-header-cell {
        background-color: blue;
    }

**Change the font size for field labels on the detail page:**

    .corpora-detail-field-label {
        font-size: 20px;
    }

Most HTML elements used to build Corpora search tables and detailed content display are provided handy CSS classes (like the two referenced in the code blocks above) for the purpose of custom styling. To determine a class for a particular element, use your browser\'s \"inspect\" feature.
