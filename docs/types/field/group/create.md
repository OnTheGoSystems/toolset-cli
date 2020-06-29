# wp types field group create

Creates a new field group.

### OPTIONS

[\--name=&lt;string&gt;]
: The name of the group. Default: random string.

[\--title=&lt;string&gt;]
: The title of the group. Default: random string.

[\--domain=&lt;domain&gt;]
: The domain of the group. Can take values: posts, terms, users. Default: posts.

[\--status=&lt;bool&gt;]
: The status of the group.

### EXAMPLES

   wp types field group create --name=my-group --title='My Group' --domain=terms


