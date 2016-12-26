<?php
/**
 * 获取当前毫秒级时间戳
 * @return string
 */
function millisecond()
{
	$time = explode(' ', microtime());
	return $time[1] . str_pad(intval($time[0] * 1000), 3, '0', STR_PAD_LEFT);
}

/**
 * 获取当前微秒级时间戳
 * @return string
 */
function microsecond()
{
	$time = explode(' ', microtime());
	return $time[1] . str_pad(intval($time[0] * 1000000), 6, '0', STR_PAD_LEFT);
}

/**
 * 获取当前日期时间
 * @param bool $dayOnly
 * @return string
 * @internal param bool $day_only
 */
function now($dayOnly = false)
{
	$dayOnly = (bool) $dayOnly;
	if ($dayOnly) {
		return date('Y-m-d');
	} else {
		return date('Y-m-d H:i:s');
	}
}

/**
 * 生成随机字符串
 * @param int $length 要生成的随机字符串长度
 * @param int|string $type 随机码类型:
 * 0.数字+大小写字母、1.数字、2.小写字母、3.大写字母、4.特殊字符、-1.数字+大小写字母+特殊字符
 * @return string
 */
function rand_code($length = 5, $type = 0)
{
	$arr = array(
		1 => '0123456789',
		2 => 'abcdefghijklmnopqrstuvwxyz',
		3 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		4 => '~@#$%^&*(){}[]|'
	);
	if ($type == 0) {
		array_pop($arr);
		$string = implode('', $arr);
	} elseif ($type == '-1') {
		$string = implode('', $arr);
	} else {
		$string = $arr[$type];
	}
	$count = strlen($string) - 1;
	$code = '';
	for ($i = 0; $i < $length; $i++) {
		$code .= $string[mt_rand(0, $count)];
	}
	return $code;
}

/**
 * 加密
 * @param string $plain 明文
 * @return string
 */
function encrypt_password($plain)
{
	$password = '';
	for ($i=0; $i<10; $i++) {
		$password .= mt_rand();
	}
	$salt = substr(md5($password), 0, 2);
	$password = hash_hmac('sha256', $plain, $salt) . ':' . $salt;

	return $password;
}

/**
 * 验证密码
 * @param string $plain     明文
 * @param string $encrypted 密文
 * @return boolean
 */
function v_password($plain, $encrypted)
{
	if (!empty($plain) && !empty($encrypted)) {
		$stack = explode(':', $encrypted);
		if (sizeof($stack) != 2) return false;
		if (hash_hmac('sha256', $plain, $stack[1]) == $stack[0]) {
			return true;
		}
	}

	return false;
}

/**
 * 年月日格式验证
 * @param string $value
 * @return boolean
 */
function v_date($value)
{
	$pattern = '/^(((1[6-9]|[2-9]\d)(\d{2})-((0?[13578])|(1[02]))-((0?[1-9])|([12]\d)|(3[01])))|((1[6-9]|[2-9]\d)(\d{2})-((0?[469])|11)-((0?[1-9])|([12]\d)|30))|((1[6-9]|[2-9]\d)(\d{2})-0?2-((0?[1-9])|(1\d)|(2[0-8])))|((1[6-9]|[2-9]\d)([13579][26])-0?2-29)|((1[6-9]|[2-9]\d)([2468][048])-0?2-29)|((1[6-9]|[2-9]\d)(0[48])-0?2-29)|([13579]600-0?2-29)|([2468][048]00-0?2-29)|([3579]200-0?2-29))$/';
	if (preg_match($pattern, $value)) {
		return true;
	}
	return false;
}

/**
 * 年月日时间格式验证
 * @param string $value
 * @return boolean
 */
function v_datetime($value)
{
	$pattern = "/^(((1[6-9]|[2-9]\d)(\d{2})-((0?[13578])|(1[02]))-((0?[1-9])|([12]\d)|(3[01])))|((1[6-9]|[2-9]\d)(\d{2})-((0?[469])|11)-((0?[1-9])|([12]\d)|30))|((1[6-9]|[2-9]\d)(\d{2})-0?2-((0?[1-9])|(1\d)|(2[0-8])))|((1[6-9]|[2-9]\d)([13579][26])-0?2-29)|((1[6-9]|[2-9]\d)([2468][048])-0?2-29)|((1[6-9]|[2-9]\d)(0[48])-0?2-29)|([13579]600-0?2-29)|([2468][048]00-0?2-29)|([3579]200-0?2-29)) (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d$/";
	if (preg_match($pattern, $value)) {
		return true;
	}

	return false;
}

/**
 * 邮箱格式验证
 * @param string $value
 * @return boolean
 */
function v_email($value)
{
	if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
}

/**
 * 身份证号码验证
 * @param string $value
 * @return boolean
 */
function v_idcard($value)
{
	if (strlen($value) != 18) return false;
	$ac = array(
		11 => '北京', 12 => '天津', 13 => '河北', 14 => '山西', 15 => '内蒙古',
		21 => '辽宁', 22 => '吉林', 23 => '黑龙江',
		31 => '上海', 32 => '江苏', 33 => '浙江', 34 => '安徽', 35 => '福建', 36 => '江西', 37 => '山东',
		41 => '河南', 42 => '湖北', 43 => '湖南', 44 => '广东', 45 => '广西', 46 => '海南',
		50 => '重庆', 51 => '四川', 52 => '贵州', 53 => '云南', 54 => '西藏',
		61 => '陕西', 62 => '甘肃', 63 => '青海', 64 => '宁夏', 65 => '新疆',
		71 => '台湾', 81 => '香港', 82 => '澳门',
		91 => '国外'
	);
	if (!array_key_exists(substr($value, 0, 2), $ac)) {
		return false;
	}
	$year  = substr($value, 6, 4);
	$month = substr($value, 10, 2);
	$day   = substr($value, 12, 2);
	if (!checkdate($month, $day, $year)) {
		return false;
	}
	$wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	$ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
	$sigma = 0;
	for ($i = 0; $i < 17; $i++) {
		$sigma += ((int) $value{$i}) * $wi[$i];
	}
	if ($value{17} != $ai[$sigma % 11]) {
		return false;
	}
	return true;
}

/**
 * IP验证
 * @param string $value
 * @return boolean
 */
function v_ip($value)
{
	if (filter_var($value, FILTER_VALIDATE_IP)) {
		return true;
	}
	return false;
}

/**
 * 手机号码验证
 *
 * @param string $string
 * @return boolean
 */
function v_mobile($string = null)
{
	if (!is_numeric($string) || strlen($string) != '11') {
		return false;
	}
	if (preg_match('/^1[34578]\d{9}$/',$string)){
		return true;
	} else {
		return false;
	}
}

/**
 * 域名验证
 * @param string $domain
 * @return boolean
 */
function v_domain($domain) {
	if (!preg_match("/^[0-9a-z-]+[0-9a-z\.-]+[0-9a-z]+$/i", $domain)) {
		return false;
	}
	if (!preg_match("/\./i", $domain)) {
		return false;
	}
	if (preg_match("/\-\./i", $domain) || preg_match("/\-\-/i", $domain)
		|| preg_match("/\.\./i", $domain) || preg_match("/\.\-/i", $domain)) {
		return false;
	}
	$arr = explode('.', $domain);
	if (!preg_match("/[a-zA-Z]/i", $arr[count($arr) - 1])) {
		return false;
	}
	if (strlen($arr[0]) > 63 || strlen($arr[0]) < 1) {
		return false;
	}
	return true;
}

/**
 * 信用卡验证
 * @param string $value
 * @return boolean
 */
function v_creditcard($value)
{
	if (preg_match('/[^0-9 \-]+/', $value)
		|| strlen($value) != 16) {
		return false;
	}
	$nCheck = 0;
	$nDigit = 0;
	$bEven  = false;
	$value  = preg_replace('/\D/', '', $value);
	for ($n = strlen($value) - 1; $n >= 0; $n--) {
		$cDigit = $value[$n];
		$nDigit = intval($cDigit);
		if ($bEven) {
			if (($nDigit *= 2) > 9) {
				$nDigit -= 9;
			}
		}
		$nCheck += $nDigit;
		$bEven = !$bEven;
	}
	return ($nCheck % 10) === 0;
}

/**
 * 解析含uinicode汉字编码的json数据
 * @param string $str
 * @return string
 */
function decode_json($str) {
	return urldecode(json_encode(url_encode(json_decode($str, true))));
}

/**
 * 将数据转为urlcode
 * @param array|string $str
 * @return string
 */
function url_encode($str) {
	if (count($str) < 1) {
		return '';
	}
	if(is_array($str)) {
		foreach($str as $key=>$value) {
			$data[urlencode($key)] = url_encode($value);
		}
	} else {
		$data = urlencode($str);
	}

	return $data;
}

/**
 * Describe     递归去除数组内的前后空格
 * User         陈伟义
 * DateAdded    2016-10-11
 * DateModified
 *
 * @param $input
 * @return array|string
 */
function trim_array($input) {
	if (!is_array($input)) {
		return trim($input);
	}
	return array_map('trim_array', $input);
}
