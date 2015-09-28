# Baleen Documentation

## Sphinx Installation

To install Sphinx and all required dependencies you must first install Python >= 2.7.

### Unix (tested only on Mac)

Simply run `pip install sphinx sphinx-autobuild sphinx_rtd_theme sphinxcontrib-phpdomain`.

## Regenerating the Class Reference

Whenever changes are made to the source-code the class reference must be re-generated in order to incorporate those latest changes.

### Unix (tested only on Mac)

Run `./bin/generate-reference.sh`

## Building

The building process converts the reStructuredText sources into HTML documentation. 

### Unix (with pip, tested only on Mac)

Run `./bin/bulid-docs.sh`.
