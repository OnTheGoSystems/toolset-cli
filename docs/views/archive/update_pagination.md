# wp views archive update_pagination

Updates the pagination settings for a given View

### OPTIONS

&lt;id&gt;
: The post ID for View

[\--count=&lt;int&gt;]
: Posts per page count. Default: default

[\--type=&lt;string&gt;]
: Pagination type Can take values: paged, ajaxed, disabled.


### EXAMPLES

   wp views view update_pagination 37 --type="ajaxed" --count=20
   wp views wpa update_pagination 37 --type="ajaxed" --count=20


