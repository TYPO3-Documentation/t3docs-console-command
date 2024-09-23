<?php

namespace T3Docs\ConsoleCommand\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

class CommandListNode extends GeneralDirectiveNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:console:command-list';
    public const LINK_PREFIX = 'console-command-list-';

    /**
     * @param CommandNode[] $commands
     * @param array<string, CommandNode[]> $commandsByNamespace
     */
    public function __construct(
        private readonly string $id,
        protected readonly string $plainContent,
        protected readonly InlineCompoundNode $content,
        private readonly array $commands = [],
        private readonly array $commandsByNamespace = [],
        private readonly string $caption = '',
        private readonly bool $noindex = false,
        private readonly bool $showHidden = false,
    ) {
        parent::__construct('console:command-list', $plainContent, $content, $commands);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): InlineCompoundNode
    {
        return $this->content;
    }

    public function getLinkText(): string
    {
        return $this->caption;
    }

    public function isNoindex(): bool
    {
        return $this->noindex;
    }

    public function getLinkType(): string
    {
        return self::LINK_TYPE;
    }

    public function getPrefix(): string
    {
        return self::LINK_PREFIX;
    }
    public function getAnchor(): string
    {
        return $this->getPrefix() . $this->getId();
    }

    /**
     * @return CommandNode[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return array<string, CommandNode[]>
     */
    public function getCommandsByNamespace(): array
    {
        return $this->commandsByNamespace;
    }

    public function isShowHidden(): bool
    {
        return $this->showHidden;
    }
}
