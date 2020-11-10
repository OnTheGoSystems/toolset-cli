# wp types association generate

Bulk generates associations. Posts involved in associations are created automatically.

### OPTIONS

[\--count-first=&lt;number&gt;]
: How many items of the first part involved in the relationship to generate. Default: 1

[\--count-second=&lt;number&gt;]
: How many items of the second part involved in the relationship to generate for each one of the first part. Default: 10

[\--post=&lt;number&gt;]
: The ID of the first post of the association. If used, count-first parameter should be ommitted. If ommitted, a new post will be created.

[\--relationship=&lt;string&gt;]
: The relationship slug.

### EXAMPLES

   wp types association generate --count-first=2 --count-second=20 --relationship=relationship-slug
   wp types association generate --post=12 --count-second=40 --relationship=relationship-slug


