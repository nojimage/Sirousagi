<?php
/**
 * ロギング
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
class LogFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'Log';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $discliption = 'チャンネルのログを保存します。';

    /**
     * 機能をハンドリングするポイントタイプ
     * @var int
     */
    public $type = SMARTIRC_TYPE_ALL;

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
     * ログ用ファイルポインタ
     * @var resource
     */
    private $_logfp = null;

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

            // ログファイル名の設定
            $filename = $this->config['logdir'] . self::makeLogFileName($irc->getAddress(), $data->channel);

            // ファイルポインタが無ければ取得
            if ( empty($this->_logfp[$data->channel]) || !is_resource($this->_logfp[$data->channel]) ) {
                $this->_logfp[$data->channel] = @fopen($filename, 'a');
            }

            // ログ整形
            switch ($data->type) {
                case SMARTIRC_TYPE_CHANNEL:
                    $string = '<' . $data->channel . '> ' . date('[H:i:s]') . ' ' . $data->nick . ': ' . $data->message . "\n";
                    break;

                default:
                    $string = '<' . $data->channel . '> ' . date('[H:i:s]') . ' ' . $data->rawmessage . "\n";
                    break;
            }

            // ファイルへ書き込み
            if ( is_resource($this->_logfp[$data->channel]) ) {
                @fwrite($this->_logfp[$data->channel], $string);
                fflush($this->_logfp[$data->channel]);
            }


        } else if (date('i') == '00') {
            // call from timer
            $this->_logfp = array();
        }

    }

    /**
     * ログファイル名を取得
     * @param $server  string
     * @param $channel string
     * @param $time　int
     * @return string
     */
    static function makeLogFileName($server = '', $channel = '__sirousagi', $time = null)
    {

        if (empty($server)) {
            $server = 'unknown';
        }
        
        if (empty($channel)) {
            $channel = '__sirousagi';
        }

        if (empty($time)) {
            $time = time();
        }
        return $server . '-' . preg_replace('/[#&%]/', '', $channel) . '-' . date('Ymd', $time) . '.log';
    }

}
