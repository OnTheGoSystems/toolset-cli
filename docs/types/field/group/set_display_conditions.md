# wp types field group set_display_conditions

Set filters (display conditions) for a field group.

Setting a particular type of condition overwrites its previous setting completely.
This command needs to be extended to achieve full functionality.

### OPTIONS

--domain=&lt;string&gt;
: Element domain of the field group (posts|terms|users). Only post field groups are supported at the moment.

--group_slug=&lt;string&gt;
: Slug of the custom field group.

[\--post_types=&lt;string&gt;]
: Set a display condition by post type. Comma-separated list of post type slugs.

[\--terms=&lt;string&gt;]
: Set a display condition by taxonomy terms. Comma-separated list of term slugs. Must be coupled with
  the --taxonomy parameter.

[\--taxonomy=&lt;string&gt;]
: Taxonomy slug when using the --terms parameter.

[\--template=&lt;string&gt;]
: Set a display condition by page template. The value should be name of the template file.

[\--operator=&lt;string&gt;]
: Comparison operator for display conditions. Accepted values: 'AND'|'OR'.
