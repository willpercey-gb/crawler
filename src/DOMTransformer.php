<?php

namespace UWebPro\Crawler;

class DOMTransformer extends \UWebPro\DOMTransformer\DOMTransformer
{
    public function recurse($callback, \SimpleXMLElement &$xml = null, $glue = '.', $parent = '', &$link = ''): int
    {
        $xml = $xml ?? $this->getXML();
        $childCount = 0;
        foreach ($xml as $key => $value) {
            $childCount++;
            if ($key === 'a') {
                $link = @$value->attributes()['href'];
            }
            $link = isset($link) ? $link : null;
            if ($this->recurse($callback, $value, $glue, $parent . $glue . $key, $link) === 0)  // no childern, aka leaf node
            {
                $returnable = [
                    $parent . $glue . (string)$key => [
                        'value' => $value,
                        'link' => $link
                    ]
                ];
                $callback($returnable, $parent . $glue . $key);
            }
        }
        return $childCount;
    }

    public function toArray(\SimpleXMLElement $xml = null): array
    {
        $xml = $xml ?? $this->getXML();
        $parser = static function (\SimpleXMLElement $xml, array $collection = []) use (&$parser) {
            $nodes = $xml->children();
            $attributes = $xml->attributes();

            if (0 !== @count($attributes)) {
                foreach ($attributes as $attrName => $attrValue) {
                    $collection['attributes'][$attrName] = (string)$attrValue;
                }
            }

            if (0 === $nodes->count()) {
                $collection['value'] = (string)$xml;
                return $collection;
            }

            foreach ($nodes as $nodeName => $nodeValue) {
                if (count($nodeValue->xpath('../' . $nodeName)) < 2) {
                    $collection[$nodeName] = $parser($nodeValue);
                    continue;
                }

                $collection[$nodeName][] = $parser($nodeValue);
            }

            return $collection;
        };

        return [
            $xml->getName() => $parser($xml)
        ];
    }
}
