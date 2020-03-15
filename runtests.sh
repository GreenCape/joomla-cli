#!/usr/bin/env bash

phpunit -c build/phpunit.xml

cd ../joomla-cli-test && phpunit
cd ../joomla-cli

./joomla test:coverage --source=src
