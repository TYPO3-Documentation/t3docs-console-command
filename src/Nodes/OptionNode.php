<?php

namespace T3Docs\ConsoleCommand\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

class OptionNode extends GeneralDirectiveNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:console:option';
    public const LINK_PREFIX = 'console-option-';
    public function __construct(
        private readonly string $optionName,
        private readonly string $id,
        protected readonly InlineCompoundNode $content,
        array $value = [],
        private readonly string $shortcut = '',
        private readonly bool $acceptValue = false,
        private readonly bool $isValueRequired = false,
        private readonly bool $isMultipe = false,
        private readonly string $description = '',
        private readonly ?string $default = null,
        private readonly bool $noIndex = false,
    ) {
        parent::__construct('console:option', $optionName, $content, $value);
    }

    public function getOptionName(): string
    {
        return $this->optionName;
    }

    public function getShortcut(): string
    {
        return $this->shortcut;
    }

    public function isAcceptValue(): bool
    {
        return $this->acceptValue;
    }

    public function isValueRequired(): bool
    {
        return $this->isValueRequired;
    }

    public function isMultipe(): bool
    {
        return $this->isMultipe;
    }

    public function getContent(): InlineCompoundNode
    {
        return $this->content;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getLinkType(): string
    {
        return self::LINK_TYPE;
    }

    public function getLinkText(): string
    {
        return $this->optionName;
    }

    public function getPrefix(): string
    {
        return self::LINK_PREFIX;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isNoindex(): bool
    {
        return $this->noIndex;
    }
}
