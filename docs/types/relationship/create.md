# wp types relationship create

Creates a relationship between two post types.

### OPTIONS

[\--first=&lt;cpt&gt;]
: The first post type slug. Required.

[\--second=&lt;cpt&gt;]
: The second post type slug. Required.

[\--slug=&lt;string&gt;]
: The relationship slug. Required.

[\--cardinality=&lt;string&gt;]
: Relationship type: Many to many, one to many or one to one. Can take values: *..*, &lt;number&gt;..*,
&lt;number&gt;..&lt;number&gt;. Defaults to *..*

### EXAMPLES

   wp types relationship create --first=post --second=attachment --slug=featured-video --cardinality=1..*


