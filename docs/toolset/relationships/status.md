# wp toolset relationships status

Retrieves or manipulates the status of the relationship functionality in Toolset. USE WITH GREAT CAUTION.

Exactly one of the following options must be provided.

### OPTIONS

[\--summary]
: Prints the summary of the current status.

[\--set-status=&lt;status&gt;]
: Enable or disable the whole post relationship functionality. Depending on the current version of Toolset plugins, this might bring back the legacy post relationships in Types. It only sets the flag in options, the database structure is left untouched.

Options:
  - enabled
  - 1
  - true
  - disabled
  - 0
  - false
\---

[\--initialize-database]
: Attempts to create the database tables for relationships as on a clean site.

[\--set-database-layer=&lt;version&gt;]
: Set the currently used database layer to a provided version. The version value must be accepted by Toolset. It does not touch the database structure in any way.

[\--clear]
: Delete all options regarding the relationship functionality in Toolset. Doesn't directly touch database structure in any way, but note that Toolset may try to create new tables on a fresh site if it doesn't have any settings stored.


