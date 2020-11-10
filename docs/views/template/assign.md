# wp views template assign

Assign the CT to resource, CPT single, CPT archive, or taxonomies.

### OPTIONS

&lt;id&gt;
: The ID for the content template that will be duplicated.

[\--assignment_group=&lt;string&gt;]
: The usage group type. Can take values: singles, archives, taxonomy.

[\--assignment_slug=&lt;string&gt;]
: CPT or taxonomy slug.

### EXAMPLES

   wp views template assign 1 --assignment_slug="cest" --assignment_group="archives"


