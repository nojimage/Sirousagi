<?php
/**
 * 現在の時刻をお知らせ
 * 
 * PHP versions 5
 * 
 * Copyright 2009, nojimage (http://php-tips.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @version    0.3
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
class SaytimeFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'SayTime';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $discliption = '現在の時刻をお知らせします。';

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
    public $timer = 60;

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
            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, sprintf('%sだよー' , date("Y年n月j日のH時i分"))); // TODO: i18n
        } else if (date('i') == '00') {
            // call from timer
            foreach ($irc->channel as $channel => $val)
            {
                $irc->message(SMARTIRC_TYPE_NOTICE, $channel, sprintf('%sだよー' , date("Y年n月j日のH時i分"))); // TODO: i18n
                debug('saytime: ' . date('H:i:s'));
            }
        }

    }

}