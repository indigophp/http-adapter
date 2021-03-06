<?php

/*
 * This file is part of the Indigo HTTP Adapter package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Http\Message;

use Psr\Http\Message\IncomingResponseInterface;
use Psr\Http\Message\StreamableInterface;
use InvalidArgumentException;

/**
 * Implementation of PSR HTTP Incoming Response
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Response implements IncomingResponseInterface
{
    use Message;

    /**
     * Valid response phrases
     *
     * @var []
     */
    private static $reasonPhrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @var string
     */
    private $reasonPhrase;

    /**
     * @param integer             $statusCode
     * @param string|null         $reasonPhrase
     * @param array               $headers
     * @param StreamableInterface $body
     * @param string              $protocolVersion
     */
    public function __construct(
        $statusCode,
        $reasonPhrase = null,
        array $headers = [],
        StreamableInterface $body = null,
        $protocolVersion = '1.1'
    ) {
        $this->assertValidStatusCode($statusCode);

        if (empty($reasonPhrase)) {
            $reasonPhrase = $this->determineReasonPhrase($statusCode);
        }

        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        $this->headers = $this->cleanHeaders($headers);
        $this->body = $body;
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Asserts that the status code is valid
     *
     * @param integer $statusCode
     */
    private function assertValidStatusCode($statusCode)
    {
        if (!is_int($statusCode)) {
            throw new InvalidArgumentException('Status code should be an integer');
        }

        if ($statusCode < 100 || 599 < $statusCode) {
            throw new InvalidArgumentException('Status code must be between 100 and 599');
        }
    }

    /**
     * Determines the reason phrase from status code
     *
     * @param integer $statusCode
     *
     * @return string
     */
    private function determineReasonPhrase($statusCode)
    {
        if (array_key_exists($statusCode, self::$reasonPhrases)) {
            return self::$reasonPhrases[$statusCode];
        }

        return 'Unknown';
    }

    /**
     * Cleans headers
     *
     * @param array $headers
     *
     * @return array
     */
    private function cleanHeaders(array $headers)
    {
        $cleaned = [];

        foreach ($headers as $header => $value) {
            if (is_array($value)) {
                $value = array_map('strval', $value);
            } else {
                $value = [(string) $value];
            }

            $cleaned[strtolower($header)] = $value;
        }

        return $cleaned;
    }
}
