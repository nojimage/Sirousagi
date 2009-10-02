<?php
/**
 * デバッグモードの切り替え
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
class DebugFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'debug';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $discliption = 'デバッグモードのON/OFFを切り替えます。';

    /**
     * 機能をハンドリングするポイントタイプ
     * @var unknown_type
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
                if (preg_match('/on|開始/', $data->message)) { // TODO: i18n
                    $irc->setDebug(SMARTIRC_DEBUG_ALL);
                    $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, 'でばっぐもーどかいしー'); // TODO: i18n
                } else {
                    $irc->setDebug(SMARTIRC_DEBUG_NONE);
                    $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, 'でばっぐもーどしゅうりょー'); // TODO: i18n
                }
            }
        } else if (false) {
            // call from timer
        }

    }

}
?>