Tex2png
=======

This class provides a way to create easily LaTeX formulas.

With it, you can convert raw formulas like:

`\sum_{i = 0}^{i = n} \frac{i}{2}`

To nice images like:

![Sum formula](http://gregwar.com/sum.png)

Requirement
-----------

To use this library you'll need :

* **latex** : to compile formulas (with math support)
* **dvipng** : to convert dvis to png
* **shell_exec** : you need to be able to call the php `shell_exec()` function

You'll also need a temporary folder and, of courses, enough permissions to write to the 
target directory

Usage
-----

Just include the `Tex2png.php` file or register this repository directory as the
`Gregwar\Tex2png\` namespace and do the following :

```php
<?php

// This will create a formula and save it to sum.pnh
Tex2png::create('\sum_{i = 0}^{i = n} \frac{i}{2}')
    ->saveTo('sum.png')
    ->generate();
```

You can have a look at the example in `example/` directory.

License
-------

This class is under MIT license, for more information, please refer to the `LICENSE` file
