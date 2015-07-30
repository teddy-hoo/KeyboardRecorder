<?php

// for mac, not contains the key on keypad
$keyCodeMapping = [
	'0'                => 'A',
	'1'                => 'S',
	'2'                => 'D',
	'3'                => 'F',
	'4'                => 'H',
	'5'                => 'G',
	'6'                => 'z',
	'7'                => 'x',
	'8'                => 'C',
	'9'                => 'V',
	'11'               => 'B',
	'12'               => 'Q',
	'13'               => 'W',
	'14'               => 'E',
	'15'               => 'R',
	'16'               => 'Y',
	'17'               => 'T',
	'18'               => '1',
	'19'               => '2',
	'20'               => '3',
	'21'               => '4',
	'22'               => '6',
	'23'               => '5',
	'24'               => '=',
	'25'               => '9',
	'26'               => '7',
	'27'               => '-',
	'28'               => '8',
	'29'               => '0',
	'30'               => ']',
	'31'               => 'O',
	'32'               => 'U',
	'33'               => '[',
	'34'               => 'I',
	'35'               => 'P',
	'37'               => 'L',
	'38'               => 'J',
	'39'               => '\'',
	'40'               => 'K',
	'41'               => ';',
	'42'               => '\\',
	'43'               => ',',
	'44'               => '/',
	'45'               => 'N',
	'46'               => 'M',
	'47'               => '.',
	'50'               => '`',
	'36'               => 'RETURN',
	'48'               => 'TAB',
	'49'               => 'SPACE',
	'51'               => 'DELETE',
	'53'               => 'ESCAPE',
	'55'               => 'COMMAND',
	'56'               => 'SHIFT',
	'57'               => 'CAPSLOCK',
	'58'               => 'OPTION',
	'59'               => 'CONTROL',
	'60'               => 'RIGHTSHIFT',
	'61'               => 'RIGHTOPTION',
	'62'               => 'RIGHTCONTROL',
	'63'               => 'FUNCTION',
	'123'              => 'LEFTARROW',
	'124'              => 'RIGHTARROW',
	'125'              => 'DOWNARROW',
	'126'              => 'UPARROW',
];

class KeyRecorderController extends ControllerBase {

	public static $musicList = [
		'easy' => ['沉默是金$$张国荣'],
		'fuck' => ['Rammstein$$Rammstein'],
		'happ' => ['爱情木瓜$$'],

	];

	private function getKeyPressTimesRedis ($source, $day = null) {
		$key = $day ? 'key_press' . $source . $day : 'key_press' . $source . strtotime('today');
		return new \Redis\Hash($key, -1);
	}

	private function getKeyPressInTimeRangeRedis ($source) {
		$key = 'key_press' . $source;
		return new \Redis\ZSet($key, -1);
	}

	private function writeToRedis ($source, $records) {

		$keyPressTimes       = $this->getKeyPressTimesRedis($source);
		$keyPressInTimeRange = $this->getKeyPressInTimeRangeRedis($source);

		foreach ($records as $keyCode => $timestamp) {

			if ($keyPressTimes->contains($keyCode)) {
				$oldRecords   = $keyPressTimes->get($keyCode);
				$newRecords   = json_decode($oldRecords, TRUE);
				$newRecords []= $timestamp;
				$keyPressTimes->set($keyCode, json_encode($newRecords));
			} else {
				$keyPressTimes->set($keyCode, json_encode([$timestamp]));
			}

			$keyPressInTimeRange->add($keyCode . "_" . $timestamp, $timestamp);
		}
	}

	protected function record(RequestData $req) {

		$source  = $req->post('user');

		if (!$source) {
			throw new Exception("username can not be null");
		}

		$records = json_decode($req->post('records'), TRUE);

		$this->writeToRedis($source, $records);
	}

	protected function show(RequestData $req) {

		$user = $req->get('user');

		$redis = $this->getKeyPressInTimeRangeRedis($user);
		$keys  = $redis->rangeByScore(time() - 180, time());

		$count = count($keys);

		if ($count > 80) {
			$musicName = self::$musicList['fuck'][0];
		} elseif ($count > 60) {
			$musicName = self::$musicList['happ'][0];
		} else {
			$musicName = self::$musicList['easy'][0];
		}

		$metaData = file_get_contents('http://box.zhangmen.baidu.com/x?op=12&count=1&title=' . $musicName);

		$fileContents = str_replace(array("\n", "\r", "\t"), '', $metaData);

		$fileContents = trim(str_replace('"', "'", $fileContents));


		$simpleXml = simplexml_load_string($fileContents, 'SimpleXMLElement', LIBXML_NOCDATA);

		$urlComponents = explode('/', $simpleXml->url[0]->encode);

		array_pop($urlComponents);

		$newUrl    = implode('/', $urlComponents);
		$newUrl    = $newUrl . '/' .$simpleXml->url[0]->decode;
		$musicFile = file_get_contents($newUrl);

		return ResponseData::createDefault($req)->contentType('audio/mp3')->content($musicFile);
	}

}