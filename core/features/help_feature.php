<?php
/**
 * ヘルプ情報の表示
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
 * @since      File available since Release 0.3.1
 * @modifiedby nojimage <nojimage at gmail.com>
 *
 */
class HelpFeature extends FeatureBase
{

    /**
     * 機能名
     * @var string
     */
    public $name = 'Help';

    /**
     * 機能の説明
     * helpコマンドで呼び出した場合に表示される
     * @var string
     */
    public $description = 'ヘルプ情報を表示します。';

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
            // -- actionHandlerからBotオブジェクト取得
            $actionHandlers = $irc->getActionHandler();
            /**
             * @var Net_SmartIRC_actionhandler
             */
            $actionHandler = $actionHandlers[0];
            /**
             *
             * @var BotSirousagi
             */
            $bot = $actionHandler->object;
            $features = $bot->getFeatures();

            $command = null;
            $message = array();
            if (preg_match('/!help\s+(\S+)/i', $data->message, $matches)) {
                
                if (!empty($features[$matches[1]])) {
                    $f = $features[$matches[1]];
                    $message[] = $f->name . ': ' . $f->description;
                    if ($f->allowCallCommand > 0) {
                        $message[] = sprintf('!%s 形式での実行が可能です。', strtolower($f->name)); // TODO: i18n
                    }
                    if ($f->timer > 0) {
                        $message[] = sprintf('%d秒ごとに実行されます。', $f->timer); // TODO: i18n
                    }
                }

            } else {

                $message[] = '  Loaded features:';
                foreach ($features as $name => $feature) {
                    $message[] = '    '  . $name . ': ' . $feature->description;
                }
            }

            $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $message);

        } else if (false) {
            // call from timer
        }

    }

}
