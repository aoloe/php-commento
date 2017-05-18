# PHP-Commento

This is an alternative PHP backend for the the [Commento](https://github.com/adtac/commento) comments engine.

Commento is a lightweight, open source, tracking-free comment engine alternative to Disqus written in Go and Javascript.

This PHP backend is a simple implementation that can be installed on any "modern" shared hosting and stores the comments in json files. It uses the same Javascript libraries as Commento.

## Features

- Threaded comments on any website.
- Store the comments on your own server.

## Requirements

- PHP 7 (only tested with PHP 7, but might work with PHP 5.4+)

## Installing

- Get the <src/commento.php> file.
- Copy the <sample/index.php> in a directory that can be reached through HTTP.


## Future plans

- Add moderation:
  - Per email.
  - Moderation buttons on the page (as a "plugin" to the Commento Javascript).
- Eventually, add a version using SQlLite
- Eventually, offer a version that works without Javascript enabled.

## Todo

- Implement the Commento honeypot
- Cross site js!
- Use the config file to define a an array of valid origins for the comments

## File structure

### The `src/` directory



### The `sample/` directory

### The `data/` directory

~~~
data/url.json
data/comments/
data/comments/*.json
~~~

## Notes about Commento

~~~
routes: http.go:GetCommentsHandler

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
        
/assets

w.Header().Set("Access-Control-Allow-Origin", "*")
~~~
