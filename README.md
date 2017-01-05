GitIgnoreWriter
============
Interface for editing .gitignore files in PHP

----------

Basic Usage
-----
In general, you will open a .gitignore file, use the `add`, `before`, or `after` methods to append, or insert some
values, and then call the `save` method to write the result.

Open a file and add a value:
```
use GitIgnoreWriter\GitIgnoreWriter;

$writer = new GitIgnoreWriter('../.gitignore');
$writer->add('.env');
$writer->save();
```

Also supports fluent interface:
```
(new GitIgnoreWriter('../.gitignore'))
    ->add('setup.php')
    ->before('setup.php', 'install.php')
    ->save();
```

Output File
---------------
There are multiple ways to read from a source file, make some changes, and write the result to a different output file.  The end result is the same, so choose whichever method best fits your use case:
```
$writer = (new GitIgnoreWriter('.gitignore.example'))
	->setOutputPath('.gitignore');
// ...
$writer->save();
```
Or alternately:
```
$writer = (new GitIgnoreWriter('.gitignore.example'));
// ...
$writer->save('.gitignore');
```
Or using the `load()` method:
```
$writer = (new GitIgnoreWriter('.gitignore'))
	->load('.gitignore.example');
// ...
$writer->save();
```

Adding and Inserting Values
-------------
The `add` method appends lines starting at the current position in the file, which defaults to the end of the file.
```
$writer->add($value);
```
The `before` and `after` methods first seek the current position to a given line, and then start inserting lines relative to that position
```
$writer->before($find, $prependValue);
$writer->after($find, $appendValue);
```

The `add`,  `before` and `after` methods each will accept the values to be inserted in multiple formats:

 - a single-line string
 - a multi-line string
 - an array of strings

More examples:
```
$writer->add('
    # You can write comments
    First/Line/Path

    # blank lines are retained as well
    Second/Line/Path
')->save();

$writer->add([
    'line1',
    'line2',
    'line3',
])->save();
```

Test if a line exists
--------------------
The `exists` method returns a boolean to test if a line exists in the file.

`$result = $writer->exists('.env');`



































































































































































































































































































































































































































































































































































































































