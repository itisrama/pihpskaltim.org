<?php
/*
 * @author Herwin Pradana
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JKHelperAuthencrypt{

	public static function encrypt($msg, $eKey, $aKey){
		// Generate initialization vector for AES 128 mode CBC.
		$ivSize = openssl_cipher_iv_length('aes-128-cbc');
		$iv = openssl_random_pseudo_bytes($ivSize);
		
		// Encrypt message with encryption key.
		$ciphertext = openssl_encrypt(
		    $msg,
		    'aes-128-cbc',
		    $eKey,
		    OPENSSL_RAW_DATA,
		    $iv
		);
		
		// Document consists of IV and encrypted message.
		// Signature is made by hashing the document using SHA256 (HMAC method) with the auth key.
		$doc = $iv.$ciphertext;
		$sig = hash_hmac('sha256', $doc, $aKey, true);

		// Final string is sig + document encoded with Base64.
		return base64_encode($sig.$doc);
	}

	public static function decrypt($msg, $eKey, $aKey){
		// Decode the string. Signature is taken from byte 0-32. IV starts from bit 32. Rest is ciphertext.
		$msg		= base64_decode($msg);
		$ivSize 	= openssl_cipher_iv_length('aes-128-cbc');
		$sig 		= mb_substr($msg, 0, 32, '8bit');
		$iv 		= mb_substr($msg, 32, $ivSize, '8bit');
		$ciphertext = mb_substr($msg, 32 + $ivSize, null, '8bit');
	
		// Make new signature using IV and ciphertext with the same algorithm and method used while encrypting.
		$calculatedSig = hash_hmac('sha256', $iv.$ciphertext, $aKey, true);

		// Check if signature's authenticity and integrity.
		if(hash_equals($sig, $calculatedSig)){
			$decrypted = openssl_decrypt(
				$ciphertext,
				'aes-128-cbc',
				$eKey,
				OPENSSL_RAW_DATA,
				$iv
			);
		
			// Handle errors
			while ($err = openssl_error_string())
				echo $err . "<br />\n";
		
			return $decrypted;
		}
		else{
			return 'Auth failed.';
		}
	}
	
}
?>
