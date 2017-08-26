# Zwartpet\SwaggerMocker
[![Build](https://travis-ci.org/Zwartpet/swagger-mocker.svg?branch=master)](https://travis-ci.org/Zwartpet/swagger-mocker)
[![Coverage](https://coveralls.io/repos/github/Zwartpet/swagger-mocker/badge.svg?branch=master)](https://coveralls.io/github/Zwartpet/swagger-mocker?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/Zwartpet/swagger-mocker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Zwartpet/swagger-mocker/?branch=master)

Transform your Swagger ([Open API](https://openapis.org/)) spec examples into a Symfony REST mock server

## SwaggerBundle

This project uses the [Kleijnweb\SwaggerBundle](https://github.com/kleijnweb/swagger-bundle) to provide the routes and definition validation.

## Usage

### Quick and simple

1. Checkout the project and install dependencies
2. Replace the content of web/swagger/default.json with the specification you want to mock
3. Run the application with docker-compose

### Use the docker image

[Available here](https://hub.docker.com/r/zwartpet/swagger-mocker/)

## Custom examples

By default it will use the examples specified in your definition. To be able to adjust the examples for each and every project you can add example files.
As an example I've added such an [example](https://github.com/Zwartpet/swagger-mocker/tree/master/web/swagger/examples) in the directory the examples should go.
The files should be named as follows:
* ROUTE_NAME + url parameter key=value + query params key=value
* The url and query parameters are both ordered by alphabet by key and separated by an '&'
* You can find the ROUTE_NAME by running app/console debug:router in the container

Eg:
```
url = /pets/{id}?debug=0&auth=1 // where id=1 and ROUTE_NAME=swagger.default.pets.id.findPetById
filename = swagger.default.pets.id.findPetById&id=1&auth=1&debug=0
```
