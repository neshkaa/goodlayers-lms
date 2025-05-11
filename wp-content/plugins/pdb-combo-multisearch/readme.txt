=== Participants Database Combo Multi Search ===
Contributors: xnau
Donate link: https://xnau.com/work/wordpress-plugins/
Requires at least: 5.8
Tested up to: 6.7.1
License: GPLv3
License URI: https://wordpress.org/about/gpl/
Tags: supporter, member, volunteer, database, sign up form, survey, management, non-profit, political, community, organization, mailing list, team, records

Adds fully-configurable multi-mode search the the Participants Database WordPress plugin.

== Description ==
This plugin adds the ability to filter a Participants Database list with one or both of two different type of search control.

= Multi-Search =
This provides a separate search control for multiple fields, so that several different search criteria can be applied to the search. The search control for each form element is approtiate to the type of element it is: text and text areas use a text search, dropdowns and radio button fields use dropdowns and radio selectors to select the search terms, etc.

Multiple-choice search controls can be configured to show those options that are defined for the field or they can be populated by data from the records. This is so that those selections will be certain to make a match.

= Combo Search =
Combo search allows multiple fields to be searched by a single text input. Which fields are added to the search is configurable. This presents the simplest possible user interface. The text input can also have a \"autosuggest\" pop-up to help user make a selection.

German translation by Markus Hermann (HessenHub)

== Installation ==
1. Download the zip file
2. Click on "Add New" in the plugins menu
3. At the top of the "Add Plugins" page find and click the "Upload Plugin" button
4. Select the zip file on your computer and upload it
5. The plugin will install itself. Click on "activate" to activate the plugin

== Changelog ==

= 2.7.4 =
* security tweak on unseralizations
* php 8.2 compatibility

= 2.7.3 =
* new Whole Word Strict Matching option to force match with entire contents of db field
* fixed issue with search phrases from the autosuggest not returning results
* more efficient caching of autosuggest terms
* update the French translations

= 2.7.2 =
* fixed issue with combo search and commas when using non-phrase search mode
* php 8.2 compatibility fixes

= 2.7.1 =
* added script enqueue check to make loading more reliable

= 2.7 =
* fixed bug when using a database dropdown UI type for a text field
* improved options for radio button fields

= 2.6.11 =
* fixed issue with single-letter search terms getting dropped

= 2.6.10 =
* added German translation
* option-type fields in multisearch are not limited by min-length settings

= 2.6.9 =
* fixed issue with inline autocomplete throwing JS error on search

= 2.6.8 =
* a compatibility update for the Chosen Element plugin

= 2.6.7 =
* fixed issue with chosen selectors not getting initialized
* expanded support for using the "chosen" selector in the search control
* compatibility with the Participant Log plugin and autosuggest terms

= 2.6.6 =
* corrected text domain for translated strings

= 2.6.5 =
* improved handling of autosuggest terms from multiselects
* php 8.1 compatibility

= 2.6.4 =
* shortcode filter values now pre-fill search inputs
* use of the search_fields attribute with multiple list shortcodes now working

= 2.6.3 =
* fixed issue with high ascii characters getting encoded in the search term

= 2.6.2 =
* added preference to allow use of same field in both search modes

= 2.6.1 =
* fixed minor issue with unicode characters in the search input

= 2.6 =
* now using the xnau updater plugin required for PDB version 2.3

= 2.5.1 =
* fixed bug when using "suppress" with regular list search

= 2.5 =
* support for PDB calculated fields
* global minimum text search term length setting
* global "require all search fields" setting
* numerous minor bug fixes
* compatibility with php 8.1

= 2.4.9 =
* several fixes to the multifields UI preventing duplicate fields
* string combine fields now searchable in multi search

= 2.4.8 =
* fixed issue with single date fields getting treated as a range
* multi field definition attributes now completely override the main field definition attributes

= 2.4.7 =
* fixed issue with multiselect searches only searching on the first term

= 2.4.6 =
* allow for general term search on multiselect field when using combo search

= 2.4.5 =
* fixed issue with combo searches on "link" fields not returning partial matches

= 2.4.4 =
* prevent duplicate search term when using search URL
* improved caching of database dropdown values

= 2.4.3 =
* fixes issue with fields still available to add after adding to multifields
* removed call to undefined method

= 2.4.2 =
* updates the language template

= 2.4.1 =
* new field selection UI for the Combo Search configuration
* fixed issue with filter attributes in the list shortcode and multi-search
* added fieldname-based class to field elements in the responsive template

= 2.4 =
* shortcode list filters now applied to the Combo search autosuggest list
* date range searches that cross 0 epoch are now correctly returned

= 2.3.7 =
* added minimum autocomplete length setting

= 2.3.6 =
* sort control now available when using the search shortcode with the built-in multisearch template

= 2.3.5 =
* new preference for handling default values
* fixed issue in multisearch field configuration with field deleted from main plugin

= 2.3.4 =
* new update for the POT

= 2.3.3 =
* updated the POT

= 2.3.2 =
* fixed end range date for ranged date searches

= 2.3.1 =
* making sure the WP timezone is used for date conversions

= 2.3 =
* reworked date field searches for more accurate matches and support for partial dates

= 2.2.11 =
* fixed issue with inline autosuggest terms when suppress was set in the shortcode
* fixed translation filter on field titles in responsive template

= 2.2.10 =
* compatibility fix for IE11

= 2.2.9 =
* fixed issue when using more than one chosen selector in the multi search

= 2.2.8 =
* autosuggest caches cleared on CSV import
* fixed issue with certain special characters in multi search whole-word-mode

= 2.2.7 =
* fixed support for chosen selectors
* minor ui improvements
* search form clear selects "any" options if available

= 2.2.6 =
* added support for the search_fields attribute in the pdb_search shortcode

= 2.2.5 =
* make sure the search control element ids are unique

= 2.2.4 =
* fixed issue with search clear not working on checkbox and radio button controls
* fixed missing search error message
* search and clear button disabled while awaiting results

= 2.2.3 =
* better handling of term matches with selectors
* new radio/other field options

= 2.2.2 =
* fixed issue with search terms on selector fields that contain word-boundary characters

= 2.2.1 =
* fixed issue with multiselect or mode setting

= 2.2 =
* further optimization of autosuggest code
* multisearch clear now sets selectors to "any" value if available

= 2.1.10 =
* fixed error when autocomplete is disabled

= 2.1.9 =
* fixed display issues with checkbox, radio button type fields in the multisearch

= 2.1.8 =
* shortcode filter no longer overridden by search on same field
* autosuggest terms now respect shortcode filter
* uninstall handler added

= 2.1.7 =
* text fields with db dropdown now include blank option
* remote searches working on all browsers
* searches on multiselect fields now working as expected

= 2.1.6 =
* fixed issue with multisearch field delete
* fixed remote searches
* hidden dynamic fields don't show default value

= 2.1.5 =
* combo search "any" mode now works as expected in filter mode
* multilingual compatibility fixes

= 2.1.4 =
* fixed: commas splitting terms in multiselect fields
* dropdown options now alphabetized with alpha sort preference
* blank "any" option title allowed

= 2.1.3 =
* compatibility with chosen dropdown plugin
* multifields delete and reordering issue fixes

= 2.1.2 =
* better support for multiselect dropdown fields
* fixed missing optgroup bug 

= 2.1.1 =
* fixed bug that prevented adding new multifields

= 2.1 =
* improved operation with search URLs
* better handling of custom form elements in the search control

= 2.0.2 =
* fixed bug when the search field in the URL is not a configured search field

= 2.0.1 =
* minor bugfixes
* pass helptext and label through translation filter

= 2.0 =
* added new UI for configuring multisearch fields
* more options for presenting search fields in multi search
* preference to show the field label in the search result feedback
* better handling of searches with selectors
* several minor bug fixes

= 1.40 =
* added minimum search term length for multi search configuration
* wildcards no longer count toward valid term length

= 1.31 =
* search "clear" now restores text search option default

= 1.30.1 =
* added input range preference for date fields
* updated POT to correctly handle plurals

= 1.30 =
* correct issue with search result feedback with multiselect checkboxes and numeric ranges
* minified javascript assets
* resyncing version numbers

= 1.5.10 =
* fixed issue of search not clearing when selectors rest to "any"
* better support for substring searches on multi-select fields

= 1.5.9 =
* fixed query logic error when filter mode is on and ranged searches are configured in multisearch #158

= 1.5.8 =
* "chosen" selectors with read only now show as selectors in the search control
* client-side validation using the required attribute now supported for most element types

= 1.5.7 =
* "any" option now shown in multiselect search fields #163
* autosuggest terms no longer show html entities #165

= 1.5.6 =
* fixed bug with dropdown values when using alpha sort #162

= 1.5.5 =
* fixed issue with autocomplete list not refreshing #154

= 1.5.4 =
* fixed missing text-field-as-dropdown values in the search control
* now using natural sort for search dropdown value sorting

= 1.5.3 =
* fixed issue with serialized strings showing in multi-dropdowns when use db values is enabled

= 1.5.2 =
* fixed issue with blank option not appearing in dropdown controls

= 1.5.1 =
* fixed issue with PDB 1.9.3.4

= 1.5 =
* added "multiselect as OR" setting
* fixed bugs with multiselects using optgroups
* radio buttons no longer alpha sorted
* dropdowns now work as expected with defined null_select strings

= 1.4.3 =
* fixed bugs in search result count display

= 1.4.2 =
* clear now working in remote search control
* compatibility with HTML5 date fields
* fixed use of apostrophes in search term
* alternate session method supported

= 1.4.1 =
* fixed minor issue in control element class

= 1.4 =
* timestamp ranged searches now working
* fixed several bugs with ranged numeric searches
* support for cookieless sessions

= 1.3 =
* addressed XSS vulnerability in multi/combo search control
* better support for search result URLs

= 1.21 =
* multi-search control now compatible with list filter URLs (see https://xnau.com/creating-links-to-show-a-list-result/ )

= 1.20 =
* numeric values now appear in autosuggest pop-out
* fixed bug with comma entities splitting values in field options

= 1.19 =
* remember last search now working for checkboxes
* improvements to the handling of radio-button search controls

= 1.18 =
* fixed bug that incorrectly compared range values

= 1.17 =
* support for preference requiring all multisearch terms be filled
* ranged searches for all numeric fields
* "inverted" range searches will work as expected
* search terms can be filtered before use
* improvements to the search result feedback display

= 1.16 =
* fixed mismatch issue when using Whole Word Match and multisearch

= 1.15 =
"add any" option no longer applies to multiselect fields

= 1.14 =
* fixed bug with non-English characters and whole word match

= 1.13 =
* multisearch js no longer loaded on plain search page

= 1.12 =
* search terms can be preloaded in the URL
* improved handling of saved searches

= 1.11 =
* search form is remembered/restored when navigating away from the search page #67

= 1.10 =
* fixed query construction when using both combo and multi searches in filter mode #63

= 1.9.16 =
* fixed incompatibility with modular search add-on and the use of the search_fields shortcode attribute

= 1.9.15 =
* added fallback methods for AJAX search, sort, clear and pagination if session value is unavailable
* added support for CSV export control when using AJAX

= 1.9.14 =
* added support for using a different type of search control than the field definition

= 1.9.13 =
* added support for custom elements in the multi search control (now works with the Chosen add-on)

= 1.9.12 =
* fixed ranged searches for numeric and date fields
* better handling of multi-value search fields
* whole-word search mode enforces literal match, no wildcards

= 1.9.11 =
* enforce minimum length for search terms
* better handling of list query sessions
* added fallback method for missing session value in AJAX callback

= 1.9.9 =
* improve functioning of multiselects in multisearch
* multiselect terms now correctly appear in the search summary #40
* quoted phrases and mixed quoted phrases now work as expected in combo search #38

= 1.9.8 =
* whole-word match preferences added

= 1.9.7 =
* hidden fields can now be used as multisearch fields
* suppress validation on search fields when HTML5 add-on is enabled

= 1.9.6 =
* added responsive template: pdb-list-multisearch-responsive.php

= 1.9.5 =
* fixed bug where the combo search autocomplete fields setting was not saved #28

= 1.9.4 =
* html attributes can be set in the field definition for some form element types

= 1.9.3 =
* date, numeric and timestamp searches all support range search

= 1.9.2 =
* added support for numeric fields in multi search #23
* fixed bug when using simple value lists in a dropdown in multisearch #24

= 1.9.1 =
* suppressed validation on search fields #14
* datepicker now appears on date fields if datepicker add-on is installed #21
* fixed issue with using datepicker on date ranges #22

= 1.9 =
* fixed apostrophes and quotes in search terms #9 #17
* list multisearch template updated with improved result feedback #16
* updated translation strings

= 1.8 =
* added "any" option title setting #13
* fixed loading of mutlisearch javascript on non-multisearch list pages #8

= 1.7 =
* first public release

= 1.61 =
* Fixes a bug in the javascript that was preventing the sort control from working.

= 1.6 =
* Refactors the plugin, adding new features like caching the autosuggest and multi search field help text.

= 1.0 =
* Initial release of the plugin