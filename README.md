# PHP-Commento

This is an alternative PHP backend for the the [Commento](https://github.com/adtac/commento) comments engine.

[Commento](https://github.com/adtac/commento) is a lightweight, open source, tracking-free comment engine alternative to Disqus written in Go and Javascript.

This PHP backend is a simple PHP implementation that can be installed on any "modern" shared hosting and stores the comments in Json files. On the client side, it uses the [Commento](https://github.com/adtac/commento) Javascript libraries.

## Features

- Threaded comments on any website.
- Store the comments on your own server.

## Requirements

- tested with PHP 7.0+ and PHP 5.6+ (but might work with PHP 5.4+)

## Installing

- Get [PHP-Commento release package]() and unpack it.
- Create a `data/` directory and set it in the `COMMENTO_DATA_PATH` constant in the `index.php` file (by default, `data/` will be used).
- Install `PHP-Commento` by calling the `install` route.
- Add PHP-Commento to your HTML page(s):

  ~~~.html
  <!DOCTYPE html>
  <html lang="en">
    <head>
      <meta charset="utf-8">
      <script src="http://www.the-url-with.your/php-commento/js/commento.js"></script>
    </head>
    <body>
      <div class="content">
        <p>... Your content...</p>
      </div>

      <div id="commento">
        <!-- Commento will populate this div with comments -->
      </div>

      <script>
        Commento.init({
          serverUrl: "http://www.the-url-with.your/php-commento"
        })
      </script>
    <body>
  </html>
  ~~~

## Packaging / Installing from the sources

- Create a directory for PHP-Commento (`php-commento/`).
- Copy the <sample/index.php> file and place it in the `php-commento/` directory.
- Add the <sample/htaccess> file to the same directory and rename it to `.htaccess`.
- Get the <src/commento.php> file and place it somewhere it can be loaded from the `index.php` file.
- Create a `data/` directory and set it in the `COMMENTO_DATA_PATH` constant. (by default, `data/` will be used).
- Install `PHP-Commento` by calling `install` on the path containing (`https://your-path.com/php-commento/install`).
- Put in the `js/` directory the minified Javascript files from the Commento project:
  - <https://raw.githubusercontent.com/adtac/commento/master/assets/js/commento.js> as `commento.min.js`.
  - <https://raw.githubusercontent.com/adtac/commento/master/assets/vendor/showdown.min.js>.
- Put in the `css/` directory the minified CSS files from the Commento project:
  - <https://raw.githubusercontent.com/adtac/commento/master/assets/style/commento.css> as `commento.min.css`.
  - <https://raw.githubusercontent.com/adtac/commento/master/assets/vendor/spectre.min.css>.
- Add PHP-Commento to your HTML page(s).

If needed, you can minify the Javascript and CSS and Java with [minifier.org](http://www.minifier.org/).

## The file structure

### The `src/` directory

The `src/` directory contains the `commento.php` file with the PHP-Commento engine.

### The `sample/` directory

The `sample/` directory contains

### The `data/` directory

~~~
data/url.json
data/comments/
data/comments/*.json
~~~

## The routes

The Comment routes:

~~~
/
    - not implemented yet
    - sets w.Header().Set("Access-Control-Allow-Origin", "*")

/assets
    - not implemented

/create
    - post, form:
        - url: string, where the comment is coming from
        - comment: string, the comment
        - name: string, the name
        - parent: int, -1 if none
    - reply, json:
        - {
              "success":true,
              "message":"Comment successfully created"
          }

/get
    - post, form:
          - string url: where the comment is coming from
    - reply, json:
      {
          "success":true,
          "message":"",
          "comments":
              [
                  {
                      "id":1,
                      "url":"http://ww.impagina.org/commento/commento.html",
                      "comment":"this is a test",
                      "name":"ale",
                      "timestamp":"2017-05-12T08:32:04.333415146+02:00",
                      "parent":-1
                  }
              ]
      }
       
~~~

The routes are defined in `http.go:GetCommentsHandler`

Additional PHP-Commento routes:

~~~
/install
~~~
