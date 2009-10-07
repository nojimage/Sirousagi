<?php
/**
 * JOINメッセージが発生した場合のメッセージ
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
 * 
 */
class SayHelloFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'SayHello';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $description = 'JOINメッセージが発生した場合にメッセージを返します。';

    /**
     * 機能をハンドリングするポイントタイプ
     * @var int
     */
    public $type = SMARTIRC_TYPE_JOIN;

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
    public $callRegex = '/^.+$/';

    /**
     * !$name形式でのコマンド実行を許可するか
     * @var boolean
     */
    public $allowCallCommand = false;

    /**
     * 名前マッチなしでも呼び出し可能か
     * @var boolean
     */
    public $allowNoCall = true;

    /**
     * 設定配列
     * @var array
     */
    protected $config;

    /**
     *
     * @param $irc Net_SmartIRC_Ja
     * @param $data Net_SmartIRC_data
     */
    public function run(&$irc, &$data = null)
    {

        $channels = array();
        if (is_object($data)) {
            // call from command
            $nick = '';
            if ($data->nick != $irc->_nick) {
                $nick = sptintf('%sさん、', $data->nick); // TODO: i18n
            }
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $nick . 'こんにちはー'); // TODO: i18n

        } else {
            // call from timer
        }

    }

}
