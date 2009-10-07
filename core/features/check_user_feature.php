<?php
/**
 * JOIN中のチャンネルを巡回しユーザを確認する
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
class CheckUserFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'CheckUser';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $description = 'チャンネルの状態をチェックし必要があれば再JOINします。';

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
    public $allowCallCommand = false;

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
            $channels = $data->channel;

        } else if (date('i') == '00') {
            // call from timer
            $channels = array_keys($irc->channel);
        }
        
        // 状態を更新
        $irc->names($channels);
        foreach ($irc->channel as $channel => $cdata)
        {
            if ( count($cdata->users) == 1 && count($cdata->ops) == 0) {
                $_old_mode = $cdata->mode;
                $irc->part($channel, 'restart'); // TODO: i18n
                $irc->join($channel);
                $irc->mode($channel, $_old_mode);
                debug('rejoin');
            }
        }

    }

}