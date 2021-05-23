<?php

namespace Codememory\Components\Parser;

use JetBrains\PhpStorm\ArrayShape;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

/**
 * Class Meta
 * @package Codememory\Components\Parser
 *
 * @author  Codememory
 */
class Meta
{

    /**
     * @var Dom
     */
    private Dom $dom;

    /**
     * Parser constructor.
     *
     * @param Dom $dom
     */
    public function __construct(Dom $dom)
    {

        $this->dom = $dom;

    }

    /**
     * @var string|null
     */
    private ?string $parsingUrl = null;

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Set the URL from which to take information
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $url
     *
     * @return Meta
     */
    public function setParsingUrl(string $url): Meta
    {

        $this->parsingUrl = $url;

        return $this;

    }

    /**
     * @return Dom
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    private function getContent(): Dom
    {

        return $this->dom->loadStr(file_get_contents($this->parsingUrl));

    }

    /**
     * @param string $tag
     *
     * @return array
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    private function handlerTags(string $tag): array
    {

        $metaTags = $this->getContent()->find($tag);
        $tags = [];

        if ($metaTags->getIterator()->count() > 0) {
            foreach ($metaTags as $tag) {
                $attrs = $tag->tag->getAttributes();
                $attributes = [];

                if ([] !== $attrs) {
                    foreach ($attrs as $name => $value) {
                        $attributes[$name] = $value->getValue();
                    }
                }

                $tags[] = [
                    'attr' => $attributes
                ];
            }
        }

        return $tags;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get all META tags
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return array
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getMetaTags(): array
    {

        return $this->handlerTags('meta');

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get all html <link>
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return array
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getLinks(): array
    {

        return $this->handlerTags('link');

    }

    /**
     * @param array  $attrs
     * @param string $getAttr
     * @param array  $attributes
     *
     * @return string|null
     */
    private function handleGetTag(array $attrs, string $getAttr, array $attributes): ?string
    {

        $content = null;

        foreach ($attrs as $name => $value) {
            foreach ($attributes as $attribute) {
                if (is_string($name)) {
                    if (array_key_exists($name, $attribute['attr']) && in_array($value, $attribute['attr'])) {
                        $content = $attribute['attr'][$getAttr];
                    }
                } else {
                    if (in_array($value, $attribute['attr'])) {
                        $content = $attribute['attr'][$getAttr];
                    }
                }
            }
        }

        return $content;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get a specific META tag using the attribute name and value
     * & 2 argument - the name of the attribute to get
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array  $attrs
     * @param string $getAttr
     *
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getMeta(array $attrs, string $getAttr): ?string
    {

        return $this->handleGetTag($attrs, $getAttr, $this->getMetaTags());

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get a specific <link> tag using the attribute name and value
     * & 2 argument - the name of the attribute to get
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param array  $attrs
     * @param string $getAttr
     *
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getLink(array $attrs, string $getAttr): ?string
    {

        return $this->handleGetTag($attrs, $getAttr, $this->getLinks());

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get Site Title from META tag or <title>
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getTitle(): ?string
    {

        $metaTitle = $this->getMeta(['property' => 'og:title'], 'content');
        $tagTitle = $this->getContent()->find('title');

        if (null === $metaTitle && $tagTitle->getIterator()->count() > 0) {
            return $tagTitle[0]->text();
        } else {
            return $metaTitle;
        }

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get site name from META tag property = og:
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getSiteName(): ?string
    {

        $metaName = $this->getMeta(['property' => 'og:site_name'], 'content');

        return null !== $metaName ? $metaName : $this->getTitle();

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get site description from meta tags
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getDescription(): ?string
    {

        $ogDesc = $this->getMeta(['property' => 'og:description'], 'content');
        $desc = $this->getMeta(['property' => 'description', 'Description'], 'content');

        return null !== $ogDesc ? $ogDesc : $desc;

    }

    /**
     * @return string
     */
    private function generateUrl(): string
    {

        $parse = parse_url($this->parsingUrl);

        return sprintf('%s://%s/', $parse['scheme'], $parse['host']);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get full site url from META tags
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getUrl(): ?string
    {

        $ogUrl = $this->getMeta(['property' => 'og:url'], 'content');

        return null !== $ogUrl ? $ogUrl : $this->generateUrl();

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * & Get all information about the picture that should be shown
     * & when sending a link in social networks
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return array
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    #[ArrayShape(['url' => "null|string", 'width' => "null|string", 'height' => "null|string"])]
    public function getImage(): array
    {

        $metaImage = $this->getMeta(['property' => 'og:image'], 'content');
        $data = [
            'url'    => null,
            'width'  => null,
            'height' => null
        ];

        if (null !== $metaImage) {
            $url = !str_starts_with($metaImage, $this->getUrl()) ? $this->getUrl() . $metaImage : $metaImage;

            $data['url'] = $url;
            $data['width'] = $this->getMeta(['property' => 'og:image:width'], 'content');
            $data['height'] = $this->getMeta(['property' => 'og:image:height'], 'content');
        }

        return $data;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>
     * & Get Site Favicon
     * <=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return string|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function getFavicon(): ?string
    {

        $linkFavicon = $this->getLink(['rel' => 'shortcut icon', 'type' => 'image/x-icon'], 'href');

        if (null !== $linkFavicon) {
            return !str_starts_with($linkFavicon, $this->getUrl()) ? $this->getUrl() . $linkFavicon : $linkFavicon;
        } else {
            return null;
        }

    }

}