
Attendance Application
----------------------

I have written this application two ways in the same folder:

* As a single PHP file using the "Dr. Chuck Old School" MVC pattern
* As a Silex Application with nice URL routing and Twig templating (PHP > 7.1)

As a default, we run the non-Silex version of the code.

If you want to run the Silex version copy the `.htaccess` file

    cp .htaccess-silex .htaccess

You can check which version is running by looking for a comment
like: 

    <!-- Rendered from Attend.twig -->
    <!-- Rendered using old school MVC index.php -->

Both versions do the same thing.

Old School
----------

All the model, view, and controller are stored in `index.php`

Silex Version
-------------

The `.htaccess` file moves all requests into `silex.php`.  
The template is stored in `templates/Cookies.twig`.


