<?php
/**
 * ログをメールで送信
 * FIXME: is feature not work
 * 
 * PHP versions 5
 * 
 * Copyright 2009, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @version    0.1
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
require_once('log_feature.php');

class LogMailFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'logMail';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $discliption = 'あらかじめ設定されたメールアドレスへログを送信します';

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
    public $timer = 3600;

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

        if (is_object($data)) {
            // call from command
            if ($this->checkAdmin($irc, $data)) {
                $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, 'メールを送ったよー'); // FIXME: i18n
            }
        } else if (date('H') == '00') {
            // call from timer
            foreach ($irc->channel as $channel => $val) {
                $irc->message(SMARTIRC_TYPE_NOTICE, $channel, 'メールを送ったよー'); // FIXME: i18n
            }
        }

    }

    /**
     * メールを送信
     * @param $data Net_SmartIRC_data
     * @return boolean
     */
    function _sendLogMail($data) {
        // -- 取得するログを決定
        $filename = $this->config['logdir'] . LogFeature::makeLogFileName($data->channel, strtotime('-1 day'));
        // -- TODO: PEAR::Mailでatachment, send
    }
}
