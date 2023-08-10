<h1>JSM Show Term Metadata</h1>

<table>
<tr><th align="right" valign="top" nowrap>Plugin Name</th><td>JSM Show Term Metadata</td></tr>
<tr><th align="right" valign="top" nowrap>Summary</th><td>Show term metadata in a metabox when editing terms - a great tool for debugging issues with term metadata.</td></tr>
<tr><th align="right" valign="top" nowrap>Stable Version</th><td>3.1.1</td></tr>
<tr><th align="right" valign="top" nowrap>Requires PHP</th><td>7.2.34 or newer</td></tr>
<tr><th align="right" valign="top" nowrap>Requires WordPress</th><td>5.5 or newer</td></tr>
<tr><th align="right" valign="top" nowrap>Tested Up To WordPress</th><td>6.2.2</td></tr>
<tr><th align="right" valign="top" nowrap>Contributors</th><td>jsmoriss</td></tr>
<tr><th align="right" valign="top" nowrap>License</th><td><a href="https://www.gnu.org/licenses/gpl.txt">GPLv3</a></td></tr>
<tr><th align="right" valign="top" nowrap>Tags / Keywords</th><td>taxonomy, meta, term meta, categories, tags, delete, debug, inspector</td></tr>
</table>

<h2>Description</h2>

<p><strong>The JSM Show Term Metadata plugin displays term (ie. categories, tags, and custom taxonomies) meta keys and their unserialized values in a metabox at the bottom of term editing pages.</strong></p>

<p>The current user must have the <a href="https://wordpress.org/support/article/roles-and-capabilities/#manage_options">WordPress <em>manage_options</em> capability</a> (allows access to administration options) to view the Term Metadata metabox, and the <em>manage_options</em> capability to delete individual meta keys.</p>

<p>The default <em>manage_options</em> capability can be modified using the 'jsmstm_show_metabox_capability' and 'jsmstm_delete_meta_capability' filters (see filters.txt in the plugin folder).</p>

<p>There are no plugin settings - simply install and activate the plugin.</p>

<h4>Shows Yoast SEO Term Meta</h4>

<p>Yoast SEO stores its term (ie. categories, tags, and custom taxonomies) metadata in the WordPress options table, not the term meta table. The JSM Show Term Metadata plugin will read and display Yoast SEO's term metadata, but it cannot be deleted (as it does not reside in the WordPress term meta table).</p>

<h4>Related Plugins</h4>

<ul>
<li><a href="https://wordpress.org/plugins/jsm-show-comment-meta/">JSM Show Comment Metadata</a></li>
<li><a href="https://wordpress.org/plugins/jsm-show-post-meta/">JSM Show Post Metadata</a></li>
<li><a href="https://wordpress.org/plugins/jsm-show-user-meta/">JSM Show User Metadata</a></li>
<li><a href="https://wordpress.org/plugins/jsm-show-registered-shortcodes/">JSM Show Registered Shortcodes</a></li>
</ul>

