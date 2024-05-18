<?php

namespace T3Docs\ConsoleCommand\Nodes;

use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

class CommandNode extends GeneralDirectiveNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:command';
    public const LINK_PREFIX = 'command-';
    public function __construct(
        private readonly string $commandName,
        private readonly string $id,
        protected readonly InlineCompoundNode $content,
        array $value = [],
        private readonly string $description = '',
        private readonly ?CompoundNode $help = null,
        private readonly array $usage = [],
        private readonly array $argumentList = [],
        private readonly array $optionList = [],
        private readonly bool $noindex = false,
    ) {
        parent::__construct('command', $commandName, $content, $value);
    }

    public function getCommandName(): string
    {
        return $this->commandName;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): InlineCompoundNode
    {
        return $this->content;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getHelp(): ?CompoundNode
    {
        return $this->help;
    }

    public function getUsage(): array
    {
        return $this->usage;
    }

    public function getArgumentList(): array
    {
        return $this->argumentList;
    }

    public function getOptionList(): array
    {
        return $this->optionList;
    }

    public function isNoindex(): bool
    {
        return $this->noindex;
    }

    public function getLinkType(): string
    {
        return self::LINK_TYPE;
    }

    public function getLinkText(): string
    {
        return $this->commandName;
    }

    public function getPrefix(): string
    {
        return self::LINK_PREFIX;
    }
}
