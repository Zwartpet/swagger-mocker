# Zwartpet\SwaggerMocker
[![Build](https://travis-ci.org/Zwartpet/swagger-mocker.svg?branch=master)](https://travis-ci.org/Zwartpet/swagger-mocker)
[![Coverage](https://coveralls.io/repos/github/Zwartpet/swagger-mocker/badge.svg?branch=master)](https://coveralls.io/github/Zwartpet/swagger-mocker?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/Zwartpet/swagger-mocker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Zwartpet/swagger-mocker/?branch=master)

Transform your Swagger ([Open API](https://openapis.org/)) spec examples into a Symfony REST mock server

## SwaggerBundle

This project uses the [Kleijnweb\SwaggerBundle](https://github.com/kleijnweb/swagger-bundle) to provide the routes and definition validation.

## Usage

### Local usage

1. Checkout the project and install dependencies
2. Replace the content of web/swagger/default.json with the specification you want to mock
3. Run the application with docker-compose
