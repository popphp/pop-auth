<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Http;

/**
 * Auth HTTP response class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.0
 */
class Response
{

    /**
     * HTTP version
     * @var string
     */
    protected $version = '1.1';

    /**
     * Response code
     * @var int
     */
    protected $code = null;

    /**
     * Response message
     * @var string
     */
    protected $message = null;

    /**
     * Response headers
     * @var array
     */
    protected $headers = [];

    /**
     * Response body
     * @var string
     */
    protected $body = null;

    /**
     * Set the HTTP version
     *
     * @param  string $version
     * @return Response
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get the HTTP code
     *
     * @param  string $code
     * @return Response
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Set the HTTP message
     *
     * @param  string $message
     * @return Response
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the HTTP response headers
     *
     * @param  array $headers
     * @return Response
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    /**
     * Set an HTTP response header
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Response
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set the HTTP response body
     *
     * @param  string
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get the HTTP version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the HTTP code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the HTTP message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the HTTP response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get an HTTP response header
     *
     * @param  string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        return (isset($this->headers[$name])) ? $this->headers[$name] : null;
    }

    /**
     * Get the HTTP response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Send the request
     *
     * @param  string $uri
     * @param  array $context
     * @param  array $contextParams
     * @return void
     */
    public function sendRequest($uri, array $context = null, array $contextParams = null)
    {
        $http_response_header = null;
        $firstLine            = null;

        if (null !== $context) {
            $stream = (null !== $contextParams) ?
                @fopen($uri, 'r', false, stream_context_create($context, $contextParams)) :
                @fopen($uri, 'r', false, stream_context_create($context));
        } else {
            $stream = @fopen($uri, 'r');
        }

        if ($stream != false) {
            $meta = stream_get_meta_data($stream);
            $firstLine = $meta['wrapper_data'][0];
            unset($meta['wrapper_data'][0]);
            $allHeadersAry = $meta['wrapper_data'];
            $this->body = stream_get_contents($stream);
        } else if (null !== $http_response_header) {
            $firstLine = $http_response_header[0];
            unset($http_response_header[0]);
            $allHeadersAry = $http_response_header;
            $this->body = null;
        }

        if (null !== $firstLine) {
            // Get the version, code and message
            $this->version = substr($firstLine, 0, strpos($firstLine, ' '));
            $this->version = substr($this->version, (strpos($this->version, '/') + 1));
            preg_match('/\d\d\d/', trim($firstLine), $match);
            $this->code    = $match[0];
            $this->message = str_replace('HTTP/' . $this->version . ' ' . $this->code . ' ', '', $firstLine);

            // Get the headers
            foreach ($allHeadersAry as $hdr) {
                $name = substr($hdr, 0, strpos($hdr, ':'));
                $value = substr($hdr, (strpos($hdr, ' ') + 1));
                $this->headers[trim($name)] = trim($value);
            }

            // If the body content is encoded, decode the body content
            if (array_key_exists('Content-Encoding', $this->headers)) {
                if (isset($this->headers['Transfer-Encoding']) && ($this->headers['Transfer-Encoding'] == 'chunked')) {
                    $this->body = self::decodeChunkedBody($this->body);
                }
                $this->body = self::decodeBody($this->body, $this->headers['Content-Encoding']);
            }
        }
    }

    /**
     * Decode the body data.
     *
     * @param  string $body
     * @param  string $decode
     * @throws Exception
     * @return string
     */
    public static function decodeBody($body, $decode = 'gzip')
    {
        switch ($decode) {
            // GZIP compression
            case 'gzip':
                if (!function_exists('gzinflate')) {
                    throw new Exception('Gzip compression is not available.');
                }
                $decodedBody = gzinflate(substr($body, 10));
                break;

            // Deflate compression
            case 'deflate':
                if (!function_exists('gzinflate')) {
                    throw new Exception('Deflate compression is not available.');
                }
                $zlibHeader = unpack('n', substr($body, 0, 2));
                $decodedBody = ($zlibHeader[1] % 31 == 0) ? gzuncompress($body) : gzinflate($body);
                break;

            // Unknown compression
            default:
                $decodedBody = $body;

        }

        return $decodedBody;
    }

    /**
     * Decode a chunked transfer-encoded body and return the decoded text
     *
     * @param string $body
     * @return string
     */
    public static function decodeChunkedBody($body)
    {
        $decoded = '';

        while($body != '') {
            $lfPos = strpos($body, "\012");

            if ($lfPos === false) {
                $decoded .= $body;
                break;
            }

            $chunkHex = trim(substr($body, 0, $lfPos));
            $scPos    = strpos($chunkHex, ';');

            if ($scPos !== false) {
                $chunkHex = substr($chunkHex, 0, $scPos);
            }

            if ($chunkHex == '') {
                $decoded .= substr($body, 0, $lfPos);
                $body = substr($body, $lfPos + 1);
                continue;
            }

            $chunkLength = hexdec($chunkHex);

            if ($chunkLength) {
                $decoded .= substr($body, $lfPos + 1, $chunkLength);
                $body = substr($body, $lfPos + 2 + $chunkLength);
            } else {
                $body = '';
            }
        }

        return $decoded;
    }

}
