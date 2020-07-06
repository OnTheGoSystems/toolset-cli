# wp views template create

Creates a new Content Template.

### OPTIONS

[\--title=&lt;string&gt;]
: The title of the CT. Default: random string.

[\--content=&lt;string&gt;]
: The Template content.

[\--assignment_group=&lt;string&gt;]
: The usage group type. Can take values: singles, archives, taxonomy.

[\--assignment_slug=&lt;string&gt;]
: Post type or taxonomy slug.


[\--porcelain]
: Prints the created Content Template ID when passed.

### EXAMPLES

   wp views template create --title="Test Thingy" --content="some html and shortcodes" \--assignment_group="singles" --assignment_slug="cest" --porcelain


