<?php

/**
 * PHP MsgPack encode/decode
 * http://code.google.com/p/msgpack-php
 * MsgPack: http://msgpack.sourceforge.net/
 *
 * Author: S.Schwuchow http://www.Schottenland.de
 *
 * @TODO check signed int >16bit
 * @TODO raise more warning of php incompatiblities
 * @TODO check float twice
 * @TODO build a big and complex test binaryString from other implementation and test against it
 *
 *
 */
class MsgPack_Coder {

	// fixed length
	const VALUE_SCALAR_NULL = 192; // xC0
	const VALUE_SCALAR_FALSE = 194; // xC2
	const VALUE_SCALAR_TRUE = 195; // xC3
	const VALUE_SCALAR_FLOAT = 202; // xCA
	const VALUE_SCALAR_DOUBLE = 203; // xCB
	// x00-x7f - integer 0-127 positive fixnum
	const VALUE_INT_FIX_NEGATIVE = 224; // 111XXXXX xE0-xFF -1 - -32 // unclear 11100000 = -1 ??
	const VALUE_INT_UNSIGNED_8 = 204; // xCC + 1 byte
	const VALUE_INT_UNSIGNED_16 = 205; // xCD + 2 byte
	const VALUE_INT_UNSIGNED_32 = 206; // xCE + 4 byte
	const VALUE_INT_UNSIGNED_64 = 207; // xCF + 8 byte
	const VALUE_INT_SIGNED_8 = 208; // xD0 + 1 byte
	const VALUE_INT_SIGNED_16 = 209; // xD1 + 2 byte
	const VALUE_INT_SIGNED_32 = 210; // xD2 + 4 byte
	const VALUE_INT_SIGNED_64 = 211; // xD3 + 8 byte
	// raw bytes
	const VALUE_RAW_FIX = 160; // xA0 101XXXXX + max 31 byte len
	const VALUE_RAW_16 = 218; // xDA save raw bytes up to (2^16)-1 bytes.
	const VALUE_RAW_32 = 219; // xDB save raw bytes up to (2^32)-1 bytes.
	// container
	const VALUE_LIST_FIX = 144; // x90 1001XXXX save an array up to 15 elements. 
	const VALUE_LIST_16 = 220; // xDC save an array up to (2^16)-1 elements. 
	const VALUE_LIST_32 = 221; // xDD save an array up to (2^32)-1 elements. 
	const VALUE_MAP_FIX = 128; // x80 1000XXXX save a map up to 15 elements. odd elements are key and next element of the key is its associate value.
	const VALUE_MAP_16 = 222; // xDE save a map up to (2^16)-1 elements. odd elements are key and next element of the key is its associate value.
	const VALUE_MAP_32 = 223; // xDF save a map up to (2^32)-1 elements. odd elements are key and next element of the key is its associate value.

	/**
	 * encode a PHP-Variable to binary MsgPack String
	 *
	 * @static
	 * @param mixed $message
	 * @return string
	 */
	static public function encode($message) {
		$messagePack = null;
		if( $message===null ) {
			$messagePack.= chr(self::VALUE_SCALAR_NULL);
		} elseif( $message===true ) {
			$messagePack.= chr(self::VALUE_SCALAR_TRUE);
		} elseif( $message===false ) {
			$messagePack.= chr(self::VALUE_SCALAR_FALSE);
		} elseif( is_double($message) ) {
			$binary = pack("d", $message);
			if( strlen($binary)==4 ) {
				$messagePack.= chr(self::VALUE_SCALAR_FLOAT).$binary;
			} elseif( strlen($binary)==8 ) {
				$messagePack.= chr(self::VALUE_SCALAR_DOUBLE).$binary;
			} else {
				user_error(__METHOD__.': unexpected pack() result-len!', E_USER_ERROR);
				$messagePack.= self::VALUE_SCALAR_NULL;
			}
		} elseif( is_float($message) ) {
			// it look like a float is always a double...
			$binary = pack("f", $message);
			if( strlen($binary)==4 ) {
				$messagePack.= chr(self::VALUE_SCALAR_FLOAT).$binary;
			} elseif( strlen($binary)==8 ) {
				$messagePack.= chr(self::VALUE_SCALAR_DOUBLE).$binary;
			} else {
				user_error(__METHOD__.': unexpected pack() result-len!', E_USER_ERROR);
				$messagePack.= self::VALUE_SCALAR_NULL;
			}
		} elseif( is_int($message) ) {
			if( $message<0 ) {
				if( $message>=-32 ) {
					$messagePack.= pack('c',$message);
				} elseif( $message>=-128 ) {
					$messagePack.= chr(self::VALUE_INT_SIGNED_8);
					$messagePack.= pack('c',$message); // signed char
				} elseif( $message>=-65535 ) {
					$messagePack.= chr(self::VALUE_INT_SIGNED_16);
					$messagePack.= self::getNibblesFromInt(65536+$message, 2); // FF FF = -1
				} elseif( $message>=-pow(2,32)-1 ) {
					$messagePack.= chr(self::VALUE_INT_SIGNED_32);
					$messagePack.= self::getNibblesFromInt(abs($message), 4);
				} else {
					$messagePack.= chr(self::VALUE_INT_SIGNED_64);
					$messagePack.= self::getNibblesFromInt(abs($message), 8);
				}
			} elseif( $message<=127 ) {
				$messagePack.= chr($message);
			} elseif( $message<=255 ) {
				$messagePack.= chr(self::VALUE_INT_UNSIGNED_8);
				$messagePack.= self::getNibblesFromInt($message, 1);
			} elseif( $message<=65535 ) {
				$messagePack.= chr(self::VALUE_INT_UNSIGNED_16);
				$messagePack.= self::getNibblesFromInt($message, 2);
			} elseif( $message<=pow(2,32)-1 ) {
				$messagePack.= chr(self::VALUE_INT_UNSIGNED_32);
				$messagePack.= self::getNibblesFromInt($message, 4);
			} else {
				$messagePack.= chr(self::VALUE_INT_UNSIGNED_64);
				$messagePack.= self::getNibblesFromInt($message, 8);
			}
		} elseif( is_string($message) ) {
			$len = strlen($message);
			if( $len<=31 ) {
				$messagePack.= chr(self::VALUE_RAW_FIX+$len);
			} elseif( $len<=65535 ) { // 2^16-1
				$messagePack.= chr(self::VALUE_RAW_16);
				$messagePack.= self::getNibblesFromInt($len, 2);
			} else {
				$messagePack.= chr(self::VALUE_RAW_32);
				$messagePack.= self::getNibblesFromInt($len, 4);
			}
			$messagePack.= $message;
		} elseif( is_array($message) ) {
			$assoc = false;
			$index = 0;
			foreach( $message as $key=>$value ) {
				if( $key!=$index++ ) { // key ist nicht index
					$assoc = true;
					break;
				}
			}
			$count = count($message);
			if( $count<=15 ) {
				if( $assoc ) {
					$messagePack.= chr(self::VALUE_MAP_FIX+$count);
				} else {
					$messagePack.= chr(self::VALUE_LIST_FIX+$count);
				}
			} elseif( $count<65536) {
				if( $assoc ) {
					$messagePack.= chr(self::VALUE_MAP_16);
				} else {
					$messagePack.= chr(self::VALUE_LIST_16);
				}
				$messagePack.= self::getNibblesFromInt($count, 2);
			} else {
				if( $assoc ) {
					$messagePack.= chr(self::VALUE_MAP_32);
				} else {
					$messagePack.= chr(self::VALUE_LIST_32);
				}
				$messagePack.= self::getNibblesFromInt($count, 4);
			}
			foreach( $message as $key=>$value ) {
				if( $assoc ) {
					$messagePack.= self::encode($key);
				}
				$messagePack.= self::encode($value);
			}
		} else {
			$messagePack = 'encoding failed! messagepack:'.$messagePack;
		}
		return $messagePack;
	}



	/**
	 * decode a MsgPack to php-Variable
	 * the affected bytes will be removed
	 *
	 * @static
	 * @param string $messagePack
	 * @return mixed
	 */
	static public function decode(&$messagePack) {
		$message = null;
		$messageByte = ord(substr($messagePack,0,1));
		$messagePack = substr($messagePack,1);
		if( $messageByte==self::VALUE_SCALAR_NULL ) {
			$message = null;
		} elseif( $messageByte==self::VALUE_SCALAR_TRUE ) {
			$message = true;
		} elseif( $messageByte==self::VALUE_SCALAR_FALSE ) {
			$message = false;
		} elseif( $messageByte==self::VALUE_SCALAR_DOUBLE ) {
			$unpack = unpack('d', $messagePack);
			$message = $unpack[1];
			$messagePack = substr($messagePack, 8);
		} elseif( $messageByte==self::VALUE_SCALAR_FLOAT ) {
			// it seem that unpack('f'... returns a double, so the result can be different from source e.g. for 1.3 (float) = 1.29999995232 (double)
			$unpack = unpack('f', $messagePack);
			$message = $unpack[1];
			$messagePack = substr($messagePack, 4);
		} elseif( $messageByte<=127 ) {
			$message = $messageByte;
		} elseif( $messageByte==self::VALUE_INT_UNSIGNED_8 ) {
			$message = self::getIntFromMessagePack($messagePack, 1);
		} elseif( $messageByte==self::VALUE_INT_UNSIGNED_16 ) {
			$message = self::getIntFromMessagePack($messagePack, 2);
		} elseif( $messageByte==self::VALUE_INT_UNSIGNED_32 ) {
			$message = self::getIntFromMessagePack($messagePack, 4);
		} elseif( $messageByte==self::VALUE_INT_UNSIGNED_64 ) {
			$message = self::getIntFromMessagePack($messagePack, 8);
		} elseif( $messageByte>=self::VALUE_INT_FIX_NEGATIVE AND $messageByte<=self::VALUE_INT_FIX_NEGATIVE+31) {
			$message = -256+$messageByte;
		} elseif( $messageByte==self::VALUE_INT_SIGNED_8 ) {
			$message = -256+self::getIntFromMessagePack($messagePack, 1);
		} elseif( $messageByte==self::VALUE_INT_SIGNED_16 ) {
			$message = -65536+self::getIntFromMessagePack($messagePack, 2);
		} elseif( $messageByte==self::VALUE_INT_SIGNED_32 ) {
			$message = 0-self::getIntFromMessagePack($messagePack, 4);
		} elseif( $messageByte==self::VALUE_INT_SIGNED_64 ) {
			$message = 0-self::getIntFromMessagePack($messagePack, 8);
		} elseif( $messageByte>=self::VALUE_RAW_FIX AND $messageByte<=self::VALUE_RAW_FIX+31) {
			$len = $messageByte-self::VALUE_RAW_FIX;
			$message = substr($messagePack,0,$len);
			$messagePack = substr($messagePack,$len);
		} elseif( $messageByte==self::VALUE_RAW_16 ) {
			$len = self::getIntFromMessagePack($messagePack,2);
			$message = substr($messagePack,0,$len);
			$messagePack = substr($messagePack,$len);
		} elseif( $messageByte==self::VALUE_RAW_32 ) {
			$len = self::getIntFromMessagePack($messagePack,4);
			$message = substr($messagePack,0,$len);
			$messagePack = substr($messagePack,$len);
		} elseif( $messageByte>=self::VALUE_LIST_FIX AND $messageByte<=self::VALUE_LIST_FIX+15) {
			$count = $messageByte-self::VALUE_LIST_FIX;
			$message = self::getArrayFromMessagesPack($messagePack, $count, false);
		} elseif( $messageByte>=self::VALUE_MAP_FIX AND $messageByte<=self::VALUE_MAP_FIX+15) {
			$count = $messageByte-self::VALUE_MAP_FIX;
			$message = self::getArrayFromMessagesPack($messagePack, $count, true);
		} elseif( $messageByte==self::VALUE_LIST_16 ) {
			$len = self::getIntFromMessagePack($messagePack, 2);
			$message = self::getArrayFromMessagesPack($messagePack, $len, false);
		} elseif( $messageByte==self::VALUE_LIST_32 ) {
			$len = self::getIntFromMessagePack($messagePack, 4);
			$message = self::getArrayFromMessagesPack($messagePack, $len, false);
		} elseif( $messageByte==self::VALUE_MAP_16 ) {
			$len = self::getIntFromMessagePack($messagePack, 2);
			$message = self::getArrayFromMessagesPack($messagePack, $len, true);
		} elseif( $messageByte==self::VALUE_MAP_32 ) {
			$len = self::getIntFromMessagePack($messagePack, 4);
			$message = self::getArrayFromMessagesPack($messagePack, $len, true);
		} else {
			$message = 'resolve Failed';
		}
		return $message;
	}

	/**
	 * dump a binary String for debugging
	 * @static
	 * @param string $messagePack
	 * @return string
	 */
	static public function hexDump($messagePack) {
		$out = '';
		for($i=0; $i<strlen($messagePack); $i++) {
			$out.= ' '.dechex(ord($messagePack[$i]));
		}
		return $out;
	}

	/**
	 * build binary Nibbles for PHP-int
	 * @static
	 * @param int $value
	 * @param int $len
	 * @return string
	 */
	static protected function getNibblesFromInt($intValue, $len) {
		$result = '';
		for($i=1; $i<=$len; $i++ ) {
			$result = chr($intValue % 256).$result;
			$intValue = $intValue/256;
		}
		return $result;
	}
	/**
	 * get an PHP-Int from binary String
	 * the affected bytes will by removed
	 *
	 * @static
	 * @param string $messagePack
	 * @param int $len
	 * @return int
	 */
	static protected function getIntFromMessagePack(&$messagePack, $len) {
		$int = 0;
		for($i=0; $i<$len; $i++ ) {
			$int += ord(substr($messagePack,$len-1-$i,1)) * pow(2,$i*8);
		}
		$messagePack = substr($messagePack,$len);
		return $int;
	}

	/**
	 * get an PHP-Array from binary string
	 * the affected bytes will be removed
	 *
	 * @static
	 * @param string $messagePack
	 * @param int $count
	 * @param bool $assoc
	 * @return array
	 */
#segfault	static protected function getArrayFromMessagesPack(string &$messagePack, int $count, boolean $assoc) {
	static protected function getArrayFromMessagesPack(&$messagePack, $count, $assoc) {
		$message = array();
		for( $i=0; $i<$count; $i++ ) {
			if( $assoc ) {
				$message[self::decode($messagePack)] = self::decode($messagePack);
			} else {
				$message[] = self::decode($messagePack);
			}
		}
		return $message;
	}

}