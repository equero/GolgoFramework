<?php

namespace Core\Controllers\Http\Psr;

use Core\Helpers\Collection;
use Core\Controllers\Http\Psr\Interfaces\RequestInterface;
use Core\Controllers\Http\Psr\Interfaces\UriInterface;
use Core\Controllers\Http\Psr\Interfaces\StreamInterface;
use Core\Controllers\Http\Psr\UploadedFile;
use Core\Controllers\ExceptionController;
use Core\Helpers\Utils;
use Core\Controllers\Http\Psr\Interfaces\CookiesInterface;

/**
 * Headers
 *
 * This class represents a collection of HTTP headers
 * that is used in both the HTTP request and response objects.
 * It also enables header name case-insensitivity when
 * getting or setting a header value.
 *
 * Each HTTP header can have multiple values. This class
 * stores values into an array for each header name. When
 * you request a header value, you receive an array of values
 * for that header.
 */
class Request extends Message implements RequestInterface {

	
	protected $method;
	
	protected $uri;
	
	protected $postParams;
	
	protected $getParams;
	
	/**
	 * Uri parsed params from path
	 * @var string
	 */
	protected $routeParams;
	
	protected $cookies;
	
	protected $bodyParsers = [];
	
	protected $uploadedFiles;
	
	protected $isApiRequest;
	
	/**
	 * If the uri has a matched route
	 * @var boolean
	 */
	protected $hasMatch = false;
	
	/**
	 * @var Route
	 * @see	Route
	 */
	protected $matchedRoute;
	
	protected $response;
	
	
	public function __construct($method, UriInterface $uri, Headers $headers, CookiesInterface $cookies, StreamInterface $body, array $uploadedFiles = array()) {
		$this->method = $method;
		$this->uri = $uri;
		$this->headers = $headers;
		$this->cookies = $cookies;
		$this->body = $body;
		$this->uploadedFiles = !is_null($uploadedFiles) ? $uploadedFiles : array();
		
		$this->isApiRequest = strpos($this->uri->getPath(), "/api/") !== false ? true : false;
		
		$this->addInitialBodyParsers();
		$this->response = new Response();
	}
	
	public static function parseRequest() {
		
		$stream = fopen('php://temp', 'w+');
		stream_copy_to_stream(fopen('php://input', 'r'), $stream);
		$streamBody = new Stream($stream);
		$streamBody->rewind();
		
		$header = new Headers();
		foreach (getallheaders() as $key => $value) {
			$header->add($key, $value);
		}
		$method = $_SERVER['REQUEST_METHOD'];
		
		$url =  "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		$escaped_url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		$uri = Uri::createFromString($escaped_url);
		
		$allHeaders = getallheaders();
		if(isset($allHeaders['Cookie'])) {
			$cookieHeader = $allHeaders['Cookie'];
			$cookieData = Cookies::parseHeader($cookieHeader);
			$cookie = new Cookies($cookieData);
		} else {
			$cookie = new Cookies();
		}
	
		
		$files = UploadedFile::parseRequestFiles();
		
		return new static($method, $uri, $header, $cookie, $streamBody, $files);
		
	}
	
	protected function addInitialBodyParsers() {
		$this->bodyParsers['application/json'] = function($body) {
			$result = json_decode($body, true);
			if (!is_array($result)) {
				return null;
			}
			return $result;
		};
		
		$this->bodyParsers['application/xml'] = function($body) {
			$backup = libxml_disable_entity_loader(true);
			$backup_errors = libxml_use_internal_errors(true);
			$result = simplexml_load_string($body);
			libxml_disable_entity_loader($backup);
			libxml_clear_errors();
			libxml_use_internal_errors($backup_errors);
			if ($result === false) {
				return null;
			}
			return $result;
		};
		$this->bodyParsers['text/xml'] = function($body) {
			$backup = libxml_disable_entity_loader(true);
			$backup_errors = libxml_use_internal_errors(true);
			$result = simplexml_load_string($body);
			libxml_disable_entity_loader($backup);
			libxml_clear_errors();
			libxml_use_internal_errors($backup_errors);
			if ($result === false) {
				return null;
			}
			return $result;
		};
		$this->bodyParsers['application/x-www-form-urlencoded'] = function($body) {
			parse_str($body, $data);
			return $data;
		};
		$this->bodyParsers['application/x-www-form-urlencoded; charset=UTF-8'] = function($body) {
			parse_str($body, $data);
			return $data;
		};
		
		$this->bodyParsers['default'] = function($body) {
			$postvars = $_POST;
			foreach($postvars as $field => $value) {
				$this->postParams[$field] = $value;
			}
		};
		
	}
	
	public function parseRouteParams($argument_keys, $matches) {
		foreach ($argument_keys as $key => $name) {
			if (isset($matches[$key])) {
				$this->routeParams[$name] = $matches[$key];
			}
		}
	}
	
	
	public function parseIncomingParams() {
		$this->parseGetParams();
		$this->parsePostParams();
	}
	
	public function parseGetParams() {
		if ($query = $this->uri->getQuery()) {
			parse_str($query, $this->getParams);
			foreach($this->getParams as $field => $value) {
				$this->getParams[$field] = Utils::xssafe($value);
		
			}
		}
	}
	
	public function parsePostParams() {
		if (isset($_SERVER["CONTENT_TYPE"]) && $contentType = strtolower($_SERVER["CONTENT_TYPE"])) {
			$body = (string)$this->getBody()->__toString();
			
			if (isset($this->bodyParsers[$contentType]) === true) {
				$parsed = $this->bodyParsers[$contentType]($body);
				$this->postParams = $parsed;
			} else {
				$parsed = $this->bodyParsers["default"]($body);
				$this->postParams = $parsed;
			}
		}
		
		
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \Core\Controllers\Http\Psr\Interfaces\RequestInterface::getRequestTarget()
	 */
	public function getRequestTarget() {
		
		if ($this->uri === null) {
			return '/';
		}
		
		$path = $this->uri->getPath();
		$path = '/' . ltrim($path, '/');
		
		$query = $this->uri->getQuery();
		if ($query) {
			$path .= '?' . $query;
		}
		
		return $path;

	}

	/**
	 * {@inheritDoc}
	 * @see \Core\Controllers\Http\Psr\Interfaces\RequestInterface::withRequestTarget()
	 */
	public function withRequestTarget($requestTarget) {
		if (preg_match('#\s#', $requestTarget)) {
			throw new \InvalidArgumentException('Invalid request target provided; must be a string and cannot contain whitespace');
		}
		$clone = clone $this;
		$clone->requestTarget = $requestTarget;
		$clone->uri = Uri::createFromString($requestTarget);
		
		return $clone;

	}

	/**
	 * {@inheritDoc}
	 * @see \Core\Controllers\Http\Psr\Interfaces\RequestInterface::getMethod()
	 */
	public function getMethod() {
		return $this->method;

	}

	/**
	 * {@inheritDoc}
	 * @see \Core\Controllers\Http\Psr\Interfaces\RequestInterface::withMethod()
	 */
	public function withMethod($method) {
		$clone = clone $this;
		$clone->method = $method;
		
		return $clone;

	}

	/**
	 * {@inheritDoc}
	 * @see \Core\Controllers\Http\Psr\Interfaces\RequestInterface::getUri()
	 */
	public function getUri() {
		return $this->uri;

	}

	/**
	 * {@inheritDoc}
	 * @see \Core\Controllers\Http\Psr\Interfaces\RequestInterface::withUri()
	 */
	public function withUri(UriInterface $uri, $preserveHost = false) {
		$clone = clone $this;
		$clone->uri = $uri;
		
		if (!$preserveHost) {
			if ($uri->getHost() !== '') {
				$clone->headers->set('Host', $uri->getHost());
			}
		} else {
			if ($uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeaderLine('Host') === '')) {
				$clone->headers->set('Host', $uri->getHost());
			}
		}
		
		return $clone;

	}
	
	public function dispatchNoMatch() {
		if($this->isApiRequest) {
			ExceptionController::routeNotFound();
		} else {
			ExceptionController::show404();
		}
	
	}
	
	public function executeRequest() {
	
		if(!$this->hasMatch) {
			$this->dispatchNoMatch();
	
		} else {
			$matchedRoute = $this->getMatchedRoute();
			if($matchedRoute->function != null) {
				call_user_func_array($matchedRoute->function, array($this));
			} else {
				$class = $matchedRoute->getTargetClass();
	
				if ($matchedRoute->getTargetClassMethod() != null) {
					call_user_func_array(array($class, $matchedRoute->getTargetClassMethod()), array($this));
				} else {
					if(class_exists($class))
						new $class($this);
					else ExceptionController::classNotFound();
				}
			}
	
	
		}
	}
	
	public function sendResponse() {
		$this->response->sendResponse();
	}
	
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}
	public function setUri($uri) {
		$this->uri = $uri;
		return $this;
	}
	public function getPostParams() {
		return $this->postParams;
	}
	public function setPostParams($postParams) {
		$this->postParams = $postParams;
		return $this;
	}
	public function getGetParams() {
		return $this->getParams;
	}
	public function setGetParams($getParams) {
		$this->getParams = $getParams;
		return $this;
	}
	public function getRouteParams() {
		return $this->routeParams;
	}
	public function setRouteParams($routeParams) {
		$this->routeParams = $routeParams;
		return $this;
	}
	public function getCookies() {
		return $this->cookies;
	}
	public function setCookies($cookies) {
		$this->cookies = $cookies;
		return $this;
	}
	public function getBodyParsers() {
		return $this->bodyParsers;
	}
	public function setBodyParsers($bodyParsers) {
		$this->bodyParsers = $bodyParsers;
		return $this;
	}
	public function addBodyParsers($bodyParsers) {
		$this->bodyParsers[] = $bodyParsers;
		return $this;
	}
	public function getUploadedFiles() {
		return $this->uploadedFiles;
	}
	public function setUploadedFiles($uploadedFiles) {
		$this->uploadedFiles = $uploadedFiles;
		return $this;
	}
	public function getIsApiRequest() {
		return $this->isApiRequest;
	}
	public function setIsApiRequest($isApiRequest) {
		$this->isApiRequest = $isApiRequest;
		return $this;
	}
	public function getHasMatch() {
		return $this->hasMatch;
	}
	public function setHasMatch($hasMatch) {
		$this->hasMatch = $hasMatch;
		return $this;
	}
	public function getMatchedRoute() {
		return $this->matchedRoute;
	}
	public function setMatchedRoute($matchedRoute) {
		$this->matchedRoute = $matchedRoute;
		return $this;
	}
	public function getResponse() {
		return $this->response;
	}
	public function setResponse($response) {
		$this->response = $response;
		return $this;
	}
	
	

}
