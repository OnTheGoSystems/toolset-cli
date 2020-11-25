# wp toolset relationships migrate

Perform a single database migration step based on the current migration state and return the next one.

If no state is provided, the initial one will be generated.

### OPTIONS

[\--state=&lt;state-value&gt;]
: Serialized migration state.

[\--porcelain]
: If set, only the next state will be printed (or nothing if an error occurs).

[\--complete]
: Run the whole migration procedure, all steps from beginning to end. If combined with the --state argument,
the migration will attempt to resume from the given state and continue until the end.

[\--rollback]
: Provided the old association table still exists, bring it back and set the database layer mode back to `version1`.

[\--stop-at-step=&lt;step-number&gt;]
: When running the complete migration, stop before executing the step with a given number.

[\--without-sql-transaction]
: Don't use SQL transactions when migrating association batches.

[\--only-relationships=&lt;relationship-ids&gt;]
: Migrate only associations with given IDs. The parameter value needs to be a comma-separated list of relationship
  IDs. For debugging purposes only. Use with great caution! This WILL break your site.

[\--yes]
: Do not require user's confirmation for operations that normally require it.
