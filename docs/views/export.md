# wp views export

Exports a Views XML or ZIP file.

### Options

[\--format=<zip|xml>]
: The format can be either "zip" or "xml". If omitted, it will be inferred from the file extension.

[\--overwrite]
: Allow for overwriting an existing file.

&lt;file&gt;
: The XML or ZIP file to export.

### Examples

    wp views export <file>
    wp views export --overwrite --format=zip <file>
