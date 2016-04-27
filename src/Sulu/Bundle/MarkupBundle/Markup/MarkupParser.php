<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\MarkupBundle\Markup;

use Sulu\Bundle\MarkupBundle\Tag\TagRegistryInterface;

/**
 * Parses html content and replaces special tags.
 */
class MarkupParser implements MarkupParserInterface
{
    const ATTRIBUTE_REGEX = '/(?<name>\b\w+\b)\s*=\s*"(?<value>[^"]*)"/';
    const CONTENT_REGEX = '/(?:>(?<content>.*)<)/';
    const TAG_REGEX = '/(?<tag><%s:(?<name>[a-z]*)[^\/>]*(?:\/>|>.*<\/%s:[^\/>]*>))/';

    /**
     * @var TagRegistryInterface
     */
    private $tagRegistry;
    /**
     * @var string
     */
    private $namespace;

    /**
     * @param TagRegistryInterface $tagRegistry
     * @param string $namespace
     */
    public function __construct(TagRegistryInterface $tagRegistry, $namespace = 'sulu')
    {
        $this->tagRegistry = $tagRegistry;
        $this->namespace = $namespace;
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function parse($html)
    {
        if (!preg_match_all(sprintf(self::TAG_REGEX, $this->namespace, $this->namespace), $html, $matches)) {
            return $html;
        }

        $sortedTags = [];
        for ($i = 0, $length = count($matches['name']); $i < $length; ++$i) {
            $tag = $matches['tag'][$i];
            $name = $matches['name'][$i];
            if (!array_key_exists($name, $sortedTags)) {
                $sortedTags[$name] = [];
            }

            $sortedTags[$name][$tag] = $this->getAttributes($tag);
        }

        foreach ($sortedTags as $name => $tags) {
            $tags = $this->tagRegistry->getTag($name)->parseAll($tags);

            foreach ($tags as $tag => $newTag) {
                $html = str_replace($tag, $newTag, $html);
            }
        }

        return $html;
    }

    /**
     * Returns attributes of given html-tag.
     *
     * @param string $tag
     *
     * @return array
     */
    private function getAttributes($tag)
    {
        if (!preg_match_all(self::ATTRIBUTE_REGEX, $tag, $matches)) {
            return [];
        }

        $attributes = [];
        for ($i = 0, $length = count($matches['name']); $i < $length; ++$i) {
            $attributes[$matches['name'][$i]] = $matches['value'][$i];
        }

        if (preg_match(self::CONTENT_REGEX, $tag, $matches)) {
            $attributes['content'] = $matches['content'];
        }

        return $attributes;
    }
}
