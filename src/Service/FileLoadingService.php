<?php

namespace T3Docs\ConsoleCommand\Service;

use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use Psr\Log\LoggerInterface;

class FileLoadingService
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function loadFile(
        string $path,
        BlockContext $blockContext,
    ): string|null {
        $parserContext = $blockContext->getDocumentParserContext()->getParser()->getParserContext();
        $path = $parserContext->absoluteRelativePath($path);
        $origin = $parserContext->getOrigin();
        if (!$origin->has($path)) {
            $this->logger->warning(sprintf('Cannot find file "%s".', $path), $blockContext->getLoggerInformation());
            return null;
        }
        $contents = $origin->read($path);
        if ($contents === false) {
            $this->logger->warning(sprintf('Could not load file from path %s', $path), $blockContext->getLoggerInformation());
            return null;
        }
        return $contents;
    }
}
