<?php
/**
 * Bets site Bet365.com service implementation.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Bets site Bet365.com service implementation.
 *
 * @category  ZendExt
 * @package   ZendExt_Service_Bets
 * @author    Franco Zeoli <fzeoli@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Service_Bets_Bet365 extends ZendExt_Service_Bets_Abstract
{

    const CHANNEL_SOCCER_WORLD_CUP =
        'ZendExt_Service_Bets_Bet365_Channel_SoccerWorldCup';

    protected $_availableChannels = array(self::CHANNEL_SOCCER_WORLD_CUP);
    protected $_timeOut = 10;

    /**
     * @var FBFCoach_Service_Bets_Bet365_Channel_Interface
     */
    private $_channel;

    /**
     * @var ZendExt_Service_Bets_Bet365_Channel_Parser_Interface
     */
    private $_parser;

    /**
     * Creates a new service.
     *
     * @param mixed $channel Which channel to use.
     *
     * @return ZendExt_Service_Bets_Abstract
     */
    public function __construct($channel)
    {
        parent::__construct($channel);

        $this->_parser = new ZendExt_Service_Bets_Bet365_Channel_Parser();
        $this->_channel = new $this->_channelClass();

        $this->_parser->setInputText(
            $this->_getOutput(
                $this->_getUrl(),
                $this->_getRawCookies(),
                $this->_getMethod(),
                $this->_getRawPostData()
            )
        );

        $this->_channel->setParser($this->_parser);
    }

    /**
     * Retrieves the match payback ratio.
     *
     * @param string $local   The local team code.
     * @param string $visitor The visitor team code.
     *
     * @throws ZendExt_Service_Bets_Exception
     *
     * @return array The ratios are in the keys '1', 'X', '2'.
     */
    public function getMatchPayback($local, $visitor)
    {
        return $this->_channel->getMatchPayback($local, $visitor);
    }

    /**
     * Retrieves the url to request.
     *
     * @return string
     */
    private function _getUrl()
    {
        return 'http://www.bet365.com/home/mainpage.asp?rn=358572546';
    }

    /**
     * Retrieves the cookies needed in the request.
     *
     * @return string
     */
    private function _getRawCookies()
    {
        return 'aps03=oty=2&tzi=4&cf=N&ct=10&hd=N&lng=3;';
    }

    /**
     * Retrieves the method used in the request.
     *
     * @return string
     */
    private function _getMethod()
    {
        return 'POST';
    }

    /**
     * Retrieves the post data to be sent.
     *
     * @return string
     */
    private function _getRawPostData()
    {
        return
        'GID=0&txtsd=11000%231%231&txtTKN=59C26F5E1C834398920340FA25FF0EBA00' .
        '0002&txtGTKN=&sitegroupid=&txtc1text=&txtc1id=0&txtc1idtable=0&txtc' .
        '2text=&txtc2id=0&txtc2idtable=0&txtNPID=100000&txtPPID=1010&txtCPID' .
        '=1020&txtLCP=1020&txtCurrentPageID=1020&txtPLBTID=0&txtClassID=1&tx' .
        'tNavigationPB=languageid%3D3%3Bdeviceid%3D0%3Bpageid%3D1020%3Bsitei' .
        'd%3D11000%3BSiteContentTypeID%3D1%3Boddstypeid%3D2%3Bserverid%3D0%3' .
        'Bclassificationid%3D1%3Btimezoneid%3D4%3Bdisplayoddstypeid%3D2%3Bla' .
        'nguageprefix%3Dspa&txtSiteNavigationPB=pagetemplateid%3D100000%3Bc1' .
        'text%3D%3Bc1textselect%3D%3Bc2text%3D%3Bc3text%3D%3Bchallengeid%3D0' .
        '%3Bplbtid%3D0%3Bfixtureid%3D0%3Bplayerid%3D0%3Bcompetitionid%3D0%3B' .
        'cardcouponid%3D0%3Bpopupid%3D0%3Blotterycode%3D%3Bpopupneed%3D0%3Bc' .
        '1id%3D1%3Bc1idtable%3D13%3Bc2id%3D22096305%3Bc2idtable%3D2%3Bc3id%3' .
        'D%3Bc3idtable%3D%3Bc1idselected%3D1%3Bc1idtableselected%3D13%3Bc2id' .
        'selected%3D22096305%3Bc2idtableselected%3D2%3Bc3idselected%3D%3Bc3i' .
        'dtableselected%3D%3Bc4idselected%3D0%3Bc4idtableselected%3D0%3Bsect' .
        'ionid%3D1%3Bsplashoption%3D%3BresultTime%3D00%3A00%3A00%3Bnext24hr%' .
        '3DFalse%3Bclassificationid%3D1%3Bannouncementstate%3D0%3Bdummy%3D0&' .
        'txtSiteNavigationCachePB=pagetemplateid%3D1020%3Bc1text%3D%3Bc1text' .
        'select%3D%3Bc2text%3D%3Bc3text%3D%3Bchallengeid%3D0%3Bplbtid%3D0%3B' .
        'fixtureid%3D0%3Bplayerid%3D0%3Bcompetitionid%3D0%3Bcardcouponid%3D0' .
        '%3Bpopupid%3D0%3Blotterycode%3D%3Bpopupneed%3D0%3Bc1id%3D0%3Bc1idta' .
        'ble%3D0%3Bc2id%3D0%3Bc2idtable%3D0%3Bc3id%3D0%3Bc3idtable%3D0%3Bc1i' .
        'dselected%3D0%3Bc1idtableselected%3D0%3Bc2idselected%3D0%3Bc2idtabl' .
        'eselected%3D0%3Bc3idselected%3D0%3Bc3idtableselected%3D0%3Bc4idsele' .
        'cted%3D0%3Bc4idtableselected%3D0%3Bsectionid%3D0%3Bsplashoption%3D%' .
        '3BresultTime%3D00%3A00%3A00%3Bnext24hr%3DFalse%3Bclassificationid%3' .
        'D1%3Bannouncementstate%3D0%3Bdummy%3D0&txtSNPPB=pagetemplateid%3D0%' .
        '3Bc1text%3D%3Bc1textselect%3D%3Bc2text%3D%3Bc3text%3D%3Bchallengeid' .
        '%3D0%3Bplbtid%3D0%3Bfixtureid%3D0%3Bplayerid%3D0%3Bcompetitionid%3D' .
        '0%3Bcardcouponid%3D%3Bpopupid%3D0%3Blotterycode%3D%3Bpopupneed%3D0%' .
        '3Bc1id%3D0%3Bc1idtable%3D0%3Bc2id%3D0%3Bc2idtable%3D0%3Bc3id%3D0%3B' .
        'c3idtable%3D0%3Bc1idselected%3D0%3Bc1idtableselected%3D0%3Bc2idsele' .
        'cted%3D0%3Bc2idtableselected%3D0%3Bc3idselected%3D0%3Bc3idtablesele' .
        'cted%3D0%3Bc4idselected%3D0%3Bc4idtableselected%3D0%3Bsectionid%3D0' .
        '%3Bsplashoption%3D%3BresultTime%3D00%3A00%3A00%3Bnext24hr%3DFalse%3' .
        'Bclassificationid%3D0%3Bannouncementstate%3D0%3Bdummy%3D0&StyleName' .
        '=sportsbook_vB19.css&txtLPx=spa&txtOC=&txtLBC=0&txtLFE=0&txtBST=&tx' .
        'tSSite=0&tLbft=0&txtJL=0&txtAS=0&txtScreenSize=1024+x+768&txtFlashV' .
        'ersion=10.0.45&txtFlashEnable=true&txtSiteView=0&txtSCTypeID=1&txtE' .
        'rrMsg=&tWEXV=1&txtDCur=&txtCTR=10&tcocsel=&txtIBS=0&txtTempCardID=0' .
        '&txtDOT=2&txtTOC=&navblurbOC=0&navmenuOC=0&navblurbPID=0&tLS=0&txtI' .
        'NA=0&tpdID=1&txtST=&txtAT=4BB10593&txtPHSection=&txtPHLocation=&txt' .
        'ACOW=False&txtSelectedItem=0&txtRemoveItem=0&txtDefaultChanged=0&tx' .
        'tFlashDisabled=0&txtShowLiveStreaming=0&FlashEvent=&FlashDiary=&txt' .
        'CpnSelected=&txtMURL=members.bet365.com%2Fhome%2Fmainpage.asp&txtHU' .
        'RL=help.bet365.com%2Fhome%2Fmainpage.asp&txtRURL=results.bet365.com' .
        '%2Fhome%2Fmainpage.asp&txtLGN=0&tPLang=3&tOCG=0&tAnC=0&txtFO=0&tSBP' .
        'ID=0&txtURL=www.bet365.com%2Fhome%2Fmainpage.asp&txtMSD=&txtMSA=&tT' .
        'D=&tBSFS=&tRLP=&tFLS=&txtHAB=0&txtBBB=0&txtGBB=0&closeBonusID=&txtO' .
        'CPID=-1&tIPID=1010&txtHSAB=0&txtHGAB=0&txtHBAB=0&txtHGB=0&txtHBB=0&' .
        'txtHACBB=0&txtHACGB=0&txtHASBB=0&txtHAGT=0&txtHABT=0&txtSBBB=0&txtS' .
        'BBFB=0&txtSBBBD=0&txtSBID=0&txtSBBAW=0&txtFPA=&txtFPE=&txtFPEM=&txt' .
        'FPJEM=&txtHPFV=4.39.79.40&txtPageSource=0&txtKYCPID=0&tSO=10337000&' .
        'txtUserName=&txtUserName1=Usuario&txtPassword=&txtPassword1=Contras' .
        'e%C3%B1a&gotcombos=0&cboSportC1=Acceso+r%C3%A1pido+%3E&cboSportC2=S' .
        'eleccionar+%3E';
    }

}
