<?php

namespace T3Docs\ConsoleCommand\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericLinkProvider;
use Psr\Log\LoggerInterface;
use T3Docs\ConsoleCommand\Nodes\CommandNode;
use T3Docs\ConsoleCommand\Service\FileLoadingService;

final class CommandDirective extends SubDirective
{
    public const NAME = 'command';
    public function __construct(
        Rule $startingRule,
        GenericLinkProvider $genericLinkProvider,
        private readonly LoggerInterface $logger,
        private readonly AnchorNormalizer $anchorReducer,
        private readonly FileLoadingService $fileLoadingService,
    ) {
        parent::__construct($startingRule);
        $genericLinkProvider->addGenericLink(self::NAME, CommandNode::LINK_TYPE, CommandNode::LINK_PREFIX);
    }
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        if (!$directive->hasOption('json')) {
            $this->logger->warning('Directive command expects argument :json: to be set. ', $blockContext->getLoggerInformation());
            return null;
        }
        $commandName = trim($directive->getData());
        if ($commandName === '') {
            $this->logger->warning('Directive command expects content to be set. ', $blockContext->getLoggerInformation());
            return null;
        }
        $jsonPath = $directive->getOption('json')->toString();
        $contents = $this->fileLoadingService->loadFile($jsonPath, $blockContext);
        if ($contents == null) {
            return null;
        }
        $json = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            $this->logger->warning(sprintf('JSON file %s did not contain a valid json array.', $jsonPath), $blockContext->getLoggerInformation());
            return null;
        }
        if (!is_array($json['commands'] ?? false)) {
            $this->logger->warning(sprintf('No commands found in file %s.', $jsonPath), $blockContext->getLoggerInformation());
            return null;
        }
        $children = $collectionNode->getChildren();
        foreach ($json['commands'] as $command) {
            if (!is_array($command) || !is_string($command['name'] ?? false)) {
                $this->logger->warning(sprintf('Commands in file %s contained unexpected values.', $jsonPath), $blockContext->getLoggerInformation());
                return null;
            }
            if (trim($command['name']) === $commandName) {
                $id = $commandName;
                if ($directive->hasOption('name')) {
                    $id = $directive->getOption('name')->toString();
                }
                $id = $this->anchorReducer->reduceAnchor($id);
                $usage = [];
                if (is_array($command['usage'])) {
                    foreach ($command['usage'] as $item) {
                        if (!is_string($item)) {
                            continue;
                        }
                        $usage[] = new CodeNode(['vendor/bin/typo3 ' . $item], 'bash');
                    }
                }
                return new CommandNode(
                    $commandName,
                    $id,
                    $directive->getDataNode(),
                    $children,
                    $command['description'] ?? '',
                    new InlineCompoundNode(),
                    $usage,
                    [],
                    [],
                    $directive->hasOption('noindex'),
                );
            }
        }
        $this->logger->warning(sprintf('Command %s not found in file %s.', $commandName, $jsonPath), $blockContext->getLoggerInformation());
        return null;
    }
    public function getName(): string
    {
        return self::NAME;
    }
}
