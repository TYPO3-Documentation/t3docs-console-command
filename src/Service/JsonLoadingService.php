<?php

namespace T3Docs\ConsoleCommand\Service;

use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Psr\Log\LoggerInterface;

class JsonLoadingService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FileLoadingService $fileLoadingService,
    )
    {}

    /**
     * @return array<mixed>|null
     */
    public function loadJsonArrayFromDirective(
        BlockContext $blockContext,
        Directive $directive,
        string $optionName,
    ): array|null
    {
        if (!$directive->hasOption($optionName)) {
            $this->logger->warning(
                sprintf('Directive %s expects argument :%s: to be set. ', $directive->getName(), $optionName),
                $blockContext->getLoggerInformation()
            );
            return null;
        }
        $jsonPath = $directive->getOption('json')->toString();
        return $this->loadJsonArrayFromAbsolutePath($blockContext, $jsonPath);
    }
    /**
     * @return array<mixed>|null
     */
    public function loadJsonArrayFromAbsolutePath(
        BlockContext $blockContext,
        string $jsonPath,
    ): array|null
    {
        $contents = $this->fileLoadingService->loadFile($jsonPath, $blockContext);
        if ($contents == null) {
            return null;
        }
        $json = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            $this->logger->warning(sprintf('JSON file %s did not contain a valid json array.', $jsonPath), $blockContext->getLoggerInformation());
            return null;
        }
        return $json;
    }
}
