<?php

namespace T3Docs\ConsoleCommand\Directives;

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
use T3Docs\ConsoleCommand\Nodes\CommandListNode;
use T3Docs\ConsoleCommand\Nodes\CommandNode;
use T3Docs\ConsoleCommand\Service\CommandNodeService;
use T3Docs\ConsoleCommand\Service\DirectiveParameterService;
use T3Docs\ConsoleCommand\Service\JsonLoadingService;

final class CommandListDirective extends SubDirective
{
    public const NAME = 'console:command-list';
    public function __construct(
        Rule $startingRule,
        GenericLinkProvider $genericLinkProvider,
        private readonly LoggerInterface $logger,
        private readonly CommandNodeService $commandNodeService,
        private readonly JsonLoadingService $jsonLoadingService,
        private readonly DirectiveParameterService $directiveParameterService,
        private readonly AnchorNormalizer $anchorReducer,
    ) {
        parent::__construct($startingRule);
        $genericLinkProvider->addGenericLink(self::NAME, CommandNode::LINK_TYPE, CommandNode::LINK_PREFIX);
    }

    /**
     * @param array<string>|null $includeCommand
     * @param array<mixed>|null $namespaces
     * @return array<string>|null
     */
    public function getIncludesFromNamespace(?array $includeCommand, string $namespaceName, ?array $namespaces, string $jsonPath, BlockContext $blockContext): mixed
    {
        if ($includeCommand === null && $namespaceName !== '') {
            $includeCommand = [];
            if (is_array($namespaces ?? false)) {
                foreach ($namespaces as $namespace) {
                    if ($namespace['id'] === $namespaceName) {
                        foreach ($namespace['commands'] as $commandInNamespace) {
                            $includeCommand[] = $commandInNamespace;
                        }
                    }
                }
                if ($includeCommand === []) {
                    $this->logger->warning(sprintf('Namespace %s in file %s was not found or did not contain commands.', $namespaceName, $jsonPath), $blockContext->getLoggerInformation());
                }
            } else {
                $this->logger->warning(sprintf('No namespaces in file %s.', $jsonPath), $blockContext->getLoggerInformation());
            }
        }
        return $includeCommand;
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $showHidden = $directive->hasOption('show-hidden');

        $json = $this->jsonLoadingService->loadJsonArrayFromDirective($blockContext, $directive, 'json');
        $jsonPath = $directive->getOption('json')->toString();
        if (!is_array($json['commands'] ?? false)) {
            $this->logger->warning(sprintf('No commands found in file %s.', $jsonPath), $blockContext->getLoggerInformation());
            return null;
        }
        $children = $collectionNode->getChildren();

        $namespaceName = trim($directive->getData());

        $excludeCommand = $this->directiveParameterService->getExcludedOptions($directive, 'exclude-command');
        $includeCommand = $this->directiveParameterService->getIncludedOptions($directive, 'include-command');
        $includeCommand = $this->getIncludesFromNamespace($includeCommand, $namespaceName, $json['namespaces'], $jsonPath, $blockContext);
        $commands = [];
        foreach ($json['commands'] as $command) {
            if (!is_array($command) || !is_string($command['name'] ?? false)) {
                $this->logger->warning(sprintf('Commands in file %s contained unexpected values.', $jsonPath), $blockContext->getLoggerInformation());
                continue;
            }
            if (!$showHidden && isset($command['hidden']) && $command['hidden'] === true) {
                continue;
            }
            $commandName = $command['name'];
            if (in_array($commandName, $excludeCommand, true)) {
                continue;
            }
            if (is_array($includeCommand) && !in_array($commandName, $includeCommand, true)) {
                continue;
            }
            $commands[] = $this->commandNodeService->createCommandNode($blockContext, $commandName, $directive, $command, $children);
        }

        $groupedByNamespace = [];

        foreach ($commands as $command) {
            $namespace = $command->getNamespace();
            if (!isset($groupedByNamespace[$namespace])) {
                $groupedByNamespace[$namespace] = [];
            }
            $groupedByNamespace[$namespace][] = $command;
        }
        ksort($groupedByNamespace);

        $id = $directive->getOptionString(
            'name',
            $directive->getOptionString(
                'caption',
                $blockContext->getDocumentParserContext()->getDocument()->getFilePath(),
            ),
        );
        $id = $this->anchorReducer->reduceAnchor($id);
        return new CommandListNode(
            $id,
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $commands,
            $groupedByNamespace,
            $directive->getOptionString('caption'),
            $directive->getOptionBool('noindex'),
            $showHidden,
        );
    }
    public function getName(): string
    {
        return self::NAME;
    }
}
