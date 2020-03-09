#!/usr/bin/env bash

phing phpunit -Dbasedir=.

cd ../joomla-cli-test && phpunit
cd ../joomla-cli

./joomla test:coverage --source=src
