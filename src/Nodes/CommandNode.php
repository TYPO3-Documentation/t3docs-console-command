<?php

namespace T3Docs\ConsoleCommand\Nodes;

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\LinkTargetNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\OptionalLinkTargetsNode;
use phpDocumentor\Guides\Nodes\PrefixedLinkTargetNode;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;

class CommandNode extends GeneralDirectiveNode implements LinkTargetNode, OptionalLinkTargetsNode, PrefixedLinkTargetNode
{
    public const LINK_TYPE = 'std:console:command';
    public const LINK_PREFIX = 'console-command-';
    public function __construct(
        private readonly string $commandName,
        private readonly string $id,
        protected readonly InlineCompoundNode $content,
        array $value = [],
        private readonly string $description = '',
        private ?CompoundNode $help = null,
        private readonly array $usage = [],
        private readonly array $argumentList = [],
        private readonly array $optionList = [],
        private readonly bool $noindex = false,
        private readonly string $namespace = '',
        private readonly bool $hidden = false,
    ) {
        parent::__construct('console:command', $commandName, $content, $value);
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

    /**
     * The command's help comes after the directive's own content as a child
     * of its own, so that the compiler reaches it as it does any content: a
     * directive in the help, such as the "warning" a command's help may
     * contain, is turned into its node only while compiling.
     *
     * @return Node[]
     */
    public function getChildren(): array
    {
        $children = parent::getChildren();
        if ($this->help !== null) {
            $children[] = $this->help;
        }

        return $children;
    }

    public function replaceNode(int $key, Node $node): self
    {
        if (!$this->isHelpKey($key)) {
            return parent::replaceNode($key, $node);
        }

        $result = clone $this;
        $result->help = $node instanceof CompoundNode ? $node : new CollectionNode([$node]);

        return $result;
    }

    public function removeNode(int $key): self
    {
        if (!$this->isHelpKey($key)) {
            return parent::removeNode($key);
        }

        $result = clone $this;
        $result->help = null;

        return $result;
    }

    /** Whether the child at this key is the help, which comes after the content. */
    private function isHelpKey(int $key): bool
    {
        return $this->help !== null && $key === \count(parent::getChildren());
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
    public function getAnchor(): string
    {
        return $this->getPrefix() . $this->getId();
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
