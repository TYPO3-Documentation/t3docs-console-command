<?php

namespace T3Docs\ConsoleCommand\Directives;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericLinkProvider;
use Psr\Log\LoggerInterface;
use T3Docs\ConsoleCommand\Nodes\ArgumentNode;
use T3Docs\ConsoleCommand\Nodes\CommandNode;
use T3Docs\ConsoleCommand\Nodes\OptionNode;
use T3Docs\ConsoleCommand\Service\CommandNodeService;
use T3Docs\ConsoleCommand\Service\JsonLoadingService;

final class CommandDirective extends SubDirective
{
    public const NAME = 'console:command';
    public function __construct(
        Rule $startingRule,
        GenericLinkProvider $genericLinkProvider,
        private readonly LoggerInterface $logger,
        private readonly CommandNodeService $commandNodeService,
        private readonly JsonLoadingService $jsonLoadingService,
    ) {
        parent::__construct($startingRule);
        $genericLinkProvider->addGenericLink(self::NAME, CommandNode::LINK_TYPE, CommandNode::LINK_PREFIX);
        $genericLinkProvider->addGenericLink(self::NAME, ArgumentNode::LINK_TYPE, ArgumentNode::LINK_PREFIX);
        $genericLinkProvider->addGenericLink(self::NAME, OptionNode::LINK_TYPE, OptionNode::LINK_PREFIX);
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {

        $commandName = trim($directive->getData());
        if ($commandName === '') {
            $this->logger->warning('Directive command expects content to be set. ', $blockContext->getLoggerInformation());
            return null;
        }

        $json = $this->jsonLoadingService->loadJsonArrayFromDirective($blockContext, $directive, 'json');
        $jsonPath = $directive->getOption('json')->toString();
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
                return $this->commandNodeService->createCommandNode($blockContext, $commandName, $directive, $command, $children);
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
