<?php
namespace Ttree\JsonApi\View;

/*
 * This file is part of the Ttree.JsonApi package.
 *
 * (c) ttree - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\View\AbstractView;

/**
 * Basic REST controller for the Ttree.Medialib package
 */
class JsonApiView extends AbstractView
{
    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var array
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->encoder->encodeData($this->data);
    }

    public function setEncoder(EncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    /**
     * @param mixed $data
     */
    public function setData($data) {
        $this->data = $data;
    }

}
