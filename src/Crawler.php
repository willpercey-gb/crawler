<?php

namespace UWebPro\Crawler;

use Illuminate\Support\Collection;
use UWebPro\DOMTransformer\DOMTransformer;
use UWebPro\Str\SubstringHelper;

class Crawler extends \Symfony\Component\DomCrawler\Crawler
{
    protected string|\DOMDocument|\DOMElement|\SimpleXMLElement|null $originalDocument;

    public function __construct($node = null, ?string $uri = null, ?string $baseHref = null)
    {
        $this->originalDocument = $node;
        if (!$this->isJson($node)) {
            parent::__construct($node, $uri, $baseHref);
        }
    }

    /**
     * @param bool $associative
     *
     * @return Collection
     *
     * @throws \JsonException
     */
    public function collect(bool $associative = false): Collection
    {
        if ($this->isJson()) {
            return collect(json_decode($this->originalDocument, $associative, 512, JSON_THROW_ON_ERROR));
        }

        return collect($this->tree()->toArray());
    }

    public function tree(): DOMTransformer
    {
        return DOMTransformer::fromDOM($this->html());
    }

    public function toArray(): array
    {
        $tree = $this->tree()->toArray();
        return $tree['html']['body'] ?? $tree;
    }

    public function isJson($node = null): bool
    {
        if (is_object($node)) {
            return false;
        }
        $node ??= $this->originalDocument;
        \Safe\json_encode(\Safe\json_decode($node));
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getContent(): string|\DOMDocument|\DOMElement|\SimpleXMLElement|null
    {
        return $this->originalDocument;
    }

    public function substring(?string $start = null, ?string $end = null): string
    {
        if (is_object($this->originalDocument)) {
            $node = $this->html();
        }
        return SubstringHelper::method($node ?? $this->originalDocument, $start, $end);
    }

    public function decode(bool $associative = false): array|object
    {
        return \Safe\json_decode($this->originalDocument, $associative);
    }
}
