You have installed the hello-world example of CyclonePHP.

This example assumes that the default route is set up, so please check
your index.php if it contains the following route definition:

req\Route::set('default', '(<controller>(/<action>(/<id>)))')
        ->defaults(array(
            'controller' => 'main',
            'action' => 'index',
            'namespace' => 'app\\controller'
        ));

If yes, then point your browser to http://localhost/cyclonephp/
(or wherever you installed the framework) and if everything went all
right then you should see a "Hello World" message there.

If the URL rewriting is properly configured then you can access the same content at
http://localhost/cyclonephp/main/index/
otherwise it should be
http://localhost/cyclonephp/index.php/main/index/

If none of the above URL-s display "Hello World" then probably you have some configuration problem.

To browse the source read the app/classes/app/controller/MainController.php file.
