# wp toolset relationships migrate

Perform a single database migration step based on the current migration state and return the next one.

If no state is provided, the initial one will be generated.

### OPTIONS

[\--state=&lt;state-value&gt;]
: Serialized migration state.

[\--porcelain]
: If set, only the next state will be printed (or nothing if an error occurs).

[\--complete]
: Run the whole migration procedure, all steps from beginning to end.

[\--rollback]
: Provided the old association table still exists, bring it back and replace set the database layer mode back to version1.


