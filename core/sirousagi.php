<?php
/**
 * IRC BOT sirousagi
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
 * TODO: JOIN,PART,QUITもロギングする
 * TODO: ログのメール送信機能を作る
 * TODO: 伝言機能
 * TODO: 自動応答メッセージ
 * TODO: RSSリーダー
 * TODO: i18n
 *
 */
include_once('lib.php');

/**
 *
 * @var string
 */
define('SIROUSAGI_VERSION', '0.1');

/**
 * IRC BOT Sirousagi
 */
class BotSirousagi
{

    /**
     * 設定
     * @var unknown_type
     */
    public $config = array();

    /**
     * 設定
     * @var unknown_type
     */
    public $irc_config = array();

    /**
     * 機能オブジェクトを格納するための配列
     * @var array
     */
    private $features = array();

    /**
     * $ircオブジェクト
     * @var Net_SmartIRC_ja
     */
    public $irc;

    /**
     * コンストラクタ
     * @param $irc_config string
     * @return unknown_type
     */
    function __construct($irc_config = 'default') {

        // 機能読み込み
        $this->config = BOT_CONFIG::$bot;
        $this->irc_config = BOT_CONFIG::$irc[$irc_config];

        // IRC初期化
        $this->__initIRC();

        // 機能をロードする
        $this->__loadFeatures();

        // 各TYPEごとのActionHandlerをセット
        $this->__hookActions();
    }

    /**
     *
     *
     * @param $irc_config
     * @return BotSirousagi
     */
    static function create($irc_config = 'default')
    {
        return new BotSirousagi($irc_config);
    }

    /**
     * IRC オブジェクト初期化
     *
     * @return unknown_type
     */
    private function __initIRC()
    {
        $this->irc = new Net_SmartIRC_Ja();

        $this->irc->setDebug(SMARTIRC_DEBUG_NONE);
        # $this->irc->setDebug(SMARTIRC_DEBUG_ALL);
        $this->irc->setLogdestination(SMARTIRC_FILE);
        $this->irc->setLogfile($this->config['logdir'] . 'debug-' . date('Ymd') . '.log');

        $this->irc->setUseSockets(true);
        #$irc->setAutoReconnect(true);
        #$irc->setAutoRetry(true);
        $this->irc->setChannelSyncing(true);
        $this->irc->setIrcEncoding($this->irc_config['encoding']);

    }

    /**
     *
     * @return BotSirousagi
     */
    public function start()
    {
        debug('Start Connect');
        $this->connectIRC();
        return $this;
    }

    /**
     *
     * @return BotSirousagi
     */
    public function connectIRC($server = null, $port = null)
    {
        debug('Connect to: ' . $this->irc_config['server']);
        $this->irc->connect($this->irc_config['server'], $this->irc_config['port']);
        $this->irc->login($this->config['name'], 'Sirousagi - IRC bot. ver' . SIROUSAGI_VERSION );
        $this->irc->listen();
        return $this;
    }

    /**
     *
     * @param $channel
     * @return BotSirousagi
     */
    public function joinChannel($channel = null)
    {
        $channels = func_get_args();
        if (is_array($channel)) {
            $channels = $channel;
        }
        if (empty($channels)) {
            $channels = $this->irc_config['channels'];
        }

        debug($channels);
        $this->irc->join($channels);
        return $this;
    }

    /**
     * 拡張機能読み込み
     *
     * @return unknown_type
     */
    private function __loadFeatures()
    {

        // feature dirsearch
        $features = array();
        $core_feature_dir = dirname(__FILE__) . DS . 'features';
        $feature_dir = dirname(dirname(__FILE__)) . DS . 'features';

        /**
         * @var $dir Directory
         */
        $dir = dir($core_feature_dir);
        while (false !== ($entry = $dir->read())) {
            if (is_file($core_feature_dir . DS . $entry) && preg_match('/^(.+)_feature\.php$/', $entry, $matches)) {
                include_once($core_feature_dir . DS . $entry);
                $features[$matches[1]] = $entry;
            }
        }
        $dir->close();

        $dir = dir($feature_dir);
        while (false !== ($entry = $dir->read())) {
            if (is_file($feature_dir . DS . $entry) && preg_match('/^(.+)_feature\.php$/', $entry, $matches)) {
                include_once($feature_dir . DS . $entry);
                $features[$matches[1]] = $entry;
            }
        }
        $dir->close();

        // -- load features
        foreach ($features as $name => $file) {
            $className = $name . 'Feature';
            if (class_exists($className)) {
                $this->features[$name] = new $className($this->config);
                // if Feature::timer > 0 hook timer action
                if ($this->features[$name]->timer > 0) {
                    debug('Hook Timer: ' . $name);
                    $this->irc->registerTimehandler($this->features[$name]->timer * 1000, $this->features[$name], 'run');
                }
            }
        }

        debug($features);
    }

    /**
     * 各呼び出しタイプに応じた機能呼び出しメソッドをHOOKする
     *
     * @param $irc Net_SmartIRC_Ja
     * @return unknown_type
     */
    private function __hookActions()
    {

        $this->irc->registerActionHandler(SMARTIRC_TYPE_LOGIN, '^.+$', $this, 'callLoginFeature');
        $this->irc->registerActionHandler(SMARTIRC_TYPE_JOIN, '^.+$', $this, 'callJoinFeature');
        $this->irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^.+$', $this, 'callCannelFeature');
        $this->irc->registerActionHandler(SMARTIRC_TYPE_ACTION, '^.+$', $this, 'callActionFeature');
        $this->irc->registerActionHandler(SMARTIRC_TYPE_QUIT, '^.+$', $this, 'callQuitFeature');
        $this->irc->registerActionHandler(SMARTIRC_TYPE_ERROR, '^.+$', $this, 'callErrorFeature');
        $this->irc->registerActionHandler(SMARTIRC_TYPE_NOTICE, '^.+$', $this, 'callNoticeFeature');

    }


    /**
     * Featureを呼び出し実行
     *
     * @param $data Net_SmartIRC_data
     * @param $type
     * @return unknown_type
     */
    private function __callFeature(&$data, $type)
    {

        $feature = new FeatureBase();

        foreach ($this->features as $name => $feature) {
            if ($feature->type & $type) {
                // TODO: 呼び出し条件の見直し
                if ($feature->allowNoCall && preg_match($feature->callRegex, $data->message)) {
                    // マッチする
                    debug('called 1: ' . $name);
                    $feature->run($this->irc, $data);

                } else if (preg_match($this->config['callnames'], $data->message) && preg_match($feature->callRegex, $data->message)) {
                    // マッチする
                    debug('called 2: ' . $name);
                    $feature->run($this->irc, $data);

                } else if ($feature->allowCallCommand && preg_match($this->config['callnames'], $data->message) && preg_match('/!' . $feature->name . '( |$)/i', $data->message)) {
                    debug('called 3: ' . $name);
                    $feature->run($this->irc, $data);
                }
            }
        }
    }

    /**
     * ログイン時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callLoginFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_LOGIN);
    }

    /**
     * JOIN時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callJoinFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_JOIN);
    }

    /**
     * 通常チャット時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callCannelFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_CHANNEL);
    }

    /**
     * ACTION時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callActionFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_ACTION);
    }

    /**
     * QUIT時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callQuitFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_QUIT);
    }

    /**
     * 全ての動作におけるFeature呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callAllFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_ALL);
    }

    /**
     * PART時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callPartFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_PART);
    }

    /**
     * ERROR時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callErrorFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_ERROR);
    }


    /**
     * NOTICE時のFeatureを呼び出し
     *
     * @param $irc Net_SmartIRC_ja
     * @param $data Net_SmartIRC_data
     * @return unknown_type
     */
    public function callNoticeFeature(&$irc, &$data)
    {
        $this->__callFeature($data, SMARTIRC_TYPE_NOTICE);
    }

}
