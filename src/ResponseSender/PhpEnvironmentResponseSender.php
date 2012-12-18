<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Mvc
 */

namespace Zend\Mvc\ResponseSender;

use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Http\Header\MultipleHeaderInterface;
use Zend\Http\PhpEnvironment\Response;

/**
 * @category   Zend
 * @package    Zend_Mvc
 * @subpackage ResponseSender
 */
class PhpEnvironmentResponseSender implements ResponseSenderInterface
{
    /**
     * Send HTTP headers
     *
     * @param SendResponseEvent $event
     * @return PhpEnvironmentResponseSender
     */
    public function sendHeaders(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($response->headersSent() || $event->headersSent()) {
            return $this;
        }
        $status  = $response->renderStatusLine();
        header($status);
        /* @var \Zend\Http\Header\HeaderInterface $header */
        foreach ($response->getHeaders() as $header) {
            if ($header instanceof MultipleHeaderInterface) {
                header($header->toString(), false);
                continue;
            }
            header($header->toString());
        }
        $event->setHeadersSent();
        return $this;
    }

    /**
     * Send content
     *
     * @param SendResponseEvent $event
     * @return PhpEnvironmentResponseSender
     */
    public function sendContent(SendResponseEvent $event)
    {
        if ($event->contentSent()) {
            return $this;
        }
        $response = $event->getResponse();
        echo $response->getContent();
        $event->setContentSent();
        return $this;
    }

    /**
     * Send HTTP response
     *
     * @param SendResponseEvent $event
     * @return PhpEnvironmentResponseSender
     */
    public function __invoke(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($response instanceof Response) {
            $this->sendHeaders($event)
                 ->sendContent($event);
            $event->stopPropagation(true);
        }
        return $this;
    }

}
