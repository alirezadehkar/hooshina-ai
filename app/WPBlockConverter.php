<?php
namespace HooshinaAi\App;

use DOMDocument;
use DOMElement;
use DOMNode;

class WPBlockConverter
{
    protected $html;
    protected $dom;

    public function __construct($html)
    {
        $this->html = $this->sanitize($html);
        $this->dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->dom->loadHTML(mb_convert_encoding($this->html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
    }

    protected function sanitize(string $html): string
    {
		$html = trim($html);
		$inlineTags = ['img', 'video', 'audio', 'iframe', 'table', 'pre', 'blockquote', 'figure'];

		foreach ($inlineTags as $tag) {
			$html = preg_replace(
				'/<p>\s*(<' . $tag . '[^>]*>(?:.*?)?<\/' . $tag . '>|<' . $tag . '[^>]*\/?>)\s*<\/p>/is',
				'$1',
				$html
			);
		}
	
		return $html;
    }

    public function convert(): string
    {
        $body = $this->dom->getElementsByTagName('body')->item(0);
        $content = '';

        foreach ($body->childNodes as $node) {
            $content .= $this->nodeToBlock($node);
        }

        return trim($content);
    }

    protected function nodeToBlock(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) !== '') {
            return $this->wrapBlock('paragraph', trim($node->textContent));
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $tagName = strtolower($node->nodeName);

        switch ($tagName) {
            case 'p':
                return $this->wrapBlock('paragraph', $this->safeHtml($node));
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                return $this->handleHeadingBlock($node);
            case 'img':
                return $this->handleImageBlock($node);
            case 'ul':
            case 'ol':
                return $this->handleListBlock($node);
            case 'pre':
            case 'code':
                return $this->wrapBlock('code', '<pre><code>' . htmlspecialchars($node->textContent) . '</code></pre>');
            case 'blockquote':
                return $this->wrapBlock('quote', $this->safeHtml($node));
            case 'hr':
                return '<!-- wp:separator --><hr class="wp-block-separator"/><!-- /wp:separator -->';
            case 'table':
                return $this->handleTableBlock($node);
            case 'video':
                return $this->handleVideoBlock($node);
            case 'audio':
                return $this->wrapBlock('audio', $this->safeHtml($node));
            case 'iframe':
                return $this->handleEmbedBlock($node);
            case 'figure':
                return $this->handleFigureBlock($node);
            case 'figcaption':
                return '<figcaption>' . $node->textContent . '</figcaption>';
            case 'div':
            case 'span':
                $html = '';
                foreach ($node->childNodes as $child) {
                    $html .= $this->nodeToBlock($child);
                }
                return $html;
            default:
                return $this->wrapBlock('html', '<pre><code>' . htmlspecialchars($this->dom->saveHTML($node)) . '</code></pre>');
        }
    }

    protected function wrapBlock(string $blockName, string $content): string
    {
        return <<<HTML
			<!-- wp:{$blockName} -->
			{$content}
			<!-- /wp:{$blockName} -->
			HTML;
    }

    protected function handleHeadingBlock(DOMElement $headingNode): string
    {
        $tagName = strtolower($headingNode->nodeName);
        $level = (int) filter_var($tagName, FILTER_SANITIZE_NUMBER_INT);

        $content = $this->safeHtml($headingNode);

        return <<<HTML
            <!-- wp:heading {"level":{$level}} -->
            {$content}
            <!-- /wp:heading -->
            HTML;
    }

    protected function handleImageBlock(DOMElement $imgNode): string
    {
        $src = $imgNode->getAttribute('src');
        $alt = $imgNode->getAttribute('alt');

        return <<<HTML
			<!-- wp:image -->
			<figure class="wp-block-image"><img src="{$src}" alt="{$alt}" /></figure>
			<!-- /wp:image -->
			HTML;
    }

    protected function handleListBlock(DOMElement $list): string
    {
        $isOrdered = (bool) strtolower($list->nodeName) === 'ol';
        $tag = $isOrdered ? 'ol' : 'ul';
        $items = '';

        foreach ($list->getElementsByTagName('li') as $li) {
            $items .= '<!-- wp:list-item --><li>' . htmlspecialchars($li->textContent) . '</li><!-- /wp:list-item -->';
        }

        return <<<HTML
			<!-- wp:list {"ordered":{$isOrdered}} -->
			<{$tag}>{$items}</{$tag}>
			<!-- /wp:list -->
			HTML;
    }

    protected function handleTableBlock(DOMElement $table): string
    {
        $html = $this->safeHtml($table);
        return <<<HTML
			<!-- wp:table -->
			<figure class="wp-block-table">{$html}</figure>
			<!-- /wp:table -->
			HTML;
    }

    protected function handleVideoBlock(DOMElement $video): string
    {
        $src = '';
        foreach ($video->getElementsByTagName('source') as $source) {
            if ($source->getAttribute('src')) {
                $src = $source->getAttribute('src');
                break;
            }
        }

        if (!$src && $video->getAttribute('src')) {
            $src = $video->getAttribute('src');
        }

        if ($src) {
            return <<<HTML
				<!-- wp:video -->
				<figure class="wp-block-video"><video controls src="{$src}"></video></figure>
				<!-- /wp:video -->
				HTML;
        }

        return $this->wrapBlock('video', $this->safeHtml($video));
    }

    protected function handleEmbedBlock(DOMElement $iframe): string
    {
        $src = $iframe->getAttribute('src');
        if (!$src) return '';

        return <<<HTML
			<!-- wp:embed {"url":"{$src}","type":"rich","providerNameSlug":"custom"} -->
			<figure class="wp-block-embed">
				<div class="wp-block-embed__wrapper">
					{$src}
				</div>
			</figure>
			<!-- /wp:embed -->
			HTML;
    }

    protected function handleFigureBlock(DOMElement $figure): string
    {
        $content = '';
        foreach ($figure->childNodes as $child) {
            if ($child->nodeName === 'img') {
                $content .= $this->handleImageBlock($child);
            } elseif ($child->nodeName === 'figcaption') {
                $content .= '<figcaption>' . htmlspecialchars($child->textContent) . '</figcaption>';
            }
        }

        return <<<HTML
			<!-- wp:image -->
			<figure class="wp-block-image">{$content}</figure>
			<!-- /wp:image -->
			HTML;
    }

    protected function safeHtml(DOMNode $node): string
    {
        return htmlspecialchars_decode(htmlspecialchars($this->dom->saveHTML($node)));
    }
}
