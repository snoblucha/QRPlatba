<?php
class QRPlatba  {
	
	const VERSION = '1.0';
	
	private $keys = array(

	//PODPORA VSECH BANK

		'ACC' => null, // Max. 46 - znaků IBAN, BIC Identifikace protistrany !povinny
		'ALT-ACC' => null, // Max. 93 - znaků Seznam alternativnich uctu. odddeleny carkou, 
		'AM' => null, //Max. 10 znaků - Desetinné číslo Výše částky platby.
		'CC' => 'CZK', // Právě 3 znaky - Měna platby.
		'DT' => null, // Právě 8 znaků - Datum splatnosti YYYYMMDD.
		'MSG' => null, // Max. 60 znaků - Zpráva pro příjemce.
		'X-VS' => null, // Max. 10 znaků - Celé číslo - Variabilní symbol
		'X-SS' => null, // Max. 10 znaků - Celé číslo - Specifický symbol
		'X-KS' => null, // Max. 10 znaků - Celé číslo - Konstantní symbol

	// OSTATNI

		'RF' => null, // Max. 16 znaků - Identifikátor platby pro příjemce.
		'RN' => null, // Max. 35 znaků - Jméno příjemce.
		
		'PT' => null, // Právě 3 znaky - Typ platby.
		'PT' => null, // Právě 3 znaky - Typ platby.
		'CRC32' => null, // Právě 8 znaků - Kontrolní součet - HEX.
		'NT' => null, // Právě 1 znak P|E - Identifikace kanálu pro zaslání notifikace výstavci platby.
		'NTA' => null, //Max. 320 znaků - Telefonní číslo v mezinárodním nebo lokálním vyjádření nebo E-mailová adresa

	//OSTATNI lokalni

		'X-PER' => null, // Max. 2 znaky -  Celé číslo - Počet dní, po které se má provádět pokus o opětovné provedení neúspěšné platby
		'X-ID' => null, // Max. 20 znaků. -  Identifikátor platby na straně příkazce. Jedná se o interní ID, jehož použití a interpretace závisí na bance příkazce.
		'X-URL' => null, // Max. 140 znaků. -  URL, které je možno využít pro vlastní potřebu


	);
	
	public function __construct($account, $amount, $variable = null){
		$this->setAccount($account);
		$this->setAmount($amount);
		if($variable){
			$this->setVariableSym($variable);
		}
	}
	
	/**
	 * 
	 * Czech account - 
	 * @param String $account - format 235-56469/0546 or 9875667/0100
	 */
	public function setAccount($account){
		$this->keys['ACC'] = $this->accountToIban($account);
		return $this;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param float $amount - max 10 digits. 12345678.90
	 * @return QRPlatba
	 */
	public function setAmount($amount){
		$this->keys['AM'] = sprintf("%.2f",$amount);
		return $this;
	}
	
	/**
	 * 
	 * Variable symbol. 
	 * @param String $vs max 10 numbers
	 * @return QRPlatba
	 */
	public function setVariableSym($vs){
		$this->keys['X-VS'] = $vs;
		return $this;
	}
	
	/**
	 * 
	 * Const symbol. 
	 * @param String $cs max 10 numbers
	 * @return QRPlatba
	 */
	public function setConstSym($cs){
		$this->keys['X-CS'] = $cs;
		return $this;
	}
	
	/**
	 * 
	 * Specific symbol. 
	 * @param String $ss max 10 numbers
	 * @return QRPlatba
	 */
	public function setSpecificSym($ss){
		$this->keys['X-SS'] = $ss;
		return $this;
	}
	
	/**
	 * 
	 * Set message. Max 60 chars. Will be cut off!
	 * @param string $msg
	 * @return QRPlatba
	 */
	public function setMessage($msg){
		$this->keys['MSG'] = substr($msg, 0, 60);
		return $this;
	}
	
	/**
	 * 
	 * Set Due date, datum splatnosti. YYYYMMDD  - date('Ymd')
	 * @param String $date 'Ymd'
	 * @return QRPlatba
	 */
	public function setDueDate($date){
		$this->keys['DT'] = $date;
		return $this;
	}
	
	public function __toString(){
		
		$chunks = array('SPD', self::VERSION);
		foreach ($this->keys as $key=>$value) {
			if($value === null) continue;
			$chunks[] = "$key:$value";						
		}
		return implode('*', $chunks);
	}



	/**
	 * Converts czech account number to czech IBAN	 
	 * @param string $accountNumber account in format 60256-1258614/0800 or  4568779/0300
	 * @return string
	 */
	public function accountToIban($accountNumber) {

		$accountNumber = explode('/',$accountNumber);		
		$bank = $accountNumber[1];
		$pre = 0;
		$acc = 0;
		if(strpos($accountNumber[0], '-') === false ){
			$acc = $accountNumber[0];
		} else {
			list($pre, $acc) = explode('-', $accountNumber[0]);
		}
		
		$accountPart = sprintf("%06d%010d",$pre,$acc);
		$iban = 'CZ00'.$bank.$accountPart;
			
		$alfa = "A B C D E F G H I J K L M N O P Q R S T U V W X Y Z";
		$alfa = explode(" ", $alfa);
		for($i = 1; $i<27; $i++) {
			$alfa_replace[] = $i+9;
		}
		$controlegetal = str_replace($alfa, $alfa_replace, substr($iban, 4, strlen($iban)-4).substr($iban, 0, 2)."00");
		$controlegetal = 98 - (int)bcmod($controlegetal,97);
		$iban = sprintf("CZ%02d%04d%06d%010d",$controlegetal,$bank,$pre,$acc);
		return $iban;
	}



}