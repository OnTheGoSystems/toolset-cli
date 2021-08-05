# wp types posttype create

Creates a new post type.

### OPTIONS

[\--slug=&lt;string&gt;]
: The name of the post type. Default: random string.

[\--singular=&lt;string&gt;]
: The singular name of the post type. Default: random string.

[\--plural=&lt;string&gt;]
: The plural name of the post type. Default: random string.

[\--editor=&lt;string&gt;]
: Which editor to use. Can take values: classic, block. Default: classic.

[\--show_in_rest=&lt;bool&gt;]
: Whether show_in_rest option is enabled. Default: false.

[\--hierarchical=&lt;bool&gt;]
: Whether hierarchical option is enabled. Default: false.

[\--publicly_queryable=&lt;bool&gt;]
: Whether publicly_queryable option is enabled. Default: true.

### EXAMPLES

   wp types posttype create --slug='book' --singular='Book' --plural='Books' --editor=block --show_in_rest=true --hierarchical=true


