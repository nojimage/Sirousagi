<?php
/**
 * Bot Feature Base class
 *
 * PHP versions 5
 *
 * Copyright 2009, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @version    Sirousagi 0.4
 * @author     nojimage <nojimage at gmail.com>
 * @copyright  2009 nojimage
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://php-tips.com/
 * @package    sirousagi
 * @subpackage sirousagi.core
 * @since      File available since Release 0.1
 * @modifiedby nojimage <nojimage at gmail.com>
 */
class FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'example';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $description = 'example feature.';

    /**
     * この機能を有効化するか
     * @var bool
     */
    public $active = true;

    /**
     * 機能をハンドリングするポイントタイプ
     * @var int
     */
    public $type = SMARTIRC_TYPE_CHANNEL;

    /**
     * 実行間隔(s)
     * 0以上の場合はtimerHandlerに登録される
     * @var int
     */
    public $timer = 0;

    /**
     * 呼び出すためのメッセージマッチ
     * @var stirng
     */
    public $callRegex = '/^$/';

    /**
     * !$name形式でのコマンド実行を許可するか
     * @var boolean
     */
    public $allowCallCommand = true;

    /**
     * 名前マッチなしでも呼び出し可能か
     * @var boolean
     */
    public $allowNoCall = false;

    /**
     * 設定配列
     * @var array
     */
    protected $config;

    /**
     *
     * @param $config
     */
    function __construct($config = null)
    {
        $this->config = $config;

        if (!empty($config[$this->name])) {
            // クラス変数を上書き
            foreach ( get_object_vars($this) as $var => $value) {
                if (array_key_exists($var, $config[$this->name])) {
                    $this->$var = $config[$this->name][$var];
                }
            }
        }

    }

    /**
     *
     * @param $irc Net_SmartIRC_Ja
     * @param $data Net_SmartIRC_data
     */
    public function run(&$irc, &$data = null)
    {

    }

    /**
     * 管理者権限のチェック
     *
     * @param $irc Net_SmartIRC_Ja
     * @param $data Net_SmartIRC_data
     * @return boolean
     */
    function checkAdmin(&$irc, &$data)
    {
        if (preg_match($this->config['admin'], $data->from)) {
            return true;
        } else {
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, sprintf('%sさんは管理者じゃないよー', $data->nick)); // TODO: i18n 
            return false;
        }
    }

    /**
     * バージョン情報の取得
     *
     * @return string
     */
    public function getVersion($classname = null)
    {
        $ref = new ReflectionClass(get_class($this));
        $version = 'unknown';
        if (preg_match('/@version[\s]+(.+)[\s]*(?:\n|$)/', $ref->getDocComment(), $matches)) {
            $version = $matches[1];
        }
        return $version;
    }

}
