# wp types relationship delete

Deletes a relationship.

### OPTIONS

&lt;slug&gt;
: The slug of the relationship.

[\--cleanup=&lt;bool&gt;]
: Whether to delete related associations, intermediary post type and the intermediary post field group, if they exist. Defaults to true.

### EXAMPLES

   wp types relationship delete relationship-slug --cleanup=false


