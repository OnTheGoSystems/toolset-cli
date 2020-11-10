# wp views view create

Creates a new View.

### OPTIONS

[\--name=&lt;string&gt;]
: The name of the WPA. Default: random string.

[\--view_purpose=&lt;string&gt;]
: The WordPress archive purpose Can take values: all, parametric. Default: all.

[\--usage_type=&lt;string&gt;]
: The usage group type. Can take values: post_types, users, taxonomies.

[\--orderby=&lt;string&gt;]
: Field slug for Posts View to be ordered by.

[\--users_orderby=&lt;string&gt;]
: User field slug for Users View to be ordered by.

[\--taxonomy_orderby=&lt;string&gt;]
: Taxonomy Field slug for Taxonomy View to be ordered by.

[\--order=&lt;string&gt;]
: Order direction Can take values: DESC, ASC.

[\--porcelain]
: prints the created View ID.

### EXAMPLES

   wp views view create --name="Test Thingy" --usage_type="post_types" --usage="cest" --orderby="post_title" --order="ASC" --porcelain


