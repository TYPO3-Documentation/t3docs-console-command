<?php

namespace T3Docs\ConsoleCommand\Service;

use phpDocumentor\Guides\RestructuredText\Parser\Directive;

class DirectiveParameterService
{
    /**
     * @return string[]
     */
    public function getExcludedOptions(Directive $directive, string $argument): array
    {
        $excludedOptions = [];
        if ($directive->hasOption($argument)) {
            $excludedOptions = array_map(fn($value) => // Trim spaces, tabs, newlines, and minus signs
            trim($value, " \t\n\r\0\x0B-"), explode(',', $directive->getOption('exclude-option')->toString()));
        }
        return $excludedOptions;
    }

    /**
     * @return string[]
     */
    public function getIncludedOptions(Directive $directive, string $argument): ?array
    {
        $includedOptions = null;
        if ($directive->hasOption($argument)) {
            $includedOptions = array_map(fn($value) => trim($value, " \t\n\r\0\x0B-"), explode(',', $directive->getOption('include-option')->toString()));
            $includedOptions = array_filter($includedOptions);
        }
        return $includedOptions;
    }
}
