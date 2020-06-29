# wp types field group list

Displays a list of field groups.

### OPTIONS

[\--domain=&lt;domain&gt;]
: The domain of the group. Can take values: posts, terms, users. Default: posts.

[\--status=&lt;status&gt;]
: Whether to return public or private field groups. Can take values: public, private. Default: public.

[\--format=&lt;format&gt;]
: The format of the output. Can take values: – table, csv, ids, json, count, yaml. Default: table.

### EXAMPLES

   wp types field group list --domain=posts --status=public --format=json


