# wp views archive create

Creates a new WPA.

### OPTIONS

[\--name=&lt;string&gt;]
: The name of the WPA. Default: random string.

[\--view_purpose=&lt;string&gt;]
: The WordPress archive purpose Can take values: all, parametric. Default: all.

[\--usage_type=&lt;string&gt;]
: The usage group type. Can take values: standard, taxonomies, post_types.

[\--orderby=&lt;string&gt;]
: Field slug for WPA to be ordered by

[\--order=&lt;string&gt;]
: Order direction Can take values: DESC, ASC

[\--porcelain]
: prints the created WPA id

### EXAMPLES

   wp views archive create --name="Test Thingy" --usage_type="standard" --usage="home-blog-page" \--orderby="post_title" --order="ASC" --porcelain


