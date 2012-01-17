<?php
class tCloud {
	var $count;
	var $user;
	var $data;
	var $tags = array();

	function __construct($tags = false, $count) {
		$this->count = $count;
		if ($tags !== false && is_array($tags)) {
			foreach ($tags as $key => $value) {
				$this->addTag($value);
			}
		}
	}

	function tCloud($tags = false, $count) {
		$this->__construct($tags);
	}

	function getTweets($user) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,"http://api.twitter.com/1/statuses/user_timeline/".$user.".xml?skip_user=true&count=".$this->count);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$this->data = curl_exec($ch);
		curl_close($ch);
	}

	function getWords() {
		$this->getTweets($this->user);
		$xml = simplexml_load_string($this->data);
		$words = "";
		foreach ($xml->status as $status) {
			$text = preg_replace('/(@|#)[a-zA-Z0-9_]+/', '', $status->text); // #tags/@tags
			$text = preg_replace('/(https?|ftp|file):\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/','',$text);
			$text = preg_replace('/[^\s0-9A-Za-zА-ЯЁёсхуячертю]+/','',$text);
			$words .= $text." ";
		}
		$tags = explode(" ", $words);
		foreach ($tags as $tag) {
			if ($tag != '') {
				$this->addTag($tag);
			}
		}
	}

	function addTag($tag, $value = 1) {
		$tag = mb_convert_case($tag, MB_CASE_LOWER, "UTF-8");
		if (array_key_exists($tag, $this->tags)) {
			$this->tags[$tag] += $value;
		} else {
			$this->tags[$tag] = $value;
		}
		return $this->tags[$tag];
	}

	function getCloudSize() {
		return array_sum($this->tags);
	}

	function getClassFromPercent($percent) {
		switch ($percent) {
			case ($percent >= 99):
				$class = 9;
			break;
			case ($percent >= 70):
				$class = 8;
			break;
			case ($percent >= 60):
				$class = 7;
			break;
			case ($percent >= 50):
				$class = 6;
			break;
			case ($percent >= 40):
				$class = 5;
			break;
			case ($percent >= 30):
				$class = 4;
			break;
			case ($percent >= 20):
				$class = 3;
			break;
			case ($percent >= 10):
				$class = 2;
			break;
			case ($percent >= 5):
				$class = 1;
			break;
			case ($percent < 5):
				$class = 0;
			break;
		}
		return $class;
	}

	function shuffleCloud() {
		$keys = array_keys($this->tags);
		shuffle($keys);
		if (is_array($keys) && count($keys)) {
			$tmpArray = $this->tags;
			$this->tags = array();
			foreach ($keys as $key => $value) {
				$this->tags[$value] = $tmpArray[$value];
			}
		}
	}

	function showCloud($user) {
		$this->user = $user;
		$this->getWords();
		$this->shuffleCloud();
		$this->fullCloudSize = $this->getCloudSize();
		$this->max = max($this->tags);
		$return = false;
		if (is_array($this->tags)) {
			$return = "<ul>\n";
			foreach ($this->tags as $tag => $popularity) {
				$sizeRange = $this->getClassFromPercent(($popularity / $this->max) * 100);
				$return .= "<li><a href=\"http://search.twitter.com/search?q={$tag}\" class=\"w{$sizeRange}\" title=\"{$popularity}\">{$tag}</a></li>\n";
			}
			$return .= "</ul>";
		}
		return $return;
	}
}
?>