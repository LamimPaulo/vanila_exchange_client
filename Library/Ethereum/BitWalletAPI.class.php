<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ethereum
 *
 * @author willianchiquetto
 * 
 * Array
(
[success] => 1
[data] => Array
(
[id] => 1586203589
[from] => 0xe577e90734b29C94e90B03fc129EF6AAFe0F2ACF
[to] => 0x283309577750cD48ebe9A4Dd287f54f08291ce6a
[contract] => ETH
[amount] => 0,0001
[createDate] => 2020-04-06T20:06:30.0678906+00:00
[transaction] => 0xd79b83260a105f19fc21f8bb665965cce9bb6729179a4c93d0e99e26725e0a1d
)
 */


class BitWalletAPI{


	//private $endpoint = "http://0:5000/api";//ETHWALLET_ENDPOINT;
    private $endpoint = "http://0.0.0.0:5000/api";//ETHWALLET_ENDPOINT;
    private $ethMaxCost = 0.0004;//ETHWALLET_ENDPOINT;
	private $main_addr = "0x094DE4A27021F3cb84966c1F62A29704a85a3C7F";//"0x2652030e21eAA3fC657cc15CABB3c75B86E66BE7";//"0x283309577750cD48ebe9A4Dd287f54f08291ce6a";
	//private $contrac2token = array('ETH'=>'ETH', '0x7a094dfd89893d204436bf331a51d80f5c48a2eb'=>'TBRL', '0x01980188b4d4C8eBEc3c4d0B581dcC7Ca4a4Ac07' => 'CBRL');
	//private $token2contract = array('ETH'=>'ETH', 'TBRL'=>'0x7a094dfd89893d204436bf331a51d80f5c48a2eb', 'CBRL'=>'0x01980188b4d4C8eBEc3c4d0B581dcC7Ca4a4Ac07');


	//public function __construct($main_addr=ETH_MAIN_ADDR)	{
		//$this->main_addr = $main_addr;
	//}

	private function request($function, $data, $method='GET'){
		$url = $this->endpoint.'/'.$function;
		if (!empty($data)) $url .= '?'.http_build_query($data);

                //if($method == 'POST') exit($url);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_TIMEOUT => 300, //if the fee is too small it can take several minutes
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => '', //the library expects empty body (dont know why!)
			CURLOPT_HTTPHEADER => array(
				'accept: application/json',
				'Content-Type: application/json',
			),
		));         
                $res = curl_exec($curl);                
		$err = curl_error($curl);
		$res_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
		if (($res_code>=300) || ($err)) {
			$result['success']=false;
			$result['data'] = json_decode($res, true);
			$result['message'] = $err;
			if (strpos($err,'timed out')) $result['message_cod']='TIMED_OUT';
			else $result['message_cod'] = 'ERROR_'.$res_code;
			return $result;
		}
		curl_close($curl);
                
		$result['success']=true;
		$result['data'] = json_decode($res, true);
		return $result;
	}
        
   public function balanceCointrade($carteira, $contrato = null) {

        if (!empty($contrato)) {
            $contrato = "?contract={$contrato}";
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->endpoint}/wallets/{$carteira}/balance{$contrato}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_POSTFIELDS => '', //the library expects empty body (dont know why!)       
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function balance($addr=null, $tokens) {
        //exit(print_r($tokens));
		if (empty($addr)) $addr = $this->main_addr;
		$res = $this->request("wallets/$addr/balances", array(), 'GET');
        //exit(print_r($res));
		$result = array();
		if ($res['success']) {
            foreach ($tokens as $key => $token){
                //echo $key;
                foreach ($res['data'] as $currency) {
                    if(strtoupper($key) == strtoupper($currency["asset"])){
                        $result[$token]['total'] = $currency["value"];
                    }
                }
            }
            //exit(print_r($result));
		} else {
			return $res;
		}
		return $result;
	}


	public function get_addresses() {
		return $this->request("wallets", array(), 'GET');
	}


	public function get_new_address() {
		$res = $this->request('wallets', array(), 'POST');
		if (strpos($res['data'],'0x')!==false) {
			$result['success']=true;
			$result['address'] = $res['data'];
		} else $result['success']=false;
		return $result;
	}


	//priority = low, average, fast, fastest
	public function gas_price($priority='average') {
		$priority = strtolower($priority);
		$res = $this->request("wallets/gasprice/$priority", array(), 'GET');
		return $res;
	}

	//priority expected: low, average, fast, fastest
	public function transfer_cost($token, $from, $to, $amount, $priority='average') {
		if (empty($from))
                    $from = $this->main_addr;
                
		$data['to']=$to;
		$data['amount']=$amount;
		$data['priority']=$priority;
                //exit(print_r($data));
		if (strtoupper($token)!='ETH') {
			//if (ctype_alpha($token)) $data['contract'] = $this->token2contract[$token];
			//else $data['contract'] = $token;
                        $data['contract'] = $token;
		}     
                
		$res = $this->request("wallets/$from/transferethcost", $data, 'GET');  
                
		if ($res['success']) return array('success'=>true, 'cost'=>$res['data']);
		else return array('success'=>false, 'message'=>$res['data']['message']);
	}
        
        public function transferCostCointrade($token, $from, $to, $amount, $priority='fast') {
            
            if (empty($from)) $from = $this->main_addr;
                
            $curl = curl_init();

            exit("{$this->endpoint}/wallets/{$this->main_addr}/transferethcost?to={$to}&amount={$amount}&contract={$token}&priority={$priority}");
            
            curl_setopt_array($curl, array(
                CURLOPT_URL => "{$this->endpoint}/wallets/{$this->main_addr}/transferethcost?to={$to}&amount={$amount}&contract={$token}&priority={$priority}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));

            //    $response = curl_exec($curl);
            //    curl_close($curl);
            //    return $response;
            
            
            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                exit(print_r($err));
                //return null;   
            } else {
                exit(print_r($response));
                //return json_decode($response);
            }
            
        }
    
    public function transferCostCointradeMove($token, $from, $to, $amount, $priority='fast') {
            
            //if (empty($from)) $from = $this->main_addr;
                
            $curl = curl_init();

            //exit("{$this->endpoint}/wallets/{$from}/transferethcost?to={$to}&amount={$amount}&contract={$token}&priority={$priority}");
            
            curl_setopt_array($curl, array(
                CURLOPT_URL => "{$this->endpoint}/wallets/{$from}/transferethcost?to={$to}&amount={$amount}&contract={$token}&priority={$priority}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));

        //    $response = curl_exec($curl);
        //    curl_close($curl);
           return $response;
            
            
            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
      
            return $response;
    }

    public function transfer_to($token, $from, $to, $amount, $nonce, $cost, $calc = true ) {
		
        if($calc) {
            if (!floatval($cost)) { //must try to calculate the cost
                $cost_calc = $this->transfer_cost($token, $from, $to, $amount, $cost);
                if ($cost_calc['cost'] > 0)
                    $cost = $cost_calc['cost'];
                else
                    return array('success' => false, 'message' => 'Error calculating cost.');
            }
        }

        if ($cost > $this->ethMaxCost) $cost = $this->ethMaxCost;
                //echo "Cost {$cost}\n";
		if (empty($from)) $from = $this->main_addr;
		$data['to']=$to;
		$data['amount'] = number_format($amount, 16, ".", "") . "";
		$data['cost'] = number_format($cost, 16, ".", "") . "";
		$data['id']=$nonce;
		if (strtoupper($token)!='ETH') {
			//if (ctype_alpha($token)) $data['contract'] = $token;//$this->token2contract[$token];
			//else $data['contract'] = $token;
                    $data['contract'] = $token;
		}
                
                //exit(print_r($data));
		$res = $this->request("wallets/$from/transfer", $data, 'POST');
                
		if ($res['success'] && (isset($res['data']['transaction']))) {
			$result = $res['data'];
			$result['success']=true;
		} else {
			$result = $res['data'];
			$result['success']=false;
		}
		return $result;
	}

	public function valid_address($address) {
		$res = $this->request("wallets/$address/valid", null, 'GET');
		return $res['data'];
	}
        
    public function moverSaldo() {
        
        $addresses = $this->get_addresses();

        //amount smaller then this will not be moved to the MAIN_ADDR
        define('IGNORE_AMOUNTS', array('ETH' => 0.1, 'CBRL' => 100, 'REALT' => 100));

        if ($addresses['success'])
            foreach ($addresses['data'] as $addr) {
                if ($addr == $this->main_addr)
                    continue;
                $balances = $wallet->balance($addr);
                print_r($balances);
                //transfer ERC20-Tokens
                foreach (ERC20_TOKENS as $curr) {
                    if (isset($balances[$curr]) && ($balances[$curr]['total'] > IGNORE_AMOUNTS[$curr])) {
                        $amount = $balances[$curr]['total'];
                        $cost = $wallet->transfer_cost($curr, $addr, $this->main_addr, $amount, 'low');
                        if ($cost['success'])
                            $cost = $cost['cost'];
                        else
                            continue; //continue = ignore and try another one
                        if ($balances['ETH']['total'] < $cost) {
                            echo "Sending ETH for Gas to $addr\n";
                            $res = $wallet->transfer_to('ETH', $this->main_addr, $addr, $cost - $balances['ETH']['total'], time(), $cost);
                            if (!$res['success']) {
                                echo 'Error transfering ETH for Gas: ' . json_encode($res);
                                exit;
                            }
                            do {
                                echo "Waiting to receive ETH for Gas...\n";
                                sleep(10);
                                $balances = $wallet->balance($addr);
                            } while ($balances['ETH']['total'] < $cost);
                        }
                        echo "Transfering $amount $curr from $addr to Main addres...\n";
                        $wallet->transfer_to($curr, $addr, $this->main_addr, $amount, time(), $cost);
                        $balances = $wallet->balance($addr); //update balances
                    }
                }

                //transfer ETH
                $balances = $wallet->balance($addr); //update total
                if ($balances['ETH']['total'] > IGNORE_AMOUNTS['ETH']) {
                    $cost = $wallet->transfer_cost('ETH', $addr, $this->main_addr, IGNORE_AMOUNTS['ETH'], 'average'); //i use the amount = IGNORE_AMOUNTS['ETH'] just to calculate cost
                    if ($cost['success']) {
                        $cost = $cost['cost'];
                        $amount = $balances['ETH']['total'] - $cost;
                        echo "Transfering $amount ETH from $addr to Main addres...\n";
                        $wallet->transfer_to('ETH', $addr, $this->main_addr, $amount, time(), $cost);
                    }
                }
            }
    }
    
    public function moverSaldoCointrade($valoresMinimos, $addr, $tokens, $tokensSaldos, $onlyEth = false) {

        
        //amount smaller then this will not be moved to the MAIN_ADDR
        define('IGNORE_AMOUNTS', $valoresMinimos);

        set_time_limit(0);

        $costBnb = 0.000110;

        $carteiras = Array();
        
        if(!empty($addr)){
            if(is_array($addr)){
                $carteiras = $addr;
            } else {
                $carteiras[] = $addr;
            }        
        } else {
            $addresses = $this->get_addresses();
            $carteiras = $addresses["data"];
        }

        //exit(print_r($tokensSaldos));


        foreach ($carteiras as $addr) {

            if (!$onlyEth) {

                $balances = $this->balance($addr, $tokensSaldos);

                //exit(print_r($balances));

                //$tokensSaldos = array_reverse($tokensSaldos);

                foreach ($tokensSaldos as $key => $curr) {

                    //exit($curr);

                    //exit(print_r($balances[$curr]));

                    if (isset($balances[$curr]) && ($balances[$curr]['total'] > 0.5) && ($curr != "BNB-BSC")) {
                        $amount = $balances[$curr]['total'];

                        $amount = number_format($amount, 18, ".", "");
                        
                        $cost = 0.0002;//$this->transferCostCointradeMove($key, $addr, $this->main_addr, $amount - 1.5, 'fast');
                        //exit($cost);

                        if (!empty($cost) && is_numeric($cost)) {
                            
                        } else {
                            $cost = $this->ethMaxCost;
                        }
            //exit($cost);


                        if ($balances['BNB-BSC']['total'] < $cost) {
                            echo "Sending ETH for Gas ({$cost}) to $addr\n";

                            $res = $this->transfer_to('BNB', $this->main_addr, $addr, $cost, time(), $costBnb);

                            if (!$res['success']) {
                                echo 'Error transfering ETH for Gas: ' . json_encode($res);
                                exit();
                            }
                            do {
                                echo "Waiting to receive BNB for Gas...\n";
                                sleep(50);
                                $balances = $this->balance($addr, $tokensSaldos);
                            } while ($balances['BNB-BSC']['total'] <= $cost);
                        }
                        echo "Transfering $amount $curr from $addr to Main addres...\n";

                        //exit($key . " -- " . $addr . " -- " . $this->main_addr. " -- " . $amount);
                        $this->transfer_to($key, $addr, $this->main_addr, $amount . "", time(), $cost);
                        $balances = $this->balance($addr, $tokensSaldos); //update balances
                    }
                }
            }
            //exit("ok");
            //exit("ok");
            //transfer ETH
            $balances = $this->balance($addr, $tokensSaldos); //update total

            //exit(print_r($balances));
           
            if ($balances['BNB-BSC']['total'] > 0.0001) {
               
                //$cost = $this->transferCostCointradeMove('BNB', $addr, $this->main_addr, number_format($balances['BNB-BSC']['total'], 8, ".", ""), 'fast'); //i use the amount = IGNORE_AMOUNTS['ETH'] just to calculate cost
                
                if ($costBnb > 0) {
                    
                    $balanceEth = number_format($balances['BNB-BSC']['total'], 8, ".", "");
                    
                    //$cost = $cost['cost'] + 0.00000000001;
                    $cost = number_format($cost, 8, ".", "");
                    
                    
                    if ($balanceEth > $cost) {
                        $amount = $balanceEth - $costBnb;
                        if ($amount > 0) {
                            $result = $this->transfer_to('BNB', $addr, $this->main_addr, $amount, time(), $costBnb, false);
                            
                            if ($result["success"]) {
                                echo "Transfering $amount ETH from $addr to Main addres...\n </br>";
                                print_r($result);
                            } else {
                                echo "Fail to transfer $amount ETH from $addr to Main addres...\n </br>";
                                print_r($result);
                            }
                        }
                    } else {
                        echo "Cost is more than amount to tranfer. </br>";
                    }
                } else {
                    echo "Fail to get cost </br>";
                }
            } else {
                $result["success"] = false;
            }
            sleep(60);
        }
        return $result["success"];
    }

}

