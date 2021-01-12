# wp views import

Imports a Types XML file.

### Options

[\--views-overwrite]
: Bulk overwrite if View or WordPress Archive exists.

[\--views-delete]
: Delete any existing Views or WordPress Archives that are not in the import.

[\--view-templates-overwrite]
: Bulk overwrite if Content Template exists.

[\--view-templates-delete]
: Delete any existing Content Templates that are not in the import.

[\--view-settings-overwrite]
: Overwrite Views settings.

&lt;file&gt;
: The XML file to import.

### Examples

    wp views import <file>
    wp --user=<admin> views import --views-overwrite <file>

### Additional Notes

The import command relies on Views code that runs a user capabilities check. This check fails if the import command is not run as a user with the appropriate capabilities. This check will be removed at a future date. 
