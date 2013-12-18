[![Fat-Free Framework](ui/images/logo.png)](http://fatfree.sf.net/)

**A powerful yet easy-to-use PHP micro-framework designed to help you build dynamic and robust Web applications - fast!**

[![Flattr this project](https://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=phpfatfree&url=https://github.com/bcosca/fatfree)

Condensed in a single ~60KB file, F3 (as we fondly call it) gives you solid foundation, a mature code base, and a no-nonsense approach to writing Web applications. Under the hood is an easy-to-use Web development tool kit, a high-performance URL routing and cache engine, built-in code highlighting, and support for multilingual applications. It's lightweight, easy-to-use, and fast. Most of all, it doesn't get in your way.

Whether you're a novice or an expert PHP programmer, F3 will get you up and running in no time. No unnecessary and painstaking installation procedures. No complex configuration required. No convoluted directory structures. There's no better time to start developing Web applications the easy way than right now!

F3 supports both SQL and NoSQL databases off-the-shelf: MySQL, SQLite, MSSQL/Sybase, PostgreSQL, DB2, and MongoDB. It also comes with powerful object-relational mappers for data abstraction and modeling that are just as lightweight as the framework. No configuration needed.

That's not all. F3 is packaged with other optional plug-ins that extend its capabilities:-

* Fast and clean template engine,
* Unit testing toolkit,
* Database-managed sessions with automatic CSRF protection,
* Markdown-to-HTML converter,
* Atom/RSS feed reader,
* Image processor,
* Geodata handler,
* Google static maps,
* On-the-fly Javascript/CSS compressor,
* OpenID (consumer),
* Custom logger,
* Basket/Shopping cart,
* Pingback server/consumer,
* Unicode-aware string functions,
* SMTP over SSL/TLS,
* Tools for communicating with other servers,
* And more in a tiny supercharged package!

Unlike other frameworks, F3 aims to be usable - not usual.

[![Flattr this project](https://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=phpfatfree&url=https://github.com/bcosca/fatfree)

The philosophy behind the framework and its approach to software architecture is towards minimalism in structural components, avoiding application complexity and striking a balance between code elegance, application performance and programmer productivity.

[![Paypal](ui/images/donate.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MJSQL8N5LPDAY)
[![Bitcoin](ui/images/bitcoin.png)](https://coinbase.com/checkouts/7986a0da214006256d470f2f8e1a15cf)

## Table of Contents

* [Getting Started](#getting-started)
* [Routing Engine](#routing-engine)
* [Framework Variables](#framework-variables)
* [Views and Templates](#views-and-templates)
* [Databases](#databases)
* [Plug-Ins](#plug-ins)
* [Optimization](#optimization)
* [Unit Testing](#unit-testing)
* [Quick Reference](#quick-reference)
* [Support and Licensing](#support-and-licensing)

[![Twitter](ui/images/twitter.png)](https://twitter.com/phpfatfree)

### Version 3.2 Is Finally Here!

The latest official release marks a major milestone in the development of the Fat-Free Framework. Packed with exciting new features and outstanding documentation that consumed significant time and effort to develop and refine, version 3.2 is finally available for download. This edition is packed with a bunch of new usability and security features.

F3 has a stable enterprise-class architecture. Unbeatable performance, user-friendly features and a lightweight footprint. What more can you ask for?

It is highly recommended that experienced users develop new applications with this version to take advantage of the latest code base and its significant improvements.

## Introducing FatFreeFramework.com

**Detailed API documentation with lots of code examples and a graphic guide can now be found at [http://fatfreeframework.com/](http://fatfreeframework.com/).**

Of course this handy online reference is powered by F3! It showcases the framework's capability and performance. Check it out now.

## Getting Started

> *A designer knows he has achieved perfection not when there is nothing left to add, but when there is nothing left to take away. -- Antoine de Saint-Exupéry*

Fat-Free Framework makes it easy to build entire Web sites in a jiffy. With the same power and brevity as modern Javascript toolkits and libraries, F3 helps you write better-looking and more reliable PHP programs. One glance at your PHP source code and anyone will find it easy to understand, how much you can accomplish in so few lines of code, and how powerful the results are.

F3 is one of the best documented frameworks around. Learning it costs next to nothing. No strict set of difficult-to-navigate directory structures and obtrusive programming steps. No truck load of configuration options just to display `'Hello, World'` in your browser. Fat-Free gives you a lot of freedom - and style - to get more work done with ease and in less time.

F3's declarative approach to programming makes it easy for novices and experts alike to understand PHP code. If you're familiar with the programming language Ruby, you'll notice the resemblance between Fat-Free and Sinatra micro-framework because they both employ a simple Domain-Specific Language for ReSTful Web services. But unlike Sinatra and its PHP incarnations (Fitzgerald, Limonade, Glue - to name a few), Fat-Free goes beyond just handling routes and requests. Views can be in any form, such as plain text, HTML, XML or an e-mail message. The framework comes with a fast and easy-to-use template engine. F3 also works seamlessly with other template engines, including Twig, Smarty, and PHP itself. Models communicate with F3's data mappers and the SQL helper for more complex interactions with various database engines. Other plug-ins extend the base functionality even more. It's a total Web development framework - with a lot of muscle!

### Enough Said - See For Yourself

Unzip the contents of the distribution package anywhere in your hard drive. By default, the framework file and optional plug-ins are located in the `lib/` path. Organize your directory structures any way you want. You may move the default folders to a path that's not Web-accessible for better security. Delete the plug-ins that you don't need. You can always restore them later and F3 will detect their presence automatically.

**Important:** If your application uses APC, Memcached, WinCache, XCache, or a filesystem cache, clear all cache entries first before overwriting an older version of the framework with a new one.

Make sure you're running the right version of PHP. F3 does not support versions earlier than PHP 5.3. You'll be getting syntax errors (false positives) all over the place because new language constructs and closures/anonymous functions are not supported by outdated PHP versions. To find out, open your console (`bash` shell on Linux, or `cmd.exe` on Windows):-

```
/path/to/php -v
```

PHP will let you know which particular version you're running and you should get something that looks similar to this:-

```
PHP 5.3.15 (cli) (built: Jul 20 2012 00:20:38)
Copyright (c) 1997-2012 The PHP Group
Zend Engine v2.3.0, Copyright (c) 1998-2012 Zend Technologies
```

Upgrade if necessary and come back here if you've made the jump to PHP 5.3 or a later release. If you need a PHP 5.3+ hosting service provider, try one of these services:

* [A2 Hosting](http://www.a2hosting.com/2461-15-1-72.html)
* [DreamHost](http://www.dreamhost.com/r.cgi?665472)
* [Hostek](http://hostek.com/aff.php?aff=364&plat=L)
* [SiteGround](http://www.siteground.com/index.htm?referrerid=155694)

### Hello, World: The Less-Than-A-Minute Fat-Free Recipe

Time to start writing our first application:-

``` php
$f3=require('path/to/base.php');
$f3->route('GET /',
    function() {
        echo 'Hello, world!';
    }
);
$f3->run();
```

Prepend `base.php` on the first line with the appropriate path. Save the above code fragment as `index.php` in your Web root folder. We've written our first Web page.

The first command tells the PHP interpreter that you want the framework's functions and features available to your application. The `$f3->route()` method informs Fat-Free that a Web page is available at the relative URL indicated by the slash (`/`). Anyone visiting your site located at `http://www.example.com/` will see the `'Hello, world!'` message because the URL `/` is equivalent to the root page. To create a route that branches out from the root page, like `http://www.example.com/inside/`, you can define another route with a simple `GET /inside` string.

The route described above tells the framework to render the page only when it receives a URL request using the HTTP `GET` method. More complex Web sites containing forms use other HTTP methods like `POST`, and you can also implement that as part of a `$f3->route()` specification.

If the framework sees an incoming request for your Web page located at the root URL `/`, it will automatically route the request to the callback function, which contains the code necessary to process the request and render the appropriate HTML stuff. In this example, we just send the string `'Hello, world!'` to the user's Web browser.

So we've established our first route. But that won't do much, except to let F3 know that there's a process that will handle it and there's some text to display on the user's Web browser. If you have a lot more pages on your site, you need to set up different routes for each group. For now, let's keep it simple. To instruct the framework to start waiting for requests, we issue the `$f3->run()` command.

**Can't Get the Example Running?** If you're having trouble getting this simple program to run on your server, you may have to tweak your Web server settings a bit. Take a look at the sample Apache configuration in the following section (along with the Nginx and Lighttpd equivalents).

## Routing Engine

### Overview

Our first example wasn't too hard to swallow, was it? If you like a little more flavor in your Fat-Free soup, insert another route before the `$f3->run()` command:-

``` php
$f3->route('GET /about',
    function() {
        echo 'Donations go to a local charity... us!';
    }
);
```

You don't want to clutter the global namespace with function names? Fat-Free recognizes different ways of mapping route handlers to OOP classes and methods:-

``` php
class WebPage {
    function display() {
        echo 'I cannot object to an object';
    }
}

$f3->route('GET /about','WebPage->display');
```

HTTP requests can also be routed to static class methods:-

``` php
$f3->route('GET /login','Auth::login');
```

### Routes and Tokens

As a demonstration of Fat-Free's powerful domain-specific language (DSL), you can specify a single route to handle different possibilities:-

``` php
$f3->route('GET /brew/@count',
    function($f3) {
        echo $f3->get('PARAMS.count').' bottles of beer on the wall.';
    }
);
```

This example shows how we can specify a token `@count` to represent part of a URL. The framework will serve any request URL that matches the `/brew/` prefix, like `/brew/99`, `/brew/98`, etc. This will display `'99 bottles of beer on the wall'` and `'98 bottles of beer on the wall'`, respectively. Fat-Free will also accept a page request for `/brew/unbreakable`. (Expect this to display `'unbreakable bottles of beer on the wall'`.) When such a dynamic route is specified, Fat-Free automagically populates the global `PARAMS` array variable with the value of the captured strings in the URL. The `$f3->get()` call inside the callback function retrieves the value of a framework variable. You can certainly apply this method in your code as part of the presentation or business logic. But we'll discuss that in greater detail later.

Notice that Fat-Free understands array dot-notation. You can certainly use `@PARAMS['count']` regular notation, which is prone to typo errors and unbalanced braces. The framework also permits `@PARAMS.count` which is somehow similar to Javascript. This feature is limited to arrays in F3 templates. Take note that `@foo.@bar` is a string concatenation, whereas `@foo.bar` translates to `@foo['bar']`.

Here's another way to access tokens in a request pattern:-

``` php
$f3->route('GET /brew/@count',
    function($f3,$params) {
        echo $params['count'].' bottles of beer on the wall.';
    }
);
```

You can use the asterisk (`*`) to accept any URL after the `/brew` route - if you don't really care about the rest of the path:-

``` php
$f3->route('GET /brew/*',
    function() {
        echo 'Enough beer! We always end up here.';
    }
);
```

An important point to consider: You will get Fat-Free (and yourself) confused if you have both `GET /brew/@count` and `GET /brew/*` together in the same application. Use one or the other. Another thing: Fat-Free sees `GET /brew` as separate and distinct from the route `GET /brew/@count`. Each can have different route handlers.


### Named Routes

When you define a route, you can assign it a name. Use the route name in your code and templates instead of a typed url. Then if you need to change your urls to please the marketing overlords, you only need to make the change where the route was defined. The route names must follow php variable naming rules (no dots, dashes nor hyphens).

Let's name a route:-

``` php
$f3->route('GET @beer_list: /beer', 'Beer->list');
```

The name is inserted after the route VERB (`GET` in this example) preceeded by an `@` symbol, and separated from the URL portion by a colon `:` symbol. You can insert a space around the colon if that makes it easier to read your code (as shown here).

To access the named route in a template, get the value of the named route as the key of the `ALIASES` hive array:-

``` html
<a href="{{ @ALIASES.beer_list }}">View beer list</a>
```

To redirect the visitor to a new URL, call the named route inside the `reroute() method like:-

``` php
// a named route is a string value
$f3->reroute('@beer_list'); // note the single quotes
```

If you use tokens in your route, F3 will replace those tokens with their current value. If you want to change the token's value before calling reroute, pass it as the 2nd argument.:-

``` php
$f3->route('GET @beer_list: /beer/@country', 'Beer->bycountry');
$f3->route('GET @beer_list: /beer/@country/@village', 'Beer->byvillage');

// a set of key-value pairs is passed as argument to named route
$f3->reroute('@beer_list(@country=Germany)');

// if more than one token needed
$f3->reroute('@beer_list(@country=Germany,@village=Rhine)');
```

Don't forget to `urlencode()` your arguments if you have characters that do not comply with RFC 1738 guidelines for well-formed URLs.

### Dynamic Web Sites

Wait a second - in all the previous examples, we never really created any directory in our hard drive to store these routes. The short answer: we don't have to. All F3 routes are virtual. They don't mirror our hard disk folder structure. If you have programs or static files (images, CSS, etc.) that do not use the framework - as long as the paths to these files do not conflict with any route defined in your application - your Web server software will deliver them to the user's browser, provided the server is configured properly.

### PHP 5.4's Built-In Web Server

PHP's latest stable version has its own built-in Web server. Start it up using the following configuration:-

```
php -S localhost:80 -t /var/www/
```

The above command will start routing all requests to the Web root `/var/www`. If an incoming HTTP request for a file or folder is received, PHP will look for it inside the Web root and send it over to the browser if found. Otherwise, PHP will load the default `index.php` (containing your F3-enabled code).

### Sample Apache Configuration

If you're using Apache, make sure you activate the URL rewriting module (mod_rewrite) in your apache.conf (or httpd.conf) file. You should also create a .htaccess file containing the following:-

``` apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule .* index.php [L,QSA]
```

The script tells Apache that whenever an HTTP request arrives and if no physical file (`!-f`) or path (`!-d`) or symbolic link (`!-l`) can be found, it should transfer control to `index.php`, which contains our main/front controller, and which in turn, invokes the framework.

The `.htaccess file` containing the Apache directives stated above should always be in the same folder as `index.php`.

You also need to set up Apache so it knows the physical location of `index.php` in your hard drive. A typical configuration is:-

``` apache
DocumentRoot "/var/www/html"
<Directory "/var/www/html">
    Options -Indexes FollowSymLinks Includes
    AllowOverride All
    Order allow,deny
    Allow from All
</Directory>
```

If you're developing several applications simultaneously, a virtual host configuration is easier to manage:-

``` apache
NameVirtualHost *
<VirtualHost *>
    ServerName site1.com
    DocumentRoot "/var/www/site1"
    <Directory "/var/www/site1">
        Options -Indexes FollowSymLinks Includes
        AllowOverride All
        Order allow,deny
        Allow from All
    </Directory>
</VirtualHost>
<VirtualHost *>
    ServerName site2.com
    DocumentRoot "/var/www/site2"
    <Directory "/var/www/site2">
        Options -Indexes FollowSymLinks Includes
        AllowOverride All
        Order allow,deny
        Allow from All
    </Directory>
</VirtualHost>
```

Each `ServerName` (`site1.com` and `site2.com` in our example) must be listed in your `/etc/hosts` file. On Windows, you should edit `C:/WINDOWS/system32/drivers/etc/hosts`. A reboot might be necessary to effect the changes. You can then point your Web browser to the address `http://site1.com` or `http://site2.com`. Virtual hosts make your applications a lot easier to deploy.

### Sample Nginx Configuration

For Nginx servers, here's the recommended configuration (replace ip_address:port with your environment's FastCGI PHP settings):-

``` nginx
server {
    root /var/www/html;
    location / {
        index index.php index.html index.htm;
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass ip_address:port;
        include fastcgi_params;
    }
}
```

### Sample Lighttpd Configuration

Lighttpd servers are configured in a similar manner:-

```
$HTTP["host"] =~ "www\.example\.com$" {
    url.rewrite-once = ( "^/(.*?)(\?.+)?$"=>"/index.php/$1?$2" )
    server.error-handler-404 = "/index.php"
}
```

### Rerouting

So let's get back to coding. You can declare a page obsolete and redirect your visitors to another site/page:-

``` php
$f3->route('GET|HEAD /obsoletepage',
    function($f3) {
        $f3->reroute('/newpage');
    }
);
```

If someone tries to access the URL `http://www.example.com/obsoletepage` using either HTTP GET or HEAD request, the framework redirects the user to the URL: `http://www.example.com/newpage` as shown in the above example. You can also redirect the user to another site, like `$f3->reroute('http://www.anotherexample.org/');`.

Rerouting can be particularly useful when you need to do some maintenance work on your site. You can have a route handler that informs your visitors that your site is offline for a short period.

HTTP redirects are indispensable but they can also be expensive. As much as possible, refrain from using `$f3->reroute()` to send a user to another page on the same Web site if you can direct the flow of your application by invoking the function or method that handles the target route. However, this approach will not change the URL on the address bar of the user's Web browser. If this is not the behavior you want and you really need to send a user to another page, in instances like successful submission of a form or after a user has been authenticated, Fat-Free sends an `HTTP 303 See Other` header. For all other attempts to reroute to another page or site, the framework sends an `HTTP 301 Moved Permanently` header.

### Triggering a 404

At runtime, Fat-Free automatically generates an HTTP 404 error whenever it sees that an incoming HTTP request does not match any of the routes defined in your application. However, there are instances when you need to trigger it yourself.

Take for instance a route defined as `GET /dogs/@breed`. Your application logic may involve searching a database and attempting to retrieve the record corresponding to the value of `@breed` in the incoming HTTP request. Since Fat-Free will accept any value after the `/dogs/` prefix because of the presence of the `@breed` token, displaying an `HTTP 404 Not Found` message programmatically becomes necessary when the program doesn't find any match in our database. To do that, use the following command:-

``` php
$f3->error(404);
```

### Representational State Transfer (ReST)

Fat-Free's architecture is based on the concept that HTTP URIs represent abstract Web resources (not limited to HTML) and each resource can move from one application state to another. For this reason, F3 does not have any restrictions on the way you structure your application. If you prefer to use the [Model-View-Controller](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) pattern, F3 can help you compartmentalize your application components to stick to this paradigm. On the other hand, the framework also supports the [Resource-Method-Representation](http://www.peej.co.uk/articles/rmr-architecture.html) pattern, and implementing it is more straightforward.

Here's an example of a ReST interface:-

``` php
class Item {
    function get() {}
    function post() {}
    function put() {}
    function delete() {}
}

$f3=require('lib/base.php');
$f3->map('/cart/@item','Item');
$f3->run();
```

Fat-Free's `$f3->map()` method provides a ReST interface by mapping HTTP methods in routes to the equivalent methods of an object or a PHP class. If your application receives an incoming HTTP request like `GET /cart/123`, Fat-Free will automatically transfer control to the object's or class' `get()` method. On the other hand, a `POST /cart/123` request will be routed to the `Item` class' `post()` method.

**Note:** Browsers do not implement the HTTP `PUT` and `DELETE` methods in regular HTML forms. These and other ReST methods (`HEAD`, and `CONNECT`) are accessible only via AJAX calls to the server.

If the framework receives an HTTP method that's not implemented by a class, it generates an `HTTP 405 Method Not Allowed` error. F3 automatically responds with the appropriate headers to HTTP `OPTIONS` method requests. The framework will not map this request to a class.

### The F3 Autoloader

Fat-Free has a way of loading classes only at the time you need them, so they don't gobble up more memory than a particular segment of your application needs. And you don't have to write a long list of `include` or `require` statements just to load PHP classes saved in different files and different locations. The framework can do this automatically for you. Just save your files (one class per file) in a folder and tell the framework to automatically load the appropriate file once you invoke a method in the class:-

``` php
$f3->set('AUTOLOAD','autoload/');
```

You can assign a different location for your autoloaded classes by changing the value of the `AUTOLOAD` global variable. You can also have multiple autoload paths. If you have your classes organized and in different folders, you can instruct the framework to autoload the appropriate class when a static method is called or when an object is instantiated. Modify the `AUTOLOAD` variable this way:-

``` php
$f3->set('AUTOLOAD','admin/autoload/; user/autoload/; default/');
```

**Important:** Except for the .php extension, the class name and file name must be identical, for the framework to autoload your class properly. The basename of this file must be identical to your class invocation, e.g. F3 will look for either `Foo/BarBaz.php` or `foo/barbaz.php` when it detects a `new Foo\BarBaz` statement in your application.

### Working with Namespaces

`AUTOLOAD` allows class hierarchies to reside in similarly-named subfolders, so if you want the framework to autoload a PHP 5.3 namespaced class that's invoked in the following manner:-

``` php
$f3->set('AUTOLOAD','autoload/');
$obj=new Gadgets\iPad;
```

You can create a folder hierarchy that follows the same structure. Assuming `/var/www/html/` is your Web root, then F3 will look for the class in `/var/www/html/autoload/gadgets/ipad.php`. The file `ipad.php` should have the following minimum code:-

``` php
namespace Gadgets;
class iPad {}
```

Remember: All directory names in Fat-Free must end with a slash. You can assign a search path for the autoloader as follows:-

``` php
$f3->set('AUTOLOAD','main/;aux/');
```

### Routing to a Namespaced Class

F3, being a namespace-aware framework, allows you to use a method in namespaced class as a route handler, and there are several ways of doing it. To call a static method:-

``` php
$f3->set('AUTOLOAD','classes/');
$f3->route('GET|POST /','Main\Home::show');
```

The above code will invoke the static `show()` method of the class `Home` within the `Main` namespace. The `Home` class must be saved in the folder `classes/main/home.php` for it to be loaded automatically.

If you prefer to work with objects:-

``` php
$f3->route('GET|POST /','Main\Home->show');
```

will instantiate the `Home` class at runtime and call the `show()` method thereafter.

### Event Handlers

F3 has a couple of routing event listeners that might help you improve the flow and structure of controller classes. Say you have a route defined as follows:-

``` php
$f3->route('GET /','Main->home');
```

If the application receives an HTTP request matching the above route, F3 instantiates `Main`, but before executing the `home()` method, the framework looks for a method in this class named `beforeRoute()`. In case it's found, F3 runs the code contained in the `beforeRoute()` event handler before transferring control to the `home()` method. Once this is accomplished, the framework looks for an `afterRoute()` event handler. Like `beforeRoute()`, the method gets executed if it's defined.

### Dynamic Route Handlers

Here's another F3 goodie:-

``` php
$f3->route('GET /products/@action','Products->@action');
```

If your application receives a request for, say, `/products/itemize`, F3 will extract the `'itemize'` string from the URL and pass it on to the `@action` token in the route handler. F3 will then look for a class named `Products` and execute the `itemize()` method.

Dynamic route handlers may have various forms:-

``` php
// static method
$f3->route('GET /public/@genre','Main::@genre');
// object mode
$f3->route('GET /public/@controller/@action','@controller->@action');
```

F3 triggers an `HTTP 404 Not Found` error at runtime if it cannot transfer control to the class or method associated with the current route, i.e. an undefined class or method.

### AJAX and Synchronous Requests

Routing patterns may contain modifiers that direct the framework to base its routing decision on the type of HTTP request:-

``` php
$f3->route('GET /example [ajax]','Page->getFragment');
$f3->route('GET /example [sync]','Page->getFull');
```

The first statement will route the HTTP request to the `Page->getFragment()` callback only if an `X-Requested-With: XMLHttpRequest` header (AJAX object) is received by the server. If an ordinary (synchronous) request is detected, F3 will simply drop down to the next matching pattern, and in this case it executes the `Page->getFull()` callback.

If no modifiers are defined in a routing pattern, then both AJAX and synchronous request types are routed to the specified handler.

Route pattern modifiers are also recognized by `$f3->map()`.

## Framework Variables

### Basic Use

Variables defined in Fat-Free are global, i.e. they can be accessed by any MVC component. Framework globals are not identical to PHP globals. An F3 variable named `content` is not identical to PHP's `$content`. F3 is a domain-specific language in its own right and maintains its own separate symbol table for system and application variables. The framework, like every well-designed object-oriented program, does not pollute the PHP global namespace with constants, variables, functions or classes that might conflict with any application. Unlike other frameworks, F3 does not use PHP's `define()` statement. All framework constants are confined to classes.

To assign a value to a Fat-Free variable:

``` php
$f3->set('var',value)
```

**Note:** Fat-Free variables accept all PHP data types, including objects and anonymous functions.

To set several variables at once:

``` php
$f3->mset(
    array(
        'foo'=>'bar',
        'baz'=>123
    )
);
```

To retrieve the value of a framework variable named `var`:-

``` php
$f3->get('var')
```

To remove a Fat-Free variable from memory if you no longer need it (discard it so it doesn't interfere with your other functions/methods), use the method:-

``` php
$f3->clear('var')
```

To find out if a variable has been previously defined:-

``` php
$f3->exists('var')
```

### Globals

F3 maintains its own symbol table for framework and application variables, which are independent of PHP's. Some variables are mapped to PHP globals. Fat-Free's `SESSION` is equivalent to `$_SESSION`, and `REQUEST` maps to `$_REQUEST`. Use of framework variables is recommended, instead of PHP's, to help you with data transfer across different functions, classes and methods. They also have other advantages:-

* You can use framework variables directly in your templates.
* You don't have to instruct PHP to reference a variable outside the current scope using a global keyword inside each function or method. All F3 variables are global to your application.
* Setting the Fat-Free equivalent of a PHP global like `SESSION` also changes PHP's underlying `$_SESSION`. Altering the latter also alters the framework counterpart.

Fat-Free does not maintain just a dumb storage for variables and their values. It can also automate session management and other things. Assigning or retrieving a value through F3's `SESSION` variable auto-starts the session. If you use `$_SESSION` (or session-related functions) directly, instead of the framework variable `SESSION`, your application becomes responsible for managing sessions.

As a rule, framework variables do not persist between HTTP requests. Only `SESSION` and `COOKIE` (and their elements) which are mapped to PHP's `$_SESSION` and `$_COOKIE` global variables are exempt from the stateless nature of HTTP.

There are several predefined global variables used internally by Fat-Free, and you can certainly utilize them in your application. Be sure you know what you're doing. Altering some Fat-Free global variables may result in unexpected framework behavior.

The framework has several variables to help you keep your files and directory structures organized. We've seen how we can automate class loading by using the `AUTOLOAD`. There's a `UI` global variable, which contains the path pointing to the location of your HTML views/templates. `DEBUG` is another variable you'll be using quite often during application development and it's used for setting the verbosity of error traces.

Refer to the [Quick Reference](#quick-reference) if you need a comprehensive list of built-in framework variables.

### Naming Rules

A framework variable may contain any number of letters, digits and underscores. It must start with an alpha character and should have no spaces. Variable names are case-sensitive.

F3 uses all-caps for internal predefined global variables. Nothing stops you from using variable names consisting of all-caps in your own program, but as a general rule, stick to lowercase (or camelCase) when you set up your own variables so you can avoid any possible conflict with current and future framework releases.

You should not use PHP reserved words like `if`, `for`, `class`, `default`, etc. as framework variable names. These may cause unpredictable results.

### Working with String and Array Variables

F3 also provides a number of tools to help you with framework variables.

``` php
$f3->set('a','fire');
$f3->concat('a','cracker');
echo $f3->get('a'); // returns the string 'firecracker'

$f3->copy('a','b');
echo $f3->get('b'); // returns the same string: 'firecracker'
```

F3 also provides some primitive methods for working with array variables:-

``` php
$f3->set('colors',array('red','blue','yellow'));
$f3->push('colors','green'); // works like PHP's array_push()
echo $f3->pop('colors'); // returns 'green'

$f3->unshift('colors','purple'); // similar to array_unshift()
echo $f3->shift('colors'); // returns 'purple'

$f3->set('grays',array('light','dark'));
$result=$f3->merge('colors','grays'); // merges the two arrays
```

### Do-It-Yourself Directory Structures

Unlike other frameworks that have rigid folder structures, F3 gives you a lot of flexibility. You can have a folder structure that looks like this (parenthesized words in all-caps represent the F3 framework variables that need tweaking):-

```
/ (your Web root, where index.php is located)
app/ (application files)
    dict/ (LOCALES, optional)
    controllers/
    logs/ (LOGS, optional)
    models/
    views/ (UI)
css/
js/
lib/ (you can store base.php here)
tmp/ (TEMP, used by the framework)
   cache/ (CACHE)
```

Feel free to organize your files and directories any way you want. Just set the appropriate F3 global variables. If you want a really secure site, Fat-Free even allows you to store all your files in a non-Web-accessible directory. The only requirement is that you leave `index.php`, `.htaccess` and your public files, like CSS, JavaScript, images, etc. in a path visible to your browser.

### About the F3 Error Handler

Fat-Free generates its own HTML error pages, with stack traces to help you with debugging. Here's an example:-

> ---
> ### Internal Server Error
> strpos() expects at least 2 parameters, 0 given
>
>     • var/html/dev/main.php:96 strpos()
>     • var/html/dev/index.php:16 Base->run()
> ---

If you feel it's a bit too plain or wish to do other things when the error occurs, you may create your own custom error handler:-

``` php
$f3->set('ONERROR',
    function($f3) {
        // custom error handler code goes here
        // use this if you want to display errors in a
        // format consistent with your site's theme
        echo $f3->get('ERROR.title');
    }
);
```

F3 maintains a global variable containing the details of the latest error that occurred in your application. The `ERROR` variable is an array structured as follows:-

```
ERROR.code - displays the error code (404, 500, etc.)
ERROR.title - header and page title
ERROR.text - error context
ERROR.trace - stack trace
```

While developing your application, it's best to set the debug level to maximum so you can trace all errors to their root cause:-

``` php
$f3->set('DEBUG',3);
```

Just insert the command in your application's bootstrap sequence.

Once your application is ready for release, simply remove the statement from your application, or replace it with:-

``` php
$f3->set('DEBUG',0);
```

This will suppress the stack trace output in any system-generated HTML error page (because it's not meant to be seen by your site visitors).

`DEBUG` can have values ranging from 0 (stack trace suppressed) to 3 (most verbose).

**Don't forget!** Stack traces may contain paths, file names, database commands, user names and passwords. You might expose your Web site to unnecessary security risks if you fail to set the `DEBUG` global variable to 0 in a production environment.

### Configuration Files

If your application needs to be user-configurable, F3 provides a handy method for reading configuration files to set up your application. This way, you and your users can tweak the application without altering any PHP code.

Instead of creating a PHP script that contains the following sample code:-

``` php
$f3->set('num',123);
$f3->set('str','abc');
$f3->set('hash',array('x'=>1,'y'=>2,'z'=>3));
$f3->set('items',array(7,8,9));
$f3->set('mix',array('this',123.45,FALSE));
```

You can construct a configuration file that does the same thing:-

``` ini
[globals]
num=123
; this is a regular string
str=abc
; another way of assigning strings
str="abc"
; this is an array
hash[x]=1
hash[y]=2
hash[z]=3
; dot-notation is recognized too
hash.x=1
hash.y=2
hash.z=3
; this is also an array
items=7,8,9
; array with mixed elements
mix="this",123.45,FALSE
```

Instead of lengthy `$f3->set()` statements in your code, you can instruct the framework to load a configuration file as code substitute. Let's save the above text as setup.cfg. We can then call it with a simple:-

``` php
$f3->config('setup.cfg');
```

String values need not be quoted, unless you want leading or trailing spaces included. If a comma should be treated as part of a string, enclose the string using double-quotes - otherwise, the value will be treated as an array (the comma is used as an array element separator). Strings can span multiple lines:-

``` ini
[globals]
str="this is a \
very long \
string"
```

F3 also gives you the ability to define HTTP routes in configuration files:-

``` ini
[routes]
GET /=home
GET /404=App->page404
GET /page/@num=Page->@controller
```

Route maps can be defined in configuration files too:-

``` ini
[maps]
/blog=Blog\Login
/blog/@controller=Blog\@controller
```

The `[globals]`, `[routes]`, and `[maps]` section headers are required. You can combine both sections in a single configuration file - although having `[routes]` and `[maps]` in a separate file is recommended. This way you can allow end-users to modify some application-specific flags, and at the same time restrict them from meddling with your routing logic.

## Views and Templates

### Separation of Concerns

A user interface like an HTML page should be independent of the underlying PHP code related to routing and business logic. This is fundamental to the MVC paradigm. A basic revision like converting `<h3>` to `<p>` should not demand a change in your application code. In the same manner, transforming a simple route like `GET /about` to `GET /about-us` should not have any effect on your user interface and business logic, (the view and model in MVC, or representation and method in RMR).

Mixing programming constructs and user interface components in a single file, like spaghetti coding, makes future application maintenance a nightmare.

### PHP as a Template Engine

F3 supports PHP as a template engine. Take a look at this HTML fragment saved as `template.htm`:-.

``` html
<p>Hello, <?php echo $name; ?>!</p>
```

If short tags are enabled on your server, this should work too:-

``` html
<p>Hello, <?= $name ?></p>
```

To display this template, you can have PHP code that looks like this (stored in a file separate from the template):-

``` php
$f3=require('lib/base.php');
$f3->route('GET /',
    function($f3) {
        $f3->set('name','world');
        $view=new View;
        echo $view->render('template.htm');
        // Previous two lines can be shortened to:-
        // echo View::instance()->render('template.htm');
    }
);
$f3->run();
```

The only issue with using PHP as a template engine, due to the embedded PHP code in these files, is the conscious effort needed to stick to the guidelines on separation of concerns and resist the temptation of mixing business logic with your user interface.

### A Quick Look at the F3 Template Language

As an alternative to PHP, you can use F3's own template engine. The above HTML fragment can be rewritten as:-

``` html
<p>Hello, {{ @name }}!</p>
```

and the code needed to view this template:-

``` php
$f3=require('lib/base.php');
$f3->route('GET /',
    function($f3) {
        $f3->set('name','world');
        $template=new Template;
        echo $template->render('template.htm');
        // Above lines can be written as:-
        // echo Template::instance()->render('template.htm');
    }
);
$f3->run();
```

Like routing tokens used for catching variables in URLs (still remember the `GET /brew/@count` example in the previous section?), F3 template tokens begin with the `@` symbol followed by a series of letters and digits enclosed in curly braces. The first character must be alpha. Template tokens have a one-to-one correspondence with framework variables. The framework automatically replaces a token with the value stored in a variable of the same name.

In our example, F3 replaces the `@name` token in our template with the value we assigned to the name variable. At runtime, the output of the above code will be:-

``` html
<p>Hello, world</p>
```

Worried about performance of F3 templates? At runtime, the framework parses and compiles/converts an F3 template to PHP code the first time it's displayed via `$template->render()`. The framework then uses this compiled code in all subsequent calls. Hence, performance should be the same as PHP templates, if not better due to code optimization done by the template compiler when more complex templates are involved.

Whether you use PHP's template engine or F3's own, template rendering can be significantly faster if you have APC, WinCache or XCache available on your server.

As mentioned earlier, framework variables can hold any PHP data type. However, usage of non-scalar data types in F3 templates may produce strange results if you're not careful. Expressions in curly braces will always be evaluated and converted to string. You should limit your user interface variables to simple scalars:- `string`, `integer`, `boolean` or `float` data types.

But what about arrays? Fat-Free recognizes arrays and you can employ them in your templates. You can have something like:-

``` html
<p>{{ @buddy[0] }}, {{ @buddy[1] }}, and {{ @buddy[2] }}</p>
```

And populate the `@buddy` array in your PHP code before serving the template:-

``` php
$f3->set('buddy',array('Tom','Dick','Harry'));
```

However, if you simply insert `{{ @buddy }}` in your template, PHP 5.3 will replace it with `'Array'` because it converts the token to a string. PHP 5.4, on the other hand, will generate an `Array to string conversion` notice at runtime.

F3 allows you to embed expressions in templates. These expressions may take on various forms, like arithmetic calculations, boolean expressions, PHP constants, etc. Here are a few examples:-

``` html
{{ 2*(@page-1) }}
{{ (int)765.29+1.2e3 }}
<option value="F" {{ @active?'selected="selected"':'' }}>Female</option>
{{ var_dump(@xyz) }}
<p>That is {{ preg_match('/Yes/i',@response)?'correct':'wrong' }}!</p>
{{ @obj->property }}
```

Framework variables may also contain anonymous functions:

``` php
$f3->set('func',
    function($a,$b) {
        return $a.', '.$b;
    }
);
```

The F3 template engine will interpret the token as expected, if you specify the following expression:

``` html
{{ @func('hello','world') }}
```

### Templates Within Templates

Simple variable substitution is one thing all template engines have. Fat-Free has more up its sleeves:-

``` html
<include href="header.htm" />
```

The <include> directive will embed the contents of the header.htm template at the exact position where the directive is stated. You can also have dynamic content in the form of:-

``` html
<include href="{{ @content }}" />
```

A practical use for such template directive is when you have several pages with a common HTML layout but with different content. Instructing the framework to insert a sub-template into your main template is as simple as writing the following PHP code:-

``` php
// switch content to your blog sub-template
$f3->set('content','blog.htm');
// in another route, switch content to the wiki sub-template
$f3->set('content','wiki.htm');
```

A sub-template may in turn contain any number of <include> directives. F3 allows unlimited nested templates.

You can specify filenames with something other than .htm or .html file extensions, but it's easier to preview them in your Web browser during the development and debugging phase. The template engine is not limited to rendering HTML files. In fact you can use the template engine to render other kinds of files.

The `<include>` directive also has an optional `if` attribute so you can specify a condition that needs to be satisfied before the sub-template is inserted:-

``` html
<include if="{{ count(@items) }}" href="items.htm" />
```

### Exclusion of Segments

During the course of writing/debugging F3-powered programs and designing templates, there may be instances when disabling the display of a block of HTML may be handy. You can use the `<exclude>` directive for this purpose:-

``` html
<exclude>
    <p>A chunk of HTML we don't want displayed at the moment</p>
</exclude>
```

That's like the `<!-- comment -->` HTML comment tag, but the `<exclude>` directive makes the HTML block totally invisible once the template is rendered.

Here's another way of excluding template content or adding comments:-

``` html
{{* <p>A chunk of HTML we don't want displayed at the moment</p> *}}
```

### Conditional Segments

Another useful template feature is the `<check>` directive. It allows you to embed an HTML fragment depending on the evaluation of a certain condition. Here are a few examples:-

``` html
<check if="{{ @page=='Home' }}">
    <false><span>Inserted if condition is false</span></false>
</check>
<check if="{{ @gender=='M' }}">
    <true>
        <div>Appears when condition is true</div>
    </true>
    <false>
        <div>Appears when condition is false</div>
    </false>
</check>
```

You can have as many nested `<check>` directives as you need.

An F3 expression inside an if attribute that equates to `NULL`, an empty string, a boolean `FALSE`, an empty array or zero, automatically invokes `<false>`. If your template has no `<false>` block, then the `<true>` opening and closing tags are optional:-

``` html
<check if="{{ @loggedin }}">
    <p>HTML chunk to be included if condition is true</p>
</check>
```

### Repeating Segments

Fat-Free can also handle repetitive HTML blocks:-

``` html
<repeat group="{{ @fruits }}" value="{{ @fruit }}">
    <p>{{ trim(@fruit) }}</p>
</repeat>
```

The `group` attribute `@fruits` inside the `<repeat>` directive must be an array and should be set in your PHP code accordingly:-

``` php
$f3->set('fruits',array('apple','orange ',' banana'));
```

Nothing is gained by assigning a value to `@fruit` in your application code. Fat-Free ignores any preset value it may have because it uses the variable to represent the current item during iteration over the group. The output of the above HTML template fragment and the corresponding PHP code becomes:-

``` html
<p>apple</p>
<p>orange</p>
<p>banana</p>
```

The framework allows unlimited nesting of `<repeat>` blocks:-

``` html
<repeat group="{{ @div }}" key="{{ @ikey }}" value="{{ @idiv }}">
    <div>
        <p><span><b>{{ @ikey }}</b></span></p>
        <p>
        <repeat group="{{ @idiv }}" value="{{ @ispan }}">
            <span>{{ @ispan }}</span>
        </repeat>
        </p>
    </div>
</repeat>
```

Apply the following F3 command:-

``` php
$f3->set('div',
    array(
        'coffee'=>array('arabica','barako','liberica','kopiluwak'),
        'tea'=>array('darjeeling','pekoe','samovar')
    )
);
```

As a result, you get the following HTML fragment:-

``` html
<div>
    <p><span><b>coffee</b></span></p>
    <p>
        <span>arabica</span>
        <span>barako</span>
        <span>liberica</span>
        <span>kopiluwak</span>
    <p>
</div>
<div>
    <p><span><b>tea</b></span></p>
    <p>
        <span>darjeeling</span>
        <span>pekoe</span>
        <span>samovar</span>
    </p>
</div>
```

Amazing, isn't it? And the only thing you had to do in PHP was to define the contents of a single F3 variable `div` to replace the `@div` token. Fat-Free makes both programming and Web template design really easy.

The `<repeat>` template directive's `value` attribute returns the value of the current element in the iteration. If you need to get the array key of the current element, use the `key` attribute instead. The `key` attribute is optional.

`<repeat>` also has an optional counter attribute that can be used as follows:-

``` html
<repeat group="{{ @fruits }}" value="{{ @fruit }}" counter="{{ @ctr }}">
    <p class="{{ @ctr%2?'odd':'even' }}">{{ trim(@fruit) }}</p>
</repeat>
```

Internally, F3's template engine records the number of loop iterations and saves that value in the variable/token `@ctr`, which is used in our example to determine the odd/even classification.

### Embedding Javascript and CSS

If you have to insert F3 tokens inside a `<script>` or `<style>` section of your template, the framework will still replace them the usual way:-

``` html
<script type="text/javascript">
    function notify() {
        alert('You are logged in as: {{ @userID }}');
    }
</script>
```

Embedding template directives inside your `<script>` or `<style>` tags requires no special handling:-

``` html
<script type="text/javascript">
	var discounts=[];
    <repeat group="{{ @rates }}" value="{{ @rate }}">
        // whatever you want to repeat in Javascript, e.g.
        discounts.push("{{ @rate }}");
    </repeat>
</script>
```

### Document Encoding

By default, Fat-Free uses the UTF-8 character set unless changed. You can override this behavior by issuing something like:-

``` php
$f3->set('ENCODING','ISO-8859-1');
```

Once you inform the framework of the desired character set, F3 will use it in all HTML and XML templates until altered again.

### All Kinds of Templates

As mentioned earlier in this section, the framework isn't limited to HTML templates. You can process XML templates just as well. The mechanics are pretty much similar. You still have the same `{{ @variable }}` and `{{ expression }}` tokens, `<repeat>`, `<check>`, `<include>`, and `<exclude>` directives at your disposal. Just tell F3 that you're passing an XML file instead of HTML:-

``` php
echo Template::instance()->render('template.xml','application/xml');
```

The second argument represents the MIME type of the document being rendered.

The View component of MVC covers everything that doesn't fall under the Model and Controller, which means your presentation can and should include all kinds of user interfaces, like RSS, e-mail, RDF, FOAF, text files, etc. The example below shows you how to separate your e-mail presentation from your application's business logic:-

``` html
MIME-Version: 1.0
Content-type: text/html; charset={{ @ENCODING }}
From: {{ @from }}
To: {{ @to }}
Subject: {{ @subject }}

<p>Welcome, and thanks for joining {{ @site }}!</p>
```

Save the above e-mail template as welcome.txt. The associated F3 code would be:-

``` php
$f3->set('from','<no-reply@mysite.com>');
$f3->set('to','<slasher@throats.com>');
$f3->set('subject','Welcome');
ini_set('sendmail_from',$f3->get('from'));
mail(
    $f3->get('to'),
    $f3->get('subject'),
    Template::instance()->render('email.txt','text/html')
);
```

Tip: Replace the SMTP mail() function with imap_mail() if your script communicates with an IMAP server.

Now isn't that something? Of course, if you have a bundle of e-mail recipients, you'd be using a database to populate the firstName, lastName, and email tokens.

Here's an alternative solution using the F3's SMTP plug-in:-

``` php
$mail=new SMTP('smtp.gmail.com',465,'SSL','account@gmail.com','secret');
$mail->set('from','<no-reply@mysite.com>');
$mail->set('to','"Slasher" <slasher@throats.com>');
$mail->set('subject','Welcome');
$mail->send(Template::instance()->render('email.txt'));
```

### Multilingual Support

F3 supports multiple languages right out of the box.

First, create a dictionary file with the following structure (one file per language):-

``` php
<?php
return array(
    'love'=>'I love F3',
    'today'=>'Today is {0,date}',
    'pi'=>'{0,number}',
    'money'=>'Amount remaining: {0,number,currency}'
);
```

Save it as `dict/en.php`. Let's create another dictionary, this time for German. Save the file as `dict/de.php`:-

``` php
<?php
return array(
    'love'=>'Ich liebe F3',
    'today'=>'Heute ist {0,date}',
    'money'=>'Restbetrag: {0,number,currency}'
);
```

Dictionaries are nothing more than key-value pairs. F3 automatically instantiates framework variables based on the keys in the language files. As such, it's easy to embed these variables as tokens in your templates. Using the F3 template engine:-

``` html
<h1>{{ @love }}</h1>
<p>
{{ @today,time() | format }}.<br />
{{ @money,365.25 | format }}<br />
{{ @pi }}
</p>
```

And the longer version that utilizes PHP as a template engine:-

``` php
<?php $f3=Base::instance(); ?>
<h1><?php echo $f3->get('love'); ?></h1>
<p>
    <?php echo $f3->get('today',time()); ?>.<br />
    <?php echo $f3->get('money',365.25); ?>
    <?php echo $f3->get('pi'); ?>
</p>
```

Next, we instruct F3 to look for dictionaries in the `dict/` folder:-

``` php
$f3->set('LOCALES','dict/');
```

But how does the framework determine which language to use? F3 will detect it automatically by looking at the HTTP request headers first, specifically the `Accept-Language` header sent by the browser.

To override this behavior, you can trigger F3 to use a language specified by the user or application:-

``` php
$f3->set('LANGUAGE','de');
```

**Note:** In the above example, the key pi exists only in the English dictionary. The framework will always use English (`en`) as a fallback to populate keys that are not present in the specified (or detected) language.

You may also create dictionary files for language variants like `en-US`, `es-AR`, etc. In this case, F3 will use the language variant first (like `es-AR`). If there are keys that do not exist in the variant, the framework will look up the key in the root language (`es`), then use the `en` language file as the final fallback.
Dictionary key-value pairs become F3 variables once referenced. Make sure the keys do not conflict with any framework variable instantiated via `$f3->set()`, `$f3->mset()`, or `$f3->config()`.

Did you notice the peculiar `'Today is {0,date}'` pattern in our previous example? F3's multilingual capability hinges on string/message formatting rules of the ICU project. The framework uses its own subset of the ICU string formatting implementation. There is no need for PHP's `intl` extension to be activated on the server.

One more thing: F3 can also load .ini-style formatted files as dictionaries:-

``` ini
love="I love F3"
today="Today is {0,date}"
pi="{0,number}"
money="Amount remaining: {0,number,currency}"
```

Save it as `dict/en.ini` so the framework can load it automatically.

### Data Sanitation

By default, both view handler and template engine escapes all rendered variables, i.e. converted to HTML entities to protect you from possible XSS and code injection attacks. On the other hand, if you wish to pass valid HTML fragments from your application code to your template:-

``` php
$f3->set('ESCAPE',FALSE);
```

This may have undesirable effects. You might not want all variables to pass through unescaped. Fat-Free allows you to unescape variables individually. For F3 templates:-

``` html
{{ @html_content | raw }}
```

In the case of PHP templates:-

``` php
<?php echo Base::instance()->raw($html_content); ?>
```

As an addition to auto-escaping of F3 variables, the framework also gives you a free hand at sanitizing user input from HTML forms:-

``` php
$f3->scrub($_GET,'p; br; span; div; a');
```

This command will strip all tags (except those specified in the second argument) and unsafe characters from the specified variable. If the variable contains an array, each element in the array is sanitized recursively. If an asterisk (*) is passed as the second argument, `$f3->scrub()` permits all HTML tags to pass through untouched and simply remove unsafe control characters.

## Databases

### Connecting to a Database Engine

Fat-Free is designed to make the job of interfacing with SQL databases a breeze. If you're not the type to immerse yourself in details about SQL, but lean more towards object-oriented data handling, you can go directly to the next section of this tutorial. However, if you need to do some complex data-handling and database performance optimization tasks, SQL is the way to go.

Establishing communication with a SQL engine like MySQL, SQLite, SQL Server, Sybase, and Oracle is done using the familiar `$f3->set()` command. Connecting to a SQLite database would be:-

``` php
$db=new DB\SQL('sqlite:/absolute/path/to/your/database.sqlite'));
```

Another example, this time with MySQL:-

``` php
$db=new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=mysqldb',
    'admin',
    'p455w0rD'
);
```

### Querying the Database

OK. That was easy, wasn't it? That's pretty much how you would do the same thing in ordinary PHP. You just need to know the DSN format of the database you're connecting to. See the PDO section of the PHP manual.

Let's continue our PHP code:-

``` php
$f3->set('result',$db->exec('SELECT brandName FROM wherever'));
echo Template::instance()->render('abc.htm');
```

Huh, what's going on here? Shouldn't we be setting up things like PDOs, statements, cursors, etc.? The simple answer is: you don't have to. F3 simplifies everything by taking care of all the hard work in the backend.

This time we create an HTML template like `abc.htm` that has at a minimum the following:-

``` html
<repeat group="{{ @result }}" value="{{ @item }}">
    <span>{{ @item.brandName  }}</span>
</repeat>
```

In most instances, the SQL command set should be enough to generate a Web-ready result so you can use the `result` array variable in your template directly. Be that as it may, Fat-Free will not stop you from getting into its SQL handler internals. In fact, F3's `DB\SQL` class derives directly from PHP's `PDO` class, so you still have access to the underlying PDO components and primitives involved in each process, if you need some fine-grain control.

### Transactions

Here's another example. Instead of a single statement provided as an argument to the `$db->exec()` command, you can also pass an array of SQL statements:-

``` php
$db->exec(
    array(
        'DELETE FROM diet WHERE food="cola"',
        'INSERT INTO diet (food) VALUES ("carrot")',
        'SELECT * FROM diet'
    )
);
```

F3 is smart enough to know that if you're passing an array of SQL instructions, this indicates a SQL batch transaction. You don't have to worry about SQL rollbacks and commits because the framework will automatically revert to the initial state of the database if any error occurs during the transaction. If successful, F3 commits all changes made to the database.

You can also start and end a transaction programmatically:-

``` php
$db->begin();
$db->exec('DELETE FROM diet WHERE food="cola"');
$db->exec('INSERT INTO diet (food) VALUES ("carrot")');
$db->exec('SELECT * FROM diet');
$db->commit();
```

A rollback will occur if any of the statements encounter an error.

To get a list of all database instructions issued:-

``` php
echo $db->log();
```

### Parameterized Queries

Passing string arguments to SQL statements is fraught with danger. Consider this:-

``` php
$db->exec(
    'SELECT * FROM users '.
    'WHERE username="'.$f3->get('POST.userID'.'"')
);
```

If the `POST` variable `userID` does not go through any data sanitation process, a malicious user can pass the following string and damage your database irreversibly:-

``` sql
admin"; DELETE FROM users; SELECT "1
```

Luckily, parameterized queries help you mitigate these risks:-

``` php
$db->exec(
    'SELECT * FROM users WHERE userID=?',
    $f3->get('POST.userID')
);
```

If F3 detects that the value of the query parameter/token is a string, the underlying data access layer escapes the string and adds quotes as necessary.

Our example in the previous section will be a lot safer from SQL injection if written this way:-

``` php
$db->exec(
    array(
        'DELETE FROM diet WHERE food=:name',
        'INSERT INTO diet (food) VALUES (?)',
        'SELECT * FROM diet'
    ),
    array(
        array(':name'=>'cola'),
        array(1=>'carrot'),
        NULL
    )
);
```

### CRUD (But With a Lot of Style)

F3 is packed with easy-to-use object-relational mappers (ORMs) that sit between your application and your data - making it a lot easier and faster for you to write programs that handle common data operations - like creating, retrieving, updating, and deleting (CRUD) information from SQL and NoSQL databases. Data mappers do most of the work by mapping PHP object interactions to the corresponding backend queries.

Suppose you have an existing MySQL database containing a table of users of your application. (SQLite, PostgreSQL, SQL Server, Sybase will do just as well.) It would have been created using the following SQL command:-

``` sql
CREATE TABLE users (
    userID VARCHAR(30),
    password VARCHAR(30),
    visits INT,
    PRIMARY KEY(userID)
);
```

**Note:** MongoDB is a NoSQL database engine and inherently schema-less. F3 has its own fast and lightweight NoSQL implementation called Jig, which uses PHP-serialized or JSON-encoded flat files. These abstraction layers require no rigid data structures. Fields may vary from one record to another. They can also be defined or dropped on the fly.

Now back to SQL. First, we establish communication with our database.

``` php
$db=new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=mysqldb',
    'admin',
    'wh4t3v3r'
);
```

To retrieve a record from our table:-

``` php
$user=new DB\SQL\Mapper($db,'users');
$user->load(array('userID=?','tarzan'));
```

The first line instantiates a data mapper object that interacts with the `users` table in our database. Behind the scene, F3 retrieves the structure of the `users` table and determines which field(s) are defined as primary key(s). At this point, the mapper object contains no data yet (dry state) so `$user` is nothing more than a structured object - but it contains the methods it needs to perform the basic CRUD operations and some extras. To retrieve a record from our users table with a `userID` field containing the string value `tarzan`, we use the `load() method`. This process is called "auto-hydrating" the data mapper object.

Easy, wasn't it? F3 understands that a SQL table already has a structural definition existing within the database engine itself. Unlike other frameworks, F3 requires no extra class declarations (unless you want to extend the data mappers to fit complex objects), no redundant PHP array/object property-to-field mappings (duplication of efforts), no code generators (which require code regeneration if the database structure changes), no stupid XML/YAML files to configure your models, no superfluous commands just to retrieve a single record. With F3, a simple resizing of a `varchar` field in MySQL does not demand a change in your application code. Consistent with MVC and "separation of concerns", the database admin has as much control over the data (and the structures) as a template designer has over HTML/XML templates.

If you prefer working with NoSQL databases, the similarities in query syntax are superficial. In the case of the MongoDB data mapper, the equivalent code would be:-

``` php
$db=new DB\Mongo('mongodb://localhost:27017','testdb');
$user=new DB\Mongo\Mapper($db,'users');
$user->load(array('userID'=>'tarzan'));
```

With Jig, the syntax is similar to F3's template engine:-

``` php
$db=new DB\Jig('db/data/',DB\Jig::FORMAT_JSON);
$user=new DB\Jig\Mapper($db,'users');
$user->load(array('@userID=?','tarzan'));
```

### The Smart SQL ORM

The framework automatically maps the field `visits` in our table to a data mapper property during object instantiation, i.e. `$user=new DB\SQL\Mapper($db,'users');`. Once the object is created, `$user->password` and `$user->userID` would map to the `password` and `userID` fields in our table, respectively.

You can't add or delete a mapped field, or change a table's structure using the ORM. You must do this in MySQL, or whatever database engine you're using. After you make the changes in your database engine, Fat-Free will automatically synchronize the new table structure with your data mapper object when you run your application.

F3 derives the data mapper structure directly from the database schema. No guesswork involved. It understands the differences between MySQL, SQLite, MSSQL, Sybase, and PostgreSQL database engines.

SQL identifiers should not use reserved words, and should be limited to alphanumeric characters `A-Z`, `0-9`, and the underscore symbol (`_`). Column names containing spaces (or special characters) and surrounded by quotes in the data definition are not compatible with the ORM. They cannot be represented properly as PHP object properties.

Let's say we want to increment the user's number of visits and update the corresponding record in our users table, we can add the following code:-

``` php
$user->visits++;
$user->save();
```

If we wanted to insert a record, we follow this process:-

``` php
$user=new DB\SQL\Mapper($db,'users');
// or $user=new DB\Mongo\Mapper($db,'users');
// or $user=new DB\Jig\Mapper($db,'users');
$user->userID='jane';
$user->password=md5('secret');
$user->visits=0;
$user->save();
```

We still use the same `save()` method. But how does F3 know when a record should be inserted or updated? At the time a data mapper object is auto-hydrated by a record retrieval, the framework keeps track of the record's primary keys (or `_id`, in the case of MongoDB and Jig) - so it knows which record should be updated or deleted - even when the values of the primary keys are changed. A programmatically-hydrated data mapper - the values of which were not retrieved from the database, but populated by the application - will not have any memory of previous values in its primary keys. The same applies to MongoDB and Jig, but using object `_id` as reference. So, when we instantiated the `$user` object above and populated its properties with values from our program - without at all retrieving a record from the user table, F3 knows that it should insert this record.

A mapper object will not be empty after a `save()`. If you wish to add a new record to your database, you must first dehydrate the mapper:-

``` php
$user->reset();
$user->userID='cheetah';
$user->password=md5('unknown');
$user->save();
```

Calling `save()` a second time without invoking `reset()` will simply update the record currently pointed to by the mapper.

### Caveat for SQL Tables

Although the issue of having primary keys in all tables in your database is argumentative, F3 does not stop you from creating a data mapper object that communicates with a table containing no primary keys. The only drawback is: you can't delete or update a mapped record because there's absolutely no way for F3 to determine which record you're referring to plus the fact that positional references are not reliable. Row IDs are not portable across different SQL engines and may not be returned by the PHP database driver.

To remove a mapped record from our table, invoke the `erase()` method on an auto-hydrated data mapper. For example:-

``` php
$user=new DB\SQL\Mapper($db,'users');
$user->load(array('userID=? AND password=?','cheetah','ch1mp'));
$user->erase();
```

Jig's query syntax would be slightly similar:-

``` php
$user=new DB\Jig\Mapper($db,'users');
$user->load(array('@userID=? AND @password=?','cheetah','chimp'));
$user->erase();
```

And the MongoDB equivalent would be:-

``` php
$user=new DB\Mongo\Mapper($db,'users');
$user->load(array('userID'=>'cheetah','password'=>'chimp'));
$user->erase();
```

### The Weather Report

To find out whether our data mapper was hydrated or not:-

``` php
if ($user->dry())
    echo 'No record matching criteria';
```

### Beyond CRUD

We've covered the CRUD handlers. There are some extra methods that you might find useful:-

``` php
$f3->set('user',new DB\SQL\Mapper($db,'users'));
$f3->get('user')->copyFrom('POST');
$f3->get('user')->save();
```

Notice that we can also use Fat-Free variables as containers for mapper objects.
The `copyFrom()` method hydrates the mapper object with elements from a framework array variable, the array keys of which must have names identical to the mapper object properties, which in turn correspond to the record's field names. So, when a Web form is submitted (assuming the HTML name attribute is set to `userID`), the contents of that input field is transferred to `$_POST['userID']`, duplicated by F3 in its `POST.userID` variable, and saved to the mapped field `$user->userID` in the database. The process becomes very simple if they all have identically-named elements. Consistency in array keys, i.e. template token names, framework variable names and field names is key :)

On the other hand, if we wanted to retrieve a record and copy the field values to a framework variable for later use, like template rendering:-

``` php
$f3->set('user',new DB\SQL\Mapper($db,'users'));
$f3->get('user')->load(array('userID=?','jane'));
$f3->get('user')->copyTo('POST');
```

We can then assign {{ @POST.userID }} to the same input field's value attribute. To sum up, the HTML input field will look like this:-

``` html
<input type="text" name="userID" value="{{ @POST.userID }}"/>
```

The `save()`, `update()`, `copyFrom()` data mapper methods and the parameterized variants of `load()` and `erase()` are safe from SQL injection.

### Navigation and Pagination

By default, a data mapper's `load()` method retrieves only the first record that matches the specified criteria. If you have more than one that meets the same condition as the first record loaded, you can use the `skip()` method for navigation:-

``` php
$user=new DB\SQL\Mapper($db,'users');
$user->load('visits>3');
// Rewritten as a parameterized query
$user->load(array('visits>?',3));

// For MongoDB users:-
// $user=new DB\Mongo\Mapper($db,'users');
// $user->load(array('visits'=>array('$gt'=>3)));

// If you prefer Jig:-
// $user=new DB\Jig\Mapper($db,'users');
// $user->load('@visits>?',3);

// Display the userID of the first record that matches the criteria
echo $user->userID;
// Go to the next record that matches the same criteria
$user->skip(); // Same as $user->skip(1);
// Back to the first record
$user->skip(-1);
// Move three records forward
$user->skip(3);
```

You may use `$user->next()` as a substitute for `$user->skip()`, and `$user->prev()` if you think it gives more meaning to `$user->skip(-1)`.

Use the `dry()` method to check if you've maneuvered beyond the limits of the result set. `dry()` will return TRUE if you try `skip(-1)` on the first record. It will also return TRUE if you `skip(1)` on the last record that meets the retrieval criteria.

The `load()` method accepts a second argument: an array of options containing key-value pairs such as:-

``` php
$user->load(
    array('visits>?',3),
    array(
        'order'=>'userID DESC'
        'offset'=>5,
        'limit'=>3
    )
);
```

If you're using MySQL, the query translates to:-

``` mysql
SELECT * FROM users
WHERE visits>3
ORDER BY userID DESC
LIMIT 3 OFFSET 5;
```

This is one way of presenting data in small chunks. Here's another way of paginating results:-

``` php
$page=$user->paginate(2,5,array('visits>?',3));
```

In the above scenario, F3 will retrieve records that match the criteria `'visits>3'`. It will then limit the results to 5 records (per page) starting at page offset 2 (0-based). The framework will return an array consisting of the following elements:-

```
[subset] array of mapper objects that match the criteria
[count] number of of subsets available
[pos] actual subset position
```

The actual subset position returned will be NULL if the first argument of `paginate()` is a negative number or exceeds the number of subsets found.

### Virtual Fields

There are instances when you need to retrieve a computed value of a field, or a cross-referenced value from another table. Enter virtual fields. The SQL mini-ORM allows you to work on data derived from existing fields.

Suppose we have the following table defined as:-

``` sql
CREATE TABLE products
    productID VARCHAR(30),
    description VARCHAR(255),
    supplierID VARCHAR(30),
    unitprice DECIMAL(10,2),
    quantity INT,
    PRIMARY KEY(productID)
);
```

No `totalprice` field exists, so we can tell the framework to request from the database engine the arithmetic product of the two fields:-

``` php
$item=new DB\SQL\Mapper($db,'products');
$item->totalprice='unitprice*quantity';
$item->load(array('productID=:pid',':pid'=>'apple'));
echo $item->totalprice;
```

The above code snippet defines a virtual field called `totalprice` which is computed by multiplying `unitprice` by the `quantity`. The SQL mapper saves that rule/formula, so when the time comes to retrieve the record from the database, we can use the virtual field like a regular mapped field.

You can have more complex virtual fields:-

``` php
$item->mostNumber='MAX(quantity)';
$item->load();
echo $item->mostNumber;
```

This time the framework retrieves the product with the highest quantity (notice the `load()` method does not define any criteria, so all records in the table will be processed). Of course, the virtual field `mostNumber` will still give you the right figure if you wish to limit the expression to a specific group of records that match a specified criteria.

You can also derive a value from another table:-

``` php
$item->supplierName=
    'SELECT name FROM suppliers '.
    'WHERE products.supplierID=suppliers.supplierID';
$item->load();
echo $item->supplierName;
```

Every time you load a record from the products table, the ORM cross-references the `supplerID` in the `products` table with the `supplierID` in the `suppliers` table.

To destroy a virtual field, use `unset($item->totalPrice);`. The `isset($item->totalPrice)` expression returns TRUE if the `totalPrice` virtual field was defined, or FALSE if otherwise.

Remember that a virtual field must be defined prior to data retrieval. The ORM does not perform the actual computation, nor the derivation of results from another table. It is the database engine that does all the hard work.

### Seek and You Shall Find

If you have no need for record-by-record navigation, you can retrieve an entire batch of records in one shot:-

``` php
$frequentUsers=$user->find(array('visits>?',3),array('order'=>'userID'));
```

Jig mapper's query syntax has a slight resemblance:-

``` php
$frequentUsers=$user->find(array('@visits>?',3),array('order'=>'userID'));
```

The equivalent code using the MongoDB mapper:-

``` php
$frequentUsers=$user->find(array('visits'=>array('$gt'=>3)),array('userID'=>1));
```

The `find()` method searches the `users` table for records that match the criteria, sorts the result by `userID` and returns the result as an array of mapper objects. `find('visits>3')` is different from `load('visits>3')`. The latter refers to the current `$user` object. `find()` does not have any effect on `skip()`.

**Important:** Declaring an empty condition, NULL, or a zero-length string as the first argument of `find()` or `load()` will retrieve all records. Be sure you know what you're doing - you might exceed PHP's memory_limit on large tables or collections.

The `find()` method has the following syntax:-

``` php
find(
    $criteria,
    array(
        'group'=>'foo',
        'order'=>'foo,bar',
        'limit'=>5,
        'offset'=>0
    )
);
```

find() returns an array of objects. Each object is a mapper to a record that matches the specified criteria.:-

``` php
$place=new DB\SQL\Mapper($db,'places');
$list=$place->find('state="New York"');
foreach ($list as $obj)
    echo $obj->city.', '.$obj->country;
```

If you need to convert a mapper object to an associative array, use the `cast()` method:-

``` php
$array=$place->cast();
echo $array['city'].', '.$array['country'];
```

To retrieve the number of records in a table that match a certain condition, use the `count()` method.

``` php
if (!$user->count(array('visits>?',10)))
    echo 'We need a better ad campaign!';
```

There's also a `select()` method that's similar to `find()` but provides more fine-grained control over fields returned. It has a SQL-like syntax:-

``` php
select(
    'foo, bar, MIN(baz) AS lowest',
    'foo > ?',
    array(
        'group'=>'foo, bar',
        'order'=>'baz ASC',
        'limit'=>5,
        'offset'=>3
    )
);
```

Much like the `find()` method, `select()` does not alter the mapper object's contents. It only serves as a convenience method for querying a mapped table. The return value of both methods is an array of mapper objects. Using `dry()` to determine whether a record was found by an of these methods is inappropriate. If no records match the `find()` or `select()` criteria, the return value is an empty array.

### Profiling

If you ever want to find out which SQL statements issued directly by your application (or indirectly thru mapper objects) are causing performance bottlenecks, you can do so with a simple:-

``` php
echo $db->log();
```

F3 keeps track of all commands issued to the underlying SQL database driver, as well as the time it takes for each statement to complete - just the right information you need to tweak application performance.

### Sometimes It Just Ain't Enough

In most cases, you can live by the comforts given by the data mapper methods we've discussed so far. If you need the framework to do some heavy-duty work, you can extend the SQL mapper by declaring your own classes with custom methods - but you can't avoid getting your hands greasy on some hardcore SQL:-

``` php
class Vendor extends DB\SQL\Mapper {

    // Instantiate mapper
    function __construct(DB\SQL $db) {
        // This is where the mapper and DB structure synchronization occurs
        parent::__construct($db,'vendors');
    }

    // Specialized query
    function listByCity() {
        return $this->select(
            'vendorID,name,city',array('order'=>'city DESC'));
        /*
            We could have done the the same thing with plain vanilla SQL:-
            return $this->db->exec(
                'SELECT vendorID,name,city FROM vendors '.
                'ORDER BY city DESC;'
            );
        */
    }

}

$vendor=new Vendor;
$vendor->listByCity();
```

Extending the data mappers in this fashion is an easy way to construct your application's DB-related models.

### Pros and Cons

If you're handy with SQL, you'd probably say: everything in the ORM can be handled with old-school SQL queries. Indeed. We can do without the additional event listeners by using database triggers and stored procedures. We can accomplish relational queries with joined tables. The ORM is just unnecessary overhead. But the point is - data mappers give you the added functionality of using objects to represent database entities. As a developer, you can write code faster and be more productive. The resulting program will be cleaner, if not shorter. But you'll have to weigh the benefits against the compromise in speed - specially when handling large and complex data stores. Remember, all ORMS - no matter how thin they are - will always be just another abstraction layer. They still have to pass the work to the underlying SQL engines.

By design, F3's ORMs do not provide methods for directly connecting objects to each other, i.e. SQL joins - because this opens up a can of worms. It makes your application more complex than it should be, and there's the tendency of objects thru eager or lazy fetching techniques to be deadlocked and even out of sync due to object inheritance and polymorphism (impedance mismatch) with the database entities they're mapped to. There are indirect ways of doing it in the SQL mapper, using virtual fields - but you'll have to do this programmatically and at your own risk.

If you are tempted to apply "pure" OOP concepts in your application to represent all your data (because "everything is an object"), keep in mind that data almost always lives longer than the application. Your program may already be outdated long before the data has lost its value. Don't add another layer of complexity in your program by using intertwined objects and classes that deviate too much from the schema and physical structure of the data.

Before you weave multiple objects together in your application to manipulate the underlying tables in your database, think about this: creating views to represent relationships and triggers to define object behavior in the database engine are more efficient. Relational database engines are designed to handle views, joined tables and triggers. They are not dumb data stores. Tables joined in a view will appear as a single table, and Fat-Free can auto-map a view just as well as a regular table. Replicating JOINs as relational objects in PHP is slower compared to the database engine's machine code, relational algebra and optimization logic. Besides, joining tables repeatedly in our application is a sure sign that the database design needs to be audited, and views considered an integral part of data retrieval. If a table cross-references data from another table frequently, consider normalizing your structures or creating a view instead. Then create a mapper object to auto-map that view. It's faster and requires less effort.

Consider this SQL view created inside your database engine:-

``` sql
CREATE VIEW combined AS
    SELECT
        projects.project_id AS project,
        users.name AS name
    FROM projects
    LEFT OUTER JOIN users ON
        projects.project_id=users.project_id AND
        projects.user_id=users.user_id;
```

Your application code becomes simple because it does not have to maintain two mapper objects (one for the projects table and another for users) just to retrieve data from two joined tables:-

``` php
$combined=new DB\SQL\Mapper($db,'combined');
$combined->load(array('project=?',123));
echo $combined->name;
```

Tip:Use the tools as they're designed for. Fat-Free already has an easy-to-use SQL helper. Use it if you need a bigger hammer :) Try to seek a balance between convenience and performance. SQL will always be your fallback if you're working on complex and legacy data structures.

## Plug-Ins

### About F3 Plug-ins

Plug-ins are nothing more than autoloaded classes that use framework built-ins to extend F3's features and functionality. If you'd like to contribute, leave a note at the Fat-Free Discussion Area hosted by Google Groups or tell us about it in the FreeNode `#fatfree` IRC channel. Someone else might be involved in a similar project. The framework community will appreciate it a lot if we unify our efforts.

### CAPTCHA Images

There might be instances when you want to make your forms more secure against spam bots and malicious automated scripts. F3 provides a `captcha()` method to generate images with random text that are designed to be recognizable only by humans.

``` php
$img = new Image();
$img->captcha('fonts/CoolFont.ttf',16,5,'SESSION.captcha_code');
$img->render();
```

This example generates an random image based on your desired TrueType font. The `fonts/` folder is a subfolder within application's `UI` path. The second parameter indicates the font size, and the third argument defines the number of hexadecimal characters to generate.

The last argument represents an F3 variable name. This is where F3 will store the string equivalent of the CAPTCHA image. To make the string reload-safe, we specified a session variable:- `SESSION.captcha_code` which maps to `$_SESSION['captcha_code']`, which you can use later to verify whether the input element in the form submitted matches this string.

### Grabbing Data from Another Site

We've covered almost every feature available in the framework to run a stand-alone Web server. For most applications, these features will serve you quite well. But what do you do if your application needs data from another Web server on the network? F3 has the Web plugin to help you in this situation:-

``` php
$web=new Web;
$request=$web->request('http://www.google.com/');
// another way to do it:-
$request=Web::instance()->request('http://www.google.com/');
```

This simple example sends an HTTP request to the page located at www.google.com and stores it in the `$request` PHP variable. The `request()` method returns an array containing the HTTP response such that `$request['headers']` and `$request['body']` represent the response headers and body, respectively. We could have saved the contents using the F3::set command, or echo'ed the output directly to our browser. Retrieving another HTML page on the net may not have any practical purpose. But it can be particularly useful in ReSTful applications, like querying a CouchDB server.

``` php
$host='localhost:5984';
$web->request($host.'/_all_dbs'),
$web->request($host.'/testdb/',array('method'=>'PUT'));
```

You may have noticed that you can pass an array of additional options to the `request()` method:-

``` php
$web->request(
    'https://www.example.com:443?'.
    http_build_query(
        array(
            'key1'=>'value1',
            'key2'=>'value2'
        )
    ),
    array(
        'headers'=>array(
            'Accept: text/html,application/xhtml+xml,application/xml',
            'Accept-Language: en-us'
        ),
        'follow_location'=>FALSE,
        'max_redirects'=>30,
        'ignore_errors'=>TRUE
    )
);
```

If the framework variable `CACHE` is enabled, and if the remote server instructs your application to cache the response to the HTTP request, F3 will comply with the request and retrieve the cached response each time the framework receives a similar request from your application, thus behaving like a browser.

Fat-Free will use whatever means are available on your Web server for the `request()` method to run: PHP stream wrappers (`allow_url_fopen`), cURL module, or low-level sockets.

### Handling File Downloads

F3 has a utility for sending files to an HTTP client, i.e. fulfilling download requests. You can use it to hide the real path to your download files. This adds some layer of security because users won't be able to download files if they don't know the file names and their locations. Here's how it's done:-

``` php
$f3->route('GET /downloads/@filename',
    function($f3,$args) {
        // send() method returns FALSE if file doesn't exist
        if (!Web::instance()->send('/real/path/'.$args['filename']))
            // Generate an HTTP 404
        $f3->error(404);
    }
);
```

### Remoting and Distributed Applications

The `request()` method can also be used in complex SOAP or XML-RPC applications, if you find the need for another Web server to process data on your computer's behalf - thus harnessing the power of distributing computing. W3Schools.com has an excellent tutorial on SOAP. On the other hand, TutorialsPoint.com gives a nice overview of XML-RPC.

## Optimization

### Cache Engine

Caching static Web pages - so the code in some route handlers can be skipped and templates don't have to be reprocessed - is one way of reducing your Web server's work load so it can focus on other tasks. You can activate the framework's cache engine by providing a third argument to the `$f3->route()` method. Just specify the number of seconds before a cached Web page expires:-

``` php
$f3->route('GET /my_page','App->method',60);
```

Here's how it works. In this example, when F3 detects that the URL `/my_page` is accessed for the first time, it executes the route handler represented by the second argument and saves all browser output to the framework's built-in cache (server-side). A similar instruction is automatically sent to the user's Web browser (client-side), so that instead of sending an identical request to the server within the 60-second period, the browser can just retrieve the page locally. The framework uses the cache for an entirely different purpose - serving framework-cached data to other users asking for the same Web page within the 60-second time frame. It skips execution of the route handler and serves the previously-saved page directly from disk. When someone tries to access the same URL after the 60-second timer has lapsed, F3 will refresh the cache with a new copy.

Web pages with static data are the most likely candidates for caching. Fat-Free will not cache a Web page at a specified URL if the third argument in the `$f3->route()` method is zero or unspecified. F3 conforms to the HTTP specifications: only GET and HEAD requests can be cached.

Here's an important point to consider when designing your application. Don't cache Web pages unless you understand the possible unwanted side-effects of the cache at the client-side. Make sure that you activate caching on Web pages that have nothing to do with the user's session state.

For example, you designed your site in such a way that all your Web pages have the menu options: `"Home"`, `"About Us"`, and `"Login"`, displayed when a user is not logged into your application. You also want the menu options to change to: `"Home"`, `"About Us"`, and `"Logout"`, once the user has logged in. If you instructed Fat-Free to cache the contents of `"About Us"` page (which includes the menu options), it does so and also sends the same instruction to the HTTP client. Regardless of the user's session state, i.e. logged in or logged out, the user's browser will take a snapshot of the page at the session state it was in. Future requests by the user for the `"About Us"` page before the cache timeout expires will display the same menu options available at that time the page was initially saved. Now, a user may have already logged in, but the menu options are still the same as if no such event occurred. That's not the kind of behavior we want from our application.

Some pointers:-

* Don't cache dynamic pages. It's quite obvious you don't want to cache data that changes frequently. You can, however, activate caching on pages that contain data updated on an hourly, daily or even yearly basis.For security reasons, the framework restricts cache engine usage to HTTP `GET` routes only. It will not cache submitted forms!Don't activate the cache on Web pages that at first glance look static. In our example, the "About Us" content may be static, but the menu isn't.
* Activate caching on pages that are available only in ONE session state. If you want to cache the `"About Us"` page, make sure it's available only when a user is not logged in.
* If you have a RAMdisk or fast solid-state drive, configure the `CACHE` global variable so it points to that drive. This will make your application run like a Formula 1 race car.

**Note:** Don't set the timeout value to a very long period until you're ready to roll out your application, i.e. the release or production state. Changes you make to any of your PHP scripts may not have the expected effect on the displayed output if the page exists in the framework cache and the expiration period has not lapsed. If you do alter a program that generates a page affected by the cache timer and you want these changes to take effect immediately, you should clear the cache by erasing the files in the cache/ directory (or whatever path the `CACHE` global variable points to). F3 will automatically refresh the cache if necessary. At the client-side, there's little you can do but instruct the user to clear the browser's cache or wait for the cache period to expire.

PHP needs to be set up correctly for the F3 cache engine to work properly. Your operating system timezone should be synchronized with the date.timezone setting in the `php.ini` file.

Similar to routes, Fat-Free also allows you to cache database queries. Speed gains can be quite significant, specially when used on complex SQL statements that involve look-up of static data or database content that rarely changes. Activating the database query cache so the framework doesn't have to re-execute the SQL statements every time is as simple as adding a 3rd argument to the F3::sql command - the cache timeout. For example:-

``` php
$db->exec('SELECT * from sizes;',NULL,86400);
```

If we expect the result of this database query to always be `Small`, `Medium`, and `Large` within a 24-hour period, we specify `86400` seconds as the 2nd argument so Fat-Free doesn't have to execute the query more than once a day. Instead, the framework will store the result in the cache, retrieve it from the cache every time a request comes in during the specified 24-hour time frame, and re-execute the query when the timer lapses.

The SQL data mapper also uses the cache engine to optimize synchronization of table structures with the objects that represent them. The default is `60` seconds. If you make any changes to a table's structure in your database engine, you'll have to wait for the cache timer to expire before seeing the effect in your application. You can change this behavior by specifying a third argument to the data mapper constructor. Set it to a high value if you don't expect to make any further changes to your table structure.

``` php
$user=new DB\SQL\Mapper($db,'users',86400);
```

By default, Fat-Free's cache engine is disabled. You can enable it and allow it to auto-detect APC, WinCache or XCache. If it cannot find an appropriate backend, F3 will use the filesystem, i.e. the `tmp/cache/` folder:-

``` php
$f3->set('CACHE',TRUE);
```

Disabling the cache is as simple as:-

``` php
$f3->set('CACHE',FALSE);
```

If you wish to override the auto-detection feature, you can do so - as in the case of a Memcached back-end which F3 also supports:-

``` php
$f3->set('CACHE','memcache=localhost:11211');
```

You can also use the cache engine to store your own variables. These variables will persist between HTTP requests and remain in cache until the engine receives instructions to delete them. To save a value in the cache:-

``` php
$f3->set('var','I want this value saved',90);
```

`$f3->set()` method's third argument instructs the framework to save the variable in the cache for a 90-second duration. If your application issues a `$f3->get('var')` within this period, F3 will automatically retrieve the value from cache. In like manner, `$f3->clear('var')` will purge the value from both cache and RAM. If you want to determine if a variable exists in cache, `$f3->exists('var')); returns one of two possible values: FALSE if the framework variable passed does not exist in cache, or an integer representing the time the variable was saved (Un*x time in seconds, with microsecond precision).

### Keeping Javascript and CSS on a Healthy Diet

Fat-Free also has a Javascript and CSS compressor available in the Web plug-in. It can combine all your CSS files into one stylesheet (or Javascript files into a single script) so the number of components on a Web page are decreased. Reducing the number of HTTP requests to your Web server results in faster page loading. First you need to prepare your HTML template so it can take advantage of this feature. Something like:-

``` html
<link rel="stylesheet" type="text/css"
	href="/minify/css?files=typo.css,grid.css" />
```

Do the same with your Javascript files:-

``` html
<script type="text/javascript" src="/minify/js?&files=underscore.js">
</script>
```

Of course we need to set up a route so your application can handle the necessary call to the Fat-Free CSS/Javascript compressor:-

``` php
$f3->route('GET /minify/@type',
    function($f3,$args) {
        $f3->set('UI',$args['type'].'/');
        echo Web::instance()->minify($_GET['files']);
    },
    3600
);
```

And that's all there is to it! `minify()` reads each file (`typo.css` and `grid.css` in our CSS example, `underscore.js` in our Javascript example), strips off all unnecessary whitespaces and comments, combines all of the related items as a single Web page component, and attaches a far-future expiry date so the user's Web browser can cache the data. It's important that the `PARAMS.type` variable base points to the correct path. Otherwise, the URL rewriting mechanism inside the compressor won't find the CSS/Javascript files.

### Client-Side Caching

In our examples, the framework sends a far-future expiry date to the client's Web browser so any request for the same CSS or Javascript block will come from the user's hard drive. On the server side, F3 will check each request and see if the CSS or Javascript blocks have already been cached. The route we specified has a cache refresh period of `3600` seconds. Additionally, if the Web browser sends an `If-Modified-Since` request header and the framework sees the cache hasn't changed, F3 just sends an `HTTP 304 Not Modified` response so no content is actually delivered. Without the `If-Modified-Since` header, Fat-Free renders the output from the cached file if available. Otherwise, the relevant code is executed.

Tip: If you're not modifying your Javascript/CSS files frequently (as it would be if you're using a Javascript library like jQuery, MooTools, Dojo, etc.), consider adding a cache timer to the route leading to your Javascript/CSS minify handler (3rd argument of F3::route()) so Fat-Free doesn't have compress and combine these files each time such a request is received.

### PHP Code Acceleration

Want to make your site run even faster? Fat-Free works best with either Alternative PHP Cache (APC), XCache, or WinCache. These PHP extensions boost performance of your application by optimizing your PHP scripts (including the framework code).

### Bandwidth Throttling

A fast application that processes all HTTP requests and responds to them at the shortest time possible is not always a good idea - specially if your bandwidth is limited or traffic on your Web site is particularly heavy. Serving pages ASAP also makes your application vulnerable to Denial-of-Service (DOS) attacks. F3 has a bandwidth throttling feature that allows you to control how fast your Web pages are served. Your can specifies how much time it should take to process a request:-

``` php
$f3->route('/throttledpage','MyApp->handler',0,128);
```

In this example, the framework will serve the Web page at a rate of 128KiBps.

Bandwidth throttling at the application level can be particularly useful for login pages. Slow responses to dictionary attacks is a good way of mitigating this kind of security risk.

## Unit Testing

### Bullet-Proof Code

Robust applications are the result of comprehensive testing. Verifying that each part of your program conforms to the specifications and lives up to the expectations of the end-user means finding bugs and fixing them as early as possible in the application development cycle.

If you know little or nothing about unit testing methodologies, you're probably embedding pieces of code directly in your existing program to help you with debugging. That of course means you have to remove them once the program is running. Leftover code fragments, poor design and faulty implementation can creep up as bugs when you roll out your application later.

F3 makes it easy for you to debug programs - without getting in the way of your regular thought processes. The framework does not require you to build complex OOP classes, heavy test structures, and obtrusive procedures.

A unit (or test fixture) can be a function/method or a class. Let's have a simple example:-

``` php
function hello() {
    return 'Hello, World';
}
```

Save it in a file called `hello.php`. Now how do we know it really runs as expected? Let's create our test procedure:-

``` php
$f3=require('lib/base.php');

// Set up
$test=new Test;
include('hello.php');

// This is where the tests begin
$test->expect(
    is_callable('hello'),
    'hello() is a function'
);

// Another test
$hello=hello();
$test->expect(
    !empty($hello),
    'Something was returned'
);

// This test should succeed
$test->expect
    is_string($hello),
    'Return value is a string'
);

// This test is bound to fail
$test->expect(
    strlen($hello)==13,
    'String length is 13'
);

// Display the results; not MVC but let's keep it simple
foreach ($test->results() as $result) {
    echo $result['text'].'<br />';
    if ($result['status'])
        echo 'Pass';
    else
        echo 'Fail ('.$result['source'].')';
    echo '<br />';
}
```

Save it in a file called `test.php`. This way we can preserve the integrity of `hello.php`.

Now here's the meat of our unit testing process.

F3's built-in `Test` class keeps track of the result of each `expect()` call. The output of `$test->results()` is an array of arrays with the keys `text` (mirroring argument 2 of `expect()`), `status` (boolean representing the result of a test), and `source` (file name/line number of the specific test) to aid in debugging.

Fat-Free gives you the freedom to display test results in any way you want. You can have the output in plain text or even a nice-looking HTML template. So how do we run our unit test? If you saved `test.php` in the document root folder, you can just open your browser and specify the address `http://localhost/test.php`. That's all there is to it.

### Mocking HTTP Requests

F3 gives you the ability to simulate HTTP requests from within your PHP program so you can test the behavior of a particular route. Here's a simple mock request:-

``` php
$f3->mock('GET /test?foo=bar');
```

To mock a POST request and submit a simulated HTML form:-

``` php
$f3->mock('POST /test',array('foo'=>'bar'));
```

### Expecting the Worst that can Happen

Once you get the hang of testing the smallest units of your application, you can then move on to the bigger components, modules, and subsystems - checking along the way if the parts are correctly communicating with each other. Testing manageable chunks of code leads to more reliable programs that work as you expect, and weaves the testing process into the fabric of your development cycle. The question to ask yourself is:- Have I tested all possible scenarios? More often than not, those situations that have not been taken into consideration are the likely causes of bugs. Unit testing helps a lot in minimizing these occurrences. Even a few tests on each fixture can greatly reduce headaches. On the other hand, writing applications without unit testing at all invites trouble.

## Quick Reference

### System Variables

`string AGENT`

* Auto-detected HTTP user agent, e.g. `Mozilla/5.0 (Linux; Android 4.2.2; Nexus 7) AppleWebKit/537.31`.

`bool AJAX`

* `TRUE` if an XML HTTP request is detected, `FALSE` otherwise.

`string AUTOLOAD`

* Search path for user-defined PHP classes that the framework will attempt to autoload at runtime. Accepts a pipe (`|`), comma (`,`), or semi-colon (`;`) as path separator.

`string BASE`

* Path to the `index.php` main/front controller.

`string BODY`

* HTTP request body for ReSTful post-processing.

`bool/string CACHE`

* Cache backend. Unless assigned a value like `'memcache=localhost'` (and the PHP memcache module is present), F3 auto-detects the presence of APC, WinCache and XCache and uses the first available PHP module if set to TRUE. If none of these PHP modules are available, a filesystem-based backend is used (default directory: `tmp/cache`). The framework disables the cache engine if assigned a `FALSE` value.

`bool CASELESS`

* Pattern matching of routes against incoming URIs is case-insensitive by default. Set to `FALSE` to make it case-sensitive.

`array COOKIE, GET, POST, REQUEST, SESSION, FILES, SERVER, ENV`

* Framework equivalents of PHP globals. Variables may be used throughout an application. However, direct use in templates is not advised due to security risks.

`integer DEBUG`

* Stack trace verbosity. Assign values 1 to 3 for increasing verbosity levels. Zero (0) suppresses the stack trace. This is the default value and it should be the assigned setting on a production server.

`string DNSBL`

* Comma-separated list of [DNS blacklist servers](http://whatismyipaddress.com/blacklist-check). Framework generates a `403 Forbidden` error if the user's IPv4 address is listed on the specified server(s).

`array DIACRITICS`

* Key-value pairs for foreign-to-ASCII character translations.

`string ENCODING`

* Character set used for document encoding. Default value is `UTF-8`.

`array ERROR`

* Information about the last HTTP error that occurred. `ERROR.code` is the HTTP status code. `ERROR.title` contains a brief description of the error. `ERROR.text` provides greater detail. For HTTP 500 errors, use `ERROR.trace` to retrieve the stack trace.

`bool ESCAPE`

* Used to enable/disable auto-escaping.

`string EXEMPT`

* Comma-separated list of IPv4 addresses exempt from DNSBL lookups.

`string FALLBACK`

* Language (and dictionary) to use if no translation is available.

`bool HALT`

* If TRUE (default), framework stops execution after a non-fatal error is detected.

`array HEADERS`

* HTTP request headers received by the server.

`bool HIGHLIGHT`

* Enable/disable syntax highlighting of stack traces. Default value: `TRUE` (requires `code.css` stylesheet).

`string HOST`

* Server host name. If `$_SERVER['SERVER_NAME']` is not available, return value of `gethostname()` is used.

`string IP`

* Remote IP address. The framework derives the address from headers if HTTP client is behind a proxy server.

`array JAR`

* Default cookie parameters.

`string LANGUAGE`

* Current active language. Value is used to load the appropriate language translation file in the folder pointed to by `LOCALES`. If set to `NULL`, language is auto-detected from the HTTP `Accept-Language` request header.

`string LOCALES`

* Location of the language dictionaries.

`string LOGS`

* Location of custom logs.

`mixed ONERROR`

* Callback function to use as custom error handler.

`string PACKAGE`

* Framework name.

`array PARAMS`

* Captured values of tokens defined in a `route()` pattern. `PARAMS.0` contains the captured URL relative to the Web root.

`string PATTERN`

* Contains the routing pattern that matches the current request URI.

`string PLUGINS`

* Location of F3 plugins. Default value is the folder where the framework code resides, i.e. the path to `base.php`.

`int PORT`

* TCP/IP listening port used by the Web server.

`string PREFIX`

* String prepended to language dictionary terms.

`bool QUIET`

* Toggle switch for suppressing or enabling standard output and error messages. Particularly useful in unit testing.

`string REALM`

* Full canonical URL.

`string RESPONSE`

* The body of the last HTTP response. F3 populates this variable regardless of the `QUIET` setting.

`string ROOT`

* Absolute path to document root folder.

`array ROUTES`

* Contains the defined application routes.

`string SCHEME`

* Server protocol, i.e. `http` or `https`.

`string SERIALIZER`

* Default serializer. Normally set to `php`, unless PHP `igbinary` extension is auto-detected. Assign `json` if desired.

`string TEMP`

* Temporary folder for cache, filesystem locks, compiled F3 templates, etc. Default is the `tmp/` folder inside the Web root. Adjust accordingly to conform to your site's security policies.

`string TZ`

* Default timezone. Changing this value automatically calls the underlying `date_default_timezone_set()` function.

`string UI`

* Search path for user interface files used by the `View` and `Template` classes' `render()` method. Default value is the Web root. Accepts a pipe (`|`), comma (`,`), or semi-colon (`;`) as separator for multiple paths.

`callback UNLOAD`

* Executed by framework on script shutdown.

`string UPLOADS`

* Directory where file uploads are saved.

`string URI`

* Current HTTP request URI.

`string VERB`

* Current HTTP request method.

`string VERSION`

* Framework version.

### Template Directives

```
@token
```
* Replace `@token` with value of equivalent F3 variable.

```
{{ mixed expr }}
```
* Evaluate. `expr` may include template tokens, constants, operators (unary, arithmetic, ternary and relational), parentheses, data type converters, and functions. If not an attribute of a template directive, result is echoed.

```
{{ string expr | raw }}
```
* Render unescaped `expr`. F3 auto-escapes strings by default.

```
{{ string expr | esc }}
```
* Render escaped `expr`. This is the default framework behavior. The `| esc` suffix is only necessary if `ESCAPE` global variable is set to `FALSE`.

```
{{ string expr, arg1, ..., argN | format }}
```
* Render an ICU-formatted `expr` and pass the comma-separated arguments, where `arg1, ..., argn` is one of:- `'date'`, `'time'`, `'number, integer'`, `'number, currency'`, or `'number, percent'`.

```
<include
    [ if="{{ bool condition }}" ]
    href="{{ string subtemplate }}"
/>
```
* Get contents of `subtemplate` and insert at current position in template if optional condition is `TRUE`.

```
<exclude>text-block</exclude>
```
* Remove `text-block` at runtime. Used for embedding comments in templates.

```
<ignore>text-block</ignore>
```
* Display `text-block` as-is, without interpretation/modification by the template engine.

```
<check if="{{ bool condition }}">
    <true>true-block</true>
    <false>false-block</false>
</check>
```
* Evaluate condition. If `TRUE`, then `true-block` is rendered. Otherwise, `false-block` is used.

```
<loop
    from="{{ statement }}"
    to="{{ bool expr }}"
    [ step="{{ statement }}" ]>
    text-block
</loop>
```
* Evaluate `from` statement once. Check if the expression in the `to` attribute is `TRUE`, render `text-block` and evaluate `step` statement. Repeat iteration until `to` expression is `FALSE`.

```
<repeat
    group="{{ array @group|expr }}"
    [ key="{{ scalar @key }}" ]
    value="{{ mixed @value }}
    [ counter="{{ scalar @key }}" ]>
    text-block
</repeat>
```
* Repeat `text-block` as many times as there are elements in the array variable `@group` or the expression `expr`. `@key` and `@value` function in the same manner as the key-value pair in the equivalent PHP `foreach()` statement. Variable represented by `key` in `counter` attribute increments by `1` with every iteration.

```
<switch expr="{{ scalar expr }}">
    <case value="{{ scalar @value|expr }}" break="{{ bool TRUE|FALSE }}">
        text-block
    </case>
    .
    .
    .
</switch>
```
* Equivalent of the PHP switch-case jump table structure.

```
{{* text-block *}}
```
* Alias for `<exclude>`.

### API Documentation

The most up-to-date documentation is located at [http://fatfreeframework.com/](http://fatfreeframework.com/). It contains examples of usage of the various framework components.

The framework API documentation can also be viewed offline. It is contained in `lib/api/` folder of the distribution package. Use your favorite browser and point it to the `lib/api/index.html` file.

## Support and Licensing

Technical support is available at the official discussion forum: [`https://groups.google.com/forum/#!forum/f3-framework`](https://groups.google.com/forum/#!forum/f3-framework). If you need live support, you can talk to the development team and other members of the F3 community via IRC. We're on the FreeNode `#fatfree` channel (`chat.freenode.net`). Visit [`http://webchat.freenode.net/`](http://webchat.freenode.net/) to join the conversation. You can also download the [Firefox Chatzilla](https://addons.mozilla.org/en-US/firefox/addon/chatzilla/) add-on or [Pidgin](http://www.pidgin.im/) if you don't have an IRC client so you can participate in the live chat.

### Nightly Builds

F3 uses Git for version control. To clone the code repository on GitHub:-

``` bash
git clone git://github.com/bcosca/fatfree.git
```

If all you want is a zipball, grab it [**here**](https://github.com/bcosca/fatfree/archive/dev.zip).

To file a bug report, visit [`https://github.com/bcosca/fatfree/issues`](https://github.com/bcosca/fatfree/issues).

### Fair Licensing

**Fat-Free Framework is free and released as open source software covered by the terms of the [GNU Public License](http://www.gnu.org/licenses/gpl-3.0.html) (GPL v3).** You may not use the software, documentation, and samples except in compliance with the license. If the terms and conditions of this license are too restrictive for your use, alternative licensing is available for a very reasonable fee.

If you feel that this software is one great weapon to have in your programming arsenal, it saves you a lot of time and money, use it for commercial gain or in your business organization, please consider making a donation to the project. A significant amount of time, effort, and money has been spent on this project. Your donations help keep this project alive and the development team motivated. Donors and sponsors get priority support (24-hour response time on business days).

### Credits

The Fat-Free Framework is community-driven software. It can't be what it is today without the help and support from the following people and organizations:

* GitHub
* Square Lines, LLC
* Talis Group, Ltd.
* Mirosystems
* Tecnilógica
* Stehlik & Company
* G Holdings, LLC
* S2 Development, Ltd.
* Store Machine
* PHP Experts, Inc.
* Christian Knuth
* Sascha Ohms
* Jermaine Maree
* Sergey Zaretsky
* Daniel Kloke
* Brian Nelson
* Roberts Lapins
* Boris Gurevich
* Eyðun Lamhauge
* Jose Maria Garrido Diaz
* Dawn Comfort
* Johan Viberg
* Povilas Musteikis
* Andrew Snook
* Jafar Amjad
* Taylor McCall
* Raymond Kirkland
* Yuriy Gerassimenko
* William Stam
* Sam George
* Steve Wasiura
* Andreas Ljunggren
* Sashank Tadepalli
* Chad Bishop
* Bradley Slavik
* Lee Blue
* Alexander Shatilo
* Justin Noel
* Ivan Kovac
* Tony's Internet Solutions
* Charles Stigler
* Attila van der Velde
* Indoblo Commerce Ltd.
* Jens Níemeyer
* Raghu Veer Dendukuri
* NovelLead B.V.
* Emir Alp
* Dominic Schwarz
* Sven Zahrend
* LucidStorm
* Nevatech
* Matt Wielgos
* Maximilian Summe
* Caspar Frey
* FocusHeart
* Philip Lawrence
* Peter Beverwyk
* Randal Hintz
* Franz Josef
* Biswajit Nayak
* R Mohan
* Michael Messner
* Florent Racineux
* Jason Borseth
* Dmitrij Chernov
* Marek Toman
* Simone Cociancich
* Alan Holding
* Philipp Hirsch
* Aurélien Botermans
* Christian Treptow
* Кубарев Дмитрий
* Alexandru Catalin Trandafir
* Leigh Harrison
* Дмитриев Иван
* IT_GAP
* Sergeev Andrey
* Lars Brandi Jensen
* Steven J Mixon
* Roland Fath
* Justin Parker
* Costas Menico
* Mathieu-Philippe Bourgeois
* Ryan McKillop
* Chris Clarke
* Ngan Ting On

Special thanks to the selfless others who expressed their desire to remain anonymous, yet share their time, contribute code, send donations, promote the framework to a wider audience, as well as provide encouragement and regular financial assistance. Their generosity is F3's prime motivation.

[![Paypal](ui/images/donate.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MJSQL8N5LPDAY)
[![Bitcoin](ui/images/bitcoin.png)](https://coinbase.com/checkouts/7986a0da214006256d470f2f8e1a15cf)

**Copyright (c) 2009-2013 F3::Factory/Bong Cosca &lt;bong&#46;cosca&#64;yahoo&#46;com&gt;**

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/a0b5e3f40092429070b6647a2e5ca6ab "githalytics.com")](http://githalytics.com/bcosca/fatfree)
