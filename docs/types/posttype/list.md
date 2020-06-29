# wp types posttype list

Displays a list of post types.

### OPTIONS

[\--format=&lt;format&gt;]
: The format of the output. Can take values: table, csv, json, count, yaml. Default: table.

[\--domain=&lt;domain&gt;]
: The domain of the post types. Can take values: all, types, builtin.

[\--intermediary=&lt;bool&gt;]
: Whether to return intermediary post types. Default: false.

[\--repeating_field_group=&lt;bool&gt;]
: Whether to return repeating field group post types. Default: false.

### EXAMPLES

   wp types posttype list --format=json --domain=all --intermediary=true --repeating_field_group=true


