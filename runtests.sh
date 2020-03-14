#!/usr/bin/env bash

phpunit -c build/phpunit.xml

cd ../joomla-cli-test && phpunit
cd ../joomla-cli

./joomla test:coverage --source=src
./joomla quality --source=src --logs=build/logs
./joomla document --source=src
