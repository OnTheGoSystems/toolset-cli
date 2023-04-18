# wp types association query

Displays posts related to a given post.

## OPTIONS

\--post=&lt;number&gt;
: The ID of the post.

\--relationship=&lt;string&gt;
: The relationship slug.

[\--role-to-return=&lt;string&gt;]
: The role to return.

[\--query-by-role=&lt;string&gt;]
: The role to query by.

[\--format=&lt;format&gt;]
: The format of the output. Can take values: table, csv, json, count, yaml. Default: table.

## EXAMPLES

wp types association query --post=123 --relationship=slug

wp types association query --post=123 --relationship=slug --role-to-return=child --query-by-role=parent

wp types association query --post=123 --relationship=slug --role-to-return=child --query-by-role=parent --format=json
