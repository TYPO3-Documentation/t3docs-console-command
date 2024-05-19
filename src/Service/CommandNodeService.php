<?php

namespace T3Docs\ConsoleCommand\Service;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;
use T3Docs\ConsoleCommand\Nodes\ArgumentNode;
use T3Docs\ConsoleCommand\Nodes\CommandNode;
use T3Docs\ConsoleCommand\Nodes\OptionNode;

class CommandNodeService
{
    /**
     * @param Rule<CollectionNode> $startingRule
     */
    public function __construct(
        private readonly Rule $startingRule,
        private readonly AnchorNormalizer $anchorReducer,
        private readonly LoggerInterface $logger,
        private readonly DirectiveParameterService $directiveParameterService,
    ) {}

    /**
     * @param array<mixed> $command
     * @param array<Node> $children
     */
    public function createCommandNode(BlockContext $blockContext, string $commandName, Directive $directive, array $command, array $children): CommandNode
    {
        $id = $commandName;
        if ($directive->hasOption('name')) {
            $id = $directive->getOption('name')->toString();
        }
        $script = '';
        if ($directive->hasOption('script')) {
            $script = $directive->getOption('script')->toString() . ' ';
        }
        $id = $this->anchorReducer->reduceAnchor($id);
        $usage = [];
        if (is_array($command['usage'])) {
            foreach ($command['usage'] as $item) {
                if (!is_string($item)) {
                    continue;
                }
                $usage[] = new CodeNode([$script . $item], 'bash');
            }
        }
        $arguments = $this->extractArguments($command, $blockContext, $directive, $id);
        $options = $this->extractOptions($directive, $command, $blockContext, $id);
        $helpNode = $this->getParsedText($command, 'help', $blockContext, $script);
        return new CommandNode(
            $script . $commandName,
            $id,
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $children,
            $command['description'] ?? '',
            $helpNode,
            $usage,
            $arguments,
            $options,
            $directive->hasOption('noindex'),
        );
    }

    /**
     * @param array<mixed> $option
     */
    private function createOptionNode(string $optionName, array $option, string $commandId): OptionNode
    {
        $id = $this->anchorReducer->reduceAnchor($commandId . '-' . $optionName);
        return new OptionNode(
            $optionName,
            $id,
            new InlineCompoundNode([new PlainTextInlineNode($optionName)]),
            [],
            is_string($option['shortcut'] ?? false) ? $option['shortcut'] : '',
            isset($option['accept_value']) && $option['accept_value'] === true,
            isset($option['is_value_required']) && $option['is_value_required'] === true,
            isset($option['is_multiple']) && $option['is_multiple'] === true,
            is_string($option['description'] ?? false) ? $option['description'] : '',
            isset($option['default']) ? json_encode($option['default'], JSON_PRETTY_PRINT) : null,
        );
    }

    /**
     * @param array<mixed> $argument
     */
    private function createArgumentNode(Directive $directive, string $argumentName, array $argument, string $commandId): ArgumentNode
    {
        $id = $this->anchorReducer->reduceAnchor($commandId . '-' . $argumentName);
        return new ArgumentNode(
            $argumentName,
            $id,
            new InlineCompoundNode([new PlainTextInlineNode($argumentName)]),
            [],
            isset($argument['is_value_required']) && $argument['is_value_required'] === true,
            isset($argument['is_array']) && $argument['is_array'] === true,
            is_string($argument['description']) ? $argument['description'] : '',
            isset($argument['description']) ? ((string)$argument['description']) : null,
            $directive->hasOption('noindexArguments'),
        );
    }

    /**
     * @param array<mixed> $command
     * @return array<ArgumentNode>
     */
    public function extractArguments(array $command, BlockContext $blockContext, Directive $directive, string $id): array
    {
        $arguments = [];
        if (isset($command['definition']['arguments']) && is_array($command['definition']['arguments'])) {
            foreach ($command['definition']['arguments'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (!is_string($item['name'])) {
                    $this->logger->warning('Invalid argument in command directive: name is no string. ', $blockContext->getLoggerInformation());
                    continue;
                }
                $arguments[] = $this->createArgumentNode($directive, $item['name'], $item, $id);
            }
        }
        return $arguments;
    }

    /**
     * @param array<mixed> $command
     * @return array<OptionNode>
     */
    public function extractOptions(Directive $directive, array $command, BlockContext $blockContext, string $id): array
    {
        $excludedOptions = $this->directiveParameterService->getExcludedOptions($directive, 'exclude-option');
        $includedOptions = $this->directiveParameterService->getIncludedOptions($directive, 'include-option');
        $options = [];
        $actualOptionNames = [];
        if (isset($command['definition']['options']) && is_array($command['definition']['options'])) {
            foreach ($command['definition']['options'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (!is_string($item['name'])) {
                    $this->logger->warning('Invalid argument in command directive: name is no string. ', $blockContext->getLoggerInformation());
                    continue;
                }
                $trimmedOptionName = trim($item['name'], " \t\n\r\0\x0B-");
                $actualOptionNames[] = $trimmedOptionName;
                if (in_array($trimmedOptionName, $excludedOptions, true)) {
                    continue;
                }
                if ($includedOptions !== null && !in_array($trimmedOptionName, $includedOptions, true)) {
                    continue;
                }
                $options[] = $this->createOptionNode($item['name'], $item, $id);
            }
        }
        // Log warnings for included options not found in actual options
        if ($includedOptions !== null) {
            foreach ($includedOptions as $includedOption) {
                if (!in_array($includedOption, $actualOptionNames, true)) {
                    $this->logger->warning(sprintf('Included option "%s" not found in the actual options.', $includedOption), $blockContext->getLoggerInformation());
                }
            }
        }
        return $options;
    }

    /**
     * @param array<mixed> $command
     */
    private function getParsedText(array $command, string $field, BlockContext $blockContext, string $script): ?CollectionNode
    {
        $text = '';
        if (is_string($command[$field] ?? false)) {
            $text = $command[$field];
        }
        $text = str_replace(['.Build/bin/typo3 ', '.Build/vendor/bin/typo3 ', 'bin/typo3 ', 'vendor/bin/typo3 '], $script, $text);
        $text = preg_replace_callback('/-{3,}\n(.*?)*-{3,}/s', function ($matches) {
            $lines = explode("\n", trim($matches[0], '-'));

            $lines = array_map(fn($value) =>
                // Trim spaces, tabs, newlines, and minus signs
            '    ' . $value, $lines);
            return "::\n\n" . implode("\n", $lines);
        }, $text);
        $text = preg_replace_callback('/<fg=yellow>(.*?)<\/(?:fg|)>/s', function ($matches) {
            $lines = explode("\n", trim($matches[1], '-'));

            $lines = array_map(fn($value) =>
                // Trim spaces, tabs, newlines, and minus signs
                '    ' . $value, $lines);
            return "..  warning::\n" . implode("\n", $lines);
        }, $text);
        $text = preg_replace('/:\n\n\s+<info>(.*?)<\/(?:info|)>/', "::\n\n    $1", $text);
        $text = preg_replace('/    <info>(.*?)<\/(?:info|)>/', '    $1', $text);
        $text = preg_replace('/    <comment>(.*?)<\/(?:comment|)>/', '    $1', $text);
        $text = preg_replace('/<info>(.*?)<\/(?:info|)>/', '`$1`', $text);
        $text = preg_replace('/<comment>(.*?)\n+\s*-+<\/(?:comment|)>/', '.. rubric:: $1', $text);
        $text = preg_replace('/<fg=green>(.*?)<\/(?:fg|)>/', '*$1*', $text);
        $text = preg_replace('/<fg=yellow>(.*?)<\/(?:fg|)>/', '**$1**', $text);
        $text = preg_replace('/<comment>(.*?)<\/(?:comment|)>/', '*$1*', $text);
        $subBlockContext = new BlockContext($blockContext->getDocumentParserContext(), $text);
        return $this->startingRule->apply($subBlockContext);
    }

}
