<?php

namespace Practical\Tool;

/**
 * 
 *                    _ooOoo_
 *                   o8888888o
 *                   88" . "88
 *                   (| -_- |)
 *                    O\ = /O
 *                ____/`---'\____
 *              .   ' \\| |// `.
 *               / \\||| : |||// \
 *             / _||||| -:- |||||- \
 *               | | \\\ - /// | |
 *             | \_| ''\---/'' | |
 *              \ .-\__ `-` ___/-. /
 *           ___`. .' /--.--\ `. . __
 *        ."" '< `.___\_<|>_/___.' >'"".
 *       | | : `- \`.;`\ _ /`;.`/ - ` : | |
 *         \ \ `-. \_ __\ /__ _/ .-` / /
 * ======`-.____`-.___\_____/___.-`____.-'======
 *                    `=---='
 *
 * .............................................
 *          佛祖保佑             永无BUG
 */
class Kernel
{

    /**
     * 密码至少包含字母数字符号中的两种，6-12位
     * @param string $passwd
     * @return bool
     */
    public function checkPassword($passwd)
    {
        $str = $this->chars();
        if (preg_match("/(^(?=.*\d)(?=.*[A-Za-z])[\da-zA-Z{$str}]{6,12}$)|(^(?=.*\d)(?=.*[{$str}])[\da-zA-Z{$str}]{6,12}$)|(^(?=.*[A-Za-z])(?=.*[{$str}])[\da-zA-Z{$str}]{6,12}$)|(^(?=.*\d)(?=.*[A-Za-z])(?=.*[{$str}])[\da-zA-Z{$str}]{6,12}$)/", $passwd)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 特殊符号
     */
    public function chars()
    {
        return "~!@#$%^&*()_+`\-={}\[\]:\";'<>?,.\/";
    }

    /**
     * 过滤空格换行符
     * @param string $str
     * @return string
     */
    public function myTrim($str)
    {
        $search = array(" ", "　", "\n", "\r", "\t");
        $replace = array("", "", "", "", "");
        return str_replace($search, $replace, $str);
    }

    /**
     * 获取时间段
     * @param string $name
     * @return array
     */
    public function getTimestamp($name = 'today')
    {
        $time = time();
        switch ($name) {
            case 'today': //今天
                $timestamp['start'] = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $timestamp['end'] = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                break;
            case 'week': //本周
                $timestamp['start'] = strtotime(date('Y-m-d', strtotime("this week Monday", $time)));
                $timestamp['end'] = strtotime(date('Y-m-d', strtotime("this week Sunday", $time))) + 24 * 3600 - 1;
                break;
            case 'month': //本月
                $timestamp['start'] = mktime(0, 0, 0, date('m'), 1, date('Y'));
                $timestamp['end'] = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
                break;
            case 'yesterday': //昨天
                $yesterday = date('d') - 1;
                $timestamp['start'] = mktime(0, 0, 0, date('m'), $yesterday, date('Y'));
                $timestamp['end'] = mktime(23, 59, 59, date('m'), $yesterday, date('Y'));
                break;
            case 'permonth': //上月
                $timestamp['start'] = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
                $timestamp['end'] = mktime(23, 59, 59, date('m') - 1, date('t', $timestamp['start']), date('Y'));
                break;
            case 'preweek': //上周
                $timestamp['start'] = strtotime(date('Y-m-d', strtotime("last week Monday", $time)));
                $timestamp['end'] = strtotime(date('Y-m-d', strtotime("last week Sunday", $time))) + 24 * 3600 - 1;
                break;
            default:
                $timestamp['start'] = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $timestamp['end'] = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                break;
        }

        return $timestamp;
    }

    /**
     * 中文、字母、数字组合匹配
     * @param $chars
     * @param string $encoding
     * @return string
     */
    public function matchChinese($chars, $encoding = 'utf8')
    {
        $pattern = ($encoding == 'utf8') ? '/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u' : '/[\x80-\xFF]/';
        preg_match_all($pattern, $chars, $result);
        $temp = join('', $result[0]);
        return $temp;
    }

    /**
     * 手机号中间四位替换为*号
     * @param $str
     * @return null|string|string[]
     */
    public function switchPhone($str)
    {
        $pattern = '/(\d{3})(\d{4})(\d{4})/i';
        $replacement = '$1****$3';
        $resstr = preg_replace($pattern, $replacement, $str);
        return $resstr;
    }

    /**
     * 微信、邮箱、手机账号中间字符串以*隐藏
     */
    public function hideStr($str)
    {
        if (strpos($str, '@')) {
            $email_array = explode("@", $str);
            //邮箱前缀
            $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3);
            $count = 0;
            $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
            $rs = $prevfix . $str;
        } else {
            //正则手机号
            $pattern = '/(1[23456789]{1}[0-9])[0-9]{4}([0-9]{4})/i';
            if (preg_match($pattern, $str)) {
                $rs = preg_replace($pattern, '$1****$2', $str); // substr_replace($name,'****',3,4);
            } else {
                $rs = substr($str, 0, 3) . "***"; //. substr($str, -1);
            }
        }
        return $rs;
    }

    /**
     * @Description: 毫秒时间戳
     * @param {*}
     * @return {*}
     */
    public function msecTime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msecTime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msecTime;
    }

    /**
     * 导出功能
     * @param array $head = array('名称','ID') //表头
     * @param array $export_data = array(['name'=>'测试1', 'id'=>1], ['name'=>'测试2', 'id'=>2])
     */
    public function putCsv(array $head, $export_data, $fileName = "test.csv")
    {
        $fileName = mb_convert_encoding($fileName . '.csv', "GBK", "UTF-8");
        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        header("Content-Disposition:attachment;filename=$fileName");
        // 打开PHP文件句柄，php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'a');
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); //输出BOM头
        // 将中文标题转换编码，否则乱码
        foreach ($head as $i => $v) {
            $string = mb_detect_encoding($v, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
            $column_name[$i] = iconv($string, "GBK", $v);
        }
        // 将标题名称通过fputcsv写到文件句柄
        fputcsv($fp, $column_name);
        $cnd = 0;
        foreach ($export_data as $item) {
            $cnd++;
            if ($cnd == 10000) {
                ob_flush();
                flush();
                $cnd = 0;
            }
            $rows = array();
            foreach ($item as $value) {
                $out_string = mb_detect_encoding($value, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
                $rows[] = iconv($out_string, "GBK", $value);
            }
            fputcsv($fp, $rows);
        }
        // 将已经写到csv中的数据存储变量销毁，释放内存占用
        unset($export_data);
        ob_flush();
        ob_clean();
        flush();
        die();
    }

    /**
     * 下划线转驼峰
     * @param $uncamelizedWords
     * @param string $separator
     * @return string
     */
    public function camelize($uncamelizedWords, $separator = '_')
    {
        $uncamelizedWords = $separator . str_replace($separator, " ", strtolower($uncamelizedWords));
        return ltrim(str_replace(" ", "", ucwords($uncamelizedWords)), $separator);
    }

    /**
     * 驼峰转下划线
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    public function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * 列表转树结构
     * @param array $list
     * @param string $pk 主键
     * @param string $pid 
     * @param string $child
     * @param int $root
     */
    public function listToTree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = 0)
    {
        //创建Tree
        $tree = array();
        if (is_array($list)) {
            //创建基于主键的数组引用
            $refer = array();

            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }

            foreach ($list as $key => $data) {
                //判断是否存在parent
                $parantId = $data[$pid];

                if ($root == $parantId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parantId])) {
                        $parent = &$refer[$parantId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }

        return $tree;
    }

    /**
     * 生成随机字符串
     * @param int $length 生成随机字符串的长度
     * @param string $char 组成随机字符串的字符串
     * @return string $string 生成的随机字符串
     */
    public function strRand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        if(!is_int($length) || $length < 0) {
            return false;
        }

        $string = '';
        for($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
        }

        return $string;
    }

    /**
     * 生成32位唯一字符串
     * @return string
     */
    public function uniquelyIdentifies()
    {
        return md5(uniqid(microtime(true),true));
    }
}
