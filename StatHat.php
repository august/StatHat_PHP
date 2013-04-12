<?php
/**
 * A PHP class that facilitates posting of stats to StatHat.com
 * Based, in part, on StatHat's own stathat.php.
 * 
 * Includes support for transactions.
 *
 * @author August Trometer (http://getyowza.com/contact)
 */

define('STATHAT_EZ_ENDPOINT','https://api.stathat.com/ez');

class StatHat {

	public static $_ez_key;
	public static $_transactions = array();
	public static $_use_transaction = false;

	/**
	 * Sets the EZ Key
	 */
	public static function setEZKey($key) {

		self::$_ez_key = $key;
	}

	/**
	 * Starts the transaction
	 * 
	 * This is only necessary if you want to send data
	 * in a single POST
	 */
	public static function beginTransaction() {

		self::$_use_transaction = true;
	}

	/**
	 * Publishes a count stat
	 * 
	 * This function adds the data to an array of transactions. If we're
	 * not using transactions, then the single item is instantly posted
	 * to the server
	 */
	public static function publishCount($stat_key, $count, $timestamp = NULL, $synchronous = false) {

		$transaction = array(
			'stat' => $stat_key,
			'count' => $count,
			);

		// include timestamp information if necessary
		if ($timestamp)
			$transaction['t'] = $timestamp;

		// add to the transaction array
		self::$_transactions[] = $transaction;

		// if we're not using transactions, go ahead and 
		// post the data
		if (!self::$_use_transaction)
			return self::commitTransaction($synchronous);
	}

	/**
	 * Publishes a count stat synchronously and returns the response
	 * 
	 * This is a convenience function
	 */
	public static function publishCountSynchronous($stat_key, $count, $timestamp = NULL) {

		return self::publishCount($stat_key, $count, $timestamp, true);
	}

	/**
	 * Publishes a value stat
	 * 
	 * This function adds the data to an array of transactions. If we're
	 * not using transactions, then the single item is instantly posted
	 * to the server
	 */
	public static function publishValue($stat_key, $value, $timestamp = NULL, $synchronous = false) {

		$transaction = array(
			'stat' => $stat_key,
			'value' => $value,
			);

		// include timestamp information if necessary
		if ($timestamp)
			$transaction['t'] = $timestamp;

		self::$_transactions[] = $transaction;

		if (!self::$_use_transaction)
			return self::commitTransaction($synchronous);
	}

	/**
	 * Publishes a value stat synchronously and returns the response
	 * 
	 * This is a convenience function
	 */
	public static function publishValueSynchronous($stat_key, $value, $timestamp = NULL) {

		return self::publishValue($stat_key, $value, $timestamp, true);
	}

	/**
	 * Posts the transaction data to the server
	 */
	public static function commitTransaction($synchronous = false) {

		$data = array(
			'ezkey' => self::$_ez_key,
			'data' => array()
			);

		// build the transaction array
		foreach(self::$_transactions as $transaction) {

			$data['data'][] = $transaction;
		}

		// reset the transaction
		self::$_use_transaction = false;
		self::$_transactions = array();

		$json = json_encode($data);

		// post synchronous or asynchrnously, as desired
		if ($synchronous)
			return self::postJSONSynchronous($json);
		else
			self::postJSON($json);
	}

	/**
	 * Posts the transaction synchronously
	 * 
	 * This is a convenience function
	 */
	public static function commitTransactionSynchronous() {

		return self::commitTransaction(true);
	}

	/**
	 * Posts the JSON data asynchronously to the server
	 */
	function postJSON($json) {

		$parts = parse_url(STATHAT_EZ_ENDPOINT);

		$fp = fsockopen($parts['host'], 80, $errno, $errstr, 30);

		$out = "POST {$parts['path']} HTTP/1.1\r\n";
		$out .= "Host: {$parts['host']}\r\n";
		$out .= "Content-Type: application/json\r\n";
		$out .= "Content-Length: " . strlen($json) . "\r\n";
		$out .= "Connection: Close\r\n\r\n";

		$out .= $json;

		fwrite($fp, $out);
		fclose($fp);
	}

	/**
	 * Posts the JSON data synchronously to the server
	 */
	function postJSONSynchronous($json) {

		$url = STATHAT_EZ_ENDPOINT;

		$params = array(
				'http' => array(
					'method' => 'POST',
					'content' => $json,
					'header' => array('Content-Type: application/json')
				)
			);

		$context = stream_context_create($params);

		$fp = @fopen($url, 'rb', false, $context);

		if (!$fp)
			throw new Exception("Problem with $url, $php_errormsg");

		$response = @stream_get_contents($fp);

		if ($response === false)
			throw new Exception("Problem reading data from $url, $php_errormsg");

		return $response;
	}

} // clas StatHat
