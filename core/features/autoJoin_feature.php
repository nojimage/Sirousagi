<?php
/**
 * サーバへのConnect時に設定されたチャンネルへ自動的にログインします
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
 */
class AutoJoinFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'AutoJoin';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $discliption = 'サーバへのConnect時に設定されたチャンネルへ自動的にログインします。';

    /**
     * 機能をハンドリングするポイントタイプ
     * @var unknown_type
     */
    public $type = SMARTIRC_TYPE_LOGIN;

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
     * 設定配列
     * @var unknown_type
     */
    protected $config;

    /**
     *
     * @param $irc Net_SmartIRC_Ja
     * @param $data Net_SmartIRC_data
     */
    public function run(&$irc, &$data = null) {

        $channels = array();
        if (is_object($data)) {
            // call from command
            if (!empty($this->config['autoJoin']['channels'])) {
                $irc->join($this->config['autoJoin']['channels']);
                $irc->mode($this->config['autoJoin']['channels'], '+snt');
            }
            
            debug('auto join channel: ' . join(', ', $this->config['autoJoin']['channels']));

        } else {
            // call from timer
        }

    }

}
