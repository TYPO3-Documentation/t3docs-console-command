
=======================================================
ReStructuredText directives for Symfony console command
=======================================================

This application is an extension of the
`phpdocumentor/guides <https://github.com/phpDocumentor/guides>`__.

For usage in the TYPO3 documentation rendering read here:
:ref:`Writing TYPO3 documentation - reStructuredText <h2document:rest-quick-start>`.

For usage in other projects, read below.

Installation
============

You can require this extension via Composer:

..  code-block:: bash
    :caption: Require via Composer

    composer req t3docs/console-command

And then include it in your :file:`guides.xml`:

..  code-block:: xml
    :caption: guides.xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <guides
        xmlns="https://www.phpdoc.org/guides"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="https://www.phpdoc.org/guides vendor/phpdocumentor/guides-cli/resources/schema/guides.xsd"
        theme="mytheme"
    >
        <extension class="\phpDocumentor\Guides\RestructuredText\DependencyInjection\ReStructuredTextExtension"/>
        <extension class="\T3Docs\ConsoleCommand\DependencyInjection\ConsoleCommandExtension"/>
        <!-- ... -->
    </guides>

Usage
=====

Generate a help file for the symfony console command in json:

..  code-block:: bash

    vendor/bin/typo3 list --format=json > commands.json

Then display a specific command in your reStructuredText:

..  code-block:: rest

    ..  console:command:: cache:flush
        :json: command.json
        :script: vendor/bin/typo3
        :exclude-option: help, quiet, verbose, version, ansi, no-ansi, no-interaction

Or a namespace of commands:

..  code-block:: rest

    ..  console:command-list:: cache
        :json: command.json
        :script: vendor/bin/typo3
        :exclude-option: help, quiet, verbose, version, ansi, no-ansi, no-interaction

Or all commands, even including hidden ones:

..  code-block:: rest

    ..  console:command-list::
        :json: command.json
        :show-hidden:

