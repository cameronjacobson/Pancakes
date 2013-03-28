<?php

namespace Pancakes;

class Pancakes
{
	private $tasks = array();
	private $timeout;
	public $buffer;
	public $bufferSize;
	private $base;
	private $sockets;
	private $buffered_events;

	public function __construct(){
		if(!function_exists('event_base_new')){
			throw new \Exception('You need libevent for your pancakes');
		}
		$this->base = event_base_new();
		$this->timeout = ini_get('default_socket_timeout');
		$this->socketOptions = STREAM_CLIENT_ASYNC_CONNECT;
		$this->buffer = array();
		$this->tasks = array();
		$this->bufferSize = 1024;
	}

	public function stack($type, $data){
		$this->tasks[$type][] = $data;
	}

	public function eat(){
		foreach($this->tasks as $type=>$data){
			switch($type){
				case 'url':
					foreach($data as $url){
						$key = $url;
						$this->buffer[$type][$key] = '';
						if(!empty($url) && (strpos($url, '://') === false)){
							$url = 'tcp://'.$url;
						}
						$parts = parse_url($url);
						$parts['scheme'] = @$parts['scheme'] ? $parts['scheme'].'://' : 'tcp://';
						$parts['user'] = @$parts['user'] ? $parts['user'].'@' : '';
						$parts['pass'] = @$parts['pass'] ? $parts['pass'].':' : '';
						$parts['host'] = @$parts['host'] ?: 'localhost';
						$parts['port'] = @$parts['port'] ? ':'.$parts['port'] : ':80';
						$parts['path'] = @$parts['path'] ?: '/';
						$parts['query'] = @$parts['query'] ? '?'.$parts['query'] : ''; 
						$parts['fragment'] = @$parts['fragment'] ? '#'.$parts['fragment'] : ''; 

						$this->sockets[$key] = stream_socket_client(
							$parts['scheme'].$parts['user'].$parts['pass'].$parts['host'].$parts['port'],
							$errno,
							$errstr,
							$this->timeout,
							$this->socketOptions
						);

						stream_set_read_buffer($this->sockets[$key], $this->bufferSize);

						fwrite($this->sockets[$key], "GET ".$parts['path']." HTTP/1.0\r\nHost: ".$parts['host']."\r\nAccept: *"."/*\r\n\r\n");
						$this->buffered_events[$key] = event_buffer_new(
							$this->sockets[$key],
							array($this, 'read_callback_url'),
							array($this, 'write_callback_url'),
							array($this, 'error_callback_url'),
							array('url'=>$key,'type'=>$type)
						);
						event_buffer_watermark_set($this->buffered_events[$key], EV_READ, 1, $this->bufferSize);
						event_buffer_base_set($this->buffered_events[$key], $this->base);
						event_buffer_enable($this->buffered_events[$key], EV_READ);
					}
					break;
				default:
					break;
			}
		}
		event_base_loop($this->base);
	}

	public function read_callback_url($buf, $arg) {
		$this->buffer[$arg['type']][$arg['url']] .= event_buffer_read($buf,$this->bufferSize);
	}

	public function write_callback_url($buf, $arg) {}

	public function error_callback_url($buf, $what, $arg) {}
}
