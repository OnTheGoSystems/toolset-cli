# wp types field create

Create a new field definition.

Note: The field definition needs to be assigned to a custom field group by using
the 'types field group add_field' command.

Note: This may not work properly for field types that require specific configuration and it may need to be
extended.

### OPTIONS

--domain=&lt;string&gt;
: Element domain of the field group (posts|terms|users). Only the posts domain is supported at the moment.

--type=&lt;string&gt;
: Field type slug as defined in Toolset_Field_Type_Definition_Factory.

--slug=&lt;string&gt;
: Custom field slug. Must be unique within the given domain.

[\--name=&lt;string&gt;]
: Display name of the custom field. If not provided, the slug will be used instead.
