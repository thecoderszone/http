# HTTP Client

A wrapper built around the powerful Guzzle HTTP client.

[![Build Status](https://travis-ci.com/thecoderszone/http.svg?branch=master)](https://travis-ci.com/thecoderszone/http)
[![codecov](https://codecov.io/gh/thecoderszone/http/branch/master/graph/badge.svg)](https://codecov.io/gh/thecoderszone/http)

## What's different
- **Response body parsing**  
  The client uses the `Content-Type` header to sniff the response type and parse it accordingly, which saves you from having to continuously write
  ```php
  json_decode($response->getBody()->getContents());
  ```
  Instead, just retrieve response properties by their key.
  ```php
  $bar = $response->foo; // short form
  $bar = $response->body['foo']; // long form
  ```
- **Response header parsing**  
  If there's only one header value for a name, thecoderszone/http gives you just that, instead of an array.
  ```php
  $response->getHeader('Content-Type'); // Guzzle: ['application/json'], thecoderszone/http: 'application/json'
  ```
- **Base URL**  
  A minor nitpick, but Guzzle unhelpfully disrespects the request base URL path when you include a `/` in the endpoint. This client doesn't.
  ```php
  $client = new Client([
      'base_url' => 'https://example.com/api'
  ]);
  $response = $client->get('url'); // Guzzle: https://example.com/api/url, thecoderszone/http: https://example.com/api/url
  $response = $client->get('/url'); // Guzzle: https://example.com/url (why would anyone want this), thecoderszone/http: https://example.com/api/url
  ```
  `thecoderszone/http` uses the `base_url` (our way, more straightforward) key rather than the `base_uri` key (Guzzle way, RFC compliant) so you can choose either.
- **Response callbacks**  
  Ideal for error handling, `thecoderszone/http` lets you specify a response callback in the client config.
  ```php
  $client = new Client([
      'callback' => function (Response $response) {
          if (!$response->isSuccessful()) {
              throw new \Exception($response->message);
          }
      },
  ]);
  ```
