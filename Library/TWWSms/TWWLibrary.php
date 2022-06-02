<?php

namespace TWWSms;

/**
 * This API has created for easy php integration, using  an methods on TWW webservice available in https://webservices.twwwireless.com.br/reluzcap. The web service communication use protocol SOAP 1.1 
 * @author Alexandre Biondi Ermettti <abiondi@tww.com.br>
 * @copyright  TWW DO BRASIL S/A
 * @version 1.0.4
 * @since 1.0.0
 * @link www.tww.com.br
 *
 * @category API
 * @access public
 */ 
 

/** 
 * This class TWWLibrary contains the methods available for interaction
 * @category API
 * @access public
 * @package TWWLibrary
 */

class TWWLibrary  extends WSMethodString{
    
    /** $__NUMUSU__ - Login ( ID ), 10 alphanumeric characters long 
     * @access private
     * @var  String $__NUMUSU__
     */
    private $__NUMUSU__;
    
    /** $__SENHA__ - (Password ) – 18 alphanumeric characters long 
     * @access private
     * @var String $__SENHA__
    */
    private $__SENHA__;
    
    /** $__URL_WS__ - URL Web Service  
     * @access private
     * @var String $__URL_WS__ default value is https://webservices.twwwireless.com.br/reluzcap/
    */
    private $__URL_WS__ = "https://webservices.twwwireless.com.br/reluzcap/";
    
    /** $__SOAPAction__ - SOAP Action of Web Service 
     * @access private
     * @var String $__SOAPAction__ default value is https://www.twwwireless.com.br/reluzcap/wsreluzcap/
    */
    private $__SOAPAction__ = "https://www.twwwireless.com.br/reluzcap/wsreluzcap/";
    
    /** $__PORT__ - Port nunber of Service. Default 80. 
     * @access private
     * @var String $__PORT__ default is 443
    */
    private $__PORT__ = 443;
    
    /** $__DEFAULT_TIMEOUT__ - Timeout Varible
     * @access private
     * @var String $__DEFAULT_TIMEOUT__
    */
    private $__DEFAULT_TIMEOUT__ = 120;
    
    /** $__SOCKET__ - Socket Varible
     * @access private
     * @var String $__SOCKET__
    */
    private $__SOCKET__ =null;
    
    /** $__SOCKET__ - Temp Array URL
     * @access private
     * @var type String
    */
    private $__URL__;
    
     /** $__VERISON__ - Version Software
     * @access private
     * @var type String
    */
    private $__VERISON__  = "1.0.5" ;

   /**
    * __construct - receive para array paramters 
    
            NumUsu: Login (ID), 10 alphanumeric characters long.:
            Senha: Password:
            url: URL for web service TWW:
            port: TCP port for web service TWW
            SOAPAction:  URI Service description  
      
    * @access public
    * @param array $par
    * @return void
    * @example __construct(array("numusu"=>"TESTCAP","senha"=>"TESTCAP","url"=>"https://webservices.twwwireless.com.br/reluzcap/","port"=>"443","SOAPAction"=>""https://www.twwwireless.com.br/reluzcap/wsreluzcap/"))
    */
   public function __construct($par = array()) {
       
        $this->__load_def__();
        
        $par = (object)$par;
        if(isset($par->numusu)){ $this->__NUMUSU__=$par->numusu;}
        if(isset($par->senha)) { $this->__SENHA__=$par->senha;}
        if(isset($par->url))   { $this->__URL_WS__=$par->url;}
        if(isset($par->port))  { $this->__PORT__=$par->port;}
        if(isset($par->timeout))  { $this->__DEFAULT_TIMEOUT__=$par->timeout;}
        if(isset($par->SOAPAction)){ $this->__SOAPAction__=$par->SOAPAction;}
        
        
        $this->__URL__  = parse_url($this->__URL_WS__);
    }
    
   /**
    * AlteraSenha - Changes a user password. The password maximum lenght is 18 characters. Returns a boolean that indicates transaction success.
      |PARAMTERS 
      |___ SenhaNova - New Password
    * 
    * @access public
    * @param String $NovaSenha  New Password
    * @return boolean
    */
   public function AlteraSenha($NovaSenha){
       
       $XML = sprintf(parent::get_XML_ALTERA_SENHA(),$this->__NUMUSU__,  $this->__SENHA__,$NovaSenha);
       $resp = $this->SendPost("AlteraSenha", $XML);
       return  (isset($resp->AlteraSenhaResponse->AlteraSenhaResult) ? $resp->AlteraSenhaResponse->AlteraSenhaResult: $resp);
       
   }
       
   /**
   * BuscaSMS - Returns a Object named OutDataSet that contains a table named BuscaSMS with the messages transmitted within a maximum period of 4 days and with a maximum of 4000 SMSs . Returns Nothing in case of error. If your traffic exceeds 4000 SMSs in the period, we suggest you use the StatusSMSNaoLido function described later.
      |PARAMTERS 
      |___ DataIni - ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
      |___ DataFim - ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
    * 
    * @deprecated since version 1.0.1
    * @access public
    * @param Datetime $DataIni ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
    * @param Datetime $DataFim ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
    * @return Array Object
    */
   public function BuscaSMS($DataIni,$DataFim){
       
       trigger_error("Deprecated function called. Please use method StatusSMSNaoLido", E_USER_NOTICE);
       $XML = sprintf(parent::get_XML_BUSCA_SMS(),$this->__NUMUSU__,  $this->__SENHA__,$DataIni,$DataFim);
       $resp = $this->SendPost("BuscaSMS", $XML);
       return  (isset($resp->BuscaSMSResponse->BuscaSMSResult->diffgram->OutDataSet->BuscaSMS) ? $resp->BuscaSMSResponse->BuscaSMSResult->diffgram->OutDataSet->BuscaSMS : array($resp));
       
   }
        
   /** BuscaSMSAgenda - Returns a Object named OutDataSet that contains one scheduled message for the SeuNum informed. 
   |PARAMTERS 
   |___ SeuNum - This field is a number or alphanumeric character chain with 20 characters that is generated by the user for future queries. It´s not necessary to be ordered and could be repeated.    
   * 
   * @access public
   * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user for future queries. It´s not necessary to be ordered and could be repeated.    
   * @return Array Object 
   */
   public function BuscaSMSAgenda($SeuNum){
       
       $XML = sprintf(parent::get_XML_BUSCA_SMS_AGENDA(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum);
       $resp = $this->SendPost("BuscaSMSAgenda", $XML);
       return (isset($resp->BuscaSMSAgendaResponse->BuscaSMSAgendaResult->diffgram->OutDataSet->BuscaSMSAgenda) ? $resp->BuscaSMSAgendaResponse->BuscaSMSAgendaResult->diffgram->OutDataSet->BuscaSMSAgenda : array());
       
   }
        
   /** BuscaSMSAgendaDataset - Returns a Object named OutDataSet that contains a table called BuscaSMSAgenda with the scheduled messages. Returns Nothing in case of errors.
   * 
   * @access public
   * @param Array $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user for future queries. It´s not necessary to be ordered and could be repeated.    
   * @return Array Object 
   */
   public function BuscaSMSAgendaDataSet($SeuNum = array()){
       
       $i=1;$XML_SUENUM="";
       foreach($SeuNum as $s){
           $XML_SUENUM .= "<BuscaSMSAgenda diffgr:id=\"BuscaSMSAgenda$i\" msdata:rowOrder=\"".($i-1)."\"><seunum><![CDATA[$s]]></seunum></BuscaSMSAgenda>\r\n";          
           $i++;
       }
       $XML = sprintf(parent::get_XML_BUSCA_SMS_AGENDA_DATASET(), $this->__NUMUSU__,  $this->__SENHA__,$XML_SUENUM);
       $resp = $this->SendPost("BuscaSMSAgendaDataSet", $XML);
       return (isset($resp->BuscaSMSAgendaDataSetResponse->BuscaSMSAgendaDataSetResult->diffgram->OutDataSet->BuscaSMSAgenda) ? $resp->BuscaSMSAgendaDataSetResponse->BuscaSMSAgendaDataSetResult->diffgram->OutDataSet->BuscaSMSAgenda : array());
       
   }
   
   /**
   * BuscaSMSMO - Returns a Object named OutDataSet that contains a table named BuscaSMSMO with the messages received within a maximum period of 4 days and with a maximum of 4000 SMSs . Returns Nothing in case of error. If your traffic exceeds 4000 SMSs in the period, we suggest you use the StatusSMSNaoLido function described later.
      |PARAMTERS 
      |___ DataIni - ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
      |___ DataFim - ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
    * 
    * @deprecated since version 1.0.1
    * @access public
    * @param Datetime $DataIni ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
    * @param Datetime $DataFim ANSI DATETIME format: “YYYY-MM-DD HH:MM:SS”.
    * @return Array Object
    */
   public function BuscaSMSMO($DataIni,$DataFim){
       
       trigger_error("Deprecated function called. Please use method BuscaSMSMONaoLido", E_USER_NOTICE);
       $XML = sprintf(parent::get_XML_BUSCA_SMSMO(),  $this->__NUMUSU__,  $this->__SENHA__,$DataIni,$DataFim);
       $resp = $this->SendPost("BuscaSMSMO", $XML);
       return (isset($resp->BuscaSMSMOResponse->BuscaSMSMOResult->diffgram->OutDataSet->BuscaSMS)?  $resp->BuscaSMSMOResponse->BuscaSMSMOResult->diffgram->OutDataSet->BuscaSMS : array());
       
   }
  
   /** BuscaSMSMONaoLido - Returns a Object named OutDataSet that contains a table named SMSMO with a maximum of 400 rows, with the SMS MO still not read by this function, received in the last 4 days in response to SMSs MT sent before, and flag these MOs as read. If there are 400 rows in the table it's possible there are more unread MOs and these ones should be read using subsequent function calls. Retuns Nothing in case of error.
    * @access public
    * @return Array Object
    */
   public function BuscaSMSMONaoLido(){
       
       $XML = sprintf(parent::get_XML_BUSCA_SMSMO_NAO_LIDO(),$this->__NUMUSU__,  $this->__SENHA__);
       $resp = $this->SendPost("BuscaSMSMONaoLido", $XML);
       return ( isset($resp->BuscaSMSMONaoLidoResponse->BuscaSMSMONaoLidoResult->diffgram->OutDataSet->SMSMO) ? $resp->BuscaSMSMONaoLidoResponse->BuscaSMSMONaoLidoResult->diffgram->OutDataSet->SMSMO : array($resp));
       
   }
  
   /** DelSMSAgenda - Deletes a sheduled message. Returns OK or NOK. 
       |PARAMTERS 
       |___ Agendamento - DATETIME type, ANSI formatted: "YYYY-MM-DD HH:MM:SS"
       |___ SeuNum - This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
   * 
   * @access public
   * @param Datetime $Agendamento type, ANSI formatted: "YYYY-MM-DD HH:MM:SS"
   * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated. 
   * @return String 
   */
   public function DelSMSAgenda($Agendamento,$SeuNum){
       
       $Agendamento = str_replace(" ", "T", trim($Agendamento));
       $XML = sprintf(parent::get_XML_DEL_SMS_AGENDA(),  $this->__NUMUSU__,  $this->__SENHA__,$Agendamento,$SeuNum);
       $resp = $this->SendPost("DelSMSAgenda", $XML);
       return (isset($resp->DelSMSAgendaResponse->DelSMSAgendaResult) ?  $resp->DelSMSAgendaResponse->DelSMSAgendaResult : $resp);
   }
   
  /** EnviaSMS - Sends unique SMSs.
      |PARAMTERS 
      |___ Celular - (55DDNNNNNNNN) -Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
      |___ Mensagem - ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
      |___ SeuNum - This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.

            Returns a string with the following values: 

            OK - Message accepted for transmission 
            NOK - Message not accepted for transmission 
            Erro - Error 
            NA - (not avaiable) = System unavaiable
  * 
  * @param String $Celular Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
  * @param String $Mensagem ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
  * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
  * @access public 
  * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
  */
   public function EnviaSMS($Celular,$Mensagem,$SeuNum=""){
       
       $XML = sprintf(parent::get_XML_ENVIA_SMS(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum,$Celular,$Mensagem);
       
       $resp = $this->SendPost("EnviaSMS", $XML);
       
       return (isset($resp->EnviaSMSResponse->EnviaSMSResult) ? $resp->EnviaSMSResponse->EnviaSMSResult : $resp);
       
   }
   
   /** EnviaSMS2SN - Sends unique SMSs using 2 reference numeric fields ( SeuNum1 and SeuNum2 ) with the maximum of 24 digits each. 
          
            Returns a string with the following values: 

            OK - Message accepted for transmission 
            NOK - Message not accepted for transmission 
            Erro - Error 
            NA - (not avaiable) = System unavaiable
   
  * @param String $Celular Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
  * @param String $Mensagem ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
  * @param String $SeuNum1 This field is a number with the maximum of 24 digits that is generated by the user.
  * @param String $SeuNum2 This field is a number with the maximum of 24 digits that is generated by the user.
  * @param String $Agendamento ( Scheduling ) – DATETIME ANSI format: “YYYY-MM-DD HH:MM:SS”.
  * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
    */
   public function EnviaSMS2SN($Celular,$Mensagem,$SeuNum1,$SeuNum2,$Agendamento=""){
       
       if($Agendamento==""){$Agendamento= date("Y-m-d")."T".date("H:i:s");}
       $XML = sprintf(parent::get_XML_ENVIA_SMS2SN(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum1,$SeuNum2,$Celular,$Mensagem,$Agendamento);
       $resp = $this->SendPost("EnviaSMS2SN", $XML);
       return (isset($resp->EnviaSMS2SNResponse->EnviaSMS2SNResult) ? $resp->EnviaSMS2SNResponse->EnviaSMS2SNResult : $resp);
       
   }
  
  /** EnviSMSAge – Shedules a message to be sent at a later time
     
               Returns a string with the following values: 

               OK - Message accepted for transmission 
               NOK - Message not accepted for transmission 
               Erro - Error 
               NA - (not avaiable) = System unavaiable
     * 
     * @param String $Celular Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
     * @param String $Mensagem ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
     * @param String $Agendamento ( Scheduling ) – DATETIME ANSI format: “YYYY-MM-DD HH:MM:SS”.
     * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
     * @access public 
     * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 

    */
   public function EnviaSMSAge($Celular,$Mensagem,$Agendamento,$SeuNum=""){
        $Agendamento = str_replace(" ", "T", trim($Agendamento));
        $XML = sprintf(parent::get_XML_ENVIA_SMS_AGE(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum, $Celular,$Mensagem,$Agendamento);
        $resp = $this->SendPost("EnviaSMSAge", $XML);
        return (isset($resp->EnviaSMSAgeResponse->EnviaSMSAgeResult) ? $resp->EnviaSMSAgeResponse->EnviaSMSAgeResult : $resp); 
       
   }
   
   /** EnviaSMSAgeQuebra – Schedules a message to be sent at a later time. If the message is more than 140 characters long, it will be split in a set of 140 or less characters messages with "..." in the beginning and the end of the subsequent messages. Whenever possible the splits will occur on blank spaces:
       
               Returns a string with the following values: 

               OK - Message accepted for transmission 
               NOK - Message not accepted for transmission 
               Erro - Error 
               NA - (not avaiable) = System unavaiable 

    * @param String $Celular (55DDNNNNNNNN) -Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
    * @param String $Mensagem String ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
    * @param String $Agendamento ( Scheduling ) – DATETIME ANSI format: “YYYY-MM-DD HH:MM:SS”.
    * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
    * @access public 
    * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
    * 
    */
   public function EnviaSMSAgeQuebra($Celular,$Mensagem,$Agendamento,$SeuNum=""){
        $Agendamento = str_replace(" ", "T", trim($Agendamento));
        $XML = sprintf(parent::get_XML_ENVIA_SMS_AGE_QUEBRA(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum,$Celular,$Mensagem,$Agendamento);       
        $resp = $this->SendPost("EnviaSMSAgeQuebra", $XML);
        return  (isset($resp->EnviaSMSAgeQuebraResponse->EnviaSMSAgeQuebraResult) ? $resp->EnviaSMSAgeQuebraResponse->EnviaSMSAgeQuebraResult : $resp); 
       
   }

   /** EnviaSMSAlt : Deprecated. Please use EnviaSMS.
    @deprecated since version 1.0.1
    * @param String $user ( Login ( ID ), 10 alphanumeric characters long.
    * @param String $pwd (Password ) – 18 alphanumeric characters long.
    * @param String $phone (55DDNNNNNNNN) -Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
    * @param String $msgtext String ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
    * @param String $msgid This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
    * @access public 
    * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable  
    * 
   */
   public function EnviaSMSAlt($user,$pwd,$msgid,$phone,$msgtext){
       
       trigger_error("Deprecated function called. Please use method EnviaSMS", E_USER_NOTICE);
       $XML = sprintf(parent::get_XML_ENVIA_SMS_ALT(),$user,$pwd,$msgid,$phone,$msgtext);
       $resp = $this->SendPost("EnviaSMSAlt", $XML);
       return  (isset($resp->EnviaSMSAltResponse->EnviaSMSAltResult) ? $resp->EnviaSMSAltResponse->EnviaSMSAltResult : $resp); 
   
   }

  /**  EnviaSMSConcatenadoComAcento - Send a text message concatenated with accent to a cell. The Serie field must contain a number between 0 and 255 and must be unique for each concatenated SMS sent, and 1 each plus shipping, and when it reaches 255, start with 0 (zero) again. If this message is longer than 70 characters, it is split into multiple messages up to 140 characters and sent so as to reach concatenated into a single message, the target cell, since the concatenation operator support. If you do not support the operator, the message will be sent separately with separating + posts. Maximum message size = 4096 characters. Returns OK n (n is the number of SMS sent by the operation), NOK (invalid username or password, or greater than 2048 character message), error or NA (not available).
  * @param String $Celular Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
  * @param String $Mensagem ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
  * @param String $Serie The Serie field must contain a number between 0 and 255 and must be unique for each concatenated SMS sent, and 1 each plus shipping, and when it reaches 255, start with 0 (zero) again
  * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
  * @access public 
  * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
  * 
  */
   public function EnviaSMSConcatenadoComAcento($Celular,$Mensagem,$Serie,$SeuNum=""){
       
       $XML = sprintf(parent::get_XML_ENVIA_SMS_CONCATENADO_COM_ACENTO(),$this->__NUMUSU__,  $this->__SENHA__,  $SeuNum,$Serie,$Celular,$Mensagem);
       $resp = $this->SendPost("EnviaSMSConcatenadoComAcento", $XML);
       return  (isset($resp->EnviaSMSConcatenadoComAcentoResponse->EnviaSMSConcatenadoComAcentoResult) ? $resp->EnviaSMSConcatenadoComAcentoResponse->EnviaSMSConcatenadoComAcentoResult : $resp);
       
   }
   
   /**  EnviaSMSConcatenadoSemAcento - Send a text message concatenated without accent to a cell. The Serie field must contain a number between 0 and 255 and must be unique for each concatenated SMS sent, and 1 each plus shipping, and when it reaches 255, start with 0 (zero) again. If this message is longer than 140 characters, it is split into multiple messages up to 140 characters and sent so as to reach concatenated into a single message, the target cell, since the concatenation operator support. If you do not support the operator, the message will be sent separately with separating + posts. Maximum message size = 4096 characters. Returns OK n (n is the number of SMS sent by the operation), NOK (invalid username or password, or greater than 4096 character message), error or NA (not available).
  * @param String $Celular Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
  * @param String $Mensagem ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
  * @param String $Serie The Serie field must contain a number between 0 and 255 and must be unique for each concatenated SMS sent, and 1 each plus shipping, and when it reaches 255, start with 0 (zero) again
  * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
  * @access public 
  * @return String OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
  * 
  */
   public function EnviaSMSConcatenadoSemAcento($Celular,$Mensagem,$Serie,$SeuNum=""){
       
       $XML = sprintf(parent::get_XML_ENVIA_SMS_CONCATENADO_SEM_ACENTO(),$this->__NUMUSU__,  $this->__SENHA__,  $SeuNum,$Serie,$Celular,$Mensagem);
       $resp = $this->SendPost("EnviaSMSConcatenadoSemAcento", $XML);
       return  (isset($resp->EnviaSMSConcatenadoSemAcentoResponse->EnviaSMSConcatenadoSemAcentoResult) ? $resp->EnviaSMSConcatenadoSemAcentoResponse->EnviaSMSConcatenadoSemAcentoResult : $resp);
       
   }
   
   /** EnviaSMSDataSet - For batch message sending. Receives a Object SMSDataSet with the SMS messages to be sent
    * @param \SMSDataSet $DS Object SMSDataSet
      @return String OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
    */
   public function EnviaSMSDataSet($DS){
   
       if($DS instanceof SMSDataSet){  
           
            $XML = sprintf(parent::get_XML_ENVIA_SMS_DATASET() ,$this->__NUMUSU__,  $this->__SENHA__,  $DS->getDataSet() );
             $resp= $this->SendPost("EnviaSMSDataSet", $XML);
            return  (isset($resp->EnviaSMSDataSetResponse->EnviaSMSDataSetResult) ? $resp->EnviaSMSDataSetResponse->EnviaSMSDataSetResult : $resp);
       }else{
           
           trigger_error("Object  is a not  DataSet!",E_USER_ERROR);
           
           return "NOK";
       }
   }
   
  /** EnviaSMSOTA8Bit - Special purpose method for sending binary SMS, with DCS=F5. ( For more information, please contact us. )   
  * 
  * @param String $Celular 
  * @param String $Header
  * @param String $Data 
  * @param String $SeuNum
  * @access public 
  * @return string 
  */
   public function EnviaSMSOTA8Bit($Celular,$Header,$Data,$SeuNum=""){
        
       $XML = sprintf(parent::get_XML_ENVIA_SMSOTA_8BIT(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum,$Celular,$Header,$Data);
       $resp = $this->SendPost("EnviaSMSOTA8Bit", $XML);
       return  (isset($resp->EnviaSMSOTA8BitResponse->EnviaSMSOTA8BitResult) ? $resp->EnviaSMSOTA8BitResponse->EnviaSMSOTA8BitResult : $resp);
       
   }

   /**  EnviaSMSQuebra - Sends a single SMS immediately. If the message has more than 140 characters, it will be split in a set of 140 characters messages with "..." in the beginning and the end of the subsequent messages. Whenever possible the split will occur at a blank space
        |PARAMTERS 
          |___ Celular - (55DDNNNNNNNN) -Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
          |___ Mensagem - ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
          |___ SeuNum - This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.

            Returns a string with the following values: 

            OK - Message accepted for transmission 
            NOK - Message not accepted for transmission 
            Erro - Error 
            NA - (not avaiable) = System unavaiable
  * 
  * @param String $Celular Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
  * @param String $Mensagem ASCII Text (140 char. Maximum size). ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed). 
  * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
  * @access public 
  * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
  */
   public function EnviaSMSQuebra($Celular,$Mensagem,$SeuNum=""){
       
       $XML = sprintf(parent::get_XML_ENVIA_SMS_QUEBRA(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum,$Celular,$Mensagem);
       $resp = $this->SendPost("EnviaSMSQuebra", $XML);
       return  (isset($resp->EnviaSMSQuebraResponse->EnviaSMSQuebraResult) ? $resp->EnviaSMSQuebraResponse->EnviaSMSQuebraResult : $resp);
       
   }
   
   /**  EnviaSMSTIM – Deprecated, please use EnviaSMSXML.
    * @deprecated since version 1.0.1
    * @param String $XMLString XMLString
    * @return String 
    */
   public function EnviaSMSTIM($XMLString){
       
       trigger_error("Deprecated function called. Please use method EnviaSMSXML", E_USER_NOTICE);
       $XML = sprintf(parent::get_XML_ENVIA_SMS_TIM(),$XMLString);
       $resp = $this->SendPost("EnviaSMSTIM", $XML);
       return  (isset($resp->EnviaSMSTIMResponse->EnviaSMSTIMResult) ? $resp->EnviaSMSTIMResponse->EnviaSMSTIMResult : $resp);
       
   }

   /**  EnviaSMSXML – Receives a XML with the SMS messages to be sent, with the following fields:
     
       Fields:
     
            Seunum - This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It's not necessary to be ordered, and could be repeated.
            Celular - (55DDNNNNNNNN) - Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
            Mensagem - (Message Text) - ASCII Text (140 char. Maximum size) . ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed).
            Agendamento - (Scheduling) - DATETIME ANSI format: "YYYY-MM-DD HH:MM:SS"
    * 
    * Returns a string with the following values: 

            OK - Message accepted for transmission 
            NOK - Message not accepted for transmission 
            Erro - Error 
            NA - (not avaiable) = System unavaiable
    *  
    * @param String $XMLString Xml Messages
    * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
    */
   public function EnviaSMSXML($XMLString){
       
       $XML = sprintf(parent::get_XML_ENVIA_SMS_XML(),  $this->__NUMUSU__,  $this->__SENHA__,$XMLString);
       $resp = $this->SendPost("EnviaSMSXML", $XML);
       return  (isset($resp->EnviaSMSXMLResponse->EnviaSMSXMLResult) ? $resp->EnviaSMSXMLResponse->EnviaSMSXMLResult : $resp);
       
   }
   
   /**  InsBL - Insert some number in the black list
    * Fields:
    * 
    *   Celular - (55DDNNNNNNNN) - Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
    * 
    * @return string 1 - Number inserted |  0 - Number was interted | -1 Error
    */
   public function InsBL($celular){
       
       $XML = sprintf(parent::get_XML_INS_BL(),  $this->__NUMUSU__,  $this->__SENHA__,$celular);
       $resp = $this->SendPost("InsBL", $XML);
       return  (isset($resp->InsBLResponse->InsBLResult) ? $resp->InsBLResponse->InsBLResult : $resp);
       
   }

   /**  ResetaMOLido - Resets the READ status of the SMS MOs from 1 day before up to now.
    * @return string OK |   NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
    */
   public function ResetaMOLido(){
       
       $XML = sprintf(parent::get_XML_RESETA_MO_LIDO(),  $this->__NUMUSU__,  $this->__SENHA__);
       $resp = $this->SendPost("ResetaMOLido", $XML);
       return  (isset($resp->ResetaMOLidoResponse->ResetaMOLidoResult) ? $resp->ResetaMOLidoResponse->ResetaMOLidoResult : $resp);
       
   }

   /**  ResetaStatusLido - Resets the READ status of the SMS  from 1 day before up to now.
    * @return string OK - Message accepted for transmission |  NOK - Message not accepted for transmission | Erro - Error | NA - (not avaiable) = System unavaiable 
    */
   public function ResetaStatusLido(){
       
       $XML = sprintf(parent::get_XML_RESETA_STATUS_SMS_NAO_LIDO(),  $this->__NUMUSU__,  $this->__SENHA__);
       $resp = $this->SendPost("ResetaStatusLido", $XML);
       return  (isset($resp->ResetaStatusLidoResponse->ResetaStatusLidoResult) ? $resp->ResetaStatusLidoResponse->ResetaStatusLidoResult : $resp);
       
   }

   /**  StatusSMS - Returns a Object named OutDataSet that contains sent message for the SeuNum informed. 
    * @param String $SeuNum This field is a number or alphanumeric character chain with 20 characters that is generated by the user for future queries. It´s not necessary to be ordered and could be repeated.    
    * @return Array Object
   */
   public function StatusSMS($SeuNum){
       
       $XML = sprintf(parent::get_XML_STATUS_SMS(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum);
       $resp = $this->SendPost("StatusSMS", $XML);
       return  (isset($resp->StatusSMSResponse->StatusSMSResult->diffgram->OutDataSet ) ? $resp->StatusSMSResponse->StatusSMSResult->diffgram->OutDataSet  : $resp);
              
   }
   
   /**  StatusSMS2SN - Returns a DataSet named OutDataSet with one message status. The search is done using the 2 SeuNum fields. Returns Nothing in case of error.
    * 
    * @param String $SeuNum1 This field is a number with the maximum of 24 digits that is generated by the user.
    * @param String $SeuNum2 This field is a number with the maximum of 24 digits that is generated by the user.
    * 
    * @return Array Object
    */
   public function StatusSMS2SN($SeuNum1,$SeuNum2){
       
       $XML = sprintf(parent::get_XML_STATUS_SMS2SN(),  $this->__NUMUSU__,  $this->__SENHA__,$SeuNum1,$SeuNum2);
       $resp = $this->SendPost("StatusSMS2SN", $XML);
       return  (isset($resp->StatusSMS2SNResponse->StatusSMS2SNResult->diffgram->OutDataSet ) ? $resp->StatusSMS2SNResponse->StatusSMS2SNResult->diffgram->OutDataSet  : $resp);
   }
    
   /**  StatusSMSDataset – ( Deprecated. Please use StatusSMSNaoLido )
    * 
    * @deprecated since version 1.0.1
    * @param array $SeuNum 
   */
   public function StatusSMSDataSet($SeuNum = array()){
        trigger_error("Deprecated function called. Please use method StatusSMSNaoLido", E_USER_NOTICE);
       $i=1;$XML_SUENUM="";
       foreach($SeuNum as $s){
           $XML_SUENUM .= "<StatusSMS diffgr:id=\"StatusSMS$i\" msdata:rowOrder=\"".($i-1)."\"><seunum><![CDATA[$s]]></seunum></StatusSMS>\r\n";          
           $i++;
       }
       $XML = sprintf(parent::get_XML_STATUS_SMS_DATASET(), $this->__NUMUSU__,  $this->__SENHA__,$XML_SUENUM);
       $resp = $this->SendPost("StatusSMSDataSet", $XML);
        return (isset($resp->StatusSMSDataSetResponse->StatusSMSDataSetResult->diffgram->OutDataSet) ? $resp->StatusSMSDataSetResponse->StatusSMSDataSetResult->diffgram->OutDataSet : array());
       
   }
  
   /**  StatusSMSNaoLido - Returns a Object named OutDataSet that contains a table named SatusSMS with the maximum of 400 rows with the SMS status for the last 4 days that have not been read yet, and flag these messages as read. It there are 400 rows in the table, there can be more unread status at the server. Please make subsequent function calls. Returns nothing in case of errors.
        @return void 
   */
   public function StatusSMSNaoLido(){
       
       $XML = sprintf(parent::get_XML_STATUS_SMS_NAO_LIDO(), $this->__NUMUSU__,  $this->__SENHA__);
       $resp = $this->SendPost("StatusSMSNaoLido", $XML);
       return  (isset($resp->StatusSMSNaoLidoResponse->StatusSMSNaoLidoResult->diffgram->OutDataSet) ? $resp->StatusSMSNaoLidoResponse->StatusSMSNaoLidoResult->diffgram->OutDataSet : $resp);
       
   }
   
   /** VerCredito - Return available credit, -1 Login invalid, -2 Not applicable 
       @return int available credit | -1 Login invalid | -2 Not applicable 
    */
   public function VerCredito(){
       
        $XML = sprintf(parent::get_XML_VER_CREDITO(),  $this->__NUMUSU__,  $this->__SENHA__);
        $resp = $this->SendPost("VerCredito", $XML);
        return  (isset($resp->VerCreditoResponse->VerCreditoResult ) ? $resp->VerCreditoResponse->VerCreditoResult  : $resp);
       
   }
   
   /**  VerValidade - Return  credit expiration date
    * @return Datetime  credit expiration date
    */
   public function VerValidade(){
        
        $XML = sprintf(parent::get_XML_VER_VALIDADE(),  $this->__NUMUSU__,  $this->__SENHA__);
        $resp = $this->SendPost("VerValidade", $XML);
        return  (isset($resp->VerValidadeResponse->VerValidadeResult ) ? $resp->VerValidadeResponse->VerValidadeResult   : $resp);
        
   }
   
   /**  VerBL - Return array with number in blacklist
    * @return Array with number in blacklist
    */
   public function VerBL(){
        
        $XML = sprintf(parent::get_XML_VER_BL(),  $this->__NUMUSU__,  $this->__SENHA__);
        $resp = $this->SendPost("VerBL", $XML);
        if (isset($resp->VerBLResponse->VerBLResult)){
            $result = array();
            
            foreach($resp->VerBLResponse->VerBLResult->diffgram->OutDataSet->blacklist as $r){
                
                array_push($result,(string)$r->celular);
            }
            
            return $result;
        } else{
            return $resp;
        }  
        
   }
     
   /** SocketCom - Socket connnection
   * @access private
   * @return void
   */
   private function SocketCom(){
        $host = $this->__URL__["host"];
        if($this->__URL__['scheme'] == 'https' || $this->__PORT__==443) { 
            $host="ssl://".$host;
        }
        
        
        if (($fp = @fsockopen($host, $this->__PORT__,$errno, $errstr,$this->__DEFAULT_TIMEOUT__)) === false){
            trigger_error("Error $errno: $errstr\tPlease check your internet connection!" , E_USER_ERROR); 
          
        }
                
        $this->__SOCKET__ =$fp;
       
   }
   
   /** getSocket - Get Socket 
   * @access private
   * @return socket connection
   */
   private function getSocket(){
       if($this->__SOCKET__==null){
           $this->SocketCom();
       }
       return $this->__SOCKET__;       
   }
   
   /** SocketClose - Close Socket 
   * @access private
   * @return void
   */
   private function SocketClose(){
        if($this->__SOCKET__==null){
            fclose($this->__SOCKET__);
        }
        $this->__SOCKET__=null;
   }

   /** SendPost - Send Action for webservice
    * @param String $Action Webservice action
    * @param String $XML XML SOAP 1.1 Request
    * @return String XML HTTP Response 
    * @access private
    */
   private function SendPost($Action, $XML){
       
         $sockt = $this->getSocket();$result="";
         
         if($sockt){
            $in = sprintf(parent::get_XML_SOCKET_HEADER__(),$this->__URL__["path"],$this->__URL__["host"],strlen($XML),$this->__SOAPAction__.$Action,$XML);
            
            fputs($sockt, $in, strlen($in));
            while ($line = fgets($sockt)){$result .= $line;}
            $this->SocketClose();
            return $this->ReturnXML($result);
         }else{
                 trigger_error("Error " , E_USER_ERROR);           
         }
   }
   
   /** ReturnXML - Convert HTTP Response in XML 
    * @param String $data HTTP Response
    * @return object Object XML
    * @access private
    */
   private function ReturnXML($data){
        
        $data = $this->x_encode($data);
        $Invaild =$this->IsVaildRequest($data);
        if($Invaild){
            return $Invaild;
        }
        
        $i = strpos($data, "?>")+2;
       if(strpos($data,"faultstring")>-1){
          
           $data= substr($data, strpos($data,"faultstring")+12,$i);
           $data =substr($data,0, strpos($data,"faultstring")-2);
           
           $data= "<erro>".$data."</erro>";
          
           return simplexml_load_string($data);
       }else{
           
            $xml_test = str_replace(array("diffgr:","msdata:"),'', substr($data, $i));
            
            $data = simplexml_load_string($xml_test);
            
            $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children();
       
            return $response;
       }
       
   }
   
   private function IsVaildRequest($data){
       $v = explode(" ", $data);
       if(count($v)>1 && $v[1]==200){return FALSE;}
       
      
       $t = (strlen($data)>95 ? 95 : strlen($data));
       $sub = substr($data, 0,$t);
       trigger_error("Error $sub" , E_USER_ERROR);           
       return "<erro>".$sub."</erro>";
   }
   
   /** x_encode - Replace special characters in XML
    * @param String $data HTTP Response
    * @return String HTTP Response
    * @access private
   */
   private function x_encode($data){
       
       $data = str_replace("&", "", $data);
       $data = str_replace("#x1", "", $data);
       $data = str_replace("#x0", "", $data);
       
       return $data;
       
   }
   
   /** __load_def__ - Load defined variables 
    * @return void void
    * @access private
    */
   private function __load_def__(){
        if(defined("__TWW_NUMUSU__"))   { $this->__NUMUSU__=__TWW_NUMUSU__;    }
        if(defined("__TWW_SENHA__"))    { $this->__SENHA__=__TWW_SENHA__;     }
        if(defined("__TWW_URL__")) { $this->__URL_WS__=__TWW_URL__; }
        if(defined("__TWW_SOAP_ACTION__")) { $this->__SOAPAction__=__TWW_SOAP_ACTION__; }
        if(defined("__TWW_PORT__")) { $this->__PORT__=__TWW_PORT__; }
        if(defined("__DEFAULT_TIMEOUT__")) { $this->__DEFAULT_TIMEOUT__=__DEFAULT_TIMEOUT__; }
        
   }
}

/**
 * This class WSMethodString contains the XML String for methods invoked.
 * @access protected
 * @package TWWLibrary
 * @example Classe WSMethodString.  
*/
class WSMethodString{
    
    /** $__SOCKET_HEADER__
     * @access private
     * @var String $__SOCKET_HEADER__
    */
    private static $__SOCKET_HEADER__ = "POST %swsreluzcap.asmx HTTP/1.1\r\nHost: %s\r\nUser-Agent: PHP-LIBRARY\r\nContent-Type: text/xml; charset=utf-8\r\nContent-Length: %d\r\nSOAPAction: \"%s\"\r\nConnection: close\r\n\r\n%s\r\n\r\n";
    
    /** $ALTERA_SENHA
     * @access private
     * @var String $ALTERA_SENHA
    */
    private static $ALTERA_SENHA = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns1=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><SOAP-ENV:Body><ns1:AlteraSenha><ns1:NumUsu><![CDATA[%s]]></ns1:NumUsu><ns1:SenhaAntiga><![CDATA[%s]]></ns1:SenhaAntiga><ns1:SenhaNova><![CDATA[%s]]></ns1:SenhaNova></ns1:AlteraSenha></SOAP-ENV:Body></SOAP-ENV:Envelope>";
    
    /** $BUSCA_SMS
     * @access private
     * @var String $BUSCA_SMS
    */
    private static $BUSCA_SMS =  "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><BuscaSMS xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><DataIni><![CDATA[%s]]></DataIni><DataFim><![CDATA[%s]]></DataFim></BuscaSMS></soap:Body></soap:Envelope>";
    
    /** $BUSCA_SMS_AGENDA
     * @access private
     * @var String $BUSCA_SMS_AGENDA
    */
    private static $BUSCA_SMS_AGENDA =  "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><BuscaSMSAgenda xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum></BuscaSMSAgenda></soap:Body></soap:Envelope>";
    
    /** $BUSCA_SMS_AGENDA_DATASET
     * @access private
     * @var String $BUSCA_SMS_AGENDA_DATASET
    */
    private static $BUSCA_SMS_AGENDA_DATASET = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ns1=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"><SOAP-ENV:Body><ns1:BuscaSMSAgendaDataSet><ns1:NumUsu><![CDATA[%s]]></ns1:NumUsu><ns1:Senha><![CDATA[%s]]></ns1:Senha><ns1:DS><xs:schema xmlns=\"\" xmlns:xs=\"http://www.w3.org/2001/XMLSchema\" xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\" id=\"OutDataSet\"><xs:element name=\"OutDataSet\" msdata:IsDataSet=\"true\" msdata:UseCurrentLocale=\"true\"><xs:complexType><xs:choice minOccurs=\"0\" maxOccurs=\"unbounded\"><xs:element name=\"BuscaSMSAgenda\"><xs:complexType><xs:sequence><xs:element name=\"seunum\" type=\"xs:string\" minOccurs=\"0\"/></xs:sequence></xs:complexType></xs:element></xs:choice></xs:complexType></xs:element></xs:schema><diffgr:diffgram xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\" xmlns:diffgr=\"urn:schemas-microsoft-com:xml-diffgram-v1\"><OutDataSet xmlns=\"\">%s </OutDataSet></diffgr:diffgram></ns1:DS></ns1:BuscaSMSAgendaDataSet></SOAP-ENV:Body></SOAP-ENV:Envelope>";
    
    /** $BUSCA_SMSMO
     * @access private
     * @var String $BUSCA_SMSMO
    */
    private static $BUSCA_SMSMO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><BuscaSMSMO xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><DataIni><![CDATA[%s]]></DataIni><DataFim><![CDATA[%s]]></DataFim></BuscaSMSMO></soap:Body></soap:Envelope>";
    
    /** $BUSCA_SMSMO_NAO_LIDO
     * @access private
     * @var String $BUSCA_SMSMO_NAO_LIDO
    */
    private static $BUSCA_SMSMO_NAO_LIDO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><BuscaSMSMONaoLido xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha></BuscaSMSMONaoLido></soap:Body></soap:Envelope>";
    
    /** $DEL_SMS_AGENDA
     * @access private
     * @var String $DEL_SMS_AGENDA
    */
    private static $DEL_SMS_AGENDA = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><DelSMSAgenda xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><Agendamento><![CDATA[%s]]></Agendamento><SeuNum><![CDATA[%s]]></SeuNum></DelSMSAgenda></soap:Body></soap:Envelope>";
   
    /** $ENVIA_SMS
     * @access private
     * @var String $ENVIA_SMS
    */
    private static $ENVIA_SMS = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMS xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum><Celular><![CDATA[%s]]></Celular><Mensagem><![CDATA[%s]]></Mensagem></EnviaSMS></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS2SN
     * @access private
     * @var String $ENVIA_SMS2SN
    */
    private static $ENVIA_SMS2SN = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMS2SN xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum1><![CDATA[%s]]></SeuNum1><SeuNum2><![CDATA[%s]]></SeuNum2><Celular><![CDATA[%s]]></Celular><Mensagem><![CDATA[%s]]></Mensagem><Agendamento><![CDATA[%s]]></Agendamento></EnviaSMS2SN></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS_AGE
     * @access private
     * @var String $ENVIA_SMS_AGE
    */
    private static $ENVIA_SMS_AGE ="<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSAge xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum><Celular><![CDATA[%s]]></Celular><Mensagem><![CDATA[%s]]></Mensagem><Agendamento><![CDATA[%s]]></Agendamento></EnviaSMSAge></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS_AGE_QUEBRA
     * @access private
     * @var String $ENVIA_SMS_AGE_QUEBRA
    */  
    private static $ENVIA_SMS_AGE_QUEBRA = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSAgeQuebra xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum><Celular><![CDATA[%s]]></Celular><Mensagem><![CDATA[%s]]></Mensagem><Agendamento><![CDATA[%s]]></Agendamento></EnviaSMSAgeQuebra></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS_ALT
     * @access private
     * @var String $ENVIA_SMS_ALT
    */
    private static $ENVIA_SMS_ALT = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSAlt xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><user>%c</user><pwd>%c</pwd><msgid>%c</msgid><phone>%c</phone><msgtext>%c</msgtext></EnviaSMSAlt></soap:Body></soap:Envelope>";
   
    /** $ENVIA_SMS_CONCATENADO_COM_ACENTO
     * @access private
     * @var String $ENVIA_SMS_CONCATENADO_COM_ACENTO
    */
    private static $ENVIA_SMS_CONCATENADO_COM_ACENTO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSConcatenadoComAcento xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu>%s</NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum><Serie><![CDATA[%s]]></Serie><Celular><![CDATA[%s]]></Celular><Mensagem><![CDATA[%s]]></Mensagem></EnviaSMSConcatenadoComAcento></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS_CONCATENADO_SEM_ACENTO
     * @access private
     * @var String $ENVIA_SMS_CONCATENADO_SEM_ACENTO
    */
    private static $ENVIA_SMS_CONCATENADO_SEM_ACENTO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSConcatenadoSemAcento xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu>%s</NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum><Serie><![CDATA[%s]]></Serie><Celular><![CDATA[%s]]></Celular><Mensagem><![CDATA[%s]]></Mensagem></EnviaSMSConcatenadoSemAcento></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS_DATASET
     * @access private
     * @var String $ENVIA_SMS_DATASET
    */
    private static $ENVIA_SMS_DATASET = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSDataSet xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu>%s</NumUsu><Senha>%s</Senha><DS>%s</DS></EnviaSMSDataSet></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMSOTA_8BIT
     * @access private
     * @var String $ENVIA_SMSOTA_8BIT
    */
    private static $ENVIA_SMSOTA_8BIT = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSOTA8Bit xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum><Celular><![CDATA[%s]]></Celular><Header><![CDATA[%s]]></Header><Data><![CDATA[%s]]></Data></EnviaSMSOTA8Bit></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS_QUEBRA
     * @access private
     * @var String $ENVIA_SMS_QUEBRA
    */
    private static $ENVIA_SMS_QUEBRA = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSQuebra xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum><Celular><![CDATA[%s]]></Celular><Mensagem><![CDATA[%s]]></Mensagem></EnviaSMSQuebra></soap:Body></soap:Envelope>";
    
    /** $ENVIA_SMS_TIM
     * @access private
     * @var String $ENVIA_SMS_TIM
    */
    private static $ENVIA_SMS_TIM = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap12:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap12=\"http://www.w3.org/2003/05/soap-envelope\"><soap12:Body><EnviaSMSTIM xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><XMLString>%s</XMLString></EnviaSMSTIM></soap12:Body></soap12:Envelope>";
    
    /** $ENVIA_SMS_XML
     * @access private
     * @var String $ENVIA_SMS_XML
    */
    private static $ENVIA_SMS_XML = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><EnviaSMSXML xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><StrXML><![CDATA[%s]]></StrXML></EnviaSMSXML></soap:Body></soap:Envelope>";
    
    /** $InsBL
     * @access private
     * @var String $ENVIA_SMS_XML
    */
    private static $INS_BL_XML = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><InsBL xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><Celular><![CDATA[%s]]></Celular></InsBL></soap:Body></soap:Envelope>";
    
    
    /** $RESETA_MO_LIDO
     * @access private
     * @var String $RESETA_MO_LIDO
    */
    private static $RESETA_MO_LIDO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><ResetaMOLido xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha></ResetaMOLido></soap:Body></soap:Envelope>";
    
    /** $RESETA_STATUS_SMS_NAO_LIDO
     * @access private
     * @var String $RESETA_STATUS_SMS_NAO_LIDO
    */
    private static $RESETA_STATUS_SMS_NAO_LIDO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><ResetaStatusLido xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha></ResetaStatusLido></soap:Body></soap:Envelope>";
    
    /** $STATUS_SMS
     * @access private
     * @var String $STATUS_SMS
    */
    private static $STATUS_SMS = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><StatusSMS xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum><![CDATA[%s]]></SeuNum></StatusSMS></soap:Body></soap:Envelope>";
    
    /** $STATUS_SMS2SN
     * @access private
     * @var String $STATUS_SMS2SN
    */
    private static $STATUS_SMS2SN = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><StatusSMS2SN xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><SeuNum1><![CDATA[%s]]></SeuNum1><SeuNum2><![CDATA[%s]]></SeuNum2></StatusSMS2SN></soap:Body></soap:Envelope>";
    
    /** $STATUS_SMS_DATASET
     * @access private
     * @var String $STATUS_SMS_DATASET
    */
    private static $STATUS_SMS_DATASET = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><StatusSMSDataSet xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha><DS><xs:schema xmlns=\"\" xmlns:xs=\"http://www.w3.org/2001/XMLSchema\" xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\" id=\"OutDataSet\"><xs:element name=\"OutDataSet\" msdata:IsDataSet=\"true\" msdata:UseCurrentLocale=\"true\"><xs:complexType><xs:choice minOccurs=\"0\" maxOccurs=\"unbounded\"><xs:element name=\"StatusSMS\"><xs:complexType><xs:sequence><xs:element name=\"seunum\" type=\"xs:string\" minOccurs=\"0\"/></xs:sequence></xs:complexType></xs:element></xs:choice></xs:complexType></xs:element></xs:schema><diffgr:diffgram xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\" xmlns:diffgr=\"urn:schemas-microsoft-com:xml-diffgram-v1\"><OutDataSet xmlns=\"\">%s</OutDataSet></diffgr:diffgram></DS></StatusSMSDataSet></soap:Body></soap:Envelope>";
    
    /** $STATUS_SMS_NAO_LIDO
     * @access private
     * @var String $STATUS_SMS_NAO_LIDO
    */
    private static $STATUS_SMS_NAO_LIDO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><StatusSMSNaoLido xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha></StatusSMSNaoLido></soap:Body></soap:Envelope>";
    
    /** $VER_CREDITO
     * @access private
     * @var String $VER_CREDITO
    */
    private static $VER_CREDITO = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><VerCredito xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha></VerCredito></soap:Body></soap:Envelope>";
    
    /** $VER_VALIDADE
     * @access private
     * @var String $VER_VALIDADE
    */
    private static $VER_VALIDADE = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><VerValidade xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha></VerValidade></soap:Body></soap:Envelope>";
    
    /** $VER_BL
     * @access private
     * @var String $VER_BL
    */
    private static $VER_BL = "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"><soap:Body><VerBL xmlns=\"https://www.twwwireless.com.br/reluzcap/wsreluzcap\"><NumUsu><![CDATA[%s]]></NumUsu><Senha><![CDATA[%s]]></Senha></VerBL></soap:Body></soap:Envelope>";
    
    
    
    
    /** get_XML_SOCKET_HEADER__()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_SOCKET_HEADER__() {
        return self::$__SOCKET_HEADER__;
    }

    /** get_XML_ALTERA_SENHA()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ALTERA_SENHA() {
        return self::$ALTERA_SENHA;
    }

    /** get_XML_BUSCA_SMS()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_BUSCA_SMS() {
        return self::$BUSCA_SMS;
    }

    /** get_XML_BUSCA_SMS_AGENDA()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_BUSCA_SMS_AGENDA() {
        return self::$BUSCA_SMS_AGENDA;
    }

    /** get_XML_BUSCA_SMS_AGENDA_DATASET()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_BUSCA_SMS_AGENDA_DATASET() {
        return self::$BUSCA_SMS_AGENDA_DATASET;
    }

    /** get_XML_BUSCA_SMSMO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_BUSCA_SMSMO() {
        return self::$BUSCA_SMSMO;
    }

    /** get_XML_BUSCA_SMSMO_NAO_LIDO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_BUSCA_SMSMO_NAO_LIDO() {
        return self::$BUSCA_SMSMO_NAO_LIDO;
    }

    /** get_XML_DEL_SMS_AGENDA()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_DEL_SMS_AGENDA() {
        return self::$DEL_SMS_AGENDA;
    }

     /** get_XML_ENVIA_SMS()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS() {
        return self::$ENVIA_SMS;
    }

     /** get_XML_ENVIA_SMS2SN()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS2SN() {
        return self::$ENVIA_SMS2SN;
    }

     /** get_XML_ENVIA_SMS_AGE()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_AGE() {
        return self::$ENVIA_SMS_AGE;
    }

     /** get_XML_ENVIA_SMS_AGE_QUEBRA()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_AGE_QUEBRA() {
        return self::$ENVIA_SMS_AGE_QUEBRA;
    }

     /** get_XML_ENVIA_SMS_ALT()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_ALT() {
        return self::$ENVIA_SMS_ALT;
    }

     /** get_XML_ENVIA_SMS_CONCATENADO_COM_ACENTO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_CONCATENADO_COM_ACENTO() {
        return self::$ENVIA_SMS_CONCATENADO_COM_ACENTO;
    }

     /** get_XML_ENVIA_SMS_CONCATENADO_SEM_ACENTO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_CONCATENADO_SEM_ACENTO() {
        return self::$ENVIA_SMS_CONCATENADO_SEM_ACENTO;
    }

     /** get_XML_ENVIA_SMS_DATASET()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_DATASET() {
        return self::$ENVIA_SMS_DATASET;
    }

     /** get_XML_ENVIA_SMSOTA_8BIT()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMSOTA_8BIT() {
        return self::$ENVIA_SMSOTA_8BIT;
    }

     /** get_XML_ENVIA_SMS_QUEBRA()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_QUEBRA() {
        return self::$ENVIA_SMS_QUEBRA;
    }

     /** get_XML_ENVIA_SMS_TIM()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_TIM() {
        return self::$ENVIA_SMS_TIM;
    }

     /** get_XML_ENVIA_SMS_XML()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_ENVIA_SMS_XML() {
        return self::$ENVIA_SMS_XML;
    }
    
    /** get_XML_INS_BL()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_INS_BL() {
        return self::$INS_BL_XML;
    }


     /** get_XML_RESETA_MO_LIDO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_RESETA_MO_LIDO() {
        return self::$RESETA_MO_LIDO;
    }

     /** get_XML_RESETA_STATUS_SMS_NAO_LIDO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_RESETA_STATUS_SMS_NAO_LIDO() {
        return self::$RESETA_STATUS_SMS_NAO_LIDO;
    }

     /** get_XML_STATUS_SMS()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_STATUS_SMS() {
        return self::$STATUS_SMS;
    }

     /** get_XML_STATUS_SMS2SN()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_STATUS_SMS2SN() {
        return self::$STATUS_SMS2SN;
    }

     /** get_XML_STATUS_SMS_DATASET()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_STATUS_SMS_DATASET() {
        return self::$STATUS_SMS_DATASET;
    }

     /** get_XML_STATUS_SMS_NAO_LIDO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_STATUS_SMS_NAO_LIDO() {
        return self::$STATUS_SMS_NAO_LIDO;
    }

     /** get_XML_VER_CREDITO()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_VER_CREDITO() {
        return self::$VER_CREDITO;
    }

     /** get_XML_VER_VALIDADE()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_VER_VALIDADE() {
        return self::$VER_VALIDADE;
    }
    
    /** get_XML_VER_BL()
     * @access protected
     * @return String XML SOAP 1.1 VALUE.
    */
    protected static function get_XML_VER_BL() {
        return self::$VER_BL;
    }
    

}

/**
 * This class SMSDataSet struct for method EnviaSMSDataSet
 * @access protected
 * @package TWWLibrary
 * @example Classe SMSDataSet.  
*/
class SMSDataSet{
    
    /** @ignore */
    private $XML_DATA_SET = "<xs:schema id=\"myDataSet\" xmlns=\"\" xmlns:xs=\"http://www.w3.org/2001/XMLSchema\" xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\"><xs:element name=\"myDataSet\" msdata:IsDataSet=\"true\" msdata:UseCurrentLocale=\"true\"><xs:complexType><xs:choice minOccurs=\"0\" maxOccurs=\"unbounded\"><xs:element name=\"enviaSMSDataset\"><xs:complexType><xs:sequence><xs:element name=\"seunum\" type=\"xs:string\" minOccurs=\"0\"/><xs:element name=\"celular\" type=\"xs:string\" minOccurs=\"0\"/><xs:element name=\"mensagem\" type=\"xs:string\" minOccurs=\"0\"/><xs:element name=\"agendamento\" type=\"xs:dateTime\" minOccurs=\"0\"/></xs:sequence></xs:complexType></xs:element></xs:choice></xs:complexType></xs:element></xs:schema><diffgr:diffgram xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\" xmlns:diffgr=\"urn:schemas-microsoft-com:xml-diffgram-v1\"><myDataSet xmlns=\"\">%s</myDataSet></diffgr:diffgram>";
    
     /** @ignore */
    private $XML_DATA_SET_ROW_TEMPLATE = "<enviaSMSDataset diffgr:id=\"enviaSMSDataset%d\" msdata:rowOrder=\"%d\"><seunum xml:space=\"preserve\"><![CDATA[%s]]></seunum><celular><![CDATA[%s]]></celular><mensagem><![CDATA[%s]]></mensagem><agendamento><![CDATA[%s]]></agendamento></enviaSMSDataset>";
    
     /** @ignore */
    private $Rows=array();
    
    /** addRow - Add SMS row  in SMSDataSet Object
     * @access public
     * @param String $seunum This field is a number or alphanumeric character chain with 20 characters that is generated by the user and, that is recorded with the message, for future queries. It´s not necessary to be ordered, and could be repeated.
     * @param String $celular (55DDNNNNNNNN) – Destination Cell Phone Number , where D = Area Code and N = Cell Phone Number
     * @param String $mensagem (Message Text) - ASCII Text (140 char. Maximum size) . ASCII betwenn ASCII-32 e ASCII-126 would be accepted (Characters out of this range will be changed).
     * @param String $agendamento (Scheduling) – DATETIME ANSI format: “YYYY-MM-DD HH:MM:SS”.
     * @return int index row
     */
    public function addRow($seunum,$celular,$mensagem,$agendamento=""){
        
        if($agendamento==""){$agendamento=date("Y-m-d\\TH:i:s");}
        $agendamento = str_replace(" ", "T", trim($agendamento));
         
        if(strlen($celular)<6 || !is_numeric($celular)){trigger_error("Invalid field  \"celular\"= ($celular)", E_USER_ERROR); return -1;}
        if(strlen($mensagem)<1){trigger_error("Invalid field \"mensagem\"", E_USER_ERROR); return -1;}
        
        $newRow = array(
                "seunum" => $seunum,
                "celular" => $celular,
                "mensagem" => $mensagem,
                "agendamento" => $agendamento
            );
        
        $i =  count($this->Rows); 
        $this->Rows[$i]=$newRow;
        
        return $i;
        
    }

    /** delRow - Delete of SMSDataSet 
     * @access public
     * @param int $index Index of Row
     * @return void void
     */
    public function delRow($index){
        unset($this->Rows[$index]);        
    }

    /** getDataSet - Return DataSet XML 
     * @access public
     * @return String DataSet XML
     */
    public function getDataSet(){
        
        $RowsString = "";
        $i=0;
        foreach($this->Rows  as $row){
            $RowsString.=sprintf($this->XML_DATA_SET_ROW_TEMPLATE,($i+1),$i,$row["seunum"],$row["celular"],$row["mensagem"],$row["agendamento"])."\r\n";
            $i++;
        }
        
        $resp =  sprintf($this->XML_DATA_SET,$RowsString);
        
        return $resp;
    }

}