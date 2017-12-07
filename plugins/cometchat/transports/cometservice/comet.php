<?php

/*

CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license

*/

class Comet {
    private $ORIGIN        = 'x3.chatforyoursite.com';
    private $LIMIT         = 1800;
    private $PUBLISH_KEY   = '';
    private $SUBSCRIBE_KEY = '';
    private $SECRET_KEY    = false;
    private $SSL           = false;

    function __construct(
        $publish_key,
        $subscribe_key,
        $secret_key = false,
        $ssl = false
    ) {
        $this->PUBLISH_KEY   = $publish_key;
        $this->SUBSCRIBE_KEY = $subscribe_key;
        $this->SECRET_KEY    = $secret_key;
        $this->SSL           = $ssl;

        if ($ssl) $this->ORIGIN = 'https://' . $this->ORIGIN;
        else      $this->ORIGIN = 'http://'  . $this->ORIGIN;
    }

	function publish($args) {
        if (!($args['channel'] && $args['message'])) {
            echo('Missing Channel or Message');
            return false;
        }

        $channel = $args['channel'];
        $message = json_encode($args['message']);
        $string_to_sign = implode( '/', array(
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $this->SECRET_KEY,
            $channel,
            $message
        ) );

        $signature = $this->SECRET_KEY ? md5($string_to_sign) : '0';

        if (strlen($message) > $this->LIMIT) {
            echo('Message TOO LONG (' . $this->LIMIT . ' LIMIT)');
            return array( 0, 'Message Too Long.' );
        }

        return $this->_request(array(
            'publish',
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $signature,
            $channel,
            '0',
            $message
        ));
    }


    function time() {
        $response = $this->_request(array(
            'time',
            '0'
        ));

        return $response[0];
    }

    private function _request($request) {
        $request = array_map( 'Comet_encode', $request );
        array_unshift( $request, $this->ORIGIN );

        $ctx = stream_context_create(array(
            'http' => array( 'timeout' => 200 )
        ));

        return json_decode( file_get_contents_curl(
            implode( '/', $request )
        ), true );
    }

}

	function new_str_split($part) {
		if(function_exists('str_split')) {
			return str_split($part);
		}
		$arr = array();
		$i = 0;
		$part = (string)$part;
		while(isset($part[$i])) {
			$arr[] = $part[$i++];
		}
		return $arr;
	}

	function Comet_encode($part) {
		return implode( '', array_map(
			'Comet_encode_char', new_str_split($part)
		));
	}

	function Comet_encode_char($char) {
		if (strpos( ' ~`!@#$%^&*()+=[]\\{}|;\':",./<>?', $char ) === false)
			return $char;
		return rawurlencode($char);
	}

	function file_get_contents_curl($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}