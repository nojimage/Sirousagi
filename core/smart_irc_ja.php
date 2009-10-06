<?php
/**
 * 文字コード変換機能付きSmartIRC
 *
 * PHP versions 5
 *
 * @version    0.3
 * @author     nojimage <nojimage at gmail.com>
 * @link       http://php-tips.com/
 * @package    sirousagi
 * @subpackage sirousagi.core
 * @since      File available since Release 0.1
 * @modifiedby nojimage <nojimage at gmail.com>
 */
require_once('Net' . DS . 'SmartIRC.php');

class Net_SmartIRC_Ja extends Net_SmartIRC
{

    /**
     * IRCサーバ側のエンコーディング
     * @var string
     * @access private
     */
    private $_irc_encoding = 'UTF-8';

    /**
     * システム側のエンコーディング
     * @var string
     * @access private
     */
    private $_system_encoding = 'UTF-8';

    /**
     * IRCサーバ側のエンコーディング取得
     *
     * @return string
     */
    function getIrcEncoding()
    {
        return $this->_irc_encoding;
    }

    /**
     * IRC側エンコーディングの設定
     *
     * @param $encoding
     * @return unknown_type
     */
    function setIrcEncoding($encoding = null)
    {

        if (!empty($encoding)) {
            $encoding = strtolower($encoding);
        }

        if (preg_match('/sjis/', $encoding)) {
            $this->_irc_encoding = 'sjis-win';

        } else if (preg_match('/euc|ujis/', $encoding)) {
            $this->_irc_encoding = 'eucjp-win';

        } else if (preg_match('/jis|iso-2022-jp/', $encoding)) {
            $this->_irc_encoding = 'iso-2022-jp';

        } else {
            $this->_irc_encoding = 'utf-8';

        }

        return $this->getIrcEncoding();

    }

    
    /**
     * 
     * @return string
     */
    function getAddress()
    {
        return $this->_address;
    }
    
    /**
     * (non-PHPdoc)
     * @see extlibs/Pear/Net/Net_SmartIRC_base#_rawsend($data)
     */
    function _rawsend($data)
    {

        // check encoding
        if (!mb_check_encoding($data, $this->getIrcEncoding())) {
            $this->log(SMARTIRC_DEBUG_NOTICE, 'WARNIG: Input charactor has worong encoding.', __FILE__, __LINE__);
            return parent::_rawsend('');
        }

        $data = mb_convert_encoding($data, $this->getIrcEncoding(), 'UTF-8');
        return parent::_rawsend($data);
    }

    /**
     * (non-PHPdoc)
     * @see extlibs/Pear/Net/Net_SmartIRC_base#_rawreceive()
     */
    function _rawreceive()
    {
        $lastpart = '';
        $rawdataar = array();

        while ($this->_state() == SMARTIRC_STATE_CONNECTED)
        {
            $this->_checkbuffer();

            $timeout = $this->_selecttimeout();
            if ($this->_usesockets == true) {
                $sread = array($this->_socket);
                $result = @socket_select($sread, $w = null, $e = null, 0, $timeout*1000);

                if ($result == 1) {
                    // the socket got data to read
                    $rawdata = @socket_read($this->_socket, 10240);
                } else if ($result === false) {
                    // panic! panic! something went wrong!
                    $this->log(SMARTIRC_DEBUG_NOTICE, 'WARNING: socket_select() returned false, something went wrong! Reason: '.socket_strerror(socket_last_error()), __FILE__, __LINE__);
                    exit;
                } else {
                    // no data
                    $rawdata = null;
                }
            } else {
                usleep($this->_receivedelay*1000);
                $rawdata = @fread($this->_socket, 10240);
            }

            $this->_checktimer();
            $this->_checktimeout();

            if ($rawdata !== null && !empty($rawdata)) {
                # change
                $rawdata = mb_convert_encoding($rawdata, 'UTF-8', $this->getIrcEncoding());
                $this->_lastrx = time();
                $rawdata = str_replace("\r", '', $rawdata);
                $rawdata = $lastpart.$rawdata;

                $lastpart = substr($rawdata, strrpos($rawdata ,"\n")+1);
                $rawdata = substr($rawdata, 0, strrpos($rawdata ,"\n"));

                $rawdataar = explode("\n", $rawdata);
            }

            // loop through our received messages
            while (count($rawdataar) > 0)
            {
                $rawline = array_shift($rawdataar);
                $validmessage = false;

                $this->log(SMARTIRC_DEBUG_IRCMESSAGES, 'DEBUG_IRCMESSAGES: received: "'.$rawline.'"', __FILE__, __LINE__);

                // building our data packet
                $ircdata = &new Net_SmartIRC_data();
                $ircdata->rawmessage = $rawline;
                $lineex = explode(' ', $rawline);
                $ircdata->rawmessageex = $lineex;
                $messagecode = $lineex[0];

                if (substr($rawline, 0, 1) == ':') {
                    $validmessage = true;
                    $line = substr($rawline, 1);
                    $lineex = explode(' ', $line);

                    // conform to RFC 2812
                    $from = $lineex[0];
                    $messagecode = $lineex[1];
                    $exclamationpos = strpos($from, '!');
                    $atpos = strpos($from, '@');
                    $colonpos = strpos($line, ' :');
                    if ($colonpos !== false) {
                        // we want the exact position of ":" not beginning from the space
                        $colonpos += 1;
                    }
                    $ircdata->nick = substr($from, 0, $exclamationpos);
                    $ircdata->ident = substr($from, $exclamationpos+1, ($atpos-$exclamationpos)-1);
                    $ircdata->host = substr($from, $atpos+1);
                    $ircdata->type = $this->_gettype($rawline);
                    $ircdata->from = $from;
                    if ($colonpos !== false) {
                        $ircdata->message = substr($line, $colonpos+1);
                        $ircdata->messageex = explode(' ', $ircdata->message);
                    }

                    if ($ircdata->type & (SMARTIRC_TYPE_CHANNEL|
                    SMARTIRC_TYPE_ACTION|
                    SMARTIRC_TYPE_MODECHANGE|
                    SMARTIRC_TYPE_KICK|
                    SMARTIRC_TYPE_PART|
                    SMARTIRC_TYPE_JOIN)) {
                        $ircdata->channel = $lineex[2];
                    } else if ($ircdata->type & (SMARTIRC_TYPE_WHO|
                    SMARTIRC_TYPE_BANLIST|
                    SMARTIRC_TYPE_TOPIC|
                    SMARTIRC_TYPE_CHANNELMODE)) {
                        $ircdata->channel = $lineex[3];
                    } else if ($ircdata->type & SMARTIRC_TYPE_NAME) {
                        $ircdata->channel = $lineex[4];
                    }

                    if ($ircdata->channel !== null) {
                        if (substr($ircdata->channel, 0, 1) == ':') {
                            $ircdata->channel = substr($ircdata->channel, 1);
                        }
                    }

                    $this->log(SMARTIRC_DEBUG_MESSAGEPARSER, 'DEBUG_MESSAGEPARSER: ircdata nick: "'.$ircdata->nick.
                                                                '" ident: "'.$ircdata->ident.
                                                                '" host: "'.$ircdata->host.
                                                                '" type: "'.$ircdata->type.
                                                                '" from: "'.$ircdata->from.
                                                                '" channel: "'.$ircdata->channel.
                                                                '" message: "'.$ircdata->message.
                                                                '"', __FILE__, __LINE__);
                }

                // lets see if we have a messagehandler for it
                $this->_handlemessage($messagecode, $ircdata);

                if ($validmessage == true) {
                    // now the actionhandlers are comming
                    $this->_handleactionhandler($ircdata);
                }

                if (isset($ircdata)) {
                    unset($ircdata);
                }
            }
        }
    }
}