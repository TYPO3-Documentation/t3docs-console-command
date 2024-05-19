<?php

namespace T3Docs\ConsoleCommand\Nodes;

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

class ArgumentNode extends GeneralDirectiveNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:console:argument';
    public const LINK_PREFIX = 'console-argument-';
    public function __construct(
        private readonly string $argumentName,
        private readonly string $id,
        protected readonly InlineCompoundNode $content,
        array $value = [],
        private readonly bool $isRequired = false,
        private readonly bool $isArray = false,
        private readonly string $description = '',
        private readonly ?string $default = null,
        private readonly bool $noIndex = false,
    ) {
        parent::__construct('console:argument', $argumentName, $content, $value);
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }

    public function getContent(): InlineCompoundNode
    {
        return $this->content;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isArray(): bool
    {
        return $this->isArray;
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
        return $this->argumentName;
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
